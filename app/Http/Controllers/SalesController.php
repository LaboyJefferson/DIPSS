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

    public function showReceipt($id)
    {
        $saleGroup = DB::table('sales')
            ->join('sales_details', 'sales.sales_id', '=', 'sales_details.sales_id')
            ->join('inventory', 'sales_details.inventory_id', '=', 'inventory.inventory_id')
            ->join('product', 'sales_details.product_id', '=', 'product.product_id')
            ->where('sales.sales_id', $id)
            ->select(
                'sales.*',
                'sales_details.*',
                'product.product_name',
                'inventory.*', // Added this line
            )
            ->get();

        if ($saleGroup->isEmpty()) {
            abort(404);
        }

        $mainSale = DB::table('sales')
            ->where('sales_id', $id)
            ->select(
                '*',
                DB::raw('(SELECT SUM(sales_quantity) FROM sales_details WHERE sales_id = '.$id.') as items')
            )
            ->first();

        return view('sales.receipt', [
            'sales' => $saleGroup,
            'mainSale' => $mainSale
        ]);
    }

    public function dashboard()
    {
        // Sales Statistics
        $stats = [
            'today_sales' => DB::table('sales')
                ->whereDate('sales_date', today())
                ->sum('total_amount'),
            
            'total_revenue' => DB::table('sales')
                ->sum('total_amount'),
            
            'avg_order_value' => DB::table('sales')
                ->avg('total_amount'),
            
            'items_sold' => DB::table('sales_details')
                ->sum('sales_quantity'),
            
            'recent_sales' => DB::table('sales')
                ->join('user', 'sales.user_id', '=', 'user.user_id')
                ->select('sales.*', 'user.first_name', 'user.last_name')
                ->orderBy('sales_date', 'desc')
                ->limit(10)
                ->get(),
            
            'monthly_sales' => DB::table('sales')
                ->select(DB::raw('MONTH(sales_date) as month'), 
                        DB::raw('SUM(total_amount) as total'))
                ->groupBy(DB::raw('MONTH(sales_date)'))
                ->orderBy(DB::raw('MONTH(sales_date)'))
                ->get()
        ];

        return view('sales.dashboard', compact('stats'));
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
        $search = $request->input('search');
        
        $products = DB::table('inventory')
            ->join('product', 'inventory.product_id', '=', 'product.product_id')
            ->select(
                'product.product_id as id', // Alias to match frontend expectation
                'product.product_name as name',
                'inventory.inventory_id as hidden_id',
                'inventory.sale_price_per_unit as price',
                'inventory.unit_of_measure as unit',
                'inventory.tax_rate',       // Changed from tax_amount to tax_rate
                'inventory.in_stock',
                'product.description'
            )
            ->where(function($query) use ($search) {
                $query->where('product.product_id', 'like', "%$search%")
                    ->orWhere('product.product_name', 'like', "%$search%");
            })
            ->distinct() // Prevent duplicate entries
            ->get();

        if ($products->isNotEmpty()) {
            return response()->json([
                'success' => true,
                'products' => $products->map(function ($product) {
                    return [
                        'id' => $product->id,
                        'hidden_id' => $product->hidden_id,
                        'name' => $product->name,
                        'price' => (float)$product->price,
                        'tax_rate' => (float)$product->tax_rate,
                        'unit' => $product->unit,
                        'stock' => $product->in_stock,
                        'description' => json_decode($product->description, true)
                    ];
                })
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No matching products found'
        ]);
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'items' => 'required|array',
                'items.*' => 'sometimes|required|array',
                'items.*.product_id' => 'required|integer|exists:product,product_id',
                'items.*.inventory_id' => 'required|integer|exists:inventory,inventory_id',
                'items.*.quantity' => 'required|integer|min:1',
                'items.*.price' => 'required|numeric',
                'subtotal_amnt' => 'required|numeric',
                'discount_amnt' => 'required|numeric',
                'tax_amnt' => 'required|numeric',
                'total_amnt' => 'required|numeric',
                'payment_method' => 'required|string|in:cash,gcash',
                'payment_amount' => 'required|numeric|min:0'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            // This will show validation errors
            return response()->json([
                'status' => 'error',
                'errors' => $e->errors()
            ], 422);
        }

        $userId = Auth::id();
        try {
            DB::transaction(function () use ($userId, $validatedData) {
                $salesId = $this->generateId('sales');
                $totalItems = array_sum(array_column($validatedData['items'], 'quantity'));

                // Insert into sales table
                DB::table('sales')->insert([
                    'sales_id' => $salesId,
                    'user_id' => $userId,
                    'items' => $totalItems,
                    'subtotal' => $validatedData['subtotal_amnt'],
                    'discount' => $validatedData['discount_amnt'],
                    'tax' => $validatedData['tax_amnt'],
                    'total_amount' => $validatedData['total_amnt'],
                    'payment_method' => $validatedData['payment_method'],
                    'sales_date' => now(),
                ]);

                // Process sales details
                $salesDetails = [];
                foreach ($validatedData['items'] as $item) {
                    $inventory = DB::table('inventory')
                        ->where('inventory_id', $item['inventory_id'])
                        ->first();

                    $salesDetails[] = [
                        'sales_details_id' => $this->generateId('sales_details'),
                        'sales_id' => $salesId,
                        'inventory_id' => $item['inventory_id'],
                        'product_id' => $item['product_id'],
                        'sales_quantity' => $item['quantity'],
                        'amount' => $item['price'],
                    ];

                    DB::table('inventory')
                        ->where('inventory_id', $item['inventory_id'])
                        ->decrement('in_stock', $item['quantity']);
                }

                DB::table('sales_details')->insert($salesDetails);
            });
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
        return redirect()->route('sales_table')->with('success', 'Sale processed successfully.');
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
            ->select(
                'inventory.*', 
                'product.*',
                'inventory.purchase_price_per_unit as purchase_price',
                'inventory.profit_margin',
                'inventory.tax_rate'
            )
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
            'profit_margin' => ['required', 'numeric', 'min:0', 'max:100'],
            'tax_rate' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        $inventory = Inventory::where('product_id', $validated['productID'])->first();
        
        // Calculate selling price
        $purchase_price = $inventory->purchase_price_per_unit;
        $profit = $validated['profit_margin']/100;
        $tax = $validated['tax_rate']/100;
        $selling_price = $purchase_price * (1+$profit) * (1+$tax);

        $inventory->update([
            'sale_price_per_unit' => $selling_price,
            'profit_margin' => $profit,
            'tax_rate' => $tax,
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
