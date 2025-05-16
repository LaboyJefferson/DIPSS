<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Inventory;
use App\Models\Supplier;
use App\Models\Stockroom;
use App\Models\StockTransfer;
use App\Models\PurchaseOrder;
use App\Models\OrderItems;
use App\Models\OrderSupplier;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Mail\ConfirmRegistration;
use Illuminate\Support\Facades\Mail;
 use Illuminate\Support\Facades\Log;
 use Exception;

class InventoryController extends Controller
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
        ->where('role', '=', 'Inventory Manager')
        ->get();

        $inventoryJoined = DB::table('inventory')
        ->join('product', 'inventory.product_id', '=', 'product.product_id')
        ->join('stock_transfer', 'stock_transfer.product_id', '=', 'product.product_id')
        ->join('stockroom', 'stock_transfer.to_stockroom_id', '=', 'stockroom.stockroom_id')
        ->join('category', 'product.category_id', '=', 'category.category_id')
        ->leftJoin('product_supplier', 'product.product_id', '=', 'product_supplier.product_id')
        ->leftJoin('supplier', 'product_supplier.supplier_id', '=', 'supplier.supplier_id')
        ->select('inventory.*', 'product.*', 'category.*', 'supplier.*', 'stock_transfer.*', 'stockroom.*')
        ->orderBy('updated_at', 'desc')
        ->distinct()
        ->get();

        // Optionally, if the `distinct()` doesn't solve the problem, you can filter by unique `product_id`
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

        return view('inventory.inventory_table', [
            'userSQL' => $userSQL,
            'inventoryJoined' => $inventoryJoined,
            'categories' => $categories,
            'suppliers' => $suppliers,
            'lowStoreStockCount' => $lowStoreStockCount,
            'lowStockroomStockCount' => $lowStockroomStockCount,
        ]);
    }

    
    public function productNameFilter(Request $request)
    {
        if (!Auth::check()) {
            return redirect('/login')->withErrors('You must be logged in.');
        }
    
        // SQL `user` to get Inventory Manager details
        $userSQL = DB::table('user')
            ->select('user.*')
            ->where('role', '=', 'Inventory Manager')
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
    
        $inventoryJoined = $inventoryQuery->get();
        $inventoryJoined = $inventoryJoined->unique('product_id');
    
        // Decode description for each inventory item
        foreach ($inventoryJoined as $item) {
            $item->descriptionArray = json_decode($item->description, true);
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
    
        // Fetch categories and suppliers for filtering or display purposes
        $categories = Category::all();
        $suppliers = Supplier::all();
    
        return view('inventory.inventory_table', [
            'userSQL' => $userSQL,
            'inventoryJoined' => $inventoryJoined,
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
        $inventoryJoined = DB::table('inventory')
            ->join('product', 'inventory.product_id', '=', 'product.product_id')
            ->join('stock_transfer', 'stock_transfer.product_id', '=', 'product.product_id')
            ->join('stockroom', 'stock_transfer.to_stockroom_id', '=', 'stockroom.stockroom_id')
            ->join('category', 'product.category_id', '=', 'category.category_id')
            ->leftJoin('product_supplier', 'product.product_id', '=', 'product_supplier.product_id')
            ->leftJoin('supplier', 'product_supplier.supplier_id', '=', 'supplier.supplier_id')
            ->select('inventory.*', 'product.*', 'category.*', 'supplier.*', 'stock_transfer.*', 'stockroom.*')
            ->orderBy('product.product_name', 'asc');

        if (!empty($categoryIds)) {
            $inventoryJoined = $inventoryJoined->whereIn('category.category_id', $categoryIds);
        }

        $inventoryJoined = $inventoryJoined->distinct()->get();

        // Filter unique products
        $inventoryJoined = $inventoryJoined->unique('product_id');

        // Decode the description for each inventory item
        foreach ($inventoryJoined as $item) {
            $item->descriptionArray = json_decode($item->description, true);
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

        return view('inventory.inventory_table', [
            'userSQL' => $userSQL,
            'inventoryJoined' => $inventoryJoined,
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
        $inventoryJoined = DB::table('inventory')
            ->join('product', 'inventory.product_id', '=', 'product.product_id')
            ->join('stock_transfer', 'stock_transfer.product_id', '=', 'product.product_id')
            ->join('stockroom', 'stock_transfer.to_stockroom_id', '=', 'stockroom.stockroom_id')
            ->join('category', 'product.category_id', '=', 'category.category_id')
            ->leftJoin('product_supplier', 'product.product_id', '=', 'product_supplier.product_id')
            ->leftJoin('supplier', 'product_supplier.supplier_id', '=', 'supplier.supplier_id')
            ->select('inventory.*', 'product.*', 'category.*', 'supplier.*', 'stock_transfer.*', 'stockroom.*')
            ->orderBy('product.product_name', 'asc');

        if (!empty($supplierIds)) {
            $inventoryJoined = $inventoryJoined->whereIn('supplier.supplier_id', $supplierIds);
        }

        $inventoryJoined = $inventoryJoined->distinct()->get();

        // Filter unique products
        $inventoryJoined = $inventoryJoined->unique('product_id');

        // Decode the description for each inventory item
        foreach ($inventoryJoined as $item) {
            $item->descriptionArray = json_decode($item->description, true);
        }

        // Fetch all categories for filtering or display purposes
        $categories = Category::all();

        // Fetch all categories for filtering or display purposes
        $suppliers = Supplier::all();

        return view('inventory.inventory_table', [
            'userSQL' => $userSQL,
            'inventoryJoined' => $inventoryJoined,
            'categories' => $categories,
            'suppliers' => $suppliers,
        ]);
    }

    public function LowStock(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:product,product_id',
        ]);

        $productId = $request->product_id;
        $userId = Auth::id();

        // Find the product
        $product = Product::find($productId);

        if (!$product) {
            return response()->json(['message' => 'Product not found.'], 404);
        }

        if ($product->suppliers->isEmpty()) {
            return response()->json(['message' => 'Product has no assigned supplier.'], 422);
        }
        

        $toOrderStatus = 1; // 'to order' status code

        // Check if the product is already included in any 'to-order' purchase order
        $existingItem = OrderItems::join('purchase_order', 'order_items.purchase_order_id', '=', 'purchase_order.purchase_order_id')
            ->where('purchase_order.order_status', $toOrderStatus) // Ensure the purchase order is in 'to order' status
            ->where('order_items.product_id', $productId) // Check if the product already exists
            ->exists();

        if ($existingItem) {
            return response()->json(['message' => 'Product is already in the to-order list.']);
        }

        $supplier = $product->suppliers->first();

        // Find existing 'to-order' purchase order by this user and supplier
        $purchaseOrder = PurchaseOrder::where('created_by', $userId)
            ->where('order_status', $toOrderStatus)
            ->whereHas('suppliers', function ($query) use ($supplier) {
                $query->where('suppliers.id', $supplier->id);
            })
            ->first();


        if (!$purchaseOrder) {
            // Create a new purchase order if none exists
            $purchaseOrder = PurchaseOrder::create([
                'purchase_order_id' => $this->generateId('purchase_order'),
                'type' => 'Purchasing Order',
                'payment_method' => 'to be updated',
                'billing_address' => 'to be updated',
                'shipping_address' => 'to be updated',
                'total_price' => 0.0,
                'reason' => 'Auto-created from low stock',
                'created_by' => $userId,
                'order_status' => $toOrderStatus,
                'created_at' => now(),
                'updated_at' => null
            ]);
        }

        // Create the order_supplier record
        OrderSupplier::create([
            'order_supplier_id'  => $this->generateId('order_supplier'),
            'purchase_order_id'  => $purchaseOrder->purchase_order_id,
            'supplier_id'        => $product->supplier_id
        ]);

        // Add product to order_items
        OrderItems::create([
            'order_items_id' => $this->generateId('order_items'),
            'purchase_order_id' => $purchaseOrder->purchase_order_id,
            'product_id' => $productId,
            'quantity' => 1,
            'price' => 0,
            'delivered_quantity' => null,
        ]);

        return response()->json(['message' => 'Product added to the to-order list.']);
    }


    private function generateId($table)
    {
        // Generate a random 8-digit number
        do {
            $id = random_int(10000000, 99999999);
        } while (DB::table($table)->where("{$table}_id", $id)->exists()); // Ensure the ID is unique

        return $id;
    }

    public function inventoryProductsTable()
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
        return view('inventory.inventory_products_table', [
            'userSQL' => $userSQL,
            'productJoined' => $productJoined,
            'categories' => $categories,
            'suppliers' => $suppliers,
            'lowStoreStockCount' => $lowStoreStockCount,
            'lowStockroomStockCount' => $lowStockroomStockCount,
        ]);
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

        return view('inventory.inventory_update_product', compact('userSQL', 'productJoined', 'categories', 'suppliers', 'descriptionArray'));
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
            'purchase_price_per_unit' => ['required', 'numeric'],
            'sale_price_per_unit' => ['required', 'numeric'],
            'unit_of_measure' => ['required', 'string', 'max:15'],
            'in_stock' => ['required', 'numeric'],
            'reorder_level' => ['required', 'numeric'],
            'color' => ['max:50'],
            'size' => ['max:50'],
            'description' => ['max:255'],
            'supplier_dropdown' => ['required'],
            'company_name' => ['nullable', 'string', 'max:30'],
            'contact_person' => ['nullable', 'string', 'max:30'],
            'mobile_number' => ['nullable', 'numeric'],
            'email' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:50'],
            'aisle_number' => ['numeric'],
            'cabinet_level' => ['numeric'],
            'product_quantity' => ['numeric'],
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
            $supplierId = $validatedData['supplier_dropdown'];
            if ($supplierId === 'add-new') {
                // Create a new supplier
                $supplier = Supplier::create([
                    'supplier_id' => $this->generateId('supplier'), // Generate custom ID for supplier
                    'company_name' => $validatedData['company_name'],
                    'contact_person' => $validatedData['contact_person'],
                    'mobile_number' => $validatedData['mobile_number'],
                    'email' => $validatedData['email'],
                    'address' => $validatedData['address'],
                ]);
                $supplierId = $supplier->supplier_id; // Get the new supplier's ID
            }

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
                'supplier_id' => $supplierId, // Use the existing or newly created supplier ID
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
                'product_quantity' => $validatedData['product_quantity'],
                'category_id' => $categoryId, // Use the existing or newly created category ID
            ]);

            // Update the StockTransfer if necessary
            $stockTransfer = StockTransfer::where('product_id', $product->product_id)->firstOrFail();
            $stockTransfer->update([
                'transfer_quantity' => $validatedData['product_quantity'],
                'transfer_date' => now(),
                'to_stockroom_id' => $stockroom->stockroom_id, // Use the generated stockroom_id
            ]);

            // Update the Inventory
            $inventory = Inventory::where('product_id', $product->product_id)->firstOrFail();
            $inventory->update([
                'purchase_price_per_unit' => $validatedData['purchase_price_per_unit'],
                'sale_price_per_unit' => $validatedData['sale_price_per_unit'],
                'unit_of_measure' => $validatedData['unit_of_measure'],
                'in_stock' => $validatedData['in_stock'],
                'reorder_level' => $validatedData['reorder_level'],
            ]);
        });

        // Redirect or return response after successful update
        return redirect()->route('inventory_products_table')->with('success', 'Product updated successfully.');
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
            return redirect()->route('inventory_products_table')->with('success', 'Product deleted successfully.');
        }

        // If product not found
        return back()->withErrors(['error' => 'Product not found.']);
    }

    public function inventoryFilterProductName(Request $request)
    {
        if (!Auth::check()) {
            return redirect('/login')->withErrors('You must be logged in.');
        }
    
        // SQL `user` to get Inventory Manager details
        $userSQL = DB::table('user')
            ->select('user.*')
            ->where('role', '=', 'Inventory Manager')
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
    
        return view('inventory.inventory_products_table', [
            'userSQL' => $userSQL,
            'productJoined' => $productJoined,
            'categories' => $categories,
            'suppliers' => $suppliers,
            'lowStoreStockCount' => $lowStoreStockCount,
            'lowStockroomStockCount' => $lowStockroomStockCount,
        ]);
    }
    


    public function inventoryFilterCategory(Request $request)
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

        // Fetch all categories for filtering or display purposes
        $categories = Category::all();
        // Fetch all categories for filtering or display purposes
        $suppliers = Supplier::all();

        return view('inventory.inventory_products_table', [
            'userSQL' => $userSQL,
            'productJoined' => $productJoined,
            'categories' => $categories,
            'suppliers' => $suppliers,
            'lowStoreStockCount' => $lowStoreStockCount,
            'lowStockroomStockCount' => $lowStockroomStockCount,
        ]);
    }



    public function inventoryFilterSupplier(Request $request)
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

        // Fetch all categories for filtering or display purposes
        $categories = Category::all();

        // Fetch all suppliers for filtering or display purposes
        $suppliers = Supplier::all();

        return view('inventory.inventory_products_table', [
            'userSQL' => $userSQL,
            'productJoined' => $productJoined,
            'categories' => $categories,
            'suppliers' => $suppliers,
        ]);
    }
}
