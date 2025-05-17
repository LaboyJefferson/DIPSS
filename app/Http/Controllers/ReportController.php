<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InventoryAudit;
use App\Models\PurchaseOrder;
use App\Models\OrderItems;
use App\Models\OrderStatuses;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Illuminate\Support\Facades\DB;
use App\Models\Inventory;
use Carbon\Carbon;

class ReportController extends Controller
{
    // public function generatePurchasedReport(Request $request)
    // {
    //     // Validate the date range
    //     $request->validate([
    //         'start_date' => 'required|date',
    //         'end_date' => 'required|date|after_or_equal:start_date',
    //     ]);

    //     $startDate = $request->start_date;
    //     $endDate = $request->end_date;

    //     // Build the base query
    //     $query = PurchaseOrder::join('order_items', 'purchase_order.purchase_order_id', '=', 'order_items.purchase_order_id')
    //         ->join('product', 'order_items.product_id', '=', 'product.product_id')
    //         ->join('order_statuses', 'purchase_order.order_status', '=', 'order_statuses.order_statuses')
    //         ->join('order_supplier', 'purchase_order.purchase_order_id', '=' ,'order_supplier.purchase_order_id')
    //         ->join('supplier', 'order_supplier.supplier_id', '=', 'supplier.supplier_id')
    //         ->join('user', 'user.user_id', '=', 'purchase_order.created_by')
    //         ->select('purchase_order.*', 'order_items.*', 'product.*', 'order_statuses.status_name', 'supplier.company_name', 'user.first_name', 'user.last_name')
    //         ->with(['order_items.product', 'supplier', 'user', 'status'])
    //         ->where('purchase_order.order_status', '=', '3')
    //         ->whereBetween('purchase_order.created_at', [$startDate, $endDate])
    //         ->distinct();

    //     $orders = $query->get();

    //     // Collect unique products from order items
    //     $productJoined = $orders->flatMap(function ($order) {
    //         return $order->order_items;
    //     })->unique('product_id');

    //     // Set the report title
    //     $reportTitle = 'Purchased Products Overview from ' . \Carbon\Carbon::parse($startDate)->format('F j, Y') . ' to ' . \Carbon\Carbon::parse($endDate)->format('F j, Y');

    //     $signaturePath = null;

    //     // Return the view with the report data
    //     return view('report.purchased_filtered_report', compact('orders', 'startDate', 'endDate', 'reportTitle', 'signaturePath'));
    // }

    public function generatePurchasedFilteredReport(Request $request)
    {
        // $letters = $request->input('letters');
        // $categoryIds = $request->input('category_ids');
        // $supplierIds = $request->input('supplier_ids');
        

        // Retrieve inputs and ensure they are arrays if provided as comma-separated strings
        $userIds = $this->parseArrayInput($request->input('user_ids'));
        $letters = $this->parseArrayInput($request->input('letters'));
        // $categoryIds = $this->parseArrayInput($request->input('category_ids'));
        $supplierIds = $this->parseArrayInput($request->input('supplier_ids'));
        $paymentMethods = $this->parseArrayInput($request->input('payment_methods'));


        // Validate and filter data accordingly
       // Build the base query
       $query = PurchaseOrder::join('order_items', 'purchase_order.purchase_order_id', '=', 'order_items.purchase_order_id')
        ->join('product', 'order_items.product_id', '=', 'product.product_id')
        ->join('order_statuses', 'purchase_order.order_status', '=', 'order_statuses.order_statuses')
        ->join('order_supplier', 'purchase_order.purchase_order_id', '=' ,'order_supplier.purchase_order_id')
        ->join('supplier', 'order_supplier.supplier_id', '=', 'supplier.supplier_id')
        ->join('user', 'user.user_id', '=', 'purchase_order.created_by')
        ->select('purchase_order.*', 'order_items.*', 'product.*', 'order_statuses.status_name', 'supplier.company_name', 'user.first_name', 'user.last_name')
        ->with(['order_items.product', 'suppliers', 'user', 'status'])
        ->where('purchase_order.order_status', '=', '3')
        ->distinct();

        if ($userIds) {
            $query->whereIn('purchase_order.creted_by', $userIds);
        }

        if ($letters) {
            $query->where(function ($queries) use ($letters) {
                foreach ($letters as $letterName) {
                    $queries->orWhere('product.product_name', 'like', $letterName . '%');
                }
            });
        }

        if ($supplierIds) {
            $query->whereIn('supplier.supplier_id', $supplierIds);
        }

        if ($paymentMethods) {
            $query->whereIn('purchase_order.payment_method', $paymentMethods);
        }

        $orders = $query->get();

        $productJoined = $orders->flatMap(function ($order) {
            return $order->order_items;
        })->unique('product_id');

        $productIds = $productJoined->pluck('product_id')->toArray();

        // Build the report title
        $reportTitleParts = [];
        if ($userIds) {
            $reportTitleParts[] = 'Ordered By';
        }
        if ($letters) {
            $reportTitleParts[] = 'Product Names';
        }
        if ($paymentMethods) {
            $reportTitleParts[] = 'Payment Methods';
        }
        if ($supplierIds) {
            $reportTitleParts[] = 'Product Suppliers';
        }
        
        // Join the parts into a string and build the report title
        $reportTitle = !empty($reportTitleParts) ? 
            'Purchased Products Overview by ' . implode(', ', $reportTitleParts) : 'Inventory Overview';

        $signaturePath = null;

        return view('report.purchased_filtered_report', compact('orders', 'reportTitle', 'signaturePath'));
    }

























