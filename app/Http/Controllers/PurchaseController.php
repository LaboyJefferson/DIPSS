<?php

namespace App\Http\Controllers;
use App\Models\Address;
use App\Models\Product;
use App\Models\Category;
use App\Models\Inventory;
use App\Models\PurchaseOrder;
use App\Models\Delivery;
use App\Models\OrderItems;
use App\Models\OrderStatuses;
use App\Models\OrderSupplier;
use App\Models\ProductSupplier;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Stockroom;
use App\Models\StockTransfer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Auth\Events\Validated;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

use Illuminate\Http\Request;

class PurchaseController extends Controller
{
    // A fixed buffer for reordering.
    // For example, if the reorder level is 10 and the buffer is 5, the reorder quantity becomes 15.
    protected $reorderBuffer = 10;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Check if the user is logged in
         if (!Auth::check()) {
            // If the user is not logged in, redirect to login
            return redirect('/login')->withErrors('You must be logged in.');
        }

        // 1. Get products from inventory and joins
        $productJoined = DB::table('inventory')
            ->join('product', 'inventory.product_id', '=', 'product.product_id')
            ->join('category', 'product.category_id', '=', 'category.category_id')
            ->leftJoin('product_supplier', 'product.product_id', '=', 'product_supplier.product_id')
            ->leftJoin('supplier', 'product_supplier.supplier_id', '=', 'supplier.supplier_id')
            ->select('inventory.*', 'product.*', 'category.category_name', 'supplier.supplier_id', 'supplier.company_name')
            ->get();

        // 2. Get product IDs already reordered (status: To order or Ordered)
        $reorderedProductIds = DB::table('order_items')
            ->join('purchase_order', 'order_items.purchase_order_id', '=', 'purchase_order.purchase_order_id')
            ->whereIn('purchase_order.order_status', [1, 2]) // To order or Ordered
            ->pluck('order_items.product_id')
            ->unique()
            ->toArray();

        // 3. Filter products needing reorder
        $products = collect(); // make it a Collection

        foreach ($productJoined as $product) {
            // Only consider products that currently need reorder
            if ($product->in_stock < $product->reorder_level) {
                $product->units_to_reorder = max(0, ($product->reorder_level - $product->in_stock) + $this->reorderBuffer);
        
                // Check if it has already been reordered
                $product->status = in_array($product->product_id, $reorderedProductIds)
                    ? 'Already Reordered'
                    : 'Needs Reorder';
        
                $products->push($product);
            }
        }

        $groupedProducts = $products->groupBy('supplier_id');
        $addresses = Address::all();

