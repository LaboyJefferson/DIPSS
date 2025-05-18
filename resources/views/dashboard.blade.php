@extends('layouts.app')
<!-- Include the vertical navigation bar -->
@include('common.navbar')

    <style>
        body {
            background-image: url('/storage/images/bg-photo.jpeg');
            background-size: cover; /* Cover the entire viewport */
            background-position: center; /* Center the background image */
            background-repeat: no-repeat; /* Prevent the image from repeating */
        }

        /* Main content styling */
        .content {
            margin-right: 250px; /* Leave space for the sidebar on larger screens */
            padding: 20px;
            overflow: hidden; /* Prevent content overflow */
            transition: margin-right 0.3s; /* Smooth transition when sidebar toggles */
            position: relative; /* Ensure relative positioning for overlays */
            z-index: 1; /* Ensure content is above background */
        }

        .main-content {
            padding: 20px; /* Add padding for inner spacing */
            margin: 0 20px; /* Add left and right margin */
            color: #fff !important;
            background-color: #565656 !important; 
            border-radius: 5px; /* Slightly rounded corners */
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5); 
        }

        @media (max-width: 768px) {
            .content {
                margin-right: 0; /* Remove margin on smaller screens */
            }
        }

        /* Styling for the notification bell and restock buttons */
        .notification-bell {
            display: flex;
            justify-content: flex-end;
            gap: 20px; /* Space between the buttons */
        }

        /* Styling for each restock button */
        .restock-button {
            position: relative; /* This allows absolute positioning for the notification circle */
            background-color: #3a8f66; /* Button background color */
            color: white;
            padding: 10px 20px;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: none;
        }

        .restock-button:hover {
            background-color: #64edbd; /* Darker shade on hover */
            color: #000;
        }

        /* Styling for the notification circle */
        .notification-circle {
            position: absolute;
            top: -5px;
            right: -10px;
            background-color: #64edbd; /* Red background for the notification */
            color: #000;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            text-align: center;
            font-size: 12px;
            line-height: 20px; /* Center the number inside the circle */
        }

    </style>

