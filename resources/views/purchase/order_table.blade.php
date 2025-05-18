@extends('layouts.app')
@include('common.navbar')

<style>
    body {
        background-image: url('/storage/images/bg-photo.jpeg');
        background-size: cover; /* Cover the entire viewport */
        background-position: center; /* Center the background image */
        background-repeat: no-repeat; /* Prevent the image from repeating */
    }

    /* Main content styling */
    .main-content {
        padding: 20px; /* Add padding for inner spacing */
        margin: 0 20px; /* Add left and right margin */
        color: #fff !important;
        background-color: #565656 !important; 
        border-radius: 5px; /* Slightly rounded corners */
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5); 
    }

    h1.h2 {
        color: #fff; /* Change this to your desired color */
    }
    .table th {
        font-size: 0.95em;
    }

    .table th, td {
        background-color: #565656 !important; /* Set background color for all table headers */
        color: #ffffff !important;
    }

    .btn-success {
        background-color: #28a745; /* Button color */
        border-color: #28a745; /* Border color */
    }

    /* Styling for each restock button */
    .restock-button {
        position: relative; /* This allows absolute positioning for the notification circle */
        background-color: #198754; /* Button background color */
        color: white;
        padding: 7px 12px;
        font-size: 16px;
        border-radius: 6px;
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

    .custom-date-picker {
    appearance: none; /* Removes the default appearance */
    -webkit-appearance: none; /* For Safari */
    position: relative;
    padding: 10px 40px 10px 10px; /* Adds padding to make room for the icon */
    background-color: #000; /* Ensures the input's background matches */
    color: #fff; /* White text color */
    border: 1px solid #fff; /* White border */
    border-radius: 5px;
    width: 28em;
    }

    /* This makes the original calendar icon invisible while keeping it clickable */
    .custom-date-picker::-webkit-calendar-picker-indicator {
        opacity: 0;
        display: block;
        position: absolute;
        right: 10px;
        width: 20px;
        height: 100%;
        cursor: pointer;
    }

    /* Custom white icon overlay */
    .custom-date-picker:before {
        content: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="white" viewBox="0 0 24 24"><path d="M19 4h-1V2h-2v2H8V2H6v2H5c-1.1 0-1.99.9-1.99 2L3 20c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V10h14v10zM7 12h5v5H7z"/></svg>');
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        pointer-events: none; /* Makes the icon non-clickable but allows the input's functionality */
    }
    
</style>

@section('content')
    @if(Auth::user()->role == 'Administrator' || Auth::user()->role == 'Purchase Manager')
        <div class="container-fluid">
            <main class="col-md-9 ml-sm-auto col-lg-10 px-md-4">
                <div class="main-content">
                    <!-- Alert Messages -->
                    @include('common.alert')
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center mb-3">
                        <h1 class="h2">Purchase Order Management</h1>
                        <a class="btn btn-success" href="{{ route('create_purchase_order') }}">+ Create Order</a>
                    </div>

                    {{-- Generate Report --}}
                    <form method="POST" action="{{ url('purchased_report') }}" enctype="multipart/form-data" class="mb-4 report-form" id="reportForm">
                        @csrf
                        <div class="input-group mb-3">
                            <input type="date" class="custom-date-picker" id="startDate" name="start_date" class="form-control" placeholder="Start Date" max="{{ date('Y-m-d') }}"  required>
                            <span class="input-group-text">TO</span>
                            <input type="date" class="custom-date-picker" id="endDate" name="end_date" class="form-control" placeholder="End Date" max="{{ date('Y-m-d') }}"  required>
                            <button type="submit" class="btn btn-success ms-2">
                                <i class="fa-solid fa-print"></i> Generate Report
                            </button>
                        </div>
                    </form>
                    <form method="POST" action="{{ route('generate_purchased_filter_report') }}" enctype="multipart/form-data" class="mb-4 report-form" id="reportForm">
                        @csrf
                        <div class="input-group mb-3">
                            <input type="hidden" name="user_ids" value="{{ implode(',', request('user_ids', [])) }}">
                            <input type="hidden" name="letters" value="{{ implode(',', request('letters', [])) }}">
                            <input type="hidden" name="supplier_ids" value="{{ implode(',', request('supplier_ids', [])) }}">
                            <input type="hidden" name="payment_methods" value="{{ implode(',', request('payment_methods', [])) }}">
                            
                            <!-- Wrap the button inside a div and apply a CSS class for alignment -->
                            <div class="ms-auto">
                                <button type="submit" class="btn btn-success">
                                    <i class="fa-solid fa-print"></i> Generate Filter Report
                                </button>
                            </div>
                        </div>
                    </form>

                    <!-- Dropdown with Buttons -->
                    <div class="row d-flex justify-content-end">
                        <div class="col-auto">
                            <div class="dropdown">

                                <!-- Display all -->
                                <div class="btn-group">
                                    <a type="button" class="btn btn-success mb-2" href="{{ route('purchase_order') }}">Display All</a>
                                </div>

                                <!-- Created By Dropdown -->
                                <div class="btn-group">
                                    <button class="btn btn-success dropdown-toggle mb-2" type="button" id="createdByDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                        Created By
                                    </button>
                                    <div class="dropdown-menu p-3" style="width: 250px">
                                        <form id="createdByFilterForm" method="GET" action="{{ route('created_by_filter') }}">
                                            @foreach($userSQL as $user)
                                                <div class="form-check">
                                                    <input type="checkbox" name="user_ids[]" value="{{ $user->user_id }}" class="form-check-input" id="user_{{ $user->user_id }}">
                                                    <label class="form-check-label" for="user_{{ $user->user_id }}">
                                                        {{ $user->first_name }} {{ $user->last_name }}
                                                    </label>
                                                </div>
                                            @endforeach
                                            <div class="text-center mt-2">
                                                <button type="submit" class="btn btn-success btn-sm">Filter</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>  

                                <!-- Product Name Dropdown -->
                                <div class="btn-group">
                                    <button class="btn btn-success dropdown-toggle mb-2" type="button" id="productNameDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                        Product Name
                                    </button>
                                    <ul class="dropdown-menu p-3" aria-labelledby="productNameDropdown" style="min-width: 250px;">
                                        <form id="letterFilterForm" method="GET" action="{{ route('order_product_filter') }}">
                                            <div class="row">
                                                @foreach(range('A', 'Z') as $letter)
                                                    <div class="col-4">
                                                        <label class="dropdown-item">
                                                            <input type="checkbox" name="letters[]" value="{{ $letter }}"> {{ $letter }}
                                                        </label>
                                                    </div>
                                                @endforeach
                                            </div>
                                            <div class="text-center mt-2">
                                                <button type="submit" class="btn btn-success btn-sm">Filter</button>
                                            </div>
                                        </form>
                                    </ul>
                                </div>
                                
                                <!-- Supplier Dropdown -->
                                <div class="btn-group">
                                    <button class="btn btn-success dropdown-toggle mb-2" type="button" id="supplierDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                        Supplier
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="supplierDropdown">
                                        <form id="filterForm" method="GET" action="{{ route('order_supplier_filter') }}">
                                            @foreach($suppliers as $supplier)
                                                <li>
                                                    <label class="dropdown-item">
                                                        <input type="checkbox" name="supplier_ids[]" value="{{ $supplier->supplier_id }}"> 
                                                        {{ $supplier->company_name }}
                                                    </label>
                                                </li>
                                            @endforeach
                                            <li class="text-center mt-2">
                                                <button type="submit" class="btn btn-success btn-sm">Filter</button>
                                            </li>
                                        </form>
                                    </ul>
                                </div>

                                <!-- Payment Method Dropdown -->
                                <div class="btn-group">
                                    <button class="btn btn-success dropdown-toggle mb-2" type="button" id="paymentMethodDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                        Payment Method
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="paymentMethodDropdown">
                                        <form id="filterForm" method="GET" action="{{ route('payment_method_filter') }}">
                                            @foreach($paymentMethods as $payment)
                                                <li>
                                                    <label class="dropdown-item">
                                                        <input type="checkbox" name="payment_methods[]" value="{{$payment}}"> 
                                                        {{$payment}}
                                                    </label>
                                                </li>
                                            @endforeach
                                            <li class="text-center mt-2">
                                                <button type="submit" class="btn btn-success btn-sm">Filter</button>
                                            </li>
                                        </form>
                                    </ul>
                                </div>

                                <!-- Order Status Dropdown -->
                                <div class="btn-group">
                                    <button class="btn btn-success dropdown-toggle mb-2" type="button" id="orderStatusDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                        Order Status
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="orderStatusDropdown">
                                        <form id="filterForm" method="GET" action="{{ route('order_status_filter') }}">
                                            @foreach($orderStatuses as $status)
                                                <li>
                                                    <label class="dropdown-item">
                                                        <input type="checkbox" name="order_status[]" value="{{$status->order_statuses}}"> 
                                                        {{$status->status_name}}
                                                    </label>
                                                </li>
                                            @endforeach
                                            <li class="text-center mt-2">
                                                <button type="submit" class="btn btn-success btn-sm">Filter</button>
                                            </li>
                                        </form>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Table -->
                    <table class="table table-responsive mt-4">
                        <thead>
                            <tr>
                                <th>Order No.</th>
                                <th>Type</th>
                                <th>Payment Method</th>
                                <th>Total Price</th>
                                <th>Created By</th>
                                <th>Supplier</th>
                                <th>Order Status</th>
                                <th colspan="4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($orders as $order)
                                <tr>
                                    <td>{{ $order->purchase_order_id }}</td>
                                    <td>{{ $order->type }}</td>
                                    <td>{{ $order->payment_method }}</td>
                                    <td>₱{{ number_format($order->total_price, 2) }}</td>
                                    <td>{{ $order->first_name }} {{ $order->last_name}}</td>
                                    <td>{{ $order->company_name }}</td>
                                    <td>{{ $order->status_name }}</td>
                                    <td>
                                        <button type="button" class="btn btn-light" onclick="showOrderInfo({{ $order->purchase_order_id }})">
                                            <i class="fa fa-file-invoice"></i>
                                            <strong>more info.</strong>
                                        </button>
                                    </td>
                                    <td>
                                        <!--
                                            Current order status is "To Order"
                                            This button changes the order status from "To Order" to "Ordered"
                                        -->
                                        @if ($order->order_status == 1) 
                                            <button type="button" class="btn btn-light" onclick="changeStatusToOrdered({{ $order->purchase_order_id }})">
                                                <i class="fa fa-pen"></i>
                                                <strong>Mark as Ordered</strong>
                                            </button>
                                        <!--
                                            Current order status is "Ordered"
                                            Should make order status from "Ordered" to "Delivered"
                                        -->
                                        @elseif($order->order_status == 2)
                                            <button type="button" class="btn btn-light" data-toggle="modal" data-target="#deliveryReportModal{{ $order->purchase_order_id }}" style="1em">
                                                <i class="fa fa-pen"></i>
                                                <strong>Mark as Delivered</strong>
                                            </button>
                                        @elseif($order->order_status == 3)
                                            {{-- Check if quantities and delivered quantities match --}}
                                            @php
                                                $allMatched = true;
                                            @endphp

                                            {{-- Loop through each order item --}}
                                            @foreach($order->order_items as $item)
                                                @php
                                                    if ($item->quantity != $item->delivered_quantity) {
                                                        $allMatched = false;
                                                    }
                                                @endphp
                                            @endforeach

                                            @if(!$allMatched) <!-- show button for unequallity -->
                                                @if (!$order->backorderExists)
                                                    <button type="button" class="btn btn-light" onclick="showBackOrderInfo({{ $order->purchase_order_id }}, {{ $currentUser }})">
                                                        <i class="fa fa-pen"></i>
                                                        <strong>Create Backorder</strong>
                                                    </button>
                                                @else
                                                    <button type="button" class="btn btn-light" disabled>
                                                        <i class="fa fa-pen"></i>
                                                        <strong>Backorder Created</strong>
                                                    </button>
                                                @endif
                                            @elseif($allMatched) <!-- show button for equallity -->
                                                <button type="button" class="btn btn-light" onclick="" disabled>
                                                    <i class="fa fa-pen"></i>
                                                    <strong>Order Complete</strong>
                                                </button>
                                            @endif            
                                        @endif
                                    </td>
                                    <td>
                                        @if($order->order_status == 1)
                                            <button type="button" class="btn btn-edit p-2" title="Edit" onclick="window.location.href='{{ route('edit_order', ['id' => $order->purchase_order_id]) }}' ">
                                                <i class="fa-solid fa-pen-to-square" style="font-size: 1.2rem; color: #007bff;"></i>
                                            </button>
                                        
                                            <button type="button" class="btn btn-delete p-2" title="Delete" data-toggle="modal" data-target="#deleteModal{{ $order->purchase_order_id }}">
                                                <i class="fa-solid fa-trash" style="font-size: 1.2rem; color: #dc3545;"></i>
                                            </button>
                                        @endif
                                    </td>
                                </tr>

                                {{-- Delete Modal --}}
                                <div id="deleteModal{{ $order->purchase_order_id }}" class="modal fade" style="color: black" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            
                                            <div class="modal-header">
                                                <h5 class="modal-title">Confirm Deletion</h5>
                                                <button type="button" class="close custom-close" data-dismiss="modal">&times;</button>
                                            </div>
                                            <div class="modal-body">
                                                <form action="{{ route('delete_order', $order->purchase_order_id) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    
                                                    <input type="hidden" name="user_id" value="{{ $order->user_id }}">
                                                    <input type="hidden" name="purchase_order_id" value="{{ $order->purchase_order_id }}">
                                                    <input type="hidden" name="action" value="delete">
                                                    
                                                    <!-- Admin Password Input -->
                                                    <div class="form-group">
                                                        <label for="password">Current Password <i>*Required </i></label>
                                                        <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                                               id="password_{{ $order->user_id }}" name="password" required>
                                                        <small class="form-text mt-2">
                                                            Note: Please enter your current password for confirmation.
                                                        </small>
                                                        {{-- Check for errors related to this product --}}
                                                        @if (session('delete_error') && session('error_purchase_order_id') == $order->purchase_order_id)
                                                            <div class="alert alert-danger" style="height: 4em;">
                                                                <ul>
                                                                    <li>{{ session('delete_error') }}</li>
                                                                </ul>
                                                            </div>
                                                            <script>
                                                                document.addEventListener('DOMContentLoaded', function() {
                                                                    $('#deleteModal{{ $order->purchase_order_id }}').modal('show');
                                                                });
                                                            </script>
                                                        @endif
                                                    </div>
                                                    
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                                        <button type="submit" class="btn btn-danger">Confirm Delete</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Delivery Report Modal -->
                                <div class="modal fade" id="deliveryReportModal{{ $order->purchase_order_id }}" tabindex="-1" role="dialog" aria-labelledby="deliveryReportModalLabel" aria-hidden="true">
                                    <div class="modal-dialog modal-lg" role="document">
                                        <div class="modal-content text-white" style="background-color: #565656; border-radius: 8px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5);">

                                            <!-- Modal Header -->
                                            <div class="modal-header" style="background-color: #3a8f66;">
                                                <h5 class="modal-title text-white">Delivery Report - Order #{{ $order->purchase_order_id }}</h5>
                                                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>

                                            <!-- Form -->
                                            <form id="deliveryReportForm{{ $order->purchase_order_id }}" action="{{ route('update_order_changes') }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="purchase_order_id" value="{{ $order->purchase_order_id }}">

                                                <!-- Modal Body -->
                                                <div class="modal-body">
                                                    @foreach ($order->order_items as $item)
                                                        @php
                                                            $pid = $item->product->product_id;
                                                            $orderedQuantity = $item->quantity;
                                                        @endphp
                                                        <div class="border rounded p-3 mb-3" style="border-color: #444;">
                                                            <div class="row">
                                                                <!-- Product Info -->
                                                                <div class="col-md-4">
                                                                    <h5 class="mb-1 text-white">{{ $item->product->product_name ?? 'Unknown Product' }}</h5>
                                                                    <p class="mb-1"><strong>Ordered:</strong> {{ $orderedQuantity }}</p>
                                                                
                                                                    <!-- Hidden input for ordered quantity -->
                                                                    <input type="hidden" name="products[{{ $pid }}][ordered_quantity]" value="{{ $orderedQuantity }}">
                                                                </div>                                                                
                                                            </div>

                                                            <!-- Quantities Section -->
                                                            <div class="row mt-3">
                                                                <!-- Delivered Quantity -->
                                                                <div class="col-md-3">
                                                                    <label for="delivered_quantity_{{ $pid }}"><strong>Delivered Quantity</strong> <span class="text-danger">*</span></label>
                                                                    <input type="number"
                                                                        class="form-control delivered-quantity"
                                                                        style="background-color: #212529; color: #fff; border: 1px solid #444;"
                                                                        name="products[{{ $pid }}][delivered_quantity]"
                                                                        id="delivered_quantity_{{ $pid }}"
                                                                        min="0"
                                                                        placeholder="e.g. 10"
                                                                        required>
                                                                </div>

                                                                <!-- Damaged Quantity -->
                                                                <div class="col-md-3">
                                                                    <label for="damaged_quantity_{{ $pid }}"><strong>Damaged Quantity</strong></label>
                                                                    <input type="number"
                                                                        class="form-control damaged-quantity"
                                                                        style="background-color: #212529; color: #fff; border: 1px solid #444;"
                                                                        name="products[{{ $pid }}][damaged_quantity]"
                                                                        id="damaged_quantity_{{ $pid }}"
                                                                        min="0"
                                                                        placeholder="e.g. 2">
                                                                </div>
                                                            </div>

                                                            <!-- Remarks -->
                                                            <div class="mt-3">
                                                                <label for="remarks_{{ $pid }}"><strong>Remarks</strong></label>
                                                                <textarea name="products[{{ $pid }}][remarks]"
                                                                        id="remarks_{{ $pid }}"
                                                                        class="form-control remarks"
                                                                        rows="2"
                                                                        style="background-color: #212529; color: #fff; border: 1px solid #444;"
                                                                        placeholder="Any additional notes..."></textarea>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>

                                                <!-- Modal Footer -->
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" style="background-color: #3a8f66; border: none;" data-dismiss="modal">Close</button>
                                                    <button type="submit" class="btn btn-primary" style="background-color: #3a8f66; border: none;">Submit Report</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </main>
        </div>
    @else
        <h1 class="alert alert-danger mt-2">Sorry, you do not have access to this page. Please go <button onclick="window.history.back()" class="btn btn-secondary">← Back</button>.</h1>
    @endif

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        var currentUser = @json(Auth::id());
        const orders = @json($orders);
        
        function showOrderInfo(orderId) {
            const order = orders.find(o => o.purchase_order_id === orderId);

            if (!order || !order.order_items) {
                Swal.fire('Error', 'No products found for this order.', 'error');
                return;
            }

            let html = `
                <style>
                    .order-table {
                        width: 100%;
                        border-collapse: collapse;
                        font-family: Arial, sans-serif;
                        font-size: 14px;
                        color: #333;
                    }
                    .order-table thead {
                        background-color: #f4f4f4;
                    }
                    .order-table th, .order-table td {
                        padding: 12px 15px;
                        border: 1px solid #ddd;
                        text-align: left;
                    }
                    .order-table tbody tr:hover {
                        background-color: #f9f9f9;
                    }
                    .order-table th {
                        font-weight: bold;
                    }
                    .address-block {
                        text-align: left;
                        font-family: Arial, sans-serif;
                        font-size: 14px;
                        color: #444;
                        margin-bottom: 10px;
                    }
                    .address-block strong {
                        display: inline-block;
                        width: 140px;
                        color: #000;
                    }
                    .address-block p {
                        margin: 4px 0;
                    }
                    .address-label {
                        font-weight: bold;
                        color: #000;
                        display: inline-block;
                        width: 150px;
                    }
                </style>

                 <div class="address-block">
                    <p><strong>Billing Address:</strong> ${order.billing_address || 'N/A'}</p>
                    <p><strong>Shipping Address:</strong> ${order.shipping_address || 'N/A'}</p>
                    <p><strong>Supplier Addresses:</strong><br> ${formatSuppliers(order.company_name, order.supplier_addresses)}</p>
                </div>

                <table class="order-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Quantity</th>
                            <th># of Delivered Items</th>
                            <th>Price</th>
                        </tr>
                    </thead>
                    <tbody>
            `;

            order.order_items.forEach(item => {
                html += `
                    <tr>
                        <td>${item.product?.product_name || 'N/A'}</td>
                        <td>${item.quantity}</td>
                        <td>${item.delivered_quantity || 'Awaiting Delivery'}</td>
                        <td>₱${parseFloat(item.price).toFixed(2)}</td>
                    </tr>
                `;
            });

            html += '</tbody></table>';

            Swal.fire({
                title: `Order #${order.purchase_order_id} - Products`,
                html: html,
                width: '50%',
                confirmButtonText: 'Close'
            });
        }

        // Helper function to pair supplier names and addresses
        function formatSuppliers(namesStr, addressesStr) {
            const names = namesStr?.split(',') || [];
            const addresses = addressesStr?.split(',') || [];

            if (names.length === 0) return 'N/A';

            let result = '<div class="supplier-list"><ul>';
            names.forEach((name, i) => {
                const address = addresses[i] ? addresses[i].trim() : 'N/A';
                result += `<li><strong>${name.trim()}</strong>: ${address}</li>`;
            });
            result += '</ul></div>';
            return result;
        }

        function changeStatusToOrdered(orderId) {
            if (!orderId) return;

            $.ajax({
                url: '{{ route("change_status_to_order") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    order_id: orderId,
                    status_id: 2
                },
                success: function(response) {
                    Swal.fire('Success', response.message, 'success').then(() => {
                            location.reload();
                        });
                },
                error: function(xhr) {
                    alert('Failed to update status: ' + xhr.responseText);
                    console.log(xhr.responseText);
                }
            });
        }

        function showBackOrderInfo(orderId, currentUser) {
            const order = orders.find(o => o.purchase_order_id === orderId);
            console.log(order);

            if (!order || !order.order_items) {
                Swal.fire('Error', 'No products found for this order.', 'error');
                return;
            }

            // Filter order items where quantity is not equal to delivered_quantity
            const backorderedItems = order.order_items.filter(item => item.quantity !== item.delivered_quantity);

            if (backorderedItems.length === 0) {
                Swal.fire('No Backorder Needed', 'All items have been delivered.', 'info');
                return;
            }

            let html = `
                <style>
                    .order-table {
                        width: 100%;
                        border-collapse: collapse;
                        font-family: Arial, sans-serif;
                        font-size: 14px;
                        color: #333;
                    }
                    .order-table thead {
                        background-color: #f4f4f4;
                    }
                    .order-table th, .order-table td {
                        padding: 12px 15px;
                        border: 1px solid #ddd;
                        text-align: left;
                    }
                    .order-table tbody tr:hover {
                        background-color: #f9f9f9;
                    }
                    .order-table th {
                        font-weight: bold;
                    }
                    .address-block {
                        text-align: left;
                        font-family: Arial, sans-serif;
                        font-size: 14px;
                        color: #444;
                        margin-bottom: 10px;
                    }
                    .address-block strong {
                        display: inline-block;
                        width: 140px;
                        color: #000;
                    }
                    .address-block p {
                        margin: 4px 0;
                    }
                    .address-label {
                        font-weight: bold;
                        color: #000;
                        display: inline-block;
                        width: 150px;
                    }
                </style>

                <div class="address-block">
                    <p><strong>Billing Address:</strong> ${order.billing_address || 'N/A'}</p>
                    <p><strong>Shipping Address:</strong> ${order.shipping_address || 'N/A'}</p>
                    <p><strong>Supplier Addresses:</strong><br> ${formatSuppliers(order.company_name, order.supplier_addresses)}</p>
                </div>

                <table class="order-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Quantity</th>
                            <th># of Delivered Items</th>
                            <th>Price</th>
                        </tr>
                    </thead>
                    <tbody>
            `;

            // Create arrays for product_id, quantity_to_be_backordered, and total_price
            const productIds = [];
            const quantityToBeBackordered = [];
            const totalPrice = [];

            backorderedItems.forEach(item => {
                html += `
                    <tr>
                        <td>${item.product?.product_name || 'N/A'}</td>
                        <td>${item.quantity}</td>
                        <td>${item.delivered_quantity || 'Awaiting Delivery'}</td>
                        <td>₱${parseFloat(item.price).toFixed(2)}</td>
                    </tr>
                `;
                
                // Push data into arrays
                productIds.push(item.product_id);  // Assuming 'product_id' exists in your data structure
                quantityToBeBackordered.push(item.quantity - item.delivered_quantity);
                totalPrice.push(parseFloat(item.price) * (item.quantity - item.delivered_quantity));  // Calculating the total price for the backordered quantity
            });

            html += '</tbody></table>';

            Swal.fire({
                title: `Order #${order.purchase_order_id} - Products to be backordered`,
                html: html,
                width: '50%',
                showCancelButton: true,
                confirmButtonText: 'Create Backorder',
                cancelButtonText: 'Close',  // Acting as a close button
                preConfirm: () => {
                    // Perform the AJAX request inside preConfirm
                    return new Promise((resolve, reject) => {
                        $.ajax({
                            url: '{{ route("create_backorder_request") }}',
                            type: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}',
                                order_id: orderId,
                                order_type: 'Backorder',
                                product_id: productIds,
                                payment_method: order.payment_method,
                                billing_address: order.billing_address,
                                shipping_address: order.shipping_address,
                                created_by: currentUser,
                                supplier_id: order.supplier_id.split(',').map(id => parseInt(id.trim())),
                                quantity_to_be_backordered: quantityToBeBackordered,
                                total_price: totalPrice
                            },
                            success: function(response) {
                                Swal.fire('Success', response.message, 'success').then(() => {
                                    location.reload();
                                });
                            },
                            error: function(xhr) {
                                Swal.fire('Error', 'Failed to update status: ' + xhr.responseText, 'error');
                            }
                        });
                    });
                }
            });
        }



    </script>
    
@endsection