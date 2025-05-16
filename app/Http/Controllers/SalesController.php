<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Product;
use App\Models\Inventory;
use App\Models\Category;
use App\Models\Supplier;

class SalesController extends Controller
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

        // Get Inventory Manager details
        $userSQL = DB::table('user')
            ->select('user.*')
            ->where('role', '=', 'Inventory Manager')
            ->get();

        // Join tables to get sales
        $salesJoined = DB::table('sales_details')
            ->join('sales', 'sales_details.sales_id', '=', 'sales.sales_id')
            ->join('user', 'sales.user_id', '=', 'user.user_id')
            ->join('product', 'sales_details.product_id', '=', 'product.product_id')
            ->join('inventory', 'sales_details.inventory_id', '=', 'inventory.inventory_id')
            ->join('category', 'product.category_id', '=', 'category.category_id')
            ->select('sales.*', 'sales_details.*', 'user.*', 'product.*', 'category.*', 'inventory.*')
            ->where('sales_details.sales_quantity', '!=', 0)
            ->orderBy('sales_date', 'desc')
            ->get();

            $salesGrouped = $salesJoined->groupBy('sales_id');

        // Decode the description for each sales item
        foreach ($salesGrouped as $salesItems) { // $salesItems is a collection of items for each sales_id
            foreach ($salesItems as $item) { // Loop through each item within the salesItems collection
                $item->descriptionArray = json_decode($item->description, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    Log::error('JSON decode error: ' . json_last_error_msg());
                }
            }
        }

        $deadline = now()->subDays(7);

        // Pass the inventory managers and user role to the view
        return view('sales.sales_table', [
            'userSQL' => $userSQL,
            'salesJoined' => $salesJoined,
            'deadline' => $deadline,
            'salesGrouped' => $salesGrouped
        ]);
    }

    private function generateId($table)
    {
        // Generate a random 8-digit number
        do {
            $id = random_int(10000000, 99999999);
        } while (DB::table($table)->where("{$table}_id", $id)->exists()); // Ensure the ID is unique

        return $id;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // Fetch categories and their products
        $productJoined = DB::table('product')
            ->join('category', 'product.category_id', '=', 'category.category_id')
            ->select('product.*', 'category.category_id', 'category.category_name') // Ensure you're selecting necessary fields
            ->get();

        return view('sales.create_sales', ['productJoined' => $productJoined]);
    }

    public function fetchProduct(Request $request)
    {
        $product_id = $request->input('product_id');
        $product = DB::table('inventory')
            ->join('product', 'inventory.product_id', '=', 'product.product_id')
            ->join('stock_transfer', 'stock_transfer.product_id', '=', 'product.product_id')
            ->join('stockroom', 'stock_transfer.to_stockroom_id', '=', 'stockroom.stockroom_id')
            ->join('category', 'product.category_id', '=', 'category.category_id')
            ->select('inventory.*', 'product.*', 'category.category_name', 'stockroom.*', 'stock_transfer.*')
            ->where('product.product_id', $product_id)
            ->first();

        if ($product) {
            // Decode the description JSON if available
            $product->descriptionArray = json_decode($product->description, true);
            $seller = Auth::user()->first_name . ' ' . Auth::user()->last_name; // Logged-in seller's full name
            return response()->json([
                'success' => true,
                'product' => $product,
                'seller' => $seller
            ]);
        } else {
            return response()->json(['success' => false, 'message' => 'Product not found.']);
        }
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Validate the array input for multiple products
        $validatedData = $request->validate([
            'product_id' => 'required|array',
            'product_id.*' => 'required|integer|exists:product,product_id',
            'quantity' => 'required|array',
            'quantity.*' => 'required|integer|min:1',
            'total_amount' => 'required|array',
            'total_amount.*' => 'required|numeric',
            'grand_total_amount' => 'required|numeric',
        ]);

        // Get the user ID (assuming the user is logged in)
        $userId = Auth::id();

        // Start a transaction to ensure consistency between sales and inventory updates
        DB::transaction(function () use ($userId, $validatedData) {
            // Insert a single sale record with current timestamp
            $salesId = $this->generateId('sales');
            DB::table('sales')->insert([
                'sales_id' => $salesId,
                'user_id' => $userId,
                'total_amount' => $validatedData['grand_total_amount'],
                'sales_date' => now(),
            ]);

            $salesDetails = [];

            foreach ($validatedData['product_id'] as $index => $productId) {
                $quantity = $validatedData['quantity'][$index];
                $amount = $validatedData['total_amount'][$index];

                // Retrieve inventory data for the product
                $inventory = DB::table('inventory')
                    ->join('product', 'inventory.product_id', '=', 'product.product_id')
                    ->join('stock_transfer', 'stock_transfer.product_id', '=', 'product.product_id')
                    ->join('stockroom', 'stock_transfer.to_stockroom_id', '=', 'stockroom.stockroom_id')
                    ->select('inventory.*', 'product.*', 'stockroom.*', 'stock_transfer.*')
                    ->where('inventory.product_id', $productId)
                    ->first();

                if (!$inventory || $inventory->in_stock < $quantity) {
                    throw new \Exception("Not enough stock available for product ID: {$productId}");
                }

                $storeStock = $inventory->in_stock - $inventory->product_quantity;

                // Check if additional stock transfer is needed
                if ($storeStock < $quantity) {
                    $transferQuantity = $quantity - abs($storeStock); //abs() will make negative number absolute

                    // Insert into stock_transfer
                    DB::table('stock_transfer')->insert([
                        'stock_transfer_id' => $this->generateId('stock_transfer'),
                        'transfer_quantity' => $transferQuantity,
                        'transfer_date' => now(),
                        'product_id' => $productId,
                        'user_id' => $userId,
                        'from_stockroom_id' => $inventory->stockroom_id,
                    ]);

                    // Update stockroom product quantity
                    DB::table('stockroom')
                        ->where('stockroom_id', $inventory->stockroom_id)
                        ->decrement('product_quantity', $transferQuantity);

                    // Adjust store stock back to zero after transfer
                    $storeStock = 0;
                }

                // Generate a unique sales_details_id for this product
                $salesDetailsId = $this->generateId('sales_details');

                // Prepare data for sales_details
                $salesDetails[] = [
                    'sales_details_id' => $salesDetailsId,
                    'sales_id' => $salesId,
                    'product_id' => $productId,
                    'inventory_id' => $inventory->inventory_id,
                    'sales_quantity' => $quantity,
                    'amount' => $amount,
                ];

                // Update inventory for each product
                DB::table('inventory')
                    ->where('product_id', $productId)
                    ->decrement('in_stock', $quantity);
            }

            // Bulk insert into sales_details
            DB::table('sales_details')->insert($salesDetails);
        });

        return redirect()->route('sales_table')->with('success', 'Sale completed successfully');
    }


    public function search(Request $request)
    {
        if ($request->ajax()) {
            // Get the search input from the request
            $search = $request->get('query');
            //Log::info('Search Query:', ['query' => $search]);

            // Query the sales table with necessary joins
            $salesQuery = DB::table('sales_details')
                ->join('sales', 'sales_details.sales_id', '=', 'sales.sales_id')
                ->join('user', 'sales.user_id', '=', 'user.user_id')
                ->join('product', 'sales_details.product_id', '=', 'product.product_id')
                ->join('inventory', 'sales_details.inventory_id', '=', 'inventory.inventory_id')
                ->join('category', 'product.category_id', '=', 'category.category_id')
                ->select('sales_details.*', 'sales.*', 'user.*', 'product.*', 'category.*', 'inventory.*');

            // Apply search filter if search query is present
            if (!empty($search)) {
                $salesQuery->where(function($query) use ($search) {
                    $query->where('sales_details.sales_details_id', 'LIKE', "%{$search}%")
                        ->orWhere('sales.sales_id', 'LIKE', "%{$search}%")
                        ->orWhere('user.first_name', 'LIKE', "%{$search}%")
                        ->orWhere('user.last_name', 'LIKE', "%{$search}%")
                        ->orWhere('product.product_name', 'LIKE', "%{$search}%")
                        ->orWhere('category.category_name', 'LIKE', "%{$search}%");
                });
            }

            // Execute the query and get the results
            $sales = $salesQuery->get();

            // Log the sales query results
            //Log::info('Sales Query Result:', ['result' => $sales]);

            // Decode the description JSON for each product
            foreach ($sales as $sale) {
                $sale->descriptionArray = json_decode($sale->description, true);
            }

            // Return the results as a JSON response
            return response()->json($sales);
        }

        return view('sales_table');
    }

    public function producSalePriceTable(Request $request)
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

        // Fetch all categories for filtering or display purposes
        $categories = Category::all();

        // Pass the inventory managers and user role to the view
        return view('sales.product_sale_price_table', [
            'userSQL' => $userSQL,
            'productJoined' => $productJoined,
            'categories' => $categories,
        ]);
    }

    public function productNameFilterSale(Request $request)
    {
        if (!Auth::check()) {
            return redirect('/login')->withErrors('You must be logged in.');
        }
    
        // SQL `user` to get Inventory Manager details
        $userSQL = DB::table('user')
            ->select('user.*')
            ->where('role', '=', 'Saleperson')
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
    
        // Fetch categories and suppliers for filtering or display purposes
        $categories = Category::all();
    
        return view('sales.product_sale_price_table', [
            'userSQL' => $userSQL,
            'productJoined' => $productJoined,
            'categories' => $categories,
        ]);
    }

    public function filterPriceLowToHigh(Request $request)
    {
        if (!Auth::check()) {
            return redirect('/login')->withErrors('You must be logged in.');
        }

        $sortOrder = $request->query('sort', 'asc');
        if (!in_array($sortOrder, ['asc', 'desc'])) {
            $sortOrder = 'asc';
        }

        $userSQL = DB::table('user')
            ->select('user.*')
            ->where('role', '=', 'Saleperson')
            ->get();

        $inventoryQuery = DB::table('inventory')
            ->join('product', 'inventory.product_id', '=', 'product.product_id')
            ->join('stock_transfer', 'stock_transfer.product_id', '=', 'product.product_id')
            ->join('stockroom', 'stock_transfer.to_stockroom_id', '=', 'stockroom.stockroom_id')
            ->join('category', 'product.category_id', '=', 'category.category_id')
            ->leftJoin('product_supplier', 'product.product_id', '=', 'product_supplier.product_id')
            ->leftJoin('supplier', 'product_supplier.supplier_id', '=', 'supplier.supplier_id')
            ->select('inventory.*', 'product.*', 'category.*', 'supplier.*', 'stock_transfer.*', 'stockroom.*')
            ->orderBy('inventory.sale_price_per_unit', $sortOrder)
            ->distinct();

        $productJoined = $inventoryQuery->get();
        $productJoined = $productJoined->unique('product_id');

        foreach ($productJoined as $item) {
            $item->descriptionArray = json_decode($item->description, true);
        }

        $categories = Category::all();

        return view('sales.product_sale_price_table', [
            'userSQL' => $userSQL,
            'productJoined' => $productJoined,
            'categories' => $categories,
            'sortOrder' => $sortOrder,
        ]);
    }



    public function productSalePrice(Request $request)
{
    $validated = $request->validate([
        'productID' => ['required', 'exists:product,product_id'],
        'productPrice' => ['required', 'numeric', 'min:1'],
    ]);
        // Update Inventory
        $inventory = Inventory::where('product_id', $validated['productID'])->first();

        // Update inventory details
        $inventory->update([
            'sale_price_per_unit' => $validated['productPrice'],
            'updated_at' => now(),
        ]);

    return redirect()->back()->with('success', 'Product prices updated successfully.');
}






    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // Implement logic to display a specific sales order
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        // Implement logic to show edit form for a specific sales order
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // Implement logic to update a specific sales order
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // Implement logic to delete a specific sales order
    }
}
