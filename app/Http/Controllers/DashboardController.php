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
        // Check if the user is logged in and is an Administrator
        if (Auth::check()) {
            // Fetch the logged-in user's ID
            $user = auth()->user();
            $user_id = $user->user_id;

            $productJoined = DB::table('inventory')
            ->join('product', 'inventory.product_id', '=', 'product.product_id')
            ->join('stock_transfer', 'stock_transfer.product_id', '=', 'product.product_id')
            ->join('stockroom', 'stock_transfer.to_stockroom_id', '=', 'stockroom.stockroom_id')
            ->join('category', 'product.category_id', '=', 'category.category_id')
            ->leftJoin('product_supplier', 'product.product_id', '=', 'product_supplier.product_id')
            ->leftJoin('supplier', 'product_supplier.supplier_id', '=', 'supplier.supplier_id')
            ->select('inventory.*', 'product.*', 'category.*', 'supplier.*', 'stock_transfer.*', 'stockroom.*')
            ->get();
        
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

        

            // for graph
            // Fetch products with their stockroom and store stock
            $products = Inventory::with('product')
                ->join('product', 'inventory.product_id', '=', 'product.product_id')
                ->join('stock_transfer', 'stock_transfer.product_id', '=', 'product.product_id')
                ->join('stockroom', 'stock_transfer.to_stockroom_id', '=', 'stockroom.stockroom_id')
                ->select('inventory.*', 'product.*', 'stock_transfer.*', 'stockroom.*')
                ->where('inventory.in_stock', '<=', DB::raw('reorder_level'))  // For products that need restocking
                ->orWhere('stockroom.product_quantity', '<=', DB::raw('reorder_level')) // For stockroom products that need restocking
                ->orWhere(DB::raw('inventory.in_stock - stockroom.product_quantity'), '<=', DB::raw('reorder_level'))  // For store products that need restocking
                ->get();

            // Prepare arrays for the graph data
            $productNames = [];
            $storeStock = [];
            $stockroomStock = [];

            $processedProductIds = []; // Array to track processed products

            foreach ($products as $product) {
                // If product has already been processed, skip it
                if (in_array($product->product_id, $processedProductIds)) {
                    continue;
                }

                $storeRestock = $product->in_stock - $product->product_quantity;

                // Prepare data for the chart only if the product hasn't been processed
                $productNames[] = $product->product_name;
                $storeStock[] = $storeRestock;
                $stockroomStock[] = $product->product_quantity;

                // Mark the product as processed to avoid duplication
                $processedProductIds[] = $product->product_id;
            }

            // Join `user`, `credentials`, and `contact_details` using the foreign keys
            $userSQL = DB::table('user')
                ->select('user.*')
                ->where('user_id', '=', $user_id) // Correctly filter using `user_id`
                ->first(); // Get only one user (since it's based on logged-in user)


            // Inventory dashboard graph
            // Data for inventory overview
            $totalProducts = Product::count();
            $totalStockroom = Stockroom::sum('product_quantity');
            $totalStoreStock = Inventory::sum('in_stock') - $totalStockroom;
            $totalStock = Inventory::sum('in_stock');
            $totalSalesQuantity = SalesDetails::sum('sales_quantity');

            // Stock transfer tracking
            // Retrieve stock transfer data, including related product and stockroom information
            $stockTransfers = StockTransfer::with(['product', 'from_stockroom', 'to_stockroom', ])
            ->orderBy('transfer_date', 'asc')
            ->get();

            // Prepare data for the graph (e.g., stock transfer quantities by date)
            $transferData = [];

            foreach ($stockTransfers as $transfer) {
                // Group by transfer date or any other metric you need (e.g., product name)
                $date = $transfer->transfer_date;
                $quantity = $transfer->transfer_quantity;
                $fromStockroom = $transfer->from_stockroom;  // Assuming 'name' is the field for stockroom
                $toStockroom = $transfer->to_stockroom;      // Assuming 'name' is the field for stockroom
                $productName = $transfer->product->product_name;   // Assuming 'product_name' exists

                // You can choose how to structure this data depending on the graph type
                $transferData[] = [
                    'date' => $date,
                    'quantity' => $quantity,
                    'from_stockroom' => $fromStockroom,
                    'to_stockroom' => $toStockroom,
                    'product' => $productName,
                ];
            }

            // Auditor Dashboard Content
            // Fetch all inventory audit data
            $audits = InventoryAudit::with(['inventory', 'user'])->get();

            // Prepare data for the graphs
            $discrepancies = [
                'stockroom' => 0,
                'store' => 0,
            ];
            $quantityOnHand = [];
            $storeQuantities = [];
            $stockroomQuantities = [];
            $newQuantityOnHand = [];
            $newStoreQuantities = [];
            $newStockroomQuantities = [];
            $auditDates = [];
            $discrepancyData = [];

            foreach ($audits as $audit) {
                // Sum discrepancies for stockroom and store
                $discrepancies['stockroom'] += $audit->stockroom_stock_discrepancy;
                $discrepancies['store'] += $audit->store_stock_discrepancy;

                // Prepare data for the line graph of quantity on hand vs store quantity
                $quantityOnHand[] = $audit->previous_quantity_on_hand;
                $storeQuantities[] = $audit->previous_store_quantity;
                $stockroomQuantities[] = $audit->previous_stockroom_quantity;
                $newQuantityOnHand[] = $audit->new_quantity_on_hand;
                $newStoreQuantities[] = $audit->new_store_quantity;
                $newStockroomQuantities[] = $audit->new_stockroom_quantity;
                
                // Prepare audit dates for the discrepancy trends graph
                $auditDates[] = $audit->audit_date;
                array_multisort($auditDates, SORT_ASC);
                
                // Prepare discrepancy reasons and total discrepancies for the doughnut chart
                $discrepancyData[] = $audit->in_stock_discrepancy + $audit->store_stock_discrepancy + $audit->stockroom_stock_discrepancy;
            }

            if ($userSQL && $userSQL->role === "Administrator") {
                return redirect()->route('accounts_table')->with('success', 'You have successfully logged in.');
            }

            if ($userSQL && $userSQL->role === "Salesperson") {
                return redirect()->route('show_profile')->with('success', 'You have successfully logged in.');
            }

            // Check if the user is an Administrator (role is in `credentials` table)
            if ($userSQL && $userSQL->role === "Purchase Manager" || $userSQL->role === "Inventory Manager" || $userSQL->role === "Auditor") {
                // Pass the inventory managers and user role to the view
                return view('dashboard', [
                    'userSQL' => $userSQL,
                    'lowStoreStockCount' => $lowStoreStockCount,
                    'lowStockroomStockCount' => $lowStockroomStockCount,
                    'lowStoreStockMessages' => $lowStoreStockMessages,
                    'lowStockroomStockMessages' => $lowStockroomStockMessages,
                    'totalProducts' => $totalProducts,
                    'totalStockroom'=> $totalStockroom,
                    'totalStoreStock' => $totalStoreStock,
                    'transferData' => $transferData,
                    'productNames' => $productNames,
                    'storeStock' => $storeStock,
                    'stockroomStock' => $stockroomStock,
                    'totalSalesQuantity' => $totalSalesQuantity,
                    'totalStock' => $totalStock,

                    //start chart data for auditor dashboard
                    'audits' => $audits,
                    'discrepancies' => $discrepancies,
                    'quantityOnHand' => $quantityOnHand,
                    'storeQuantities' => $storeQuantities,
                    'stockroomQuantities' => $stockroomQuantities,
                    'newQuantityOnHand' => $newQuantityOnHand,
                    'newStoreQuantities' => $newStoreQuantities,
                    'newStockroomQuantities' => $newStockroomQuantities,
                    'auditDates' => $auditDates,
                    'discrepancyData' => $discrepancyData

                ]);
            } else {
                return redirect('/login')->withErrors('Unauthorized access.');
            }
        }

        return redirect('/login')->withErrors('You must be logged in.');
    }


    // In the controller
    public function getChartData(Request $request)
    {
        $filter = $request->input('filter'); // e.g., weekly, monthly, yearly
    
        $data = [];
        switch ($filter) {
            case 'weekly':
                $data = $this->getWeeklyData();
                break;
            case 'monthly':
                $data = $this->getMonthlyData();
                break;
            case 'yearly':
                $data = $this->getYearlyData();
                break;
            default:
                $data = $this->getDefaultData();
                break;
        }
    
        return response()->json($data);
    }
    



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