    /**
     * Display the inventory report.
     */
    // public function generateReport(Request $request)
    // {
    //     // Validate the date range
    //     $request->validate([
    //         'start_date' => 'required|date',
    //         'end_date' => 'required|date|after_or_equal:start_date',
    //     ]);

    //     $startDate = $request->start_date;
    //     $endDate = $request->end_date;

    //     // Query to get the inventory report data
    //     $inventoryJoined = DB::table('inventory')
    //         ->join('product', 'inventory.product_id', '=', 'product.product_id')
    //         ->join('stock_transfer', 'stock_transfer.product_id', '=', 'product.product_id')
    //         ->join('user', 'stock_transfer.user_id', '=', 'user.user_id')
    //         ->join('stockroom', 'stock_transfer.to_stockroom_id', '=', 'stockroom.stockroom_id')
    //         ->join('category', 'product.category_id', '=', 'category.category_id')
    //         ->join('supplier', 'product.supplier_id', '=', 'supplier.supplier_id')
    //         ->select('inventory.*', 'inventory.updated_at as inventory_updated_at', 'product.*', 'category.*', 'supplier.*', 'stock_transfer.*', 'stockroom.*', 'user.*')
    //         ->whereBetween(DB::raw('DATE(inventory.updated_at)'), [$startDate, $endDate])
    //         ->orderBy('inventory.updated_at', 'desc')
    //         ->get('product.product_id');

    //     $inventoryItems = $inventoryJoined->unique('product_id');

    //     $stockTransferJoined = DB::table('stock_transfer')
    //     ->join('user', 'stock_transfer.user_id', '=', 'user.user_id')
    //     // ->join('sales_details', 'stock_transfer.product_id', '=', 'sales_details.product_id')
    //     ->select('stock_transfer.*', 'user.*')
    //     ->whereBetween(DB::raw('DATE(stock_transfer.transfer_date)'), [$startDate, $endDate])
    //     ->orderBy('transfer_date', 'desc')
    //     ->get();

    //     // Decode description to array
    //     foreach ($inventoryJoined as $item) {
    //         $item->descriptionArray = json_decode($item->description, true);
    //     }

    //     // Set the report title
    //     $reportTitle = 'Inventory Overview from ' . \Carbon\Carbon::parse($startDate)->format('F j, Y') . ' to ' . \Carbon\Carbon::parse($endDate)->format('F j, Y');

    //     $signaturePath = null;

    //     // Return the view with the report data
    //     return view('report.filtered_report', compact('inventoryItems', 'startDate', 'endDate', 'stockTransferJoined', 'reportTitle', 'signaturePath'));
    // }

