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
        
        .input-group .form-control:focus {
            background-color: #212529; /* Maintain grey background on focus */
            color: white; /* White text */
            outline: none; /* Remove default outline */
        }

        .input-group-text {
            background-color: #3a8f66; /* input group background */
            border: none; /* Remove borders */
            color: #fff; /* White text */
        }

        .custom-label {
            display: block;
            width: 100%;
            background-color: #3a8f66;
            color: white;
            padding: 0.5rem 0.75rem;
            border-radius: 4px;
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

        
    </style>

@section('content')
    @if(Auth::user()->role == 'Administrator' || Auth::user()->role == 'Purchase Manager') 
        <div class="container-fluid">
            <main class="col-md-9 ml-sm-auto col-lg-10 px-md-4">
                <div class="main-content">
                    <!-- Alert Messages -->
                    @include('common.alert')
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center mb-3">
                        <h1 class="h2">Product Reorder Management</h1>
                        {{-- <a class="btn btn-success" href="{{ route('purchase.create') }}">+ Add Product</a> --}}
                    </div>

                    <div class="row mb-2">
                        <div class="col-md-5">
                            <label class="custom-label" for="shipping_address">
                                <i class="fa-solid fa-map-marker-alt" style="margin-right: 5px;"></i>
                                Company Address&nbsp;<i>*Required</i>
                            </label>
                            <div class="custom-select">
                                <select id="address_dropdown" class="form-select" name="address_dropdown" required>
                                    @foreach($addresses as $address)
                                        <option value="{{ $address->address }}">{{ $address->address }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @error('shipping_address')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                            <small class="form-text" style="color: #f8f8f8;">
                                Choose the company address where reordered products should be delivered.
                            </small>
                        </div>

                        <div class="col-md-4">
                            <label class="custom-label" for="payment_method">
                                <i class="fa-solid fa-cash-register" style="margin-right: 5px;"></i>Payment Method&nbsp;<i>*Required</i>
                            </label>
                            <div class="custom-select">
                                <select id="payment_method" class="form-select" name="payment_method" required>
                                    <option value="Cash on Delivery (COD)">Cash on Delivery (COD)</option>
                                    <option value="Credit/Debit Card">Credit/Debit Card</option>
                                    <option value="Paypal">PayPal</option>
                                    <option value="GCash">GCash</option>
                                </select>
                            </div>
                            @error('payment_method')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{$message}}</strong>
                                </span>
                            @enderror
                            <small class="form-text" style="color: #f8f8f8;">
                                Select the payment method used for reordering.
                            </small>
                        </div>
                    </div>
                    
                    <p></p>
                    <p>*Here are the products that needs to be reordered.</p>

                    <!-- Dropdown with Buttons -->
                    {{-- <div class="row d-flex justify-content-end">
                        <div class="col-auto">
                            <div class="dropdown">

                                <!-- Display all -->
                                <div class="btn-group">
                                    <a type="button" class="btn btn-success mb-2" href="{{ route('purchase_table') }}">Display All</a>
                                </div>

                                <!-- Product Name Dropdown -->
                                <div class="btn-group">
                                    <button class="btn btn-success dropdown-toggle mb-2" type="button" id="productNameDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                        Product Name
                                    </button>
                                    <ul class="dropdown-menu p-3" aria-labelledby="productNameDropdown" style="min-width: 250px;">
                                        <form id="letterFilterForm" method="GET" action="{{ route('filter_product_name') }}">
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

                                <!-- Category Dropdown -->
                                <div class="btn-group">
                                    <button class="btn btn-success dropdown-toggle mb-2" type="button" id="categoryDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                        Category
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="categoryDropdown">
                                        <form id="filterForm" method="GET" action="{{ route('filter_category') }}">
                                            @foreach($categories as $category)
                                                <li>
                                                    <label class="dropdown-item">
                                                        <input type="checkbox" name="category_ids[]" value="{{ $category->category_id }}"> 
                                                        {{ $category->category_name }}
                                                    </label>
                                                </li>
                                            @endforeach
                                            <li class="text-center mt-2">
                                                <button type="submit" class="btn btn-success btn-sm">Filter</button>
                                            </li>
                                        </form>
                                    </ul>
                                </div>

                                <!-- Supplier Dropdown -->
                                <div class="btn-group">
                                    <button class="btn btn-success dropdown-toggle mb-2" type="button" id="supplierDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                        Supplier
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="supplierDropdown">
                                        <form id="filterForm" method="GET" action="{{ route('filter_supplier') }}">
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
                            </div>
                        </div>
                    </div> --}}

                    {{-- <table class="table table-responsive mt-4">
                        <thead>
                            <tr>
                                <th>Product Name</th>
                                <th>Current Stock</th>
                                <th>Reorder Level</th>
                                <th>Units to Reorder</th>
                                <th colspan="2">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($groupedProducts as $supplierId => $products)
                            <div class="card mb-4">
                                <div class="card-header bg-success text-white">
                                    <h5 class="mb-0">{{ $products->first()->company_name ?? 'Unknown Supplier' }}</h5>
                                </div>
                                <div class="card-body p-0">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Product Name</th>
                                                <th>Current Stock</th>
                                                <th>Reorder Level</th>
                                                <th>Units to Reorder</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($products as $product)
                                                <tr>
                                                    <td>{{ $product->product_name }}</td>
                                                    <td>{{ $product->in_stock }}</td>
                                                    <td>{{ $product->reorder_level }}</td>
                                                    <td>{{ $product->units_to_reorder }}</td>
                                                    <td>
                                                        <button type="button" class="btn btn-light" onclick="">
                                                            <i class="fa fa-file-invoice"></i>
                                                            <strong>See Details</strong>
                                                        </button>
                                                        @if (!in_array($product->product_id, $reorderedProductIds))
                                                            <a href="{{ route('create_reorder', ['product_id' => $product->product_id]) }}" class="btn btn-light">
                                                                <strong>Reorder</strong>
                                                            </a>
                                                        @else
                                                            <a class="btn btn-light" disabled>
                                                                <strong>Reorder created</strong>
                                                            </a>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @empty
                            <p class="text-center">No products need to be reordered currently.</p>
                        @endforelse

                        </tbody>
                    </table>    --}}

                    @forelse($groupedProducts as $supplierId => $products)
                        <div class="card mb-4">
                            <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                                <!-- Supplier Name -->
                                <h5 class="mb-0">{{ $products->first()->company_name ?? 'Unknown Supplier' }}</h5>
                            
                                <!-- Reorder Button -->
                                <form action="{{ route('create_reorder', ['supplier_id' => $supplierId]) }}" method="GET" class="reorder-form">
                                    <!-- Hidden Input for Shipping Address -->
                                    <input type="hidden" name="shipping_address" id="shipping_address_{{ $supplierId }}">
                                    <input type="hidden" name="payment_method" id="payment_method_{{ $supplierId }}">
                                    @php
                                        // Determine if all products for this supplier are "Needs Reorder"
                                        $alreadyReordered = $products->contains(function ($product) use ($reorderedProductIds) {
                                            return in_array($product->product_id, $reorderedProductIds);
                                        });
                                    @endphp

                                    @if (!$alreadyReordered)
                                        <!-- Show reorder button -->
                                        <button type="submit" class="btn btn-light" data-supplier-id="{{ $supplierId }}">
                                            <i class="fa fa-repeat"></i>
                                            <strong>Reorder</strong>
                                        </button>
                                    @else
                                        <!-- Show disabled pending button -->
                                        <button class="btn btn-warning text-white" style="pointer-events: none;">
                                            <i class="fa fa-clock"></i>
                                            <strong>On Order</strong>
                                        </button>
                                    @endif
                                </form>

                            </div>
                            <div class="card-body p-0">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Product Name</th>
                                            <th>Current Stock</th>
                                            <th>Reorder Level</th>
                                            <th>Units to Reorder</th>
                                            <th>Status</th>
                                            {{-- <th colspan="2">Action</th> --}}
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($products as $product)
                                            <tr>
                                                <td>{{ $product->product_name }}</td>
                                                <td>{{ $product->in_stock }}</td>
                                                <td>{{ $product->reorder_level }}</td>
                                                <td>{{ $product->units_to_reorder }}</td>
                                                <td>
                                                    <!-- Status: Needs Reorder or Already Reordered -->
                                                    <span class="badge 
                                                        {{ $product->status == 'Needs Reorder' ? 'badge-warning' : 'badge-success' }}">
                                                        {{ $product->status }}
                                                    </span>
                                                </td>
                                                {{-- <td>
                                                    <button type="button" class="btn btn-light">
                                                        <i class="fa fa-file-invoice"></i>
                                                        <strong>See Details</strong>
                                                    </button>
                                                </td> --}}
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @empty
                        <p class="text-center">No products need to be reordered currently.</p>
                    @endforelse


                </div>
            </main>
        </div>
    @else
        <h1 class="alert alert-danger mt-2">Sorry, you do not have access to this page. Please go <button onclick="window.history.back()" class="btn btn-secondary">← Back</button>.</h1>
    @endif


@endsection

<!-- JavaScript for Supplier Details and Restock Modal -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const addressDropdown = document.getElementById('address_dropdown');
        const payment_method = document.getElementById('payment_method');
        const reorderForms = document.querySelectorAll('.reorder-form');

        reorderForms.forEach(form => {
            form.addEventListener('submit', function (e) {
                const button = form.querySelector('button[data-supplier-id]');
                const supplierId = button.getAttribute('data-supplier-id');
                const hiddenInput = document.getElementById('shipping_address_' + supplierId);
                const hiddenPaymentInput = document.getElementById('payment_method_' + supplierId);
                const selectedAddress = addressDropdown.value;
                const selectedPayment = payment_method.value;

                if (!selectedAddress) {
                    e.preventDefault();
                    alert('Please select a shipping address before submitting a reorder.');
                    return;
                }

                hiddenInput.value = selectedAddress;
                hiddenPaymentInput.value = selectedPayment;
            });
        });
    });

    // sweetalerts for product description
    function showDescriptionDetail(purchasedPrice, UoM, color, size, description, updatedAt) {
        const descriptionDetails = `
        <strong>Purchased Price:</strong> ${purchasedPrice}<br>
        <strong>Unit of Measurement:</strong> ${UoM}<br>
        <strong>Color:</strong> ${color}<br>
        <strong>Size:</strong> ${size}<br>
        <strong>Description:</strong> ${description}<br>
        <strong>Date Updated:</strong> ${updatedAt}<br>
        `;

        Swal.fire({
            title: 'Description',
            html: descriptionDetails,
            icon: 'info',
            confirmButtonText: 'Close'
        });
    }

    function showSupplierDetail(companyName, contactPerson, mobileNumber, email, address) {
        const supplierDetails = `
            <strong>Supplier:</strong> ${companyName}<br>
            <strong>Contact Person:</strong> ${contactPerson}<br>
            <strong>Mobile Number:</strong> ${mobileNumber}<br>
            <strong>Email:</strong> ${email}<br>
            <strong>Address:</strong> ${address}
        `;

        Swal.fire({
            title: 'Supplier Details',
            html: supplierDetails,
            icon: 'info',
            confirmButtonText: 'Close'
        });
    }

    $(document).ready(function() {
        $('[id^="update_supplier_checkbox"]').change(function() {
            const supplierId = $(this).attr('id').match(/\d+/)[0]; // Get supplier ID from checkbox ID
            const supplierDetailsSection = `#supplier_details_section${supplierId}`;

            // Toggle supplier details section visibility based on checkbox state
            $(supplierDetailsSection).toggle(this.checked);
            $(supplierDetailsSection + ' input').prop('required', this.checked);
        });
    });
</script>