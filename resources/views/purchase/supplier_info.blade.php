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
    </style>
@section('content')
    @if(Auth::user()->role == 'Administrator' || Auth::user()->role == 'Purchase Manager') 
        <div class="container-fluid">
            <main class="col-md-9 ml-sm-auto col-lg-10 px-md-4">
                <div class="main-content">
                    <!-- Alert Messages -->
                    @include('common.alert')
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center mb-3">
                        <h1 class="h2">{{ $supplier->company_name }}</h1>
                        <div>
                            <a class="btn btn-success me-2" href="{{ route('supplier_list') }}">Go Back</a>
                            <a class="btn btn-success" href="{{ route('create_product', $supplier->supplier_id) }}">+ Add Product</a>
                        </div>
                    </div>

                    {{-- Supplier's details --}}
                    <h5>Details: </h5>
                    <div>
                        <div class="row mb-2">
                            <div class="col-md-3">
                                ID: <strong>{{ $supplier->supplier_id }}</strong>
                            </div>
                            <div class="col-md-6">
                                Contact Person: <strong>{{ $supplier->contact_person }}</strong>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-md-3">
                                Mobile Number: <strong>{{ $supplier->mobile_number }}</strong>
                            </div>
                            <div class="col-md-6">
                                Email: <strong>{{ $supplier->email }}</strong>
                            </div>
                        </div>
                        <div class="row mb-6">
                            <div class="col-md-9">
                                Address: <strong>{{ $supplier->address }}</strong>
                            </div>
                        </div>
                    </div>

                    {{-- <h3>Products</h3> --}}

                    <!-- Dropdown with Buttons -->
                    <div class="row d-flex justify-content-end">
                        <div class="col-auto">
                            <div class="dropdown">

                                <!-- Display all -->
                                <div class="btn-group">
                                    <a type="button" class="btn btn-success mb-2" href="{{ route('supplier_info', $supplier->supplier_id) }}">Display All</a>
                                </div>

                                <!-- Category Dropdown -->
                                <div class="btn-group">
                                    <button class="btn btn-success dropdown-toggle mb-2" type="button" id="categoryDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                        Category
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="categoryDropdown">
                                        <form id="filterForm" method="GET" action="{{ route('filter_category_supplier_info', $supplier->supplier_id) }}">
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

                    <table class="table table-responsive mt-4">
                        <thead>
                            <tr>
                                <th>Product No.</th>
                                <th>Image</th>
                                <th>Product Name</th>
                                <th>Category</th>
                                <th>Description</th>
                                <th>Price</th>
                                <th colspan="2">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($products as $product)
                                <tr>
                                    <td>{{ $product->product_id }}</td>
                                    <td>
                                        @if ($product->image_url)
                                            <img 
                                                src="{{ asset('storage/userImage/' . $product->image_url) }}" 
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
                                    <td>{{ $product->product_name }}</td>
                                    <td>{{ $product->category->category_name }}</td>
                                    <td>{{ $product->description }}</td>
                                    <td>{{ $product->inventory->purchase_price_per_unit }}</td>
                                    <td>
                                        <a type="button" class="btn btn-edit p-2" title="Edit" href="{{ route('edit_product', ['supplier_id' => $supplier->supplier_id, 'product_id' => $product->product_id]) }}">
                                            <i class="fa-solid fa-pen-to-square" style="font-size: 1.2rem; color: #007bff;"></i>
                                        </a>
                                        <button type="button" class="btn btn-delete p-2" title="Delete" data-toggle="modal" data-target="#deleteModal{{ $product->product_id }}">
                                            <i class="fa-solid fa-trash" style="font-size: 1.2rem; color: #dc3545;"></i>
                                        </button>
                                    </td>
                                </tr>

                                {{-- Delete Modal --}}
                                <div id="deleteModal{{ $product->product_id }}" class="modal fade" style="color: black" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            
                                            <div class="modal-header">
                                                <h5 class="modal-title">Confirm Deletion</h5>
                                                <button type="button" class="close custom-close" data-dismiss="modal">&times;</button>
                                            </div>
                                            <div class="modal-body">
                                                <form action="{{ route('delete_product', ['supplier_id' => $supplier->supplier_id, 'product_id' => $product->product_id]) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    
                                                    <input type="hidden" name="product_id" value="{{ $product->product_id }}">
                                                    <input type="hidden" name="action" value="delete">
                                                    
                                                    <!-- Admin Password Input -->
                                                    <div class="form-group">
                                                        <label for="password">Current Password <i>*Required </i></label>
                                                        <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                                            id="password_{{ $product->product_id }}" name="password" required>
                                                        <small class="form-text mt-2">
                                                            Note: Please enter your current password for confirmation.
                                                        </small>
                                                        {{-- Check for errors related to this product --}}
                                                        @if (session('delete_error') && session('error_product_id') == $product->product_id)
                                                            <div class="alert alert-danger" style="height: 4em;">
                                                                <ul>
                                                                    <li>{{ session('delete_error') }}</li>
                                                                </ul>
                                                            </div>
                                                            <script>
                                                                document.addEventListener('DOMContentLoaded', function() {
                                                                    $('#deleteModal{{ $product->product_id }}').modal('show');
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
                            @empty
                                <tr>
                                    <td colspan="7"><center>No products found.</center></td>
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
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.dropdown-menu form').forEach(function (form) {
                form.addEventListener('click', function (e) {
                    // Prevent closing dropdown on form clicks (e.g., buttons, labels)
                    e.stopPropagation();
                });
            });
        });
    </script>    

@endsection