@section('content')
    <div class="content"> <!-- Add the content class to prevent overlap -->
        @if(Auth::user()->role == "Inventory Manager")
            <div class="container">
                <!-- Alert Messages -->
                @include('common.alert')
                <div class="row justify-content-center">
                    <div class="col">
                        <div class="main-content">

                            <h4>Highest and Lowest Inventory Stocks</h4>
                            <table class="table table-bordered table-responsive text-center align-middle">
                                <thead class="table-success">
                                    <tr>
                                        <th>Product Name</th>
                                        <th>Total Stock</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        // Sort by total stock descending
                                        $sortedInventory = $inventoryJoined->sortByDesc(function($item) {
                                            return $item->in_stock;
                                        });

                                        // Get max stock value
                                        $maxStock = $sortedInventory->first()->in_stock ?? 0;

                                        // Filter to only show:
                                        // - Products with highest stock (equal to maxStock)
                                        // - OR products with zero stock
                                        $filteredInventory = $sortedInventory->filter(function($item) use ($maxStock) {
                                            return $item->in_stock == $maxStock || $item->in_stock == 0;
                                        });
                                    @endphp

                                    @forelse($filteredInventory as $data)
                                        @php
                                            $storeStock = $data->in_stock - $data->product_quantity;
                                            $stockroomStock = $data->product_quantity;
                                            $totalStock = $data->in_stock;

                                            // Define stock status
                                            if ($totalStock == 0) {
                                                $status = 'Out of Stock';
                                                $statusClass = 'text-danger fw-bold';
                                            } else {
                                                $status = 'Highest Stock';
                                                $statusClass = 'text-success fw-bold';
                                            }
                                        @endphp
                                        <tr>
                                            <td>{{ $data->product_name }}</td>
                                            <td>{{ $totalStock }}</td>
                                            <td class="{{ $statusClass }}">{{ $status }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5">No products with most or zero stock.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>

                            <h4>Product/s with the Most Significant Stock Discrepancies</h4>
                            <table class="table table-bordered table-responsive text-center align-middle">
                                <thead class="table-success">
                                    <tr>
                                        <th>Product Name</th>
                                        <th>Total pysical Counted Stocks</th>
                                        <th>Total System Recorded Stocks</th>
                                        <th>Total Discrepancy</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        // Group by inventory_id
                                        $groupedAudits = $auditLogs->groupBy('inventory_id');

                                        // Prepare summary per inventory item
                                        $discrepancySummary = $groupedAudits->map(function($logs) {
                                            $record = $logs->sortByDesc('in_stock_discrepancy')->first();
                                            return (object)[
                                                'product_name' => optional($record->inventory->product)->product_name ?? 'N/A',
                                                'total_physical' => $record->physical_storestock_count + $record->physical_stockroom_count,
                                                'total_system' => $record->system_storestock_record + $record->system_stockroom_record	,
                                                'in_stock_discrepancy' => $record->in_stock_discrepancies,
                                            ];
                                        });

                                        // Get max and min values
                                        $maxDiscrepancy = $discrepancySummary->max('in_stock_discrepancy');
                                        $minDiscrepancy = $discrepancySummary->where('in_stock_discrepancy', '>', 0)->min('in_stock_discrepancy');

                                        // Filter highest and lowest
                                        $highlightedDiscrepancies = $discrepancySummary->filter(function($item) use ($maxDiscrepancy, $minDiscrepancy) {
                                            return $item->in_stock_discrepancy == $maxDiscrepancy || $item->in_stock_discrepancy == $minDiscrepancy;
                                        });
                                    @endphp

                                    @forelse($highlightedDiscrepancies as $discrepancy)
                                        <tr class="{{ $discrepancy->in_stock_discrepancy == $maxDiscrepancy}}">
                                            <td>{{ $discrepancy->product_name }}</td>
                                            <td>{{ $discrepancy->total_physical }}</td>
                                            <td>{{ $discrepancy->total_system }}</td>
                                            <td>{{ $discrepancy->in_stock_discrepancy }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4">No discrepancies found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>


                            <h4>Most Common Discrepancy Reasons</h4>
                            <table class="table table-bordered table-responsive text-center align-middle">
                                <thead class="table-success">
                                    <tr>
                                        <th>Discrepancy Reason</th>
                                        <th>Occurrences</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        // Count frequency of each reason
                                        $reasonCounts = $auditLogs->groupBy('discrepancy_reason')
                                            ->map(function($group) {
                                                return $group->count();
                                            })->sortDesc();

                                        // Get highest count value
                                        $maxCount = $reasonCounts->first();

                                        // Only keep top reasons
                                        $topReasons = $reasonCounts->filter(function($count) use ($maxCount) {
                                            return $count == $maxCount;
                                        });
                                    @endphp

                                    @forelse($topReasons as $reason => $count)
                                        <tr>
                                            <td>{{ $reason ?: 'N/A' }}</td>
                                            <td>{{ $count }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2">No reasons found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @elseif(Auth::user()->role == "Purchase Manager")
        <div class="container-fluid">
            <div class="main-content">
                <!-- Alert Messages -->
                @include('common.alert')

                <!-- KPI Cards Section -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <h5>Total Orders</h5>
                                <p>{{ $totalOrders }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <h5>Delivered Orders</h5>
                                <p>{{ $totalDelivered }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body">
                                <h5>Pending Orders</h5>
                                <p>{{ $totalPending }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-danger text-white">
                            <div class="card-body">
                                <h5>Damaged Quantity</h5>
                                <p>{{ $totalDamaged }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Key Information for Product Reorder Management -->
                <h3 class="h2 mb-3">Reorder Management Information</h3>
                <div class="row mb-4">
                    <!-- Total Products Needing Reorder -->
                    <div class="col-md-4">
                        <div class="card bg-warning text-white">
                            <div class="card-body">
                                <h5>Products Needing Reorder</h5>
                                <p>{{ $totalProductsNeedingReorder }}</p>
                            </div>
                        </div>
                    </div>
                    <!-- Total Suppliers with Products to Reorder -->
                    <div class="col-md-4">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <h5>Suppliers with Products to Reorder</h5>
                                <p>{{ $totalSuppliersWithReorder }}</p>
                            </div>
                        </div>
                    </div>
                    <!-- Total Reordered Products -->
                    <div class="col-md-4">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <h5>Total Reordered Products</h5>
                                <p>{{ $totalReorderedProducts }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Orders Section -->
                <h3 class="h2 mb-3">Recent Orders</h3>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Status</th>
                            <th>Total Quantity</th>
                            <th>Delivered Quantity</th>
                            <th>Date Ordered</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentOrders as $order)
                            <tr>
                                <td>{{ $order->purchase_order_id }}</td>
                                <td>
                                    @if($order->order_status == 3)
                                        <span class="badge badge-success text-dark">Delivered</span>
                                    @else
                                        <span class="badge badge-warning text-dark">Pending</span>
                                    @endif
                                </td>
                                <td>{{ $order->order_items->sum('quantity') }}</td>
                                <td>{{ $order->order_items->sum('delivered_quantity') }}</td>
                                <td>{{ \Carbon\Carbon::parse($order->created_at)->format('Y-m-d') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>

@endsection
