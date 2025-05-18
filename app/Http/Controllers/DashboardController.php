<?php

namespace App\Http\Controllers;

use App\Models\InventoryAudit;
use App\Models\Inventory;
use App\Models\User;
use App\Models\Product;
use App\Models\SalesDetails;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\StockTransfer;
use App\Models\Stockroom;
use function PHPUnit\Framework\isNull;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\PurchaseOrder;
use App\Models\OrderItems;
use App\Models\Address;

class DashboardController extends Controller
{
    /** Page Access Authentication
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
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

       // Get the currently authenticated user
        $user = Auth::user();

        // Check if the authenticated user's role is 'Inventory Manager'
        if ($user && $user->role === 'Inventory Manager') {
            // Fetch product names for filter dropdown
                $allProductNames = DB::table('product')
                ->select('product_id', 'product_name')
                ->orderBy('product_name', 'asc')
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

            //For discrepancy summary
            $auditLogs = InventoryAudit::with(['inventory', 'user'])->orderBy('audit_date', 'desc')->get();
            
            // Aggregate stock_transfer to avoid duplicates
            $aggregatedStockTransfer = DB::table('stock_transfer')
            ->select(
                'product_id',
                DB::raw('MAX(to_stockroom_id) as latest_stockroom_id')
            )
            ->groupBy('product_id');

            // Fetch inventory and related details
            $productJoined = DB::table('inventory')
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

            // Fetch all categories for filtering or display purposes
            $categories = Category::all();

            return view('dashboard', [
                'userSQL' => $userSQL,
                'inventoryJoined' => $inventoryJoined,
                'categories' => $categories,
                'lowStoreStockCount' => $lowStoreStockCount,
                'lowStockroomStockCount' => $lowStockroomStockCount,
                'allProductNames' => $allProductNames,
                'auditLogs' => $auditLogs, 
                'auditors' => $auditors, 
                'discrepancyReasons' => $discrepancyReasons, 
                'dateAuditeds' => $dateAuditeds, 
                'productJoined' => $productJoined,
            ]);

            return redirect('/login')->withErrors('You must be logged in.');


        } elseif($user && $user->role === 'Purchase Manager') {
            // Fetch order statistics
            $totalOrders = PurchaseOrder::count();
            $totalDelivered = PurchaseOrder::where('order_status', 3)->count();  // Assuming 3 is 'delivered'
            $totalPending = PurchaseOrder::where('order_status', '!=', 3)->count();
            $totalDamaged = OrderItems::whereNotNull('damaged_quantity')->sum('damaged_quantity');

            // Fetch recent orders (latest 5)
            $recentOrders = PurchaseOrder::with(['order_items.product'])
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();

            // Fetch reorder products
            $productJoined = DB::table('inventory')
                ->join('product', 'inventory.product_id', '=', 'product.product_id')
                ->join('category', 'product.category_id', '=', 'category.category_id')
                ->leftJoin('product_supplier', 'product.product_id', '=', 'product_supplier.product_id')
                ->leftJoin('supplier', 'product_supplier.supplier_id', '=', 'supplier.supplier_id')
                ->select('inventory.*', 'product.*', 'category.category_name', 'supplier.supplier_id', 'supplier.company_name')
                ->get();

            $reorderedProductIds = DB::table('order_items')
                ->join('purchase_order', 'order_items.purchase_order_id', '=', 'purchase_order.purchase_order_id')
                ->whereIn('purchase_order.order_status', [1, 2]) // To order or Ordered
                ->pluck('order_items.product_id')
                ->unique()
                ->toArray();

            $products = collect();
            $totalProductsNeedingReorder = 0;  // Initialize the count for products needing reorder
            $totalSuppliersWithReorder = 0;  // Initialize the count for suppliers with products needing reorder
            $totalReorderedProducts = 0;  // Initialize the count for reordered products

            foreach ($productJoined as $product) {
                if ($product->in_stock < $product->reorder_level) {
                    $product->units_to_reorder = max(0, ($product->reorder_level - $product->in_stock) + 10);  // Adjust the reorder buffer here
                    $product->status = in_array($product->product_id, $reorderedProductIds)
                        ? 'Already Reordered'
                        : 'Needs Reorder';
                    
                    // Increment the necessary counters
                    $totalProductsNeedingReorder++;
                    $totalSuppliersWithReorder = $totalSuppliersWithReorder + 1; // Adjust this logic as needed (e.g., track unique suppliers)
                    if (in_array($product->product_id, $reorderedProductIds)) {
                        $totalReorderedProducts++;
                    }
                    
                    $products->push($product);
                }
            }

            $groupedProducts = $products->groupBy('supplier_id');
            $addresses = Address::all();

            return view('dashboard', compact(
                'totalOrders', 'totalDelivered', 'totalPending', 'totalDamaged', 
                'recentOrders', 'groupedProducts', 'addresses',
                'totalProductsNeedingReorder', 'totalSuppliersWithReorder', 'totalReorderedProducts'
            ));

        }

        
    }


    // In the controller
    // public function getChartData(Request $request)
    // {
    //     $filter = $request->input('filter'); // e.g., weekly, monthly, yearly
    
    //     $data = [];
    //     switch ($filter) {
    //         case 'weekly':
    //             $data = $this->getWeeklyData();
    //             break;
    //         case 'monthly':
    //             $data = $this->getMonthlyData();
    //             break;
    //         case 'yearly':
    //             $data = $this->getYearlyData();
    //             break;
    //         default:
    //             $data = $this->getDefaultData();
    //             break;
    //     }
    
    //     return response()->json($data);
    // }
    



// public function destroy(int $id)
// {
//     $userAccount = User::find($id);

//     if (!$userAccount) {
//         return redirect('dashboard')->with('error', 'User not found');
//     }

//     // Check if the logged-in user is an Administrator
//     if (auth()->user()->role === "Administrator") {  // Assuming role is in the credentials table
//         // Check if the user being deleted is not an Administrator or Customer
//         if ($userAccount->role != "Administrator") {
//             $userAccount->delete();
//             return redirect('dashboard')->with('success', 'User account deleted successfully');
//         } else {
//             return redirect('dashboard')->with('error', 'You cannot delete an administrator or customer account');
//         }
//     } else {
//         return redirect('dashboard')->with('error', 'Unauthorized access');
//     }
// }


}