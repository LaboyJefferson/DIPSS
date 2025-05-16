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

    .table th, td {
        background-color: #565656 !important; /* Set background color for all table headers */
        color: #ffffff !important;
    }

    .table th, td {
        background-color: #f8f9fa !important; /* Set background color for all table headers */
    }
    
    /*Icon*/
    .circle-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 30px; /* Set the width of the circle */
        height: 30px; /* Set the height of the circle */
        border-radius: 50%; /* Makes it a circle */
        background-color: #dc3545; /* Light red background */
        color: white; /* Icon color */
        font-size: 1.5rem; /* Adjust icon size */
        transition: background-color 0.3s; /* Smooth transition for background color */
    }

    .circle-icon:hover {
        background-color: #c82333; /* Darker red on hover */
    }

    /*Date Picker*/
    .input-group .form-control {
        background-color: #212529; /* Grey background for input */
        color: white; /* White text */
        border-radius: 5px; /* Rounded corners */
        border: none;
    }

    .input-group .form-control:focus {
        background-color: #212529; /* Maintain grey background on focus */
        color: white; /* White text */
        outline: none; /* Remove default outline */
    }

    .input-group .input-group-text {
        background-color: #198754; /* Background for 'to' text */
        color: white; /* Text color */
        border-radius: 5px; /* Rounded corners */
        border: none;
    }

    .btn-success {
        background-color: #28a745; /* Button color */
        border-color: #28a745; /* Border color */
    }

    .table th, td {
        background-color: #565656 !important; /* Set background color for all table headers */
        color: #ffffff !important;
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

     /* for filter */
    .dropdown-submenu .dropdown-menu {
        top: 0;
        left: 100%;
        margin-top: -5px;
        display: none;
        position: absolute;
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
@if(Auth::user()->role == 'Administrator' || Auth::user()->role == 'Inventory Manager') 
    <div class="container-fluid">
        <main class="col-md-9 ml-sm-auto col-lg-10 px-md-4">
            <div class="main-content">
                <!-- Alert Messages -->
            @include('common.alert')
            <div id="alertContainer"></div> <!-- Error message placeholder -->
            
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center">
                <h1 class="h2">Inventory Management</h1>

                <div class="btn-group d-flex justify-content-end">
                    <!-- Restock Store Button with Notification -->
                    <button class="restock-button mb-2" style="margin-right: 1em;" onclick="window.location.href='{{ route('filter_store_restock') }}'">
                        <i class="fas fa-bell"></i> Restock Store
                        @if($lowStoreStockCount > 0)
                            <span class="notification-circle">
                                {{ $lowStoreStockCount }}
                            </span>
                        @endif
                    </button>
    
                    <!-- Restock Stockroom Button with Notification -->
                    <button class="restock-button mb-2" onclick="window.location.href='{{ route('filter_stockroom_restock') }}'">
                        <i class="fas fa-bell"></i> Restock Stockroom
                        @if($lowStockroomStockCount > 0)
                            <span class="notification-circle">
                                {{ $lowStockroomStockCount }}
                            </span>
                        @endif
                    </button>                    
                </div>
            </div>

            {{-- Generate Report --}}
            <form method="POST" action="{{ url('inventory_report') }}" enctype="multipart/form-data" class="mb-4 report-form" id="reportForm">
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


            <form method="POST" action="{{ route('generate_filter_report') }}" enctype="multipart/form-data" class="mb-4 report-form" id="reportForm">
                @csrf
                <div class="input-group mb-3">
                    <input type="hidden" name="letters" value="{{ implode(',', request('letters', [])) }}">
                    <input type="hidden" name="category_ids" value="{{ implode(',', request('category_ids', [])) }}">
                    <input type="hidden" name="supplier_ids" value="{{ implode(',', request('supplier_ids', [])) }}">
                    
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
                            <a type="button" class="btn btn-success mb-2" href="{{ route('inventory_table') }}">Display All</a>
                        </div>

                       <!-- Product Name Dropdown -->
                        <div class="btn-group">
                            <button class="btn btn-success dropdown-toggle mb-2" type="button" id="productNameDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                Product Name
                            </button>
                            <ul class="dropdown-menu p-3" aria-labelledby="productNameDropdown" style="min-width: 250px;">
                                <form id="letterFilterForm" method="GET" action="{{ route('product_name_filter') }}">
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
                                <form id="filterForm" method="GET" action="{{ route('category_filter') }}">
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
                    </div>
                </div>
            </div>

            <!-- Table Section -->
            <table class="table table-responsive">
                <thead>
                    <tr>
                        <th>Product No.</th>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Store Stock</th>
                        <th>Stockroom Stock</th>
                        <th>Reorder Level</th>
                        <th>Description</th>
                        <th colspan="2">Action</th>
                    </tr>
                </thead>
                <tbody id="inventoryTableBody">
                    @forelse($inventoryJoined as $data)
                        <tr>
                            <td>{{ $data->product_id }}</td>
                            <td>
                                @if ($data->image_url)
                                    <img 
                                        src="{{ asset('storage/userImage/' . $data->image_url) }}" 
                                        alt="Product Image" 
                                        class="img-thumbnail" 
                                        style="width: 50px; height: 50px; object-fit: cover;">
                                @else
                                    <div 
                                        class="img-thumbnail d-flex justify-content-center align-items-center" 
                                        style="width: 50px; height: 50px; object-fit: cover;">
                                        <i class="fa-solid fa-box text-muted" style="font-size: 24px;"></i>
                                    </div>
                                @endif
                            </td>
                            <td>{{ $data->product_name }}</td>
                            <td>{{ $data->in_stock - $data->product_quantity }}</td>
                            <td>{{ $data->product_quantity }}</td>
                            <td>{{ $data->reorder_level }}</td>
                            <td>
                                <button type="button" class="btn btn-light" onclick="showDescriptionDetail('{{ $data->category_name }}', '{{ number_format($data->purchase_price_per_unit, 2) }}', '{{ number_format($data->sale_price_per_unit, 2) }}', '{{ $data->unit_of_measure }}', '{{ $data->descriptionArray['color'] ?? 'N/A' }}', '{{ $data->descriptionArray['size'] ?? 'N/A' }}', '{{ $data->descriptionArray['description'] ?? 'N/A' }}', '{{ $data->updated_at }}')">
                                    <strong>more info.</strong>
                                </button>
                            </td>
                            <td>
                                <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#storeRestockModal{{ $data->product_id }}" style="1em">
                                    <strong>Add Store Stock</strong>
                                </button>
                                <button type="button" class="btn btn-warning" onclick="orderLowStock('{{ $data->product_id }}')"><strong>Low Stock</strong></button>
                            </td>
                        </tr>



                        <!-- Store Restock Modal for Each Product -->
                        <div class="modal fade" style="color: black" id="storeRestockModal{{ $data->product_id }}" tabindex="-1" role="dialog" aria-labelledby="storeRestockModalLabel" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="storeRestockModalLabel">Restock Product in Store</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <form id="storeRestockForm{{ $data->product_id }}" action="{{ route('restock_store_product') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="product_id" value="{{ $data->product_id }}">
                                        <input type="hidden" name="stockroom_id" value="{{ $data->stockroom_id }}">
                                        <input type="hidden" name="product_quantity" value="{{ $data->product_quantity }}">
                                        
                                        <?php
                                            $storeStock = $data->in_stock - $data->product_quantity;
                                        ?>

                                        <div class="modal-body">
                                            <strong>Stockroom Details</strong><br>
                                            <strong>Aisle Number:</strong> {{ $data->aisle_number }}<br>
                                            <strong>Cabinet Level:</strong> {{ $data->cabinet_level }}<br>
                                            <strong>Stored Product Quantity:</strong> {{ $data->product_quantity }}<br>
                                            <strong>Category Name:</strong> {{ $data->category_name }}<br>
                                            <div class="form-group mt-3">
                                                <label for="current_quantity">Current Quantity</label>
                                                <strong>{{ $storeStock }}</strong>
                                            </div>
                                            <div class="form-group mt-3">
                                                <label for="transfer_quantity">Transfer Quantity <i>*Required </i></label>
                                                <input type="number" class="form-control" name="transfer_quantity" min="1" required>
                                            </div>
                                        </div>
                                        <!-- Modal Validation Error Alert Message-->
                                        @if ($errors->any() && old('product_id') == $data->product_id)
                                            <div class="alert alert-danger">
                                                <ul>
                                                    @foreach ($errors->all() as $error)
                                                        <li>{{ $error }}</li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                            {{-- for opening the modal --}}
                                            <script>
                                                $(document).ready(function() {
                                                    $('#storeRestockModal{{ $data->product_id }}').modal('show');
                                                });
                                            </script>
                                        @endif

                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                            <button type="submit" class="btn btn-primary">Restock</button>
                                        </div>
                                    </form>
                                    
                                </div>
                            </div>
                        </div>




                    @empty
                        <tr>
                            <td colspan="8" class="text-center">No inventory found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            </div>


           




            
        </main>
    </div>
@else
    <h1 class="alert alert-danger mt-2">Sorry, you do not have access to this page. Please go <button onclick="window.history.back()" class="btn btn-secondary">← Back</button>.</h1>
@endif

<!-- JavaScript for Supplier Details and Restock Modal -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- JavaScript for Supplier Details -->
<script>
    
    // error handling for generate report
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('reportForm');
        const startDateInput = document.getElementById('startDate');
        const endDateInput = document.getElementById('endDate');
        const alertContainer = document.getElementById('alertContainer');

        form.addEventListener('submit', function (event) {
            const startDate = new Date(startDateInput.value);
            const endDate = new Date(endDateInput.value);

            // Clear previous errors
            alertContainer.innerHTML = '';

            if (startDate > endDate) {
                event.preventDefault(); // Prevent form submission

                // Create and append alert message
                const alertMessage = `
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong>Error!</strong> The end date cannot be earlier than the start date.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                `;
                alertContainer.innerHTML = alertMessage;
                return false;
            }
        });

        // Clear the alert container when input values are changed
        [startDateInput, endDateInput].forEach(input => {
            input.addEventListener('change', () => {
                alertContainer.innerHTML = '';
            });
        });
    });

    // sweetalerts for product description
    function showDescriptionDetail(category, purchasedPrice, salePrice, UoM, color, size, description, updatedAt) {
        const descriptionDetails = `
        <strong>Category:</strong> ${category}<br>
        <strong>Purchased Price:</strong> ${purchasedPrice}<br>
        <strong>Sale Price:</strong> ${salePrice}<br>
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
    function orderLowStock(productId) {
        if (!confirm('Are you sure you want to add this product to the purchase order?')) return;

        fetch("{{ route('low_stock') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ product_id: productId })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Server error: ' + response.status);
            }
            return response.json(); // only parse JSON if response is OK
        })
        .then(data => {
            alert(data.message);
        })
        .catch(err => {
            alert('Something went wrong. Please try again.');
            console.error(err);
        });
    }

</script>

@endsection

