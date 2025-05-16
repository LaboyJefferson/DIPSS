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
    
    /* .input-group .form-control:focus {
        background-color: #212529; /* Maintain grey background on focus */
      /*  color: white; /* White text */
     /*   outline: none; /* Remove default outline */
  /*  }

   /* .input-group .input-group-text {
      /*  background-color: #198754; /* Background for 'to' text */
      /*  color: white; /* Text color */
      /*  border-radius: 5px; /* Rounded corners */
      /*  border: none;
    } */

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
                    <h1 class="h2">Product Management</h1>
                    <a class="btn btn-success" href="{{ route('purchase.create') }}">+ Add Product</a>
                </div>

                <!-- Dropdown with Buttons -->
            <div class="row d-flex justify-content-end">
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

                        <div class="btn-group">
                            <!-- Restock Store Button with Notification -->
                            {{-- <button class="restock-button mb-2" style="margin-right: 1em;" onclick="window.location.href='{{ route('filter_store_restock') }}'">
                                <i class="fas fa-bell"></i> Restock Store
                                @if($lowStoreStockCount > 0)
                                    <span class="notification-circle">
                                        {{ $lowStoreStockCount }}
                                    </span>
                                @endif
                            </button> --}}

                            <!-- Restock Stockroom Button with Notification -->
                            {{-- <button class="restock-button mb-2" onclick="window.location.href='{{ route('filter_stockroom_restock') }}'">
                                <i class="fas fa-bell"></i> Restock Stockroom
                                @if($lowStockroomStockCount > 0)
                                    <span class="notification-circle">
                                        {{ $lowStockroomStockCount }}
                                    </span>
                                @endif
                            </button>                     --}}
                        </div>
                    </div>
                </div>
            </div>

                <table class="table table-responsive mt-4">
                    <thead>
                        <tr>
                            <th>Product No.</th>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Description</th>
                            <th>Supplier Details</th>
                            <th colspan="2">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- @foreach($productJoined as $data) --}}
                        @forelse($productJoined as $data)
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
                            <td>{{ $data->category_name }}</td>
                            <td>
                                <button type="button" class="btn btn-light" onclick="showDescriptionDetail('{{ number_format($data->purchase_price_per_unit, 2) }}', '{{ number_format($data->sale_price_per_unit, 2) }}', '{{ $data->unit_of_measure }}', '{{ $data->descriptionArray['color'] ?? 'N/A' }}', '{{ $data->descriptionArray['size'] ?? 'N/A' }}', '{{ $data->descriptionArray['description'] ?? 'N/A' }}', '{{ $data->updated_at }}')">
                                    <strong>more info.</strong>
                                </button>
                            </td>
                            <td>
                                <button type="button" class="btn btn-light" onclick="showSupplierDetail('{{ $data->company_name ?? 'N/A' }}', '{{ $data->contact_person ?? 'N/A' }}', '{{ $data->mobile_number ?? 'N/A' }}', '{{ $data->email ?? 'N/A' }}', '{{ $data->address ?? 'N/A' }}')">
                                    <strong>more info.</strong>
                                </button>
                            </td>
                            <?php
                                $storeStock = $data->in_stock - $data->product_quantity;
                            ?>
                            <td>
                                <button type="button" class="btn btn-edit p-2" title="Edit" onclick="window.location.href='{{ url('edit_product', ['id' => $data->product_id]) }}'">
                                    <i class="fa-solid fa-pen-to-square" style="font-size: 1.2rem; color: #007bff;"></i>
                                </button>
                                <button type="button" class="btn btn-delete p-2" title="Delete" data-toggle="modal" data-target="#deleteModal{{ $data->product_id }}">
                                    <i class="fa-solid fa-trash" style="font-size: 1.2rem; color: #dc3545;"></i>
                                </button>
                            </td>
                            
                        </tr>

                        {{-- Delete Modal --}}
                        <div id="deleteModal{{ $data->product_id }}" class="modal fade" style="color: black" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    
                                    <div class="modal-header">
                                        <h5 class="modal-title">Confirm Deletion</h5>
                                        <button type="button" class="close custom-close" data-dismiss="modal">&times;</button>
                                    </div>
                                    <div class="modal-body">
                                        <form action="{{ route('delete_product', $data->product_id) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            
                                            <input type="hidden" name="user_id" value="{{ $data->user_id }}">
                                            <input type="hidden" name="product_id" value="{{ $data->product_id }}">
                                            <input type="hidden" name="action" value="delete">
                                            
                                            <!-- Admin Password Input -->
                                            <div class="form-group">
                                                <label for="password">Current Password <i>*Required </i></label>
                                                <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                                       id="password_{{ $data->user_id }}" name="password" required>
                                                <small class="form-text mt-2">
                                                    Note: Please enter your current password for confirmation.
                                                </small>
                                                {{-- Check for errors related to this product --}}
                                                @if (session('delete_error') && session('error_product_id') == $data->product_id)
                                                    <div class="alert alert-danger" style="height: 4em;">
                                                        <ul>
                                                            <li>{{ session('delete_error') }}</li>
                                                        </ul>
                                                    </div>
                                                    <script>
                                                        document.addEventListener('DOMContentLoaded', function() {
                                                            $('#deleteModal{{ $data->product_id }}').modal('show');
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
                                        
                                        <div class="modal-body">
                                            <strong>Stockroom Details</strong><br>
                                            <strong>Aisle Number:</strong> {{ $data->aisle_number }}<br>
                                            <strong>Cabinet Level:</strong> {{ $data->cabinet_level }}<br>
                                            <strong>Stored Product Quantity:</strong> {{ $data->product_quantity }}<br>
                                            <strong>Category Name:</strong> {{ $data->category_name }}<br>
                                            <div class="form-group mt-3">
                                                <label for="transfer_quantity">Transfer Quantity <i>*Required </i></label>
                                                <input type="number" class="form-control" name="transfer_quantity" value="{{ $storeStock }}" min="1" required>
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

                        <!-- Restock Modal for Each Product in the stockroom-->
                        <div class="modal fade" style="color: black" id="stockroomRestockModal{{ $data->product_id }}" tabindex="-1" role="dialog" aria-labelledby="stockroomRestockModalLabel" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="stockroomRestockModalLabel">Restock Product</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <form id="restockForm{{ $data->product_id }}" action="{{ route('restock_product') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="product_id" value="{{ $data->product_id }}">
                                        <input type="hidden" name="supplier_id" value="{{ $data->supplier_id }}">
                                        <input type="hidden" name="stockroom_id" value="{{ $data->stockroom_id }}">
                                        <input type="hidden" name="previous_quantity" value="{{ $data->product_quantity }}">
                                        
                                        <div class="modal-body">
                                            <div class="form-group">
                                                <label for="purchase_price_per_unit">Purchase Price Per Unit <i>*Required </i></label>
                                                <input type="text" class="form-control" name="purchase_price_per_unit" value="{{ $data->purchase_price_per_unit }}" pattern="^\d{1,6}(\.\d{1,2})?$" required>
                                            </div>
                                            <div class="form-group">
                                                <label for="sale_price_per_unit">Sale Price Per Unit <i>*Required </i></label>
                                                <input type="text" class="form-control" name="sale_price_per_unit" value="{{ $data->sale_price_per_unit }}" pattern="^\d{1,6}(\.\d{1,2})?$" required>
                                            </div>
                                            <div class="form-group">
                                                <labelfor="unit_of_measure"> Unit of Measure <i>*Required </i></label>
                                                <div class="custom-select">
                                                    <select name="unit_of_measure" id="unit_of_measure" class="form-select @error('unit_of_measure') is-invalid @enderror" required>
                                                        <option value="pcs">piece</option> 
                                                        <option value="pair">pair</option>
                                                        <option value="set">set</option>
                                                        <option value="box">box</option> 
                                                        <option value="pack">pack</option>
                                                        <option value="kit">kit</option>
                                                        <option value="liter">liter</option>
                                                        <option value="gallon">gallon</option>
                                                        <option value="roll">roll</option>
                                                        <option value="meter">meter</option>
                                                    </select>
                                                </div>
                                                @error('unit_of_measure')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </div>
                                            <div class="form-group">
                                                <label for="quantity">Quantity <i>*Required </i></label>
                                                <input type="text" class="form-control" name="quantity" value="{{ $data->product_quantity }}" pattern="^\d{1,6}$" required>
                                            </div>
                                            <div class="form-group">
                                            <input type="hidden" name="update_supplier" value="0">  <!-- Hidden input to default to false -->
                                                <input type="checkbox" id="update_supplier_checkbox{{ $data->supplier_id }}" name="update_supplier" value="1" {{ old('update_supplier') ? 'checked' : '' }}> <!-- Checkbox -->
                                                <label for="update_supplier_checkbox{{ $data->supplier_id }}">Update supplier <i>- Optional </i></label>
                                            </div>
                                            
                                            <div id="supplier_details_section{{ $data->supplier_id }}" style="display: none;">
                                                <div class="form-group">
                                                    <label for="company_name">Company Name</label>
                                                    <input type="text" class="form-control" name="company_name" value="{{ $data->company_name }}" pattern="^[a-zA-Z0-9\s\-]{1,30}$">
                                                </div>
                                                <div class="form-group">
                                                    <label for="contact_person">Contact Person</label>
                                                    <input type="text" class="form-control" name="contact_person" value="{{ $data->contact_person }}" pattern="^[a-zA-Z\s]{1,30}$">
                                                </div>
                                                <div class="form-group">
                                                    <label for="mobile_number">Mobile Number</label>
                                                    <input type="number" class="form-control" name="mobile_number" value="{{ $data->mobile_number }}" pattern="^09\d{9}$">
                                                </div>
                                                <div class="form-group">
                                                    <label for="email">Email</label>
                                                    <input type="email" class="form-control" name="email" value="{{ $data->email }}">
                                                </div>
                                                <div class="form-group">
                                                    <label for="address">Address</label>
                                                    <input type="text" class="form-control" name="address" value="{{ $data->address }}" pattern="^[a-zA-Z0-9\s.,\-]{1,100}$">
                                                </div>
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
                    
                                            <script>
                                                $(document).ready(function() {
                                                    $('#stockroomRestockModal{{ $data->product_id }}').modal('show');
                                                    
                                                    // Check if the checkbox is checked and show/hide the supplier details section accordingly
                                                    if ($('#update_supplier_checkbox{{ $data->supplier_id }}').is(':checked')) {
                                                        $('#supplier_details_section{{ $data->supplier_id }}').show();
                                                    }
                                                    
                                                    $('[id^="update_supplier_checkbox"]').change(function() {
                                                        const supplierId = $(this).attr('id').match(/\d+/)[0]; // Get supplier ID from checkbox ID
                                                        const supplierDetailsSection = `#supplier_details_section${supplierId}`;

                                                        // Toggle supplier details section visibility based on checkbox state
                                                        $(supplierDetailsSection).toggle(this.checked);
                                                        $(supplierDetailsSection + ' input').prop('required', this.checked);
                                                    });
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
                                <td colspan="13" class="text-center">No products available currently.</td>
                            </tr>
                        @endforelse
                        {{-- @endforeach --}}
                    </tbody>
                </table>   
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