    public function generateFilteredReport(Request $request)
    {
        // $letters = $request->input('letters');
        // $categoryIds = $request->input('category_ids');
        // $supplierIds = $request->input('supplier_ids');
        

        // Retrieve inputs and ensure they are arrays if provided as comma-separated strings
        $letters = $this->parseArrayInput($request->input('letters'));
        $categoryIds = $this->parseArrayInput($request->input('category_ids'));
        $supplierIds = $this->parseArrayInput($request->input('supplier_ids'));


        // Validate and filter data accordingly
        $inventoryJoined = DB::table('inventory')
        ->join('product', 'inventory.product_id', '=', 'product.product_id')
        ->join('stock_transfer', 'stock_transfer.product_id', '=', 'product.product_id')
        ->join('user', 'stock_transfer.user_id', '=', 'user.user_id')
        ->join('stockroom', 'stock_transfer.to_stockroom_id', '=', 'stockroom.stockroom_id')
        ->join('category', 'product.category_id', '=', 'category.category_id')
        // ->join('supplier', 'product_supplier.supplier_id', '=', 'supplier.supplier_id')
        ->select(
            'inventory.*',
            'inventory.updated_at as inventory_updated_at',
            'product.*',
            'category.*',
            // 'supplier.*',
            'stock_transfer.*',
            'stockroom.*',
            'user.*'
        )
        ->distinct()
        ->orderBy('inventory.updated_at', 'desc');

        if ($letters) {

            $inventoryJoined->where(function ($query) use ($letters) {
                foreach ($letters as $letterName) {
                    $query->orWhere('product.product_name', 'like', $letterName . '%');
                }
            });
        }
        
        if ($categoryIds) {
            $inventoryJoined->whereIn('category.category_id', $categoryIds);
        }

        // if ($supplierIds) {
        //     $inventoryJoined->whereIn('supplier.supplier_id', $supplierIds);
        // }

        $inventoryJoined = $inventoryJoined->get();

        $inventoryItems = $inventoryJoined->unique('product_id');

        $productIds = $inventoryItems->pluck('product_id')->toArray();

        // Get the stock transfer data filtered by the product_ids
        $stockTransferQuery = DB::table('stock_transfer')
            ->join('user', 'stock_transfer.user_id', '=', 'user.user_id')
            ->select('stock_transfer.*', 'user.*')
            ->whereIn('product_id', $productIds)  // Use the extracted product_ids here
            ->orderBy('transfer_date', 'desc');

            // Execute the query for stock transfer data
        $stockTransferJoined = $stockTransferQuery->get();

        // Build the report title
        $reportTitleParts = [];
        if ($letters) {
            $reportTitleParts[] = 'Product Names';
        }
        if ($categoryIds) {
            $reportTitleParts[] = 'Product Categories';
        }
        // if ($supplierIds) {
        //     $reportTitleParts[] = 'Product Suppliers';
        // }

        // Decode description to array
        foreach ($inventoryJoined as $item) {
            $item->descriptionArray = json_decode($item->description, true);
        }
        
        // Join the parts into a string and build the report title
        $reportTitle = !empty($reportTitleParts) ? 
            'Inventory Overview by ' . implode(', ', $reportTitleParts) : 'Inventory Overview';

        $signaturePath = null;

        return view('report.filtered_report', compact('inventoryItems', 'stockTransferJoined', 'reportTitle', 'signaturePath'));
    }

    private function parseArrayInput($input)
    {
        // If the input is a string, convert it to an array
        return is_array($input) ? $input : ($input ? explode(',', $input) : []);
    }

    /**
     * Display the audit inventory report.
     */
    // public function generateAuditReport(Request $request)
    // {
    //     // Validate the date range
    //     $request->validate([
    //         'start_date' => 'required|date',
    //         'end_date' => 'required|date|after_or_equal:start_date',
    //     ]);

    //     $startDate = $request->start_date;
    //     $endDate = $request->end_date;

    //     $auditLogs = InventoryAudit::with(['inventory', 'user'])
    //     ->whereBetween(DB::raw('DATE(audit_date)'), [$startDate, $endDate])
    //     ->orderBy('audit_date', 'desc')
    //     ->get();

    //     // Extract inventory IDs from the audit logs
    //     $inventoryIds = $auditLogs->pluck('inventory_id')->unique();

    //     // Query to get the inventory report data
    //     $inventoryJoined = DB::table('inventory')
    //         ->join('product', 'inventory.product_id', '=', 'product.product_id')
    //         ->join('stock_transfer', 'stock_transfer.product_id', '=', 'product.product_id')
    //         ->join('stockroom', 'stock_transfer.to_stockroom_id', '=', 'stockroom.stockroom_id')
    //         ->join('category', 'product.category_id', '=', 'category.category_id')
    //         ->join('supplier', 'product.supplier_id', '=', 'supplier.supplier_id')
    //         ->select('inventory.*', 'product.*', 'category.*', 'supplier.*', 'stock_transfer.*', 'stockroom.*')
    //         ->whereIn('inventory.inventory_id', $inventoryIds)
    //         ->get();

    //     // Set the report title
    //     $reportTitle = 'Stock Discrepancy: An Audit Report from ' . \Carbon\Carbon::parse($startDate)->format('F j, Y') . ' to ' . \Carbon\Carbon::parse($endDate)->format('F j, Y');

