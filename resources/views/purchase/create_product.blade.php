@extends('layouts.app')
@include('common.navbar')

@section('content')
    <style>
        body {
            background-image: url('/storage/images/bg-photo.jpeg');
            background-size: cover; /* Cover the entire viewport */
            background-position: center; /* Center the background image */
            background-repeat: no-repeat; /* Prevent the image from repeating */
            background-color: #1a1a1a; /* Dark background */
            color: #fff; /* Light text color */
        }
        .card {
            
            background-color: #565656; /* Card background */
            border: none; /* Remove border */
            border-radius: 8px; /* Rounded corners */
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5); 
        }
        .input-group-text {
            background-color: #3a8f66; /* input group background */
            border: none; /* Remove borders */
            color: #fff; /* White text */
        }
        .btn-primary {
            background-color: #3a8f66; /* Green button */
            color: #fff; /* White text */
            border: none; /* Remove button borders */
        }
        .btn-primary:hover {
            background-color: #2f6b5a; /* Darker green on hover */
        }
        .btn-secondary {
            background-color: #3a8f66; /* Dark background for role selection */
            color: #fff; /* White text */
            border: none;
        }
        .btn-secondary:hover {
            background-color: #2f6b5a; /* Darker green on hover */
        }
        .form-control {
            background-color: #fff; /* White input background */
            color: #000; /* Black text */
            border: 1px solid #444; /* Subtle border */
        }
        .form-control:focus {
            background-color: #fff; /* Focus background */
            color: #000; /* Black text */
            border-color: #3a8f66; /* Green border on focus */
            box-shadow: none; /* Remove default shadow */
        }

        .form-control {
            background-color: #212529; /* Change input background */
            color: #fff; /* White text */
            border: 1px solid #444; 
            border-radius: 4px; /* Optional: Rounded corners */
        }
        .form-control:focus {
            background-color: #212529; 
            color: #fff; 
            border-color: #3a8f66; 
            box-shadow: none; 
        }

        /* Placeholder styling */
        .form-control::placeholder {
            color: #bbb; /* Light grey for placeholder text */
            opacity: 1; /* Ensures the opacity is fully opaque */
        }
        .text {
            color: #fff;
        }

        /* Custom styling for the select dropdown */
        .custom-select select {
            background-color: #212529; /* Black background for select */
            color: white; /* White text */
            border: 1px solid #444; /* Subtle border */
            appearance: none; /* Remove default arrow */
            border-radius: 4px;
            position: relative;
        }

        /* Add a custom arrow using a background image or pseudo-element */
        .custom-select {
            position: relative;
        }

        .custom-select::after {
            content: '▼'; /* Custom arrow */
            color: white; /* White arrow */
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            pointer-events: none;
        }

        /* Style dropdown options */
        .custom-select select option {
            background-color: #333; /* Dark background for options */
            color: white; /* White text */
            padding: 8px;
        }

        /* On hover, options can change color */
        .custom-select select option:hover {
            background-color: #3a8f66; /* Slightly greenish background on hover */
            color: white;
        }

        /* for dropdwonn supplier */
        .d-none {
            display: none;
        }

    </style>

    @if(Auth::user()->role == 'Purchase Manager') 
        <div class="col-md-9 ml-sm-auto col-lg-10 px-md-4">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header text-center text-white" style="background-color: #3a8f66; color:#fff; font-weight: bold; ">{{ __('Add Product') }}</div>
                        <div class="card-body">
                            <!-- Alert Messages -->
                            @include('common.alert')
                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <form method="POST" action="{{ route('store_product', $supplier->supplier_id) }}" enctype="multipart/form-data">
                                @csrf

                                <!-- Product Details -->

                                <div class="row mb-2">
                                    <div class="col-md-4">
                                        <label class="input-group-text" for="product_name">
                                            <i class="fa-solid fa-image" style="margin-right: 5px;"></i>Product Image&nbsp;<i>*Required</i>
                                        </label>
                                        <input type="file" id="image_url" name="image_url" class="form-control @error('image_url') is-invalid @enderror"  value="{{ old('image_url') }}" accept="image/*" required>
                                        @error('image_url')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>

                                    <div class="col-md-4">
                                        <label class="input-group-text" for="product_name">
                                            <i class="fa-solid fa-box-open" style="margin-right: 5px;"></i>Product Name&nbsp;<i>*Required</i>
                                        </label>
                                        <input id="product_name" type="text" class="form-control @error('product_name') is-invalid @enderror" name="product_name" value="{{ old('product_name') }}" pattern="^[a-zA-Z0-9\s\-]{1,30}$" required>
                                        @error('product_name')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>

                                    <div class="col-md-4">
                                        <label class="input-group-text" for="category_dropdown">
                                            <i class="fa-solid fa-industry" style="margin-right: 5px;"></i> Select Category &nbsp;<i>*Required</i>
                                        </label>
                                        <div class="custom-select">
                                            <select id="category_dropdown" class="form-select @error('category_dropdown') is-invalid @enderror" name="category_dropdown" required>
                                                <option value="">Select a category...</option>
                                                @foreach($categories as $category)
                                                    <option value="{{ $category->category_id }}">{{ $category->category_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        @error('category_dropdown')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <label class="input-group-text" for="description">
                                            <i class="fa-solid fa-pen-to-square" style="margin-right: 5px;"></i> Description&nbsp;<i>-&nbsp;Optional</i>
                                        </label>
                                        <input id="description" type="text" class="form-control @error('description') is-invalid @enderror" name="description" value="{{ old('description', isset($descriptionArray['description']) ? $descriptionArray['description'] : '') }}" pattern='^[a-zA-Z0-9\s\-\.,\/\"]{1,255}$'>
                                        @error('description')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label class="input-group-text" for="purchase_price_per_unit">
                                            <i class="fa-solid fa-pen-to-square" style="margin-right: 5px;"></i> Price Per Unit&nbsp;<i>*Required</i>
                                        </label>
                                        <input id="purchase_price_per_unit" type="text" class="form-control @error('purchase_price_per_unit') is-invalid @enderror" name="purchase_price_per_unit" value="{{ old('purchase_price_per_unit') }}" pattern="^\d{1,6}(\.\d{1,2})?$" required>
                                        @error('purchase_price_per_unit')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>

                                    <div class="col-md-4">
                                        <label class="input-group-text" for="reorder_level">
                                            <i class="fa-solid fa-warehouse" style="margin-right: 5px;"></i> Reorder Limit&nbsp;<i>*Required</i>
                                        </label>
                                        <input id="reorder_level" type="text" class="form-control @error('reorder_level') is-invalid @enderror" name="reorder_level" value="{{ old('reorder_level') }}" pattern="^\d{1,6}$" required>
                                        @error('reorder_level')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-0">
                                    <div class="col-md-12 text-end">
                                        <a class="btn btn-success me-2" href="{{ route('supplier_info', $supplier->supplier_id) }}">Go Back</a>
                                        <button type="submit" class="btn btn-primary">
                                            {{ __('Add Product') }}
                                        </button>
                                    </div>
                                </div>                        
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <h1 class="alert alert-danger mt-2">Sorry, you do not have access to this page. Please go <button onclick="window.history.back()" class="btn btn-secondary">← Back</button>.</h1>
    @endif

@endsection
