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
                        <h1 class="h2">Supplier Management</h1>
                        <a class="btn btn-success" href="{{ route('create_supplier') }}">+ Add Supplier</a>
                    </div>

                    <!-- Dropdown with Buttons -->
                <div class="row d-flex justify-content-end">
                    <div class="col-auto">
                        <div class="dropdown">

                            <!-- Display all -->
                            <div class="btn-group">
                                <a type="button" class="btn btn-success mb-2" href="{{ route('supplier_list') }}">Display All</a>
                            </div>
                        </div>
                    </div>
                </div>

                    <table class="table table-responsive mt-4">
                        <thead>
                            <tr>
                                <th>Supplier No.</th>
                                <th>Company Name</th>
                                <th>Address</th>
                                <th>Contact #</th>
                                <th colspan="3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($suppliers as $supplier)
                                <tr>
                                    <td>{{ $supplier->supplier_id }}</td>
                                    <td>{{ $supplier->company_name }}</td>
                                    <td style="max-width: 230px; overflow-wrap: break-word;">{{ $supplier->address }}</td>
                                    <td>{{ $supplier->mobile_number }}</td>
                                    <td>
                                        <a type="button" class="btn btn-light" href="{{ route('supplier_info', $supplier->supplier_id) }}">
                                            <i class="fa fa-clipboard-list"></i>
                                            <strong>Manage Supplier</strong>
                                        </a>

                                        <a type="button" class="btn btn-edit p-2" title="Edit" href="{{ route('edit_supplier', $supplier->supplier_id) }}">
                                            <i class="fa-solid fa-pen-to-square" style="font-size: 1.2rem; color: #007bff;"></i>
                                        </a>
                                        
                                        <button type="button" class="btn btn-delete p-2" title="Delete" data-toggle="modal" data-target="#deleteModal{{ $supplier->supplier_id }}">
                                            <i class="fa-solid fa-trash" style="font-size: 1.2rem; color: #dc3545;"></i>
                                        </button>
                                    </td>
                                </tr>

                                {{-- Delete Modal --}}
                                <div id="deleteModal{{ $supplier->supplier_id }}" class="modal fade" style="color: black" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            
                                            <div class="modal-header">
                                                <h5 class="modal-title">Confirm Deletion</h5>
                                                <button type="button" class="close custom-close" data-dismiss="modal">&times;</button>
                                            </div>
                                            <div class="modal-body">
                                                <form action="{{ route('delete_supplier', $supplier->supplier_id) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    
                                                    <input type="hidden" name="supplier_id" value="{{ $supplier->supplier_id }}">
                                                    <input type="hidden" name="action" value="delete">
                                                    
                                                    <!-- Admin Password Input -->
                                                    <div class="form-group">
                                                        <label for="password">Current Password <i>*Required </i></label>
                                                        <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                                               id="password_{{ $supplier->supplier_id }}" name="password" required>
                                                        <small class="form-text mt-2">
                                                            Note: Please enter your current password for confirmation.
                                                        </small>
                                                        {{-- Check for errors related to this product --}}
                                                        @if (session('delete_error') && session('error_supplier_id') == $supplier->supplier_id)
                                                            <div class="alert alert-danger" style="height: 4em;">
                                                                <ul>
                                                                    <li>{{ session('delete_error') }}</li>
                                                                </ul>
                                                            </div>
                                                            <script>
                                                                document.addEventListener('DOMContentLoaded', function() {
                                                                    $('#deleteModal{{ $supplier->supplier_id }}').modal('show');
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

                            @endforeach
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

    </script>

@endsection