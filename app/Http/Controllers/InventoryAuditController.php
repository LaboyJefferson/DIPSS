<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\InventoryAudit;
use App\Models\Inventory;
use App\Models\Category;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class InventoryAuditController extends Controller
{
    public function index() {
        // Check if the user is logged in
        if (!Auth::check()) {
            // If the user is not logged in, redirect to login
            return redirect('/login')->withErrors('You must be logged in.');
        }
    
        $user = Auth::user();
    
        // Aggregate stock_transfer data to get the latest transfer for each product
        $latestStockTransfer = DB::table('stock_transfer')
            ->select(
                'product_id',
                DB::raw('MAX(to_stockroom_id) as latest_stockroom_id')  // Get the latest stockroom transfer for each product
            )
            ->groupBy('product_id');  // Group by product_id to avoid duplicates
    
        // Now join the inventory data with the aggregated stock transfer data
        $inventoryJoined = DB::table('inventory')
            ->join('product', 'inventory.product_id', '=', 'product.product_id')
            ->leftJoinSub($latestStockTransfer, 'stock_transfer', function ($join) {
                $join->on('stock_transfer.product_id', '=', 'product.product_id');
            })
            ->leftJoin('stockroom', 'stock_transfer.latest_stockroom_id', '=', 'stockroom.stockroom_id')  // Join the latest stockroom for each product
            ->join('category', 'product.category_id', '=', 'category.category_id')
            ->leftJoin('product_supplier', 'product.product_id', '=', 'product_supplier.product_id')
            ->leftJoin('supplier', 'product_supplier.supplier_id', '=', 'supplier.supplier_id')
            ->select('inventory.*', 'product.*', 'category.*', 'supplier.*', 'stockroom.*')
            ->orderBy('inventory.updated_at', 'desc')
            ->get();
    
        // Decode the description for each inventory item
        foreach ($inventoryJoined as $item) {
            $item->descriptionArray = json_decode($item->description, true); // Decode the JSON description into an array
        }

        // Fetch all categories for filtering or display purposes
        $categories = Category::all();

        // Fetch all categories for filtering or display purposes
        $suppliers = Supplier::all();
    
        if ($user->role === "Auditor") {
            // Pass the inventory managers and user role to the view
            return view('inventory_audit.audit_inventory_table', [
                'inventoryJoined' => $inventoryJoined,
                'user' => $user,
                'categories' => $categories,
                'suppliers' => $suppliers,
            ]);
        }
    }

    public function productNameFilter(Request $request)
    {
        if (!Auth::check()) {
            return redirect('/login')->withErrors('You must be logged in.');
        }
    
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
    
        // Fetch categories and suppliers for filtering or display purposes
        $categories = Category::all();
        $suppliers = Supplier::all();
    
        return view('inventory_audit.audit_inventory_table', [
            'inventoryJoined' => $inventoryJoined,
            'categories' => $categories,
            'suppliers' => $suppliers,
        ]);
    }
    


    public function CategoryFilter(Request $request)
    {
        if (!Auth::check()) {
            return redirect('/login')->withErrors('You must be logged in.');
        }

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

        // Fetch all categories for filtering or display purposes
        $categories = Category::all();

        // Fetch all categories for filtering or display purposes
        $suppliers = Supplier::all();

        return view('inventory_audit.audit_inventory_table', [
            'inventoryJoined' => $inventoryJoined,
            'categories' => $categories,
            'suppliers' => $suppliers,
        ]);
    }



    public function supplierFilter(Request $request)
    {
        if (!Auth::check()) {
            return redirect('/login')->withErrors('You must be logged in.');
        }

        // Get the selected supplier IDs
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

        return view('inventory_audit.audit_inventory_table', [
            'inventoryJoined' => $inventoryJoined,
            'categories' => $categories,
            'suppliers' => $suppliers,
        ]);
    }
    

    private function generateId($table, $column = 'audit_id')
    {
        // Generate a random 8-digit number
        do {
            $id = random_int(10000000, 99999999);
        } while (DB::table($table)->where($column, $id)->exists()); // Ensure the ID is unique

        return $id;
    }



    public function findDiscrepancies(Request $request)
{
    $request->validate([
        'store_physical.*' => 'required|integer|min:0',
        'system_store.*' => 'required|integer|min:0',
        'store_discrepancy.*' => 'required|integer',

        'stockroom_physical.*' => 'required|integer|min:0',
        'system_stockroom.*' => 'required|integer|min:0',
        'stockroom_discrepancy.*' => 'required|integer',

        'reason.*' => 'nullable|string|max:255',
    ]);

    $userId = auth()->id();
    $date = now();

    // Step 1: Prepare a lookup of latest stockroom per product
    $aggregatedStockTransfer = DB::table('stock_transfer')
        ->select('product_id', DB::raw('MAX(to_stockroom_id) as latest_stockroom_id'))
        ->groupBy('product_id');

    // Step 2: Join inventory + product + latest stockroom
    $inventoryJoined = DB::table('inventory')
        ->join('product', 'inventory.product_id', '=', 'product.product_id')
        ->leftJoinSub($aggregatedStockTransfer, 'stock_transfer', function ($join) {
            $join->on('stock_transfer.product_id', '=', 'product.product_id');
        })
        ->leftJoin('stockroom', 'stock_transfer.latest_stockroom_id', '=', 'stockroom.stockroom_id')
        ->select(
            'inventory.inventory_id',
            'inventory.in_stock',
            'product.product_id',
            'product.product_name',
            'stockroom.product_quantity',
            'stockroom.stockroom_id'
        )
        ->get();

    // Step 3: Create mapping for fast lookup
    $inventoryMap = [];
    foreach ($inventoryJoined as $row) {
        $inventoryMap[$row->inventory_id] = [
            'product_id' => $row->product_id,
            'stockroom_id' => $row->stockroom_id
        ];
    }

    // Step 4: Process discrepancies
    foreach ($request->store_physical as $inventoryId => $physicalStoreCount) {
        $storeDiscrepancy = $request->store_discrepancy[$inventoryId];
        $stockroomDiscrepancy = $request->stockroom_discrepancy[$inventoryId];
        $physicalStockroomCount = $request->stockroom_physical[$inventoryId];

        if ($storeDiscrepancy != 0 || $stockroomDiscrepancy != 0) {
            // Insert audit log
            \App\Models\InventoryAudit::create([
                'user_id' => $userId,
                'inventory_id' => $inventoryId,
                'physical_storestock_count' => $physicalStoreCount,
                'system_storestock_record' => $request->system_store[$inventoryId],
                'storestock_discrepancies' => $storeDiscrepancy,
                'physical_stockroom_count' => $physicalStockroomCount,
                'system_stockroom_record' => $request->system_stockroom[$inventoryId],
                'stockroom_discrepancies' => $stockroomDiscrepancy,
                'in_stock_discrepancies' => $storeDiscrepancy + $stockroomDiscrepancy,
                'discrepancy_reason' => $request->reason[$inventoryId] ?? null,
                'audit_date' => $date,
            ]);

            // Update inventory stock
            \App\Models\Inventory::where('inventory_id', $inventoryId)
                ->update([
                    'in_stock' => $physicalStoreCount + $physicalStockroomCount,
                ]);

            // Update stockroom quantity if applicable
            if (isset($inventoryMap[$inventoryId])) {
                $stockroomId = $inventoryMap[$inventoryId]['stockroom_id'];
                $productId = $inventoryMap[$inventoryId]['product_id'];

                // Update stockroom quantity
                DB::table('stockroom')
                    ->where('stockroom_id', $stockroomId)
                    ->update(['product_quantity' => $physicalStockroomCount]);

                // Create a stock transfer log for audit
                DB::table('stock_transfer')->insert([
                    'product_id' => $productId,
                    'transfer_quantity' => $stockroomDiscrepancy,
                    'transfer_date' => $date,
                    'from_stockroom_id' => null,
                    'to_stockroom_id' => $stockroomId,
                    'user_id' => $userId,
                ]);
            }
        }
    }
    return redirect()->route('inventory.audit.logs')->with('success', 'Audit submitted successfully.');
}



// $auditLogs = \App\Models\InventoryAudit::with('inventory.product')
//         ->where('user_id', $userId)
//         ->orderByDesc('audit_date')
//         ->take(20)
//         ->get();

    
    public function logs() {
        $auditLogs = InventoryAudit::with(['inventory', 'user'])->orderBy('audit_date', 'desc')->get();
        
        // Aggregate stock_transfer to avoid duplicates
        $aggregatedStockTransfer = DB::table('stock_transfer')
        ->select(
            'product_id',
            DB::raw('MAX(to_stockroom_id) as latest_stockroom_id')
        )
        ->groupBy('product_id');
     // Fetch inventory and related details
        $inventoryJoined = DB::table('inventory')
            ->join('product', 'inventory.product_id', '=', 'product.product_id')
            ->leftJoinSub($aggregatedStockTransfer, 'stock_transfer', function ($join) {
                $join->on('stock_transfer.product_id', '=', 'product.product_id');
            })
            ->leftJoin('stockroom', 'stock_transfer.latest_stockroom_id', '=', 'stockroom.stockroom_id')
            ->select(
                'inventory.inventory_id',
                'inventory.in_stock',
                'product.product_id',
                'product.product_name',
                'stockroom.product_quantity',
                'stockroom.stockroom_id'
            )
            ->distinct()
            ->get();
        
        // Fetch all for filtering or display purposes
        $auditors = User::where('user_roles', 'like', '%Auditor%')->get();
        $discrepancyReasons = InventoryAudit::select('discrepancy_reason')->distinct()->get();

        $dateAuditeds = InventoryAudit::all();

        return view('inventory_audit.logs', compact('auditLogs', 'auditors', 'discrepancyReasons', 'dateAuditeds', 'inventoryJoined'));
    }



public function dateAuditedFilter(Request $request)
{
    if (!Auth::check()) {
        return redirect('/login')->withErrors('You must be logged in.');
    }

    // Retrieve the selected dates
    $selectedDates = $request->get('dates', []);

    // Check if dates were selected and split if needed
    $formattedDates = [];
    foreach ($selectedDates as $date) {
        $datesArray = explode(',', $date);  // Split the comma-separated dates
        foreach ($datesArray as $singleDate) {
            // Trim any extra spaces and parse each date individually
            $formattedDates[] = Carbon::parse(trim($singleDate))->format('Y-m-d');
        }
    }

    // Aggregate stock_transfer to avoid duplicates
        $aggregatedStockTransfer = DB::table('stock_transfer')
        ->select(
            'product_id',
            DB::raw('MAX(to_stockroom_id) as latest_stockroom_id')
        )
        ->groupBy('product_id');
     // Fetch inventory and related details
        $inventoryJoined = DB::table('inventory')
            ->join('product', 'inventory.product_id', '=', 'product.product_id')
            ->leftJoinSub($aggregatedStockTransfer, 'stock_transfer', function ($join) {
                $join->on('stock_transfer.product_id', '=', 'product.product_id');
            })
            ->leftJoin('stockroom', 'stock_transfer.latest_stockroom_id', '=', 'stockroom.stockroom_id')
            ->select(
                'inventory.inventory_id',
                'inventory.in_stock',
                'product.product_id',
                'product.product_name',
                'stockroom.product_quantity',
                'stockroom.stockroom_id'
            )
            ->distinct()
            ->get();
    

    // Filter audit logs by selected dates
    $auditLogs = InventoryAudit::with(['inventory', 'user'])
        ->whereIn(DB::raw('DATE(audit_date)'), $formattedDates) // Compare the date part of the timestamp
        ->orderBy('audit_date', 'desc')
        ->get();

    // Fetch data for dropdown filters
    $auditors = User::where('user_roles', 'like', '%Auditor%')->get();
    $discrepancyReasons = InventoryAudit::select('discrepancy_reason')->distinct()->get();

    return view('inventory_audit.logs', compact('auditLogs', 'auditors', 'discrepancyReasons', 'inventoryJoined'));
}


}