    //     $signaturePath = null;

    //     // Return the view with the report data
    //     return view('report.audit_inventory_report', compact('auditLogs', 'startDate', 'endDate', 'inventoryJoined', 'reportTitle', 'signaturePath'));
    // }

    public function generateAuditFilteredReport(Request $request)
    {
        // Get the input data (these should be arrays, but they might come in as strings)
        $auditorIds = $this->parseArrayInput($request->input('user_ids', []));
        $selectedReasons = $this->parseArrayInput($request->input('discrepancy_reasons', []));
        $selectedDates = $this->parseArrayInput($request->input('dates', []));
        
        // Ensure selectedDates is an array (if it's null, default to empty array)
        $selectedDates = is_array($selectedDates) ? $selectedDates : [];
    
        // Format the dates if needed
        $formattedDates = [];
        foreach ($selectedDates as $date) {
            if (empty($date)) {
                continue; // Skip empty values
            }
    
            $datesArray = explode(',', $date); // Split the comma-separated dates
            foreach ($datesArray as $singleDate) {
                try {
                    $formattedDates[] = Carbon::parse(trim($singleDate))->format('Y-m-d');
                } catch (\Exception $e) {
                    continue;
                }
            }
        }
    
        // Ensure $formattedDates is always an array
        $formattedDates = is_array($formattedDates) ? $formattedDates : [];
    
        // Query the audit logs based on the filters
        $auditLogs = InventoryAudit::with(['inventory', 'user'])
            ->when($auditorIds, function ($query) use ($auditorIds) {
                return $query->whereIn('user_id', $auditorIds);
            })
            ->when($selectedReasons, function ($query) use ($selectedReasons) {
                return $query->whereIn('discrepancy_reason', $selectedReasons);
            })
            ->when($formattedDates, function ($query) use ($formattedDates) {
                return $query->whereIn(DB::raw('DATE(audit_date)'), $formattedDates);
            })
            ->orderBy('audit_date', 'desc')
            ->get();

        // Build the report title
        $reportTitleParts = [];
        if ($auditorIds) {
            $reportTitleParts[] = 'Auditors';
        }
        if ($selectedReasons) {
            $reportTitleParts[] = 'Discrepancy Reasons';
        }
        if ($formattedDates) {
            $reportTitleParts[] = 'Dates';
        }
        
        // Join the parts into a string and build the report title
        $reportTitle = !empty($reportTitleParts) ? 
            'Stock Discrepancy: A Report Filtered By ' . implode(', ', $reportTitleParts) : 'Stock Discrepancy Report';
        
        // Extract inventory IDs from the audit logs
        $inventoryIds = $auditLogs->pluck('inventory_id')->unique();

        // Query to get the inventory report data
        $inventoryJoined = DB::table('inventory')
            ->join('product', 'inventory.product_id', '=', 'product.product_id')
            ->join('stock_transfer', 'stock_transfer.product_id', '=', 'product.product_id')
            ->join('stockroom', 'stock_transfer.to_stockroom_id', '=', 'stockroom.stockroom_id')
            ->join('category', 'product.category_id', '=', 'category.category_id')
            ->select('inventory.*', 'product.*', 'category.*', 'stock_transfer.*', 'stockroom.*')
            ->whereIn('inventory.inventory_id', $inventoryIds)
            ->get();

        $signaturePath = null;
    
        return view('report.audit_inventory_report', compact('auditLogs', 'inventoryJoined', 'reportTitle', 'signaturePath'));
    }

    public function uploadSignature(Request $request)
{
    // Validate the uploaded file
    $request->validate([
        'signature' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
    ]);

    // Get the file from the request
    $file = $request->file('signature');

    // Generate a unique file name based on the original file name and current timestamp
    $fileNameWithExt = $file->getClientOriginalName();
    $fileName = pathinfo($fileNameWithExt, PATHINFO_FILENAME);
    $extension = $file->getClientOriginalExtension();
    $fileNameToStore = $fileName . '_' . time() . '.' . $extension;

    // Move the file to the public directory (e.g., 'public/signatures')
    $filePath = public_path('signatures/' . $fileNameToStore);
    $file->move(public_path('signatures'), $fileNameToStore);

    // Return the URL of the uploaded file for display
    return response()->json([
        'status' => 'success',
        'signature_url' => asset('signatures/' . $fileNameToStore), // Make the file accessible via a URL
    ]);
}

    
    
    
}
