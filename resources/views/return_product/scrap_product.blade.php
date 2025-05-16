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

    .table th, td {
        background-color: #565656 !important; /* Set background color for all table headers */
        color: #ffffff !important;
    }

    
</style>

@section('content')
@if(Auth::user()->role === 'Administrator' || Auth::user()->role === 'Inventory Manager')
    <div class="container-fluid">
        <main class="col-md-9 ml-sm-auto col-lg-10 px-md-4">
            <div class="main-content">
                <!-- Alert Messages -->
            @include('common.alert')
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center">
                <h1 class="h2 mb-4">Returned Product</h1>
            </div>
            <!-- Date Range Picker -->
            <form method="POST" action="{{ url('report') }}" enctype="multipart/form-data" class="mb-4 report-form">
                @csrf
                <div class="input-group mb-3">
                    <input type="date" name="start_date" class="form-control" placeholder="Start Date" required>
                    <span class="input-group-text">TO</span>
                    <input type="date" name="end_date" class="form-control" placeholder="End Date" required>
                    <button type="submit" class="btn btn-success ms-2">
                        <i class="fa-solid fa-print"></i> Generate Report
                    </button>
                </div>
            </form>

            <!-- Table Section -->
            <table class="table table-responsive">
                <thead>
                    <tr>
                        <th>Ref. No.</th>
                        <th>Seller</th>
                        <th>Product Name</th>
                        <th>Returned Quantity</th>
                        <th>Total Returned Amount</th>
                        <th>Returned Reason</th>
                        <th>Returned Timestamp</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($returnProductJoined as $data)
                        <tr>
                            <td>{{ $data->sales_id }}</td>
                            <td>{{ $data->first_name }} {{ $data->last_name }}</td>
                            <td>{{ $data->product_name }}</td>
                            <td>{{ $data->return_quantity }}</td>
                            <td>{{ $data->total_return_amount }}</td>
                            <td>{{ $data->return_reason }}</td>
                            <td>{{ $data->return_date }}</td>
                            <form method="POST" action="{{ url('delete/{id}') }}" enctype="multipart/form-data" class="mb-4 report-form">
                                <td><input type="hidden" name="product_name" value="{{ $data->product_name }}"></td>
                                <td><button type="button" class="btn btn-danger" name="scrap" value="{{ $data->sales_id }}">
                                    Scrap
                                </button></td>
                            </form>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">No returned products found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        </main>
    </div>
@endif
@endsection