        // Pass the data to the view
        return view('purchase.purchase_table', [
            'groupedProducts' => $groupedProducts,
            'reorderedProductIds' => $reorderedProductIds,
            'addresses' => $addresses,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($id)
    {
        $supplier = Supplier::find($id);
        $categories = Category::all(); // Fetch all categories

        return view('purchase.create_product', compact('supplier', 'categories')); // Pass to the view
    }


    /**
     * Generate a unique 8-digit ID for the given table.
     *
     * @param  string $table
     * @return int
     */
    private function generateId($table)
    {
        // Generate a random 8-digit number
        do {
            $id = random_int(10000000, 99999999);
        } while (DB::table($table)->where("{$table}_id", $id)->exists()); // Ensure the ID is unique

        return $id;
    }

    public function getSupplierDetails(Request $request)
    {
        $supplier = Supplier::find($request->supplier_id);
        return response()->json($supplier);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $supplierId)
    {
        // Validate the incoming request data
        $validatedData = $request->validate([
            'image_url' => ['image'],
            'product_name' => ['required', 'string', 'max:30'],
            'category_dropdown' => ['required'],
            'description' => ['nullable', 'string', 'max:255'],
            'purchase_price_per_unit' => ['required', 'regex:/^\d{1,6}(\.\d{1,2})?$/'],
            'reorder_level' => ['required', 'numeric'],
        ]);

        // Handle file upload with a default image if no file is provided
        $fileNameToStore = 'noimage.jpg'; 
        if ($request->hasFile('image_url')) {
            $fileNameToStore = $this->handleFileUpload($request->file('image_url'));
        }

        // Use a transaction to ensure data integrity
        DB::transaction(function () use ($validatedData, $fileNameToStore, $supplierId ) {
            $productId = $this->generateId('product');
            $productSupplierId = $this->generateId('product_supplier');
            
            // Create the Product
            $product = Product::create([
                'image_url' => $fileNameToStore,
                'product_id' => $productId,
                'product_name' => $validatedData['product_name'],
                'description' => $validatedData['description'],
                'category_id' => $validatedData['category_dropdown'],
            ]);

            ProductSupplier::create([
                'product_supplier_id' => $productSupplierId,
                'supplier_id' => $supplierId,
                'product_id' => $product->product_id,
            ]);

            // Create the Stockroom
            $stockroom = Stockroom::create([
                'stockroom_id' => $this->generateId('stockroom'),
                'product_quantity' => 0,
                'category_id' => $validatedData['category_dropdown'],
            ]);

            // Create the StockTransfer
            StockTransfer::create([
                'stock_transfer_id' => $this->generateId('stock_transfer'),
                'transfer_quantity' => 0,
                'transfer_date' => now(),
                'product_id' => $productId,
                'user_id' => Auth::user()->user_id,
                'to_stockroom_id' => $stockroom->stockroom_id,
            ]);

            // Create the Inventory
            Inventory::create([
                'inventory_id' => $this->generateId('inventory'),
                'purchase_price_per_unit' => $validatedData['purchase_price_per_unit'],
                'in_stock' => 0,
                'reorder_level' => $validatedData['reorder_level'],
                'product_id' => $productId,
            ]);
        });

        // Redirect or return response after successful creation
        return redirect()->route('supplier_info', $supplierId)->with('success', 'Product added successfully.');
    }

    private function handleFileUpload($file)
    {
        $fileNameWithExt = $file->getClientOriginalName();
        $fileName = pathinfo($fileNameWithExt, PATHINFO_FILENAME);
        $extension = $file->getClientOriginalExtension();
        $fileNameToStore = $fileName . '_' . time() . '.' . $extension;
        $file->storeAs('public/userImage', $fileNameToStore);

        return $fileNameToStore;
    }


    public function restock(Request $request) 
    {
        // Validate incoming request data
        $validatedData = $request->validate([
            'product_id' => ['required', 'exists:product,product_id'],
            'purchase_price_per_unit' => ['required', 'numeric'],
            'sale_price_per_unit' => ['required', 'numeric'],
            'unit_of_measure' => ['required', 'string', 'max:15'],
            'previous_quantity' => ['required', 'numeric', 'min:1'],
            'quantity' => ['required', 'numeric', 'min:1'],
            'update_supplier' => ['nullable', 'boolean'],
            'supplier_id' => 'required|exists:supplier,supplier_id',
            'stockroom_id' => ['required', 'exists:stockroom,stockroom_id'],
        ]);

        // Use DB transaction to ensure data integrity
        DB::transaction(function () use ($validatedData, $request) {
            
            // Update Inventory
            $inventory = Inventory::where('product_id', $validatedData['product_id'])->firstOrFail();

            // Update inventory details
            $inventory->update([
                'purchase_price_per_unit' => $validatedData['purchase_price_per_unit'],
                'sale_price_per_unit' => $validatedData['sale_price_per_unit'],
                'unit_of_measure' => $validatedData['unit_of_measure'],
                'in_stock' => $inventory->in_stock + $validatedData['quantity'], // Increment stock
                'updated_at' => now(),
            ]);

            $userId = Auth::id();

            // Insert into stock_transfer
            DB::table('stock_transfer')->insert([
                'stock_transfer_id' => $this->generateId('stock_transfer'),
                'transfer_quantity' => $validatedData['quantity'],
                'transfer_date' => now(),
                'product_id' => $validatedData['product_id'],
                'user_id' => $userId,
                'to_stockroom_id' => $validatedData['stockroom_id'],
            ]);

            // Update Stockroom
            $stockroom = Stockroom::where('stockroom_id', $validatedData['stockroom_id'])->firstOrFail();
            // Update stockroom details
            $stockroom->update([
                'product_quantity' => $validatedData['previous_quantity'] + $validatedData['quantity'],
            ]);

            // Check if the supplier details need to be updated
            if ($validatedData['update_supplier'] == true) {
                // Validate supplier data
                $suppliervalidatedData = $request->validate([
                    'company_name' => 'required|string',
                    'contact_person' => 'required|string',
                    'mobile_number' => 'required|numeric',
                    'email' => 'required|email',
                    'address' => 'required|string',
                ]);

                // Update supplier information if requested
                Supplier::where('supplier_id', $validatedData['supplier_id'])->update($suppliervalidatedData);
            }
        });

        // Return success response
        return redirect()->route('purchase_table')->with('success', 'Restock successful.');
    }

    public function restockStoreProduct(Request $request) 
    {
        // Validate incoming request data
        $validatedData = $request->validate([
            'product_id' => ['required', 'exists:product,product_id'],
            'stockroom_id' => ['required', 'exists:stockroom,stockroom_id'],
            'transfer_quantity' => ['required', 'numeric', 'min:1'],
            'product_quantity' => ['required', 'numeric', 'min:1'],
        ]);

        // Use DB transaction to ensure data integrity
        DB::transaction(function () use ($validatedData, $request) {

            // Get the user ID (assuming the user is logged in)
            $userId = Auth::id();

            // Insert into stock_transfer
            DB::table('stock_transfer')->insert([
                'stock_transfer_id' => $this->generateId('stock_transfer'),
                'transfer_quantity' => $validatedData['transfer_quantity'],
                'transfer_date' => now(),
                'product_id' => $validatedData['product_id'],
                'user_id' => $userId,
                'from_stockroom_id' => $validatedData['stockroom_id'],
            ]);

            // Update Stockroom
            $stockroom = Stockroom::where('stockroom_id', $validatedData['stockroom_id'])->firstOrFail();

            $productQuantity = $validatedData['product_quantity'] - $validatedData['transfer_quantity'];

            // Update inventory details
            $stockroom->update([
                'product_quantity' => $productQuantity,
            ]);

            
        });

        // Return success response
        return redirect()->route('inventory_table')->with('success', 'Restock successful.');
    }


// order filter
    public function orderProductFilter(Request $request)
    {
        if (!Auth::check()) {
            return redirect('/login')->withErrors('You must be logged in.');
        }

        $currentUser = Auth::id();

        $userSQL = DB::table('user')
                ->select('user.*')
                ->where('user_roles', 'like', '%Purchase Manager%')
                ->get();

        $selectedLetters = $request->get('letters', []);

        // Build the base query
        $query = PurchaseOrder::join('order_items', 'purchase_order.purchase_order_id', '=', 'order_items.purchase_order_id')
            ->join('product', 'order_items.product_id', '=', 'product.product_id')
            ->join('order_statuses', 'purchase_order.order_status', '=', 'order_statuses.order_statuses')
            ->join('order_supplier', 'purchase_order.purchase_order_id', '=' ,'order_supplier.purchase_order_id')
            ->join('supplier', 'order_supplier.supplier_id', '=', 'supplier.supplier_id')
            ->join('user', 'user.user_id', '=', 'purchase_order.created_by')
            ->select('purchase_order.*', 'order_statuses.status_name', 'supplier.company_name', 'user.first_name', 'user.last_name')
            ->with(['order_items.product', 'suppliers', 'user', 'status'])
            ->distinct();

        // Apply filtering by selected product name starting letters
        if (!empty($selectedLetters)) {
            $query->where(function ($q) use ($selectedLetters) {
                foreach ($selectedLetters as $letter) {
                    $q->orWhere('product.product_name', 'like', $letter . '%');
                }
            });
        }

        $orders = $query->get();

        // Get unique payment methods from orders
        $paymentMethods = $orders->pluck('payment_method')->unique()->values();

        // Collect unique products from order items
        $productJoined = $orders->flatMap(function ($order) {
            return $order->order_items;
        })->unique('product_id');

        foreach ($productJoined as $item) {
            $item->descriptionArray = json_decode($item->description, true);
        }

        $categories = Category::all();
        $suppliers = Supplier::all();
        $orderStatuses = OrderStatuses::all();

        return view('purchase.order_table', [
            'userSQL' => $userSQL,
            'productJoined' => $productJoined,
            'categories' => $categories,
            'suppliers' => $suppliers,
            'orders' => $orders,
            'orderStatuses' => $orderStatuses,
            'paymentMethods' => $paymentMethods,
            'currentUser' => $currentUser
        ]);
    }

    public function orderSupplierFilter(Request $request)
    {
        if (!Auth::check()) {
            return redirect('/login')->withErrors('You must be logged in.');
        }

        $currentUser = Auth::id();

        // Fetch Inventory Manager details
        $userSQL = DB::table('user')
            ->select('user.*')
            ->where('user_roles', 'like', '%Purchase Manager%')
            ->get();

        // Get the selected category IDs
        $supplierIds = $request->get('supplier_ids', []);

        // Build the base query
        $query = PurchaseOrder::join('order_items', 'purchase_order.purchase_order_id', '=', 'order_items.purchase_order_id')
            ->join('product', 'order_items.product_id', '=', 'product.product_id')
            ->join('order_statuses', 'purchase_order.order_status', '=', 'order_statuses.order_statuses')
            ->join('order_supplier', 'purchase_order.purchase_order_id', '=' ,'order_supplier.purchase_order_id')
            ->join('supplier', 'order_supplier.supplier_id', '=', 'supplier.supplier_id')
            ->join('user', 'user.user_id', '=', 'purchase_order.created_by')
            ->select('purchase_order.*', 'order_statuses.status_name', 'supplier.company_name', 'user.first_name', 'user.last_name')
            ->with(['order_items.product', 'suppliers', 'user', 'status'])
            ->distinct();

        if (!empty($supplierIds)) {
            $query = $query->whereIn('supplier.supplier_id', $supplierIds);
        }

        $orders = $query->get();

        // Get unique payment methods from orders
        $paymentMethods = $orders->pluck('payment_method')->unique()->values();

        // Collect unique products from order items
        $productJoined = $orders->flatMap(function ($order) {
            return $order->order_items;
        })->unique('product_id');

        // Fetch all categories, suppliers, orderStatuses for filtering or display purposes
        $categories = Category::all();
        $suppliers = Supplier::all();
        $orderStatuses = OrderStatuses::all();

        return view('purchase.order_table', [
            'userSQL' => $userSQL,
            'productJoined' => $productJoined,
            'categories' => $categories,
            'suppliers' => $suppliers,
            'orders' => $orders,
            'orderStatuses' => $orderStatuses,
            'paymentMethods' => $paymentMethods,
            'currentUser' => $currentUser
        ]);
    }

    //Created By Filter
    public function createdByFilter(Request $request)
    {
        if (!Auth::check()) {
            return redirect('/login')->withErrors('You must be logged in.');
        }

        $currentUser = Auth::id();
    
        // Get selected user IDs
        $selectedUserIds = $request->get('user_ids', []);
    
        // Build query with joins and relationships
        $query = PurchaseOrder::join('order_items', 'purchase_order.purchase_order_id', '=', 'order_items.purchase_order_id')
            ->join('product', 'order_items.product_id', '=', 'product.product_id')
            ->join('order_statuses', 'purchase_order.order_status', '=', 'order_statuses.order_statuses')
            ->join('order_supplier', 'purchase_order.purchase_order_id', '=' ,'order_supplier.purchase_order_id')
            ->join('supplier', 'order_supplier.supplier_id', '=', 'supplier.supplier_id')
            ->join('user', 'user.user_id', '=', 'purchase_order.created_by')
            ->select('purchase_order.*', 'order_statuses.status_name', 'supplier.company_name', 'user.first_name', 'user.last_name')
            ->with(['order_items.product', 'suppliers', 'status', 'user'])
            ->distinct();
    
        // Filter by selected user IDs (created_by)
        if (!empty($selectedUserIds)) {
            $query->whereIn('purchase_order.created_by', $selectedUserIds);
        }
    
        // Execute the query and eager-load relationships
        $orders = $query->get();

        // Get unique payment methods from orders
        $paymentMethods = $orders->pluck('payment_method')->unique()->values();
    
        // Unique product list
        $productJoined = $orders->flatMap(function ($order) {
            return $order->order_items;
        })->unique('product_id');
    
        // Dropdown/filter data
        $userSQL = DB::table('user')
            ->select('user.*')
            ->where('user_roles', 'like', '%Purchase Manager%')
            ->get();

    
        // $userSQL = User::where('user_roles', 'like', '%Purchase Manager%')->get(); // all relevant users
        $categories = Category::all();
        $suppliers = Supplier::all();
        $orderStatuses = OrderStatuses::all();
    
        return view('purchase.order_table', [
            'userSQL' => $userSQL,
            'productJoined' => $productJoined,
            'categories' => $categories,
            'suppliers' => $suppliers,
            'orders' => $orders,
            'orderStatuses' => $orderStatuses,
            'paymentMethods' => $paymentMethods,
            'currentUser' => $currentUser
        ]);
    }
    
    public function paymentMethodFilter(Request $request)
    {
        if (!Auth::check()) {
            return redirect('/login')->withErrors('You must be logged in.');
        }

        $currentUser = Auth::id();

        // Fetch user details
        $userSQL = DB::table('user')
            ->select('user.*')
            ->where('user_roles', 'like', '%Purchase Manager%')
            ->get();

        // Build the base query
        $query = PurchaseOrder::join('order_items', 'purchase_order.purchase_order_id', '=', 'order_items.purchase_order_id')
            ->join('product', 'order_items.product_id', '=', 'product.product_id')
            ->join('order_statuses', 'purchase_order.order_status', '=', 'order_statuses.order_statuses')
            ->join('order_supplier', 'purchase_order.purchase_order_id', '=' ,'order_supplier.purchase_order_id')
            ->join('supplier', 'order_supplier.supplier_id', '=', 'supplier.supplier_id')
            ->join('user', 'user.user_id', '=', 'purchase_order.created_by')
            ->select('purchase_order.*', 'order_statuses.status_name', 'supplier.company_name', 'user.first_name', 'user.last_name')
            ->with(['order_items.product', 'suppliers', 'user', 'status'])
            ->distinct();

        $orders = $query->get();

        // Get unique payment methods from orders
        $paymentMethods = $orders->pluck('payment_method')->unique()->values();

        // Collect unique products from order items
        $productJoined = $orders->flatMap(function ($order) {
            return $order->order_items;
        })->unique('product_id');

        // Fetch all categories, suppliers, orderStatuses for filtering or display purposes
        $categories = Category::all();
        $suppliers = Supplier::all();
        $orderStatuses = OrderStatuses::all();

        return view('purchase.order_table', [
            'userSQL' => $userSQL,
            'productJoined' => $productJoined,
            'categories' => $categories,
            'suppliers' => $suppliers,
            'orders' => $orders,
            'orderStatuses' => $orderStatuses,
            'paymentMethods' => $paymentMethods,
            'currentUser' => $currentUser
        ]);
    }

    public function orderStatusFilter(Request $request)
    {
        if (!Auth::check()) {
            return redirect('/login')->withErrors('You must be logged in.');
        }

        $currentUser = Auth::id();

        // Fetch user details
        $userSQL = DB::table('user')
            ->select('user.*')
            ->where('user_roles', 'like', '%Purchase Manager%')
            ->get();

        // Build the base query
        $query = PurchaseOrder::join('order_items', 'purchase_order.purchase_order_id', '=', 'order_items.purchase_order_id')
            ->join('product', 'order_items.product_id', '=', 'product.product_id')
            ->join('order_statuses', 'purchase_order.order_status', '=', 'order_statuses.order_statuses')
            ->join('order_supplier', 'purchase_order.purchase_order_id', '=' ,'order_supplier.purchase_order_id')
            ->join('supplier', 'order_supplier.supplier_id', '=', 'supplier.supplier_id')
            ->join('user', 'user.user_id', '=', 'purchase_order.created_by')
            ->select('purchase_order.*', 'order_statuses.status_name', 'supplier.company_name', 'user.first_name', 'user.last_name')
            ->with(['order_items.product', 'suppliers', 'user', 'status'])
            ->distinct();

        if ($request->has('order_status')) {
            $selectedStatuses = $request->input('order_status');
            $query->whereIn('purchase_order.order_status', $selectedStatuses);
        }
            
        $orders = $query->get();

        // Get unique payment methods from orders
        $paymentMethods = $orders->pluck('payment_method')->unique()->values();

        // Collect unique products from order items
        $productJoined = $orders->flatMap(function ($order) {
            return $order->order_items;
        })->unique('product_id');

        // Fetch all categories, suppliers, orderStatuses for filtering or display purposes
        $categories = Category::all();
        $suppliers = Supplier::all();
        $orderStatuses = OrderStatuses::all();

        return view('purchase.order_table', [
            'userSQL' => $userSQL,
            'productJoined' => $productJoined,
            'categories' => $categories,
            'suppliers' => $suppliers,
            'orders' => $orders,
            'orderStatuses' => $orderStatuses,
            'paymentMethods' => $paymentMethods,
            'currentUser' => $currentUser
        ]);
    }

    // product filter
    public function productNameFilter(Request $request)
    {
        if (!Auth::check()) {
            return redirect('/login')->withErrors('You must be logged in.');
        }
    
        // SQL `user` to get Inventory Manager details
        $userSQL = DB::table('user')
            ->select('user.*')
            ->where('role', '=', 'Purchased Manager')
            ->get();
    
        $selectedLetters = $request->get('letters', []); // Get selected letters from the request
    
        // Build the query with a letter filter
        $inventoryQuery = DB::table('inventory')
            ->join('product', 'inventory.product_id', '=', 'product.product_id')
            ->join('stock_transfer', 'stock_transfer.product_id', '=', 'product.product_id')
            ->join('stockroom', 'stock_transfer.to_stockroom_id', '=', 'stockroom.stockroom_id')
            ->join('category', 'product.category_id', '=', 'category.category_id')
            ->leftJoin('product_supplier', 'product.product_id', '=', 'product_supplier.product_id')
            ->leftJoin('supplier', 'product_supplier.supplier_id', '=', 'supplier.supplier_id')
            ->select('inventory.*', 'product.*', 'category.*', 'supplier.*', 'stock_transfer.*', 'stockroom.*')
            ->orderBy('product.product_name', 'asc')
            ->distinct();
    
        // Apply filtering by letters if any letters are selected
        if (!empty($selectedLetters)) {
            $inventoryQuery->where(function ($query) use ($selectedLetters) {
                foreach ($selectedLetters as $letter) {
                    $query->orWhere('product.product_name', 'like', $letter . '%');
                }
            });
        }
    
        $productJoined = $inventoryQuery->get();
        $productJoined = $productJoined->unique('product_id');
    
        // Decode description for each inventory item
        foreach ($productJoined as $item) {
            $item->descriptionArray = json_decode($item->description, true);
        }

        // low stocks
        $lowStoreStockMessages = [];
        $lowStockroomStockMessages = [];
        $processedProducts = [];  // Array to track products that have been processed

        // stockroom restock
        foreach ($productJoined as $data) {
            $restockStore = $data->in_stock - $data->product_quantity;
        
            // Check if the product is low on stock for either the store or the stockroom
            if (!in_array($data->product_id, $processedProducts)) {
                if ($restockStore <= $data->reorder_level) {
                    $lowStoreStockMessages[] = "Product ID {$data->product_id} ({$data->product_name}) is low on stock. Please restock the store.";
                    $processedProducts[] = $data->product_id; // Mark as processed
                }
        
                if ($data->product_quantity <= $data->reorder_level) {
                    $lowStockroomStockMessages[] = "Product ID {$data->product_id} ({$data->product_name}) is low on stock. Please restock the stockroom.";
                    $processedProducts[] = $data->product_id; // Mark as processed
                }
            }
        }    
            
        // Pass the counts to the view
        $lowStoreStockCount = count($lowStoreStockMessages);
        $lowStockroomStockCount = count($lowStockroomStockMessages);
    
        // Fetch categories and suppliers for filtering or display purposes
        $categories = Category::all();
        $suppliers = Supplier::all();
    
        return view('purchase.purchase_table', [
            'userSQL' => $userSQL,
            'productJoined' => $productJoined,
            'categories' => $categories,
            'suppliers' => $suppliers,
            'lowStoreStockCount' => $lowStoreStockCount,
            'lowStockroomStockCount' => $lowStockroomStockCount,
        ]);
    }

    // Category filter for products under the suppliers tab: supplier_info page
    public function CategoryFilter2(Request $request, $supplierId)
    {
        if (!Auth::check()) {
            return redirect('/login')->withErrors('You must be logged in.');
        }

        // Get the selected category IDs
        $categoryIds = $request->get('category_ids', []);

        // Find the supplier
        $supplier = Supplier::findOrFail($supplierId);

        // Fetch products related to this supplier, optionally filtering by category
        $productsQuery = $supplier->products()->with('category');

        if (!empty($categoryIds)) {
            $productsQuery->whereIn('category_id', $categoryIds);
        }

        $products = $productsQuery->get();

        // Fetch all categories for filtering or display purposes
        $categories = Category::all();

        return view('purchase.supplier_info', compact(['supplier', 'products', 'categories']));
    }

    public function CategoryFilter(Request $request)
    {
        if (!Auth::check()) {
            return redirect('/login')->withErrors('You must be logged in.');
        }

        // Get the selected category IDs
        $categoryIds = $request->get('category_ids', []);

        // If no categories are selected, show all products
        $productJoined = DB::table('inventory')
            ->join('product', 'inventory.product_id', '=', 'product.product_id')
            ->join('stock_transfer', 'stock_transfer.product_id', '=', 'product.product_id')
            ->join('stockroom', 'stock_transfer.to_stockroom_id', '=', 'stockroom.stockroom_id')
            ->join('category', 'product.category_id', '=', 'category.category_id')
            ->leftJoin('product_supplier', 'product.product_id', '=', 'product_supplier.product_id')
            ->leftJoin('supplier', 'product_supplier.supplier_id', '=', 'supplier.supplier_id')
            ->select('inventory.*', 'product.*', 'category.*', 'supplier.*', 'stock_transfer.*', 'stockroom.*')
            ->orderBy('product.product_name', 'asc');

        if (!empty($categoryIds)) {
            $productJoined = $productJoined->whereIn('category.category_id', $categoryIds);
        }

        $productJoined = $productJoined->distinct()->get();

        // Filter unique products
        $productJoined = $productJoined->unique('product_id');

        // Decode the description for each inventory item
        foreach ($productJoined as $item) {
            $item->descriptionArray = json_decode($item->description, true);
        }

        // low stocks
        $lowStoreStockMessages = [];
        $lowStockroomStockMessages = [];
        $processedProducts = [];  // Array to track products that have been processed

        // stockroom restock
        foreach ($productJoined as $data) {
            $restockStore = $data->in_stock - $data->product_quantity;
        
            // Check if the product is low on stock for either the store or the stockroom
            if (!in_array($data->product_id, $processedProducts)) {
                if ($restockStore <= $data->reorder_level) {
                    $lowStoreStockMessages[] = "Product ID {$data->product_id} ({$data->product_name}) is low on stock. Please restock the store.";
                    $processedProducts[] = $data->product_id; // Mark as processed
                }
        
                if ($data->product_quantity <= $data->reorder_level) {
                    $lowStockroomStockMessages[] = "Product ID {$data->product_id} ({$data->product_name}) is low on stock. Please restock the stockroom.";
                    $processedProducts[] = $data->product_id; // Mark as processed
                }
            }
        }    
            
        // Pass the counts to the view
        $lowStoreStockCount = count($lowStoreStockMessages);
        $lowStockroomStockCount = count($lowStockroomStockMessages);

        // Fetch all for filtering or display purposes
        $categories = Category::all();
        $suppliers = Supplier::all();

        return view('purchase.purchase_table', [
            'productJoined' => $productJoined,
            'categories' => $categories,
            'suppliers' => $suppliers,
            'lowStoreStockCount' => $lowStoreStockCount,
            'lowStockroomStockCount' => $lowStockroomStockCount,
        ]);
    }

    public function supplierFilter(Request $request)
    {
        if (!Auth::check()) {
            return redirect('/login')->withErrors('You must be logged in.');
        }

        // Fetch Inventory Manager details
        $userSQL = DB::table('user')
            ->select('user.*')
            ->where('role', '=', 'Inventory Manager')
            ->get();

        // Get the selected category IDs
        $supplierIds = $request->get('supplier_ids', []);

        // If no categories are selected, show all products
        $productJoined = DB::table('inventory')
            ->join('product', 'inventory.product_id', '=', 'product.product_id')
            ->join('stock_transfer', 'stock_transfer.product_id', '=', 'product.product_id')
            ->join('stockroom', 'stock_transfer.to_stockroom_id', '=', 'stockroom.stockroom_id')
            ->join('category', 'product.category_id', '=', 'category.category_id')
            ->leftJoin('product_supplier', 'product.product_id', '=', 'product_supplier.product_id')
            ->leftJoin('supplier', 'product_supplier.supplier_id', '=', 'supplier.supplier_id')
            ->select('inventory.*', 'product.*', 'category.*', 'supplier.*', 'stock_transfer.*', 'stockroom.*')
            ->orderBy('product.product_name', 'asc');

        if (!empty($supplierIds)) {
            $productJoined = $productJoined->whereIn('supplier.supplier_id', $supplierIds);
        }

        $productJoined = $productJoined->distinct()->get();

        // Filter unique products
        $productJoined = $productJoined->unique('product_id');

        // Decode the description for each inventory item
        foreach ($productJoined as $item) {
            $item->descriptionArray = json_decode($item->description, true);
        }

        // low stocks
        $lowStoreStockMessages = [];
        $lowStockroomStockMessages = [];
        $processedProducts = [];  // Array to track products that have been processed

        // stockroom restock
        foreach ($productJoined as $data) {
            $restockStore = $data->in_stock - $data->product_quantity;

            // Check if the product is low on stock for either the store or the stockroom
            if (!in_array($data->product_id, $processedProducts)) {
                if ($restockStore <= $data->reorder_level) {
                    $lowStoreStockMessages[] = "Product ID {$data->product_id} ({$data->product_name}) is low on stock. Please restock the store.";
                    $processedProducts[] = $data->product_id; // Mark as processed
                }

                if ($data->product_quantity <= $data->reorder_level) {
                    $lowStockroomStockMessages[] = "Product ID {$data->product_id} ({$data->product_name}) is low on stock. Please restock the stockroom.";
                    $processedProducts[] = $data->product_id; // Mark as processed
                }
            }
        }    
            
        // Pass the counts to the view
        $lowStoreStockCount = count($lowStoreStockMessages);
        $lowStockroomStockCount = count($lowStockroomStockMessages);

        // Fetch all for filtering or display purposes
        $categories = Category::all();
        $suppliers = Supplier::all();

        return view('purchase.purchase_table', [
            'userSQL' => $userSQL,
            'productJoined' => $productJoined,
            'categories' => $categories,
            'suppliers' => $suppliers,
            'lowStoreStockCount' => $lowStoreStockCount,
            'lowStockroomStockCount' => $lowStockroomStockCount,
        ]);
    }
    
    // store restock filter
    public function storeRestockFilter()
    {
        // Check if the user is logged in
         if (!Auth::check()) {
            // If the user is not logged in, redirect to login
            return redirect('/login')->withErrors('You must be logged in.');
        }

        // SQL `user` to get Inventory Manager details
        $userSQL = DB::table('user')
        ->select('user.*')
        ->where('role', '=', 'Inventory Manager')
        ->get();

        // Fetch product names for filter dropdown
        $allProductNames = DB::table('product')
            ->select('product_id', 'product_name')
            ->orderBy('product_name', 'asc')
            ->get();

        $inventoryJoined = Inventory::with('product')
        ->join('product', 'inventory.product_id', '=', 'product.product_id')
        ->join('stock_transfer', 'stock_transfer.product_id', '=', 'product.product_id')
        ->join('stockroom', 'stock_transfer.to_stockroom_id', '=', 'stockroom.stockroom_id')
        ->select('inventory.*', 'product.*', 'stock_transfer.*', 'stockroom.*')
        ->where(DB::raw('inventory.in_stock - stockroom.product_quantity'), '<=', DB::raw('reorder_level'))  // For store products that need restocking
        ->get();

        // Filter duplicates based on a unique key (e.g., product_id)
        $inventoryJoined = $inventoryJoined->unique('product_id');

        // Decode the description for each inventory item
        foreach ($inventoryJoined as $item) {
            $item->descriptionArray = json_decode($item->description, true); // Decode the JSON description into an array
        }

        // low stocks
        $lowStoreStockMessages = [];
        $lowStockroomStockMessages = [];
        $processedProducts = [];  // Array to track products that have been processed

        // stockroom restock
        foreach ($inventoryJoined as $data) {
            $restockStore = $data->in_stock - $data->product_quantity;
        
            // Check if the product is low on stock for either the store or the stockroom
            if (!in_array($data->product_id, $processedProducts)) {
                if ($restockStore <= $data->reorder_level) {
                    $lowStoreStockMessages[] = "Product ID {$data->product_id} ({$data->product_name}) is low on stock. Please restock the store.";
                    $processedProducts[] = $data->product_id; // Mark as processed
                }
        
                if ($data->product_quantity <= $data->reorder_level) {
                    $lowStockroomStockMessages[] = "Product ID {$data->product_id} ({$data->product_name}) is low on stock. Please restock the stockroom.";
                    $processedProducts[] = $data->product_id; // Mark as processed
                }
            }
        }    
            
        // Pass the counts to the view
        $lowStoreStockCount = count($lowStoreStockMessages);
        $lowStockroomStockCount = count($lowStockroomStockMessages);

        // Fetch all categories for filtering or display purposes
        $categories = Category::all();

        // Fetch all categories for filtering or display purposes
        $suppliers = Supplier::all();

        // Pass the inventory managers and user role to the view
        return view('inventory.inventory_table', [
            'userSQL' => $userSQL,
            'inventoryJoined' => $inventoryJoined,
            'categories' => $categories,
            'suppliers' => $suppliers,
            'lowStoreStockCount' => $lowStoreStockCount,
            'lowStockroomStockCount' => $lowStockroomStockCount,
            'allProductNames' => $allProductNames,
        ]);
    }

    // stockroom restock filter
    public function stockroomRestockFilter()
    {
        // Check if the user is logged in
        if (!Auth::check()) {
            // If the user is not logged in, redirect to login
            return redirect('/login')->withErrors('You must be logged in.');
        }

        // SQL `user` to get Inventory Manager details
        $userSQL = DB::table('user')
        ->select('user.*')
        ->where('role', '=', 'Inventory Manager')
        ->get();

        $inventoryJoined = Inventory::with('product')
        ->join('product', 'inventory.product_id', '=', 'product.product_id')
        ->join('stock_transfer', 'stock_transfer.product_id', '=', 'product.product_id')
        ->join('stockroom', 'stock_transfer.to_stockroom_id', '=', 'stockroom.stockroom_id')
        ->select('inventory.*', 'product.*', 'stock_transfer.*', 'stockroom.*')
        ->where('stockroom.product_quantity', '<=', DB::raw('reorder_level')) // For stockroom products that need restocking
        ->get();

        // Filter duplicates based on a unique key (e.g., product_id)
        $inventoryJoined = $inventoryJoined->unique('product_id');

        // Decode the description for each inventory item
        foreach ($inventoryJoined as $item) {
            $item->descriptionArray = json_decode($item->description, true); // Decode the JSON description into an array
        }

        // low stocks
        $lowStoreStockMessages = [];
        $lowStockroomStockMessages = [];
        $processedProducts = [];  // Array to track products that have been processed

        // stockroom restock
        foreach ($inventoryJoined as $data) {
            $restockStore = $data->in_stock - $data->product_quantity;
        
            // Check if the product is low on stock for either the store or the stockroom
            if (!in_array($data->product_id, $processedProducts)) {
                if ($restockStore <= $data->reorder_level) {
                    $lowStoreStockMessages[] = "Product ID {$data->product_id} ({$data->product_name}) is low on stock. Please restock the store.";
                    $processedProducts[] = $data->product_id; // Mark as processed
                }
        
                if ($data->product_quantity <= $data->reorder_level) {
                    $lowStockroomStockMessages[] = "Product ID {$data->product_id} ({$data->product_name}) is low on stock. Please restock the stockroom.";
                    $processedProducts[] = $data->product_id; // Mark as processed
                }
            }
        }    
            
        // Pass the counts to the view
        $lowStoreStockCount = count($lowStoreStockMessages);
        $lowStockroomStockCount = count($lowStockroomStockMessages);

        // Fetch all categories for filtering or display purposes
        $categories = Category::all();

        // Fetch all categories for filtering or display purposes
        $suppliers = Supplier::all();

        // Pass the inventory managers and user role to the view
        return view('inventory.inventory_table', [
            'userSQL' => $userSQL,
            'inventoryJoined' => $inventoryJoined,
            'categories' => $categories,
            'suppliers' => $suppliers,
            'lowStoreStockCount' => $lowStoreStockCount,
            'lowStockroomStockCount' => $lowStockroomStockCount,
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($supplierId, $productId)
    {
        // Check if the user is logged in
        if (!Auth::check()) {
            // If the user is not logged in, redirect to login
            return redirect('/login')->withErrors('You must be logged in.');
        }

        $product = Product::find($productId);

        $inventory = Inventory::where('product_id', '=', $productId)->first();

        // Fetch all categories for filtering or display purposes
        $categories = Category::all();

        return view('purchase.update_product', compact('supplierId', 'product', 'inventory', 'categories'));
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $supplierId, $productId)
    {
        // Validate the incoming request data
        $validatedData = $request->validate([
            'image_url' => ['image'],
            'product_name' => ['required', 'string', 'max:30'],
            'category_dropdown' => ['required'],
            'description' => ['nullable', 'string', 'max:255'],
            'purchase_price_per_unit' => ['required', 'regex:/^\d{1,6}(\.\d{1,2})?$/'],
            'reorder_level' => ['required', 'numeric'],
        ]);

        // Find the product by its ID
        $product = Product::findOrFail($productId);
        $fileNameToStore = $product->image_url;

        // Handle file upload if a new image is provided
        if ($request->hasFile('image_url')) {
            $fileNameToStore = $this->handleFileUpload($request->file('image_url'));
        }

        // Use a transaction to ensure data integrity
        DB::transaction(function () use ($validatedData, $fileNameToStore, $product) {
            // Update the Product
            $product->update([
                'image_url' => $fileNameToStore,
                'product_name' => $validatedData['product_name'],
                'description' => $validatedData['description'],
                'category_id' => $validatedData['category_dropdown'],
            ]);

            $productJoined = DB::table('inventory')
                ->join('product', 'inventory.product_id', '=', 'product.product_id')
                ->join('stock_transfer', 'stock_transfer.product_id', '=', 'product.product_id')
                ->join('stockroom', 'stock_transfer.to_stockroom_id', '=', 'stockroom.stockroom_id')
                ->join('category', 'product.category_id', '=', 'category.category_id')
                ->leftJoin('product_supplier', 'product.product_id', '=', 'product_supplier.product_id')
                ->leftJoin('supplier', 'product_supplier.supplier_id', '=', 'supplier.supplier_id')
                ->select('inventory.*', 'product.*', 'category.*', 'supplier.*', 'stock_transfer.*', 'stockroom.*')
                ->where('product.product_id', '=',  $product->product_id)
                ->first();

            // Retrieve the stockroom using the joined result, assuming the stockroom ID is available
            $stockroom = Stockroom::where('stockroom_id', $productJoined->stockroom_id)->firstOrFail();

            $stockroom->update([
                'category_id' => $validatedData['category_dropdown'],
            ]);

            // Update the Inventory
            $inventory = Inventory::where('product_id', $product->product_id)->firstOrFail();
            $inventory->update([
                'purchase_price_per_unit' => $validatedData['purchase_price_per_unit'],
                'reorder_level' => $validatedData['reorder_level'],
            ]);
        });

        // Redirect or return response after successful update
        return redirect()->route('supplier_info', $supplierId)->with('success', 'Product updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $supplierId, $productId)
    {
        // Validate the provided password
        $validatedData = $request->validate([
            'password' => 'required|string',
        ]);

        // Check if the current password matches
        if (!Hash::check($validatedData['password'], Auth::user()->password)) {
            // If password is incorrect, redirect back with error
            return back()->with([
                'delete_error' => 'The password you entered is incorrect.',
                'error_product_id' => $productId, // Pass the product ID with an error
            ]);
        }

        // Find and delete the product
        $product = Product::find($productId);

        if ($product) {
            $product->delete();
            // Redirect with success message
            return redirect()->route('supplier_info', $supplierId)->with('success', 'Product deleted successfully.');
        }

        // If product not found
        return back()->withErrors(['error' => 'Product not found.']);
    }

    /**
     * Display a listing of the resource (Order List).
     *
     * @param  string $table
     * @return int
     */
    public function order_list()
    {
        // Check if the user is logged in
        if (!Auth::check()) {
            // If the user is not logged in, redirect to login
            return redirect('/login')->withErrors('You must be logged in.');
        }

        $currentUser = Auth::id();

        $userSQL = DB::table('user')
            ->select('user.*')
            ->where('role', '=', 'Purchased Manager')
            ->get();

        // Get all backorders
        $backorders = DB::table('purchase_order')
            ->where('type', 'Backorder')
            ->pluck('total_price', 'purchase_order_id'); // Use more precise linking if needed

        // populate table with list of purchase order table
        $orders = PurchaseOrder::with(['order_items.product', 'order_items.product.inventory'])
            ->join('order_statuses', 'purchase_order.order_status', 'order_statuses.order_statuses')
            ->join('order_supplier', 'purchase_order.purchase_order_id', '=' ,'order_supplier.purchase_order_id')
            ->join('supplier', 'order_supplier.supplier_id', '=', 'supplier.supplier_id')
            ->join('user', 'user_id', 'created_by')
            ->select(
                'purchase_order.purchase_order_id',
                'purchase_order.type',
                'purchase_order.payment_method',
                'purchase_order.billing_address',
                'purchase_order.order_status',
                'purchase_order.shipping_address',
                'purchase_order.total_price',
                'purchase_order.created_by',
                'purchase_order.created_at',
                DB::raw('GROUP_CONCAT(supplier.supplier_id SEPARATOR ", ") as supplier_id'),
                DB::raw('GROUP_CONCAT(supplier.company_name SEPARATOR ", ") as company_name'),
                DB::raw('GROUP_CONCAT(supplier.address SEPARATOR ", ") as supplier_addresses'),
                'order_statuses.status_name',
                'user.first_name', 
                'user.last_name',
                'purchase_order.created_at'
            )
            ->groupBy(
                'purchase_order.purchase_order_id',
                'purchase_order.type',
                'purchase_order.payment_method',
                'purchase_order.billing_address',
                'purchase_order.order_status',
                'purchase_order.shipping_address',
                'purchase_order.total_price',
                'purchase_order.created_by',
                'purchase_order.created_at',
                'order_statuses.status_name',
                'user.first_name',
                'user.last_name'
            )
            ->orderBy('purchase_order.created_at', 'desc')
            ->get();

        // Get unique payment methods from orders
        $paymentMethods = $orders->pluck('payment_method')->unique()->values();

        // Fetch all categories, suppliers, orderStatuses for filtering or display purposes
        $categories = Category::all();
        $suppliers = Supplier::all();
        $orderStatuses = OrderStatuses::all();

        // Loop through orders and calculate total for each order
        foreach ($orders as $order) {
            $order->allMatched = true;
            $computedTotal = 0;

            // Loop through each order item to check for backordered items (where quantity != delivered_quantity)
            foreach ($order->order_items as $item) {
                if ($item->quantity != $item->delivered_quantity) {
                    // If there's a discrepancy, calculate the backorder total for that product
                    $order->allMatched = false;

                    // Calculate the price for the backordered quantity
                    $backorderQuantity = $item->quantity - $item->delivered_quantity;
                    $computedTotal += $backorderQuantity * $item->product->inventory->purchase_price_per_unit ?? 0;
                }
            }

            // Set the computed total for the order if there are any backordered items
            if ($order->allMatched === false) {
                $order->computed_total = $computedTotal;
            }

            // Skip if the order type is already a backorder
            if ($order->type === 'Backorder') {
                $order->backorderExists = true;
                continue;
            }

            // Check if a backorder already exists for this order based on computed total
            $supplierId = explode(',', $order->supplier_id)[0];

            // Query to check for an existing backorder
            $backorderExists = DB::table('purchase_order')
                ->join('order_items', 'purchase_order.purchase_order_id', '=', 'order_items.purchase_order_id')
                ->join('inventory', 'order_items.product_id', '=', 'inventory.product_id')
                ->join('order_supplier', 'purchase_order.purchase_order_id', '=', 'order_supplier.purchase_order_id')
                ->where('type', 'Backorder')
                ->where('created_by', $order->created_by)
                ->where('order_supplier.supplier_id', $supplierId)
                ->where('purchase_order.purchase_order_id', '!=', $order->purchase_order_id)
                ->select(DB::raw('SUM(order_items.quantity * inventory.purchase_price_per_unit) as total'))
                ->groupBy('purchase_order.purchase_order_id')
                ->get()
                ->pluck('total')
                ->contains($computedTotal);

            // Set backorderExists flag based on the query result
            $order->backorderExists = $backorderExists;
        }

        return view('purchase.order_table', compact('currentUser', 'userSQL', 'orders', 'categories', 'suppliers', 'orderStatuses', 'paymentMethods')); 
    }



    public function updateStatusToOrdered(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:purchase_order,purchase_order_id',
            'status_id' => 'required|string',
        ]);
    
        $order = PurchaseOrder::findOrFail($request->order_id);
        $order->order_status = $request->status_id;
        $order->save();

        // Create new delivery to store issued_date.
        Delivery::create([
            'delivery_id' => $this->generateId('delivery'),
            'issued_date' => Carbon::now(),
            'purchase_order_id' => $request->order_id
        ]);
    
        return response()->json(['success' => true, 'message' => 'Order status updated successfully.']);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param  string $table
     * @return int
     */
    public function create_purchase_order()
    {
        $suppliers = Supplier::all();
        $products = Product::with('suppliers')
            ->join('inventory', 'product.product_id', '=', 'inventory.product_id')
            ->select('*')
            ->get();

        $addresses = Address::all();

        // Get all products with their suppliers
        $productsFilter = Product::with(['suppliers', 'inventory'])->get();

        // Get all products from a specific supplier.
        // Format mapping: supplier_id => [product1, product2, ...]
        $supplierProductMap = [];

        foreach ($productsFilter as $product) {
            foreach ($product->suppliers as $supplier) {
                $supplierProductMap[$supplier->supplier_id][] = [
                    'product_id' => $product->product_id,
                    'product_name' => $product->product_name,
                    'price' => $product->inventory->purchase_price_per_unit
                ];
            }
        }

        return view('purchase.create_purchase_order', compact('suppliers', 'products', 'addresses', 'supplierProductMap'));
    }

    public function getProductsBySupplier($supplierId)
    {
        $products = Product::select()->join('inventory', 'product.product_id', 'inventory.product_id')
            ->where('supplier_id', $supplierId)->get();

        return response()->json($products);
    }

    public function getSupplierAddress($supplierId)
    {
        $supplier = Supplier::find($supplierId);
        
        if ($supplier) {
            return response()->json([
                'address' => $supplier->address,
            ]);
        } else {
            return response()->json(['error' => 'Supplier not found'], 404);
        }
    }

    public function storeOrder(Request $request)
    {
        // Validate the incoming request data
        $validatedData = $request->validate([
            'payment_method' => ['required'],
            'order_type' => ['required'],
            // 'billing_address' => ['nullable'],
            'address_dropdown' => ['required'],
            'address' => ['nullable', 'string', 'max:50'],
            'total_price' => ['required', 'numeric'],
            'user_id' => ['required'],

            'company_name' => ['nullable'],
            'email' => ['nullable'],
            'mobile_number' => ['nullable'],
            'address_supplier' => ['nullable'],
        ]);

        // Handle address
        $address = $validatedData['address_dropdown'];
        if ($address == 'add-new') {
            $newAddressId = $this->generateId('address');

            $createdAddress = Address::create([
                'address_id' => $newAddressId,
                'address' => $validatedData['address'],
            ]);

            $address = $createdAddress->address;
        }

        // To be used to create new order and for order items
        $newOrderId = $this->generateId('purchase_order');
        $initialOrderType = 'Purchasing Order';

        // Create new order first
        PurchaseOrder::create([
            'purchase_order_id' => $newOrderId,
            'type' => $validatedData['order_type'],
            'payment_method' => $validatedData['payment_method'],
            'billing_address' => $address,
            'shipping_address' => $address,
            'total_price' => $validatedData['total_price'],
            'created_by' => $validatedData['user_id'],
            'order_status' => 1,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);

        // Create new Order_Supplier connect to the current purchase order
        $supplierIds = $request->supplier_id;
        $company_names = $request->company_name;
        $contact_persons = $request->contact_person;
        $emails = $request->email;
        $mobile_numbers = $request->mobile_number;
        $address_suppliers = $request->address_supplier;

        $addNewIndex = 0; // This tracks how many "add-new" entries we've handled

        foreach ($supplierIds as $index => $supplierId) {
            if ($supplierId === 'add-new') {
                $newSupplierId = $this->generateId('supplier');
        
                $createdSupplier = Supplier::create([
                    'supplier_id'    => $newSupplierId,
                    'company_name'   => $company_names[$addNewIndex],
                    'contact_person' => $contact_persons[$addNewIndex],
                    'mobile_number'  => $mobile_numbers[$addNewIndex],
                    'email'          => $emails[$addNewIndex],
                    'address'        => $address_suppliers[$addNewIndex],
                ]);
        
                $supplierId = $createdSupplier->supplier_id;
                $addNewIndex++; // Move to the next set of new supplier inputs
            }
        
            // Create the order_supplier record
            OrderSupplier::create([
                'order_supplier_id'  => $this->generateId('order_supplier'),
                'purchase_order_id'  => $newOrderId,
                'supplier_id'        => $supplierId
            ]);
        }

        // Create new Order Items connected to the currect purchase order       
        $productIds = $request->product_id;
        $productQuantities = $request->product_quantity;
        
        foreach ($productIds as $index => $productId) {
            // Find existing product in inventory
            $productInInventory = Inventory::where('product_id', '=', $productId)->first();

            // Determine price
            // If the product exists in inventory, will get its purchase_price_per_unit and calculate the total price for that order item
            // If not, price returns null
            $price = $productInInventory ? $productInInventory->purchase_price_per_unit * $productQuantities[$index] : null;

            // Create order item
            OrderItems::create([
                'order_items_id'      => $this->generateId('order_items'),
                'purchase_order_id'   => $newOrderId,
                'product_id'          => $productId,
                'quantity'            => $productQuantities[$index],
                'price'               => $price,
            ]);
        }

        // Create new delivery to store issued_date.
        Delivery::create([
            'delivery_id' => $this->generateId('delivery'),
            'issued_date' => Carbon::now(),
            'purchase_order_id' => $newOrderId
        ]);

        return redirect()->route('purchase_order')->with('success', 'Order added successfully.');
    }

    public function editOrder($orderId)
    {
        // Check if the user is logged in
        if (!Auth::check()) {
            return redirect('/login')->withErrors('You must be logged in.');
         }

        $suppliers = Supplier::all();
        $products = Product::select()->join('inventory', 'product.product_id', 'inventory.product_id')->get();
        $addresses = Address::all();

        $order = PurchaseOrder::find($orderId);

        // Get all the products of the specified order.
        $purchase_products = OrderItems::where('order_items.purchase_order_id', $orderId)
            ->join('product', 'order_items.product_id', 'product.product_id')
            ->get();

        // Fetching order suppliers (the linking table for purchase order and suppliers)
        $order_suppliers = OrderSupplier::where('purchase_order_id', $orderId)->get();

        $productsFilter = Product::with(['suppliers', 'inventory'])->get(); // Get all products with their suppliers

        // Get all products from a specific supplier.
        // Format mapping: supplier_id => [product1, product2, ...]
        $supplierProductMap = [];

        foreach ($productsFilter as $product) {
            foreach ($product->suppliers as $supplier) {
                $supplierProductMap[$supplier->supplier_id][] = [
                    'product_id' => $product->product_id,
                    'product_name' => $product->product_name,
                    'price' => $product->inventory->purchase_price_per_unit
                ];
            }
        }

        return view('purchase.update_purchase_order', compact('order', 'purchase_products', 'suppliers', 'products', 'addresses', 'order_suppliers', 'supplierProductMap'));
    }

    public function update_order(Request $request, $orderId)
    {
       // Validate the incoming request data
        $validatedData = $request->validate([
            'order_type' => ['required'],
            'payment_method' => ['required'],
            'user_id' => ['required'],
            'supplier_id' => ['required'],
            'address_dropdown' => ['required'],
            'billing_address' => ['required'],
            'address' => ['nullable', 'string', 'max:50'],
            'total_price' => ['required', 'numeric']
        ]);
            
        // Get order 
        $order = PurchaseOrder::find($orderId);

        $address = $validatedData['address_dropdown'];
        if ($address == 'add-new') {
            $newAddressId = $this->generateId('address');

            $createdAddress = Address::create([
                'address_id' => $newAddressId,
                'address' => $validatedData['address'],
            ]);

            $address = $createdAddress->address;
        }

        // Upadte order first
        $order->update([
            'type' => $validatedData['order_type'],
            'payment_method' => $validatedData['payment_method'],
            'billing_address' => $validatedData['billing_address'],
            'shipping_address' => $address,
            'total_price' => $validatedData['total_price'],
            'created_by' => $validatedData['user_id'],
            'order_status' => 1,
            'updated_at' => Carbon::now()
        ]);

        // Get all the product of the specified order.
        $purchase_products = OrderItems::where('order_items.purchase_order_id', $orderId)
            ->join('product', 'order_items.product_id', 'product.product_id')
            ->get();

        // Delete all the order.
        foreach ($purchase_products as $item) {
            $item->delete();
        }

        // Create new Order Items connected to the currect purchase order       
        $productIds = $request->product_id;
        $productQuantities = $request->product_quantity;
        $productPrices = $request->product_price;
            
        foreach ($productIds as $index => $productId) {
            OrderItems::create([
                'order_items_id' => $this->generateId('order_items'),
                'purchase_order_id' => $orderId,
                'product_id' => $productId,
                'quantity' => $productQuantities[$index],
                'price' => $productPrices[$index],
            ]);
        }

        return redirect()->route('purchase_order')->with('success', 'Order updated successfully.');
    }
    
    public function destroyOrder(Request $request, $id)
    {
        // Validate the provided password
        $validatedData = $request->validate([
            'password' => 'required|string',
        ]);
    
        // Verify the password matches the current user's password
        if (!Hash::check($validatedData['password'], Auth::user()->password)) {
            return redirect()->back()->with('delete_error', 'Incorrect password. Order deletion cancelled.')
                                    ->with('error_purchase_order_id', $id);
        }
    
        // Find the order
        $order = PurchaseOrder::find($id);
        
        if (!$order) {
            return redirect()->back()->with('error', 'Order not found.');
        }
    
        // Find all order items
        $orderItems = OrderItems::where('purchase_order_id', $id)->get();
        
        // Start a transaction to ensure data integrity
        DB::beginTransaction();
        
        try {
            // Delete all related order items first
            foreach ($orderItems as $item) {
                $item->delete();
            }
            
            // Then delete the order itself
            $order->delete();
            
            // Commit the transaction
            DB::commit();
            
            return redirect()->route('purchase_order')->with('success', 'Purchase order successfully deleted.');
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->back()->with('error', 'An error occurred while deleting the order: ' . $e->getMessage());
        }
    }

    /**
     * Process updates at the orders from when the orders are delivered. 
     * Will update delivered quantity and change status of the order to be "Delivered".
     *
     * @param  string $table
     * @return int
     */
    public function updateOrderChanges(Request $request)
    {
        $order = PurchaseOrder::with(['order_items.product.inventory'])
            ->findOrFail($request->purchase_order_id);

        $products = $request->products;

        foreach ($order->order_items as $item) {
            $productId = $item->product_id;

            // Check if this product is included in the request
            if (isset($products[$productId])) {
                $data = $products[$productId];

                // Update fields on order_items table
                $item->delivered_quantity = (int) ($data['delivered_quantity'] ?? $item->delivered_quantity);
                $item->damaged_quantity = isset($data['damaged_quantity']) ? (int) $data['damaged_quantity'] : null;
                $item->remarks = $data['remarks'] ?? null;
                $item->save();

                // Update inventory
                $inventory = $item->product->inventory;
                if ($inventory) {
                    $inventory->in_stock += $item->delivered_quantity;
                    $inventory->save();
                }
            }
        }

        // Mark order as delivered
        $order->update([
            'order_status' => 3,
        ]);

        // Create or update delivery metadata
        $delivery = Delivery::firstOrNew([
            'purchase_order_id' => $order->purchase_order_id
        ]);

        $delivery->date_delivered = now();
        $delivery->save();

        return redirect()->route('purchase_order')->with('success', 'Purchase order successfully updated.');
    }


    /**
     * Creates Backorder.
     *
     * @param  string $table
     * @return int
     */
    public function createBackorderRequest(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|integer|exists:purchase_order,purchase_order_id',  // Ensure order exists
            'order_type' => 'required|string',  // Allow specific types
            'product_id' => 'required|array|min:1',  // Ensure at least one product is provided
            'product_id.*' => 'integer|exists:product,product_id',  // Each product ID should be valid
            'quantity_to_be_backordered' => 'required|array|min:1',  // Ensure quantity array matches products
            'quantity_to_be_backordered.*' => 'integer|min:1',  // Each quantity should be a positive integer
            'total_price' => 'required|array|min:1',  // Ensure price array matches products
            'total_price.*' => 'numeric|min:0',  // Each price should be a valid numeric value
            'payment_method' => 'required|string',  // Allow specific methods
            'billing_address' => 'nullable|string',
            'shipping_address' => 'required|string',
            'supplier_id' => 'required|array|min:1',
            'supplier_id.*' => 'required|integer',
            'created_by' => 'required|integer|exists:user,user_id',  // Ensure user exists
        ]);

        // You can now use the validated data
        $orderId = $validated['order_id'];
        $orderType = $validated['order_type'];
        $productIds = $validated['product_id'];
        $quantities = $validated['quantity_to_be_backordered'];
        $prices = $validated['total_price'];
        $totalPrices = array_sum($validated['total_price']);
        $paymentMethod = $validated['payment_method'];
        $billingAddress = $validated['billing_address'];
        $shippingAddress = $validated['shipping_address'];
        $supplierIds = $validated['supplier_id'];
        $createdBy = $validated['created_by'];

        $orderToBeBackordered = PurchaseOrder::find($orderId);

        // To be used to create new order and for order items
        $newBackorderId = $this->generateId('purchase_order');

        // Create the backorder record
        $order = PurchaseOrder::create([
            'purchase_order_id' => $newBackorderId,
            'type' => $orderType,
            'payment_method' => $validated['payment_method'],
            'billing_address' => $validated['billing_address'],
            'shipping_address' => $validated['shipping_address'],
            'total_price' => $totalPrices,
            'created_by' => $validated['created_by'],
            'order_status' => 1,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);

        // Create new Order Supplier connected to the new backorder
        foreach ($supplierIds as $index => $supplierId) {
            $orderSupplier = OrderSupplier::create([
                'order_supplier_id' => $this->generateId('order_supplier'),
                'purchase_order_id' => $newBackorderId,
                'supplier_id' => $supplierId,
            ]);
            $orderSupplier->save();
        }

        // Create new Order Items connected to the new backorder
        foreach ($productIds as $index => $productId) {
            $backorderItem = new OrderItems([
                'product_id' => $productId,
                'quantity' => $quantities[$index],
                'price' => $prices[$index],
                'purchase_order_id' => $newBackorderId,
            ]);
            $backorderItem->save();
        }

        return response()->json(['message' => 'Backorder request successfully created.']);
    }

    public function supplier_list()
    {
        $suppliers = Supplier::all();

        return view('purchase.suppliers_table', compact(['suppliers']));
    }

    public function supplier_info($id)
    {
        $supplier = Supplier::with(['products.category', 'products.inventory'])->find($id);
        $products = $supplier->products;

        // Fetch all categories for filtering or display purposes
        $categories = Category::all();

        return view('purchase.supplier_info', compact(['supplier', 'products', 'categories']));
    }

    public function create_supplier()
    {


       return view('purchase.create_supplier');
    }

    public function store_supplier(Request $request)
    {
        // Validate the incoming request data
        $validatedData = $request->validate([
            'company_name' => ['required', 'string', 'max:30'],
            'email' => ['required', 'email', 'max:30'],
            'mobile_number' => ['required', 'regex:/^[0-9]{10,11}$/'],
            'address' => ['required', 'string', 'max:100'],
            'contact_person' => ['required', 'string', 'max:30']
        ]);
        

        Supplier::create([
            'supplier_id' => $this->generateId('supplier'),
            'company_name' => $validatedData['company_name'],
            'contact_person' => $validatedData['contact_person'],
            'mobile_number' => $validatedData['mobile_number'],
            'email' => $validatedData['email'],
            'address' => $validatedData['address'],
        ]);

        return redirect()->route('supplier_list')->with('success', 'Supplier successfully added.');
    }

    public function edit_supplier($id)
    {
        $supplier = Supplier::with('products')->find($id);
        $products = $supplier->products;

        return view('purchase.update_supplier', compact(['supplier', 'products']));
    }

    public function update_supplier(Request $request, $id)
    {
        // Validate the incoming request data
        $validatedData = $request->validate([
            'company_name' => ['required', 'string', 'max:30'],
            'email' => ['required', 'email', 'max:30'],
            'mobile_number' => ['required', 'regex:/^[0-9]{10,11}$/'],
            'address' => ['required', 'string', 'max:100'],
            'contact_person' => ['required', 'string', 'max:30']
        ]);

        $supplier = Supplier::find($id);

        $supplier->update([
            'company_name' => $validatedData['company_name'],
            'contact_person' => $validatedData['contact_person'],
            'mobile_number' => $validatedData['mobile_number'],
            'email' => $validatedData['email'],
            'address' => $validatedData['address'],
        ]);

        return redirect()->route('supplier_list')->with('success', 'Supplier successfully updated.');
    }

    public function delete_supplier(Request $request, $id)
    {
        $validatedData = $request->validate([
            'password' => 'required|string',
        ]);
    
        if (!Hash::check($validatedData['password'], Auth::user()->password)) {
            return redirect()->back()->with('delete_error', 'Incorrect password. Supplier deletion cancelled.')
                                    ->with('error_supplier_id', $id);
        }

        try {
            $supplier = Supplier::find($id);
            $supplier->delete();
            DB::commit();

            return redirect()->route('supplier_list')->with('success', 'Supplier successfully deleted.');
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->back()->with('error', 'An error occurred while deleting the supplier: ' . $e->getMessage());
        }
    }

    public function create_reorder(Request $request, $supplier_id)
    {
        if (!Auth::check()) {
            return redirect('/login')->withErrors('You must be logged in.');
        }

        // Step 1: Fetch all products that need to be reordered (in stock is less than reorder level)
        $products = DB::table('product')
            ->join('inventory', 'product.product_id', '=', 'inventory.product_id')
            ->join('product_supplier', 'product.product_id', '=', 'product_supplier.product_id')
            ->join('supplier', 'product_supplier.supplier_id', '=', 'supplier.supplier_id')
            ->where('product_supplier.supplier_id', $supplier_id)
            ->whereColumn('inventory.in_stock', '<', 'inventory.reorder_level')
            ->select('product.*', DB::raw('inventory.purchase_price_per_unit as price'), 'inventory.in_stock', 'inventory.reorder_level', 'supplier.supplier_id', 'supplier.company_name', 'supplier.address', 'product_supplier.supplier_id')
            ->get();
        
        $supplier = $products->first()
            ? (object) [
                'supplier_id' => $products->first()->supplier_id,
                'company_name' => $products->first()->company_name,
                'address' => $products->first()->address,
            ]
            : null;
            
        if ($products->isEmpty()) {
            return redirect()->back()->withErrors('No products need reordering.');
        }

        // Step 2: Group products by supplier
        $groupedBySupplier = $products->groupBy('supplier_id');

        DB::transaction(function () use ($groupedBySupplier, $supplier, $request) {
            foreach ($groupedBySupplier as $supplierId => $products) {

                // Step 3: Create a single purchase order for each supplier
                $purchaseOrderId = DB::table('purchase_order')->insertGetId([
                    'purchase_order_id' => $this->generateId('purchase_order'),
                    'type' => 'Purchasing Order',
                    'payment_method' => $request->payment_method,
                    'billing_address' => $supplier->address,
                    'shipping_address' => $request->shipping_address,
                    'total_price' => 0, // To be calculated later
                    'created_by' => Auth::id(),
                    'order_status' => 1, // "To order"
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $totalPrice = 0;

                // Step 4: Loop through products and add them to the purchase order
                foreach ($products as $product) {
                    $unitsToReorder = max(0, ($product->reorder_level - $product->in_stock) + $this->reorderBuffer);
                    $lineTotal = ($product->price ?? 0) * $unitsToReorder;

                    DB::table('order_items')->insert([
                        'purchase_order_id' => $purchaseOrderId,
                        'product_id' => $product->product_id,
                        'quantity' => $unitsToReorder,
                        'price' => $lineTotal,
                        'delivered_quantity' => 0,
                    ]);

                    $totalPrice += $lineTotal;
                }

                // Step 5: Update the total price of the purchase order
                DB::table('purchase_order')
                    ->where('purchase_order_id', $purchaseOrderId)
                    ->update(['total_price' => $totalPrice]);

                // Step 6: Create order_supplier (order_supplier table)
                // Link purchase_order_id and supplier_id in the order_supplier table
                DB::table('order_supplier')->insert([
                    'order_supplier_id' => $this->generateId('order_supplier'),
                    'purchase_order_id' => $purchaseOrderId,
                    'supplier_id' => $supplierId,
                ]);
            }
        });

        return redirect()->back()->with('success', 'Reorder created successfully.');
    }
}