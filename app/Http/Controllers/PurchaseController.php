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

        // SQL `user` to get Inventory Manager details
        $userSQL = DB::table('user')
        ->select('user.*')
        ->where('role', '=', 'Purchase Manager')
        ->get();

        $productJoined = DB::table('inventory')
            ->join('product', 'inventory.product_id', '=', 'product.product_id')
            ->join('stock_transfer', 'stock_transfer.product_id', '=', 'product.product_id')
            ->join('stockroom', 'stock_transfer.to_stockroom_id', '=', 'stockroom.stockroom_id')
            ->join('category', 'product.category_id', '=', 'category.category_id')
            ->leftJoin('product_supplier', 'product.product_id', '=', 'product_supplier.product_id')
            ->leftJoin('supplier', 'product_supplier.supplier_id', '=', 'supplier.supplier_id')

            ->select('inventory.*', 'product.*', 'category.*', 'supplier.*', 'stock_transfer.*', 'stockroom.*')
            // Prioritize products that need restocking (either store or stockroom)
            ->orderByRaw('
            CASE 
                WHEN inventory.in_stock - stockroom.product_quantity <= inventory.reorder_level THEN 1 
                WHEN stockroom.product_quantity <= inventory.reorder_level THEN 2 
                ELSE 3 
            END, updated_at DESC')
            ->get();
            
        
        // Filter duplicates based on a unique key (e.g., product_id)
        $productJoined = $productJoined->unique('product_id');



        // Decode the description for each inventory item
        foreach ($productJoined as $item) {
            $item->descriptionArray = json_decode($item->description, true); // Decode the JSON description into an array
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

        // Fetch all categories for filtering or display purposes
        $categories = Category::all();

        // Fetch all categories for filtering or display purposes
        $suppliers = Supplier::all();

        // Pass the inventory managers and user role to the view
        return view('purchase.purchase_table', [
            'userSQL' => $userSQL,
            'productJoined' => $productJoined,
            'categories' => $categories,
            'suppliers' => $suppliers,
            'lowStoreStockCount' => $lowStoreStockCount,
            'lowStockroomStockCount' => $lowStockroomStockCount,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $suppliers = Supplier::all(); // Fetch all suppliers
        $categories = Category::all(); // Fetch all categories
        return view('purchase.create_product', compact('suppliers', 'categories')); // Pass to the view
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
    public function store(Request $request)
    {
        // Validate the incoming request data
        $validatedData = $request->validate([
            'image_url' => ['image'],
            'product_name' => ['required', 'string', 'max:30'],
            'category_dropdown' => ['required'],
            'category_name' => ['nullable', 'string', 'max:30', 'unique:category,category_name',],
            'reorder_level' => ['nullable', 'numeric'],
            'color' => ['max:50'],
            'size' => ['max:50'],
            'description' => ['max:255'],
            'aisle_number' => ['numeric'],
            'cabinet_level' => ['numeric'],
        ]);

         // Handle file upload with a default image if no file is provided
         $fileNameToStore = 'noimage.jpg'; 
         if ($request->hasFile('image_url')) {
             $fileNameToStore = $this->handleFileUpload($request->file('image_url'));
         }

        // Use a transaction to ensure data integrity
        DB::transaction(function () use ($validatedData, $fileNameToStore ) {
            // Handle category logic
            $categoryId = $validatedData['category_dropdown'];
            if ($categoryId === 'add-new-category') {
                // Create a new Category
                $category = Category::create([
                    'category_id' => $this->generateId('category'), // Generate custom ID for category
                    'category_name' => $validatedData['category_name'],
                ]);
                $categoryId = $category->category_id; // Get the new supplier's ID
            }

            $productId = $this->generateId('product');

            // Create the Product
            $product = Product::create([
                'image_url' => $fileNameToStore,
                'product_id' => $productId, // Generate custom ID for product
                'product_name' => $validatedData['product_name'],
                'description' => json_encode([ // Encode the array as JSON
                    'color' => $validatedData['color'],
                    'size' => $validatedData['size'],
                    'description' => $validatedData['description'],
                ]),
                'category_id' => $categoryId, // Use the existing or newly created category ID
            ]);

            // Handle supplier logic
            // $supplierId = $validatedData['supplier_dropdown'];
            // if($supplierId !== NULL){
            //     if ($supplierId === 'add-new') {
            //         // Create a new supplier
            //         $supplier = Supplier::create([
            //             'supplier_id' => $this->generateId('supplier'), // Generate custom ID for supplier
            //             'company_name' => $validatedData['company_name'],
            //             'contact_person' => $validatedData['contact_person'],
            //             'mobile_number' => $validatedData['mobile_number'],
            //             'email' => $validatedData['email'],
            //             'address' => $validatedData['address'],
            //         ]);
            //         $supplierId = $supplier->supplier_id; // Get the new supplier's ID
            //     }

            //     // Connect the product and supplier at product_supplier table
            //     ProductSupplier::create([
            //         'product_supplier_id' => $this->generateId('product_supplier'),
            //         'supplier_id' => $supplierId,
            //         'product_id' => $productId,
            //     ]);
            // }

            
            // Create the Stockroom
            $stockroom = Stockroom::create([
                'stockroom_id' => $this->generateId('stockroom'), // Generate custom ID for product
                // 'aisle_number' => $validatedData['aisle_number'],
                // 'cabinet_level' => $validatedData['cabinet_level'],
                'product_quantity' => 0,
                'category_id' => $categoryId, // Use the existing or newly created category ID
            ]);

            // Create the StockTransfer
            StockTransfer::create([
                'stock_transfer_id' => $this->generateId('stock_transfer'), // Generate custom ID for product
                'transfer_quantity' => 0,
                'transfer_date' => now(),
                'product_id' => $productId, // Use the generated category_id
                'user_id' => Auth::user()->user_id, // Use the logged in user_id
                'to_stockroom_id' => $stockroom->stockroom_id, // Use the generated stockroom_id
            ]);

            // Create the Inventory
            Inventory::create([
                'inventory_id' => $this->generateId('inventory'), // Generate custom ID for inventory
                // 'purchase_price_per_unit' => $validatedData['purchase_price_per_unit'],
                // 'sale_price_per_unit' => $validatedData['sale_price_per_unit'],
                // 'unit_of_measure' => $validatedData['unit_of_measure'],
                'in_stock' => 0,
                // 'reorder_level' => $validatedData['reorder_level'],
                'product_id' => $productId, // Use the generated product_id
            ]);
        });

        // Redirect or return response after successful creation
        return redirect()->route('purchase_table')->with('success', 'Product added successfully.');
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
    


    public function CategoryFilter(Request $request)
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
            'userSQL' => $userSQL,
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
    public function edit($productId)
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

        $productJoined = DB::table('inventory')
            ->join('product', 'inventory.product_id', '=', 'product.product_id')
            ->join('stock_transfer', 'stock_transfer.product_id', '=', 'product.product_id')
            ->join('stockroom', 'stock_transfer.to_stockroom_id', '=', 'stockroom.stockroom_id')
            ->join('category', 'product.category_id', '=', 'category.category_id')
            ->leftJoin('product_supplier', 'product.product_id', '=', 'product_supplier.product_id')
            ->leftJoin('supplier', 'product_supplier.supplier_id', '=', 'supplier.supplier_id')
            ->select('inventory.*', 'product.*', 'category.*', 'supplier.*', 'stock_transfer.*', 'stockroom.*')
            ->where('product.product_id', '=', $productId)
            ->first();

        // Decode the description for the product
        // Decode the description for the product if it's set
        $descriptionArray = [];
        if ($productJoined && isset($productJoined->description)) {
            $descriptionArray = json_decode($productJoined->description, true); // Decode the JSON description into an array
        }

        // Fetch all categories for filtering or display purposes
        $categories = Category::all();
        $suppliers = Supplier::all();

        return view('purchase.update_product', compact('userSQL', 'productJoined', 'categories', 'suppliers', 'descriptionArray'));
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $productId)
{
    // Validate the incoming request data
    $validatedData = $request->validate([
        'image_url' => ['image', 'nullable'],
        'product_name' => ['required', 'string', 'max:30'],
        'category_dropdown' => ['required'],
        'category_name' => ['nullable', 'string', 'max:30', 'unique:category,category_name',],
        'reorder_level' => ['required', 'numeric'],
        'color' => ['max:50'],
        'size' => ['max:50'],
        'description' => ['max:255'],
        'aisle_number' => ['numeric'],
        'cabinet_level' => ['numeric'],
    ]);

    // Find the product by its ID
    $product = Product::findOrFail($productId);
    $fileNameToStore = $product->image_url; // Default to the existing image

    // Handle file upload if a new image is provided
    if ($request->hasFile('image_url')) {
        $fileNameToStore = $this->handleFileUpload($request->file('image_url'));
    }

    // Use a transaction to ensure data integrity
    DB::transaction(function () use ($validatedData, $fileNameToStore, $product) {
        // Handle category logic
        $categoryId = $validatedData['category_dropdown'];
        if ($categoryId === 'add-new-category') {
            // Create a new Category
            $category = Category::create([
                'category_id' => $this->generateId('category'), // Generate custom ID for category
                'category_name' => $validatedData['category_name'],
            ]);
            $categoryId = $category->category_id; // Get the new category's ID
        }

        // Handle supplier logic
        // $supplierId = $validatedData['supplier_dropdown'];
        // if ($supplierId === 'add-new') {
        //     // Create a new supplier
        //     $supplier = Supplier::create([
        //         'supplier_id' => $this->generateId('supplier'), // Generate custom ID for supplier
        //         'company_name' => $validatedData['company_name'],
        //         'contact_person' => $validatedData['contact_person'],
        //         'mobile_number' => $validatedData['mobile_number'],
        //         'email' => $validatedData['email'],
        //         'address' => $validatedData['address'],
        //     ]);
        //     $supplierId = $supplier->supplier_id; // Get the new supplier's ID
        // }

        // Update the Product
        $product->update([
            'image_url' => $fileNameToStore,
            'product_name' => $validatedData['product_name'],
            'description' => json_encode([ // Encode the array as JSON
                'color' => $validatedData['color'],
                'size' => $validatedData['size'],
                'description' => $validatedData['description'],
            ]),
            'category_id' => $categoryId, // Use the existing or newly created category ID
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
            'aisle_number' => $validatedData['aisle_number'],
            'cabinet_level' => $validatedData['cabinet_level'],
            // 'product_quantity' => $validatedData['product_quantity'],
            'category_id' => $categoryId, // Use the existing or newly created category ID
        ]);

        // Update the StockTransfer if necessary
        $stockTransfer = StockTransfer::where('product_id', $product->product_id)->firstOrFail();
        $stockTransfer->update([
            'transfer_quantity' => 0,
            'transfer_date' => now(),
            'to_stockroom_id' => $stockroom->stockroom_id, // Use the generated stockroom_id
        ]);

        // Update the Inventory
        $inventory = Inventory::where('product_id', $product->product_id)->firstOrFail();
        $inventory->update([
            // 'purchase_price_per_unit' => $validatedData['purchase_price_per_unit'],
            // 'sale_price_per_unit' => $validatedData['sale_price_per_unit'],
            // 'unit_of_measure' => $validatedData['unit_of_measure'],
            // 'in_stock' => $validatedData['in_stock'],
            'reorder_level' => $validatedData['reorder_level'],
        ]);
    });

    // Redirect or return response after successful update
    return redirect()->route('purchase_table')->with('success', 'Product updated successfully.');
}

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
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
            'error_product_id' => $id, // Pass the product ID with an error
        ]);
    }

    // Find and delete the product
    $product = Product::find($id);

    if ($product) {
        $product->delete();
        // Redirect with success message
        return redirect()->route('purchase_table')->with('success', 'Product deleted successfully.');
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

        // populate table with list of purchase order table
        $orders = PurchaseOrder::with(['order_items.product'])
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
                'user.last_name'
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
            ->get();
        
            // Get unique payment methods from orders
        $paymentMethods = $orders->pluck('payment_method')->unique()->values();

        // Fetch all categories, suppliers, orderStatuses for filtering or display purposes
        $categories = Category::all();
        $suppliers = Supplier::all();
        $orderStatuses = OrderStatuses::all();

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

    public function updateOrderStatus(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:purchase_order,purchase_order_id',
            'status' => 'required|exists:order_statuses,order_statuses',
        ]);

        $order = PurchaseOrder::findOrFail($request->order_id);
        $order->order_status = $request->status;
        $order->save();

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

        return view('purchase.create_purchase_order', compact('suppliers', 'products', 'addresses'));
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
            // 'billing_address' => ['nullable'],
            'address_dropdown' => ['required'],
            'address' => ['nullable', 'string', 'max:50'],
            'total_price' => ['nullable', 'numeric'],
            'user_id' => ['required'],
            'supplier_id' => ['nullable'],

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
        $initialOrderType = 'Purchase Order';

        // Create new order first
        $order = PurchaseOrder::create([
            'purchase_order_id' => $newOrderId,
            'type' => $initialOrderType,
            'payment_method' => $validatedData['payment_method'],
            'billing_address' => $address,
            'shipping_address' => $address,
            'total_price' => 0.0,
            'created_by' => $validatedData['user_id'],
            'order_status' => 2,
            'created_at' => Carbon::now(),
            'updated_at' => null
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

        $totalPrice = 0.0;  // Initialize total price to 0

        foreach ($productIds as $index => $productId) {
            // Check if the corresponding product has a valid price in the order_items table
            $orderItem = OrderItems::where('purchase_order_id', $order->purchase_order_id)
                ->where('product_id', $productId)
                ->first();

            if ($orderItem && $orderItem->price) {
                // If the price is available, calculate the total price
                $totalPrice += $orderItem->price * $productQuantities[$index];  // Assuming quantities are in the request
            }
            // If the price is missing, simply ignore this product and do not add it to the total price
        }

        // Update the total price of the order
        $order->total_price = $totalPrice;
        $order->save();  // Save the updated order

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

        return view('purchase.update_purchase_order', compact('order', 'purchase_products', 'suppliers', 'products', 'addresses', 'order_suppliers'));
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
            'total_price' => 0,
            'created_by' => $validatedData['user_id'],
            'order_status' => 1,
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
        $productPrices = $request->product_combined_price;
            
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
            ->find($request->purchase_order_id);

            // Get all the products from the request
            $products = $request->products;

            foreach ($order->order_items as $item) {
                $productId = $item->product_id;

                // Check if the product data exists
                if (isset($products[$productId])) {
                    $data = $products[$productId];

                    // Update the delivered quantity
                    $item->delivered_quantity = $data['delivered_quantity'] ?? $item->delivered_quantity;

                    // Get the inventory related to the product
                    $inventory = $item->product->inventory;

                     // If inventory exists, update the 'in_stock' column
                    if ($inventory) {
                        $inventory->in_stock = $inventory->in_stock + $item->delivered_quantity; // Example update logic
                        $inventory->save();
                    }

                    $item->save();
                }
            }

            $order->update([
                'order_status' => 3,
            ]);

            // find delivery with foreign key: purchase_order_id
            $delivery = Delivery::where('purchase_order_id', $request->purchase_order_id)->first();

            if ($delivery) {
                $delivery->update([
                    'date_delivered' => now()
                ]);
            }


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
        dd($request);
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
            'order_type' => $orderType,
            'payment_method' => $validated['payment_method'],
            'billing_address' => $validated['billing_address'],
            'shipping_address' => $validated['shipping_address'],
            'total_price' => $totalPrices,
            'created_by' => $validated['created_by'],
            'supplier_id' => $validated['supplier_id'],
            'order_status' => 2,
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
}

