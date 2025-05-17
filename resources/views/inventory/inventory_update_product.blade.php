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

@if(Auth::user()->role == 'Inventory Manager')
<div class="col-md-9 ml-sm-auto col-lg-10 px-md-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header text-center text-white" style="background-color: #3a8f66; color:#fff; font-weight: bold; ">{{ __('Update Product') }}</div>
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

                    <form method="POST" action="{{ route('inventory_update_product', ['id' => $productJoined->product_id]) }}" enctype="multipart/form-data">
                        @csrf

                        <!-- Product Details -->
                        <div class="row mb-2">
                            <div class="col-md-4">
                                <label class="input-group-text" for="product_image">
                                    <i class="fa-solid fa-image" style="margin-right: 5px;"></i>Product Image</i>
                                </label>
                                <input type="file" id="image_url" name="image_url" class="form-control @error('image_url') is-invalid @enderror"  value="{{ old('image_url', $productJoined->image_url) }}" accept="image/*">
                                @error('image_url')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            {{-- <div class="row mb-2"> --}}
                            <div class="col-md-4">
                                <label class="input-group-text" for="product_name">
                                    <i class="fa-solid fa-box-open" style="margin-right: 5px;"></i>Product Name</i>
                                </label>
                                <input id="product_name" type="text" class="form-control @error('product_name') is-invalid @enderror" name="product_name" value="{{ old('product', $productJoined->product_name) }}" pattern="^[a-zA-Z0-9\s\-]{1,30}$" required>
                                @error('product_name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="input-group-text" for="category_dropdown">
                                    <i class="fa-solid fa-industry" style="margin-right: 5px;"></i> Select Category</i>
                                </label>
                                <div class="custom-select">
                                    <select id="category_dropdown" class="form-select @error('category_name') is-invalid @enderror" name="category_dropdown" required>
                                        <option value="">Select a category...</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->category_id }}" 
                                                @if(old('category_dropdown', $productJoined->category_id) == $category->category_id) selected @endif>
                                                {{ $category->category_name }}
                                            </option>
                                            {{-- <option value="{{ $category->category_id }}">{{ $category->category_name }}</option> --}}
                                        @endforeach
                                        <option value="add-new-category">Add New Category</option>
                                    </select>
                                </div>
                                @error('category_name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <!-- Category Details (Hidden by Default) -->
                        <div id="category-details" class="d-none">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="input-group-text" for="category_name">
                                        <i class="fa-solid fa-industry" style="margin-right: 5px;"></i> Category&nbsp;<i>*Required</i>
                                    </label>
                                    <input id="category_name" type="text" class="form-control @error('category_name') is-invalid @enderror" name="category_name">
                                </div>
                            </div>
                            @error('category_name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="input-group-text" for="color"> 
                                    <i class="fa-solid fa-paintbrush" style="margin-right: 5px;"></i> Color</i>
                                </label>
                                <input id="color" type="text" class="form-control @error('color') is-invalid @enderror" name="color" value="{{ old('color', $descriptionArray['color'] ?? '') }}" pattern="^[a-zA-Z\s]{1,20}$">
                                @error('color')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        
                            <div class="col-md-4">
                                <label class="input-group-text" for="size">
                                    <i class="fa-solid fa-ruler" style="margin-right: 5px;"></i> Size</i>
                                </label>
                                <input id="size" type="text" class="form-control @error('size') is-invalid @enderror" name="size" value="{{ old('size', $descriptionArray['size'] ?? '') }}" pattern="^[a-zA-Z0-9\s\-]{1,15}$">
                                @error('size')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        
                            <div class="col-md-4">
                                <label class="input-group-text" for="description">
                                    <i class="fa-solid fa-pen-to-square" style="margin-right: 5px;"></i> Description</i>
                                </label>
                                <input id="description" type="text" class="form-control @error('description') is-invalid @enderror" name="description" value="{{ old('description', $descriptionArray['description'] ?? '') }}" pattern="^[a-zA-Z0-9\s\-\.,]{1,255}$">
                                @error('description')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <input id="sale_price_per_unit" type="hidden" class="form-control @error('sale_price_per_unit') is-invalid @enderror" name="sale_price_per_unit" value="{{ old('sale_price_per_unit', $productJoined->sale_price_per_unit) }}" pattern="^\d{1,6}(\.\d{1,2})?$" required>
                            <input type="hidden" name="unit_of_measure" value="{{ old('unit_of_measure', $productJoined->unit_of_measure) }}">
                            <input id="purchase_price_per_unit" type="hidden" class="form-control @error('purchase_price_per_unit') is-invalid @enderror" name="purchase_price_per_unit" value="{{ old('purchase_price_per_unit', $productJoined->purchase_price_per_unit) }}" pattern="^\d{1,6}(\.\d{1,2})?$" required>
                            <input id="in_stock" type="hidden" class="form-control @error('in_stock') is-invalid @enderror" name="in_stock" value="{{ old('in_stock', $productJoined->in_stock) }}" pattern="^\d{1,6}$" required>
                            <input id="reorder_level" type="hidden" class="form-control @error('reorder_level') is-invalid @enderror" name="reorder_level" value="{{ old('reorder_level', $productJoined->reorder_level) }}" pattern="^\d{1,6}$" required>
                            <p style="color: white"><strong>Purchased Quantity: {{ old('in_stock', $productJoined->in_stock) }}</strong></p>
                        </div>
                         <!-- Stockroom Details -->
                         <div class="row mb-3">
                            <div class="col-md-3">
                                <label class="input-group-text" for="aisle_number">
                                    <i class="fa-solid fa-warehouse" style="margin-right: 5px;"></i> Aisle Number</i>
                                </label>
                                <div class="custom-select">
                                    <select name="aisle_number" id="aisle_number" class="form-control @error('aisle_number') is-invalid @enderror" required>
                                        <option value="1" @if(old('aisle_number', $productJoined->aisle_number) == 1) selected @endif>1</option>
                                        <option value="2" @if(old('aisle_number', $productJoined->aisle_number) == 2) selected @endif>2</option>
                                        <option value="3" @if(old('aisle_number', $productJoined->aisle_number) == 3) selected @endif>3</option>
                                        <option value="4" @if(old('aisle_number', $productJoined->aisle_number) == 4) selected @endif>4</option>
                                    </select>
                                </div>
                                @error('aisle_number')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label class="input-group-text" for="cabinet_level">
                                    <i class="fa-solid fa-warehouse" style="margin-right: 5px;"></i> Cabinet Level</i>
                                </label>
                                <div class="custom-select">
                                    <select name="cabinet_level" id="cabinet_level" class="form-control @error('cabinet_level') is-invalid @enderror" required>
                                        <option value="1" @if(old('cabinet_level', $productJoined->cabinet_level) == 1) selected @endif>1</option>
                                        <option value="2" @if(old('cabinet_level', $productJoined->cabinet_level) == 2) selected @endif>2</option>
                                        <option value="3" @if(old('cabinet_level', $productJoined->cabinet_level) == 3) selected @endif>3</option>
                                        <option value="4" @if(old('cabinet_level', $productJoined->cabinet_level) == 4) selected @endif>4</option>
                                        <option value="5" @if(old('cabinet_level', $productJoined->cabinet_level) == 5) selected @endif>5</option>
                                    </select>
                                </div>
                                @error('cabinet_level')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="input-group-text" for="product_quantity">
                                    <i class="fa-solid fa-boxes" style="margin-right: 5px;"></i>Stockroom Stocks</i>
                                </label>
                                <input id="product_quantity" type="number" class="form-control @error('product_quantity') is-invalid @enderror" name="product_quantity" value="{{ old('product_quantity', $productJoined->product_quantity) }}" min="0" max="{{ old('in_stock', $productJoined->in_stock) }}" required>
                                @error('product_quantity')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                         </div>
                        <div class="row mb-0">
                            <div class="col-md-12 text-end">
                                <button type="submit" name="create" class="btn btn-primary">
                                    {{ __('Update Product') }}
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

<script>
    // category dropdown
    document.getElementById('category_dropdown').addEventListener('change', function () {
        const categoryId = this.value;
        const categoryDetails = document.getElementById('category-details');
        const categoryNameInput = document.getElementById('category_name');

        if (categoryId === 'add-new-category') {
            // Show category details input and make the input field required
            categoryDetails.classList.remove('d-none');
            categoryNameInput.setAttribute('required', 'required');
            categoryNameInput.value = ''; // Clear the input field
        } else {
            // Hide category details input and remove required attribute
            categoryDetails.classList.add('d-none');
            categoryNameInput.removeAttribute('required');
            categoryNameInput.value = ''; // Clear the input field
        }
    });
</script>

@endsection
