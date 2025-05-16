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
@if(Auth::user()->role == 'Administrator' || Auth::user()->role == 'Salesperson') 
<div class="container-fluid">
    <main class="col-md-9 ml-sm-auto col-lg-10 px-md-4">
        <div class="main-content">
            <!-- Alert Messages -->
            @include('common.alert')
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center mb-3">
                <h1 class="h2">Add or Update Product Prices</h1>
            </div>

        <div class="d-flex justify-content-end">
            <!-- Product Name Dropdown -->
            <div class="btn-group">
                <button class="btn btn-success dropdown-toggle mb-2" type="button" id="productNameDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    Product Name
                </button>
                <ul class="dropdown-menu p-3" aria-labelledby="productNameDropdown" style="min-width: 250px;">
                    <form id="letterFilterForm" method="GET" action="{{ route('filter_product_name_sale') }}">
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

            <!-- Filter by Price: Low to High -->
            @php
                $nextSort = ($sortOrder ?? 'asc') === 'asc' ? 'desc' : 'asc';
            @endphp
            <div class="btn-group ms-2">
                <a href="{{ route('filter_price_low_to_high', ['sort' => $nextSort]) }}" class="btn btn-success mb-2">
                    Sort by Price: {{ ($sortOrder ?? 'asc') === 'asc' ? 'High to Low' : 'Low to High' }}
                    <i class="fa-solid {{ ($sortOrder ?? 'asc') === 'asc' ? 'fa-arrow-down-wide-short' : 'fa-arrow-up-wide-short' }}"></i>
                </a>
            </div>
        </div>
            



            <!-- Product Price Table -->
            <div class="mt-5">
                    <table class="table mt-3">
                        <thead>
                            <tr>
                                <th>Product No.</th>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Current Price</th>
                                <th>New Price</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($productJoined as $product)
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
                                    <td>
                                        @if($product->sale_price_per_unit)
                                            ₱{{ number_format($product->sale_price_per_unit, 2) }}
                                        @else
                                            <span class="text-muted">No price set</span>
                                        @endif
                                    </td>
                                    <form method="POST" action="{{ route('product_sale_price') }}">
                                        @csrf
                                        <td>
                                            <input type="hidden" name="productID" value="{{ $product->product_id }}">
                                            <input type="number" min="1" name="productPrice" class="form-control" placeholder="Enter price">
                                        </td>
                                        <td>
                                            <button type="submit" class="btn btn-light">
                                                <strong><i class="fa-solid fa-floppy-disk"></i> Save</strong>
                                            </button>
                                        </td>
                                    </form>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
            </div>
        </div>
    </main>

@else
    <h1 class="alert alert-danger mt-2">Sorry, you do not have access to this page. Please go <button onclick="window.history.back()" class="btn btn-secondary">← Back</button>.</h1>
@endif


@endsection
