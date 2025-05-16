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

                    <form method="POST" action="{{ url('purchase') }}" enctype="multipart/form-data">
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

                        {{-- <div class="row mb-2"> --}}
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
                                    <select id="category_dropdown" class="form-select @error('category_name') is-invalid @enderror" name="category_dropdown" required>
                                        <option value="">Select a category...</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->category_id }}">{{ $category->category_name }}</option>
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
                                    <i class="fa-solid fa-paintbrush" style="margin-right: 5px;"></i> Color&nbsp;<i>-&nbsp;Optional</i>
                                </label>
                                <input id="color" type="text" class="form-control @error('color') is-invalid @enderror" name="color" value="{{ old('color', isset($descriptionArray['color']) ? $descriptionArray['color'] : '') }}" pattern="^[a-zA-Z\s]{1,20}$">
                                @error('color')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        
                            <div class="col-md-4">
                                <label class="input-group-text" for="size">
                                    <i class="fa-solid fa-ruler" style="margin-right: 5px;"></i> Size&nbsp;<i>-&nbsp;Optional</i>
                                </label>
                                <input id="size" type="text" class="form-control @error('size') is-invalid @enderror" name="size" value="{{ old('size', isset($descriptionArray['size']) ? $descriptionArray['size'] : '') }}" pattern="^[a-zA-Z0-9\s\-]{1,15}$">
                                @error('size')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        
                            <div class="col-md-4">
                                <label class="input-group-text" for="description">
                                    <i class="fa-solid fa-pen-to-square" style="margin-right: 5px;"></i> Description&nbsp;<i>-&nbsp;Optional</i>
                                </label>
                                <input id="description" type="text" class="form-control @error('description') is-invalid @enderror" name="description" value="{{ old('description', isset($descriptionArray['description']) ? $descriptionArray['description'] : '') }}" pattern="^[a-zA-Z0-9\s\-\.,]{1,255}$">
                                @error('description')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        {{-- <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="input-group-text" for="purchase_price_per_unit">
                                    <i class="fa-solid fa-pen-to-square" style="margin-right: 5px;"></i> Purchased Price Per Unit&nbsp;<i>*Required</i>
                                </label>
                                <input id="purchase_price_per_unit" type="text" class="form-control @error('purchase_price_per_unit') is-invalid @enderror" name="purchase_price_per_unit" value="{{ old('purchase_price_per_unit') }}" pattern="^\d{1,6}(\.\d{1,2})?$" required>
                                @error('purchase_price_per_unit')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="input-group-text" for="sale_price_per_unit">
                                    <i class="fa-solid fa-pen-to-square" style="margin-right: 5px;"></i> Sale Price Per Unit&nbsp;<i>*Required</i>
                                </label>
                                <input id="sale_price_per_unit" type="text" class="form-control @error('sale_price_per_unit') is-invalid @enderror" name="sale_price_per_unit" value="{{ old('sale_price_per_unit') }}" pattern="^\d{1,6}(\.\d{1,2})?$" required>
                                @error('sale_price_per_unit')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="input-group-text" for="unit_of_measure">
                                    <i class="fa-solid fa-scale-balanced" style="margin-right: 5px;"></i> Select Unit of Measure&nbsp;<i>*Required</i>
                                </label>
                                <div class="custom-select">
                                    <select name="unit_of_measure" id="unit_of_measure" class="form-control  @error('unit_of_measure') is-invalid @enderror" required>
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
                        </div> --}}

                        <div class="row mb-3">
                            {{-- <div class="col-md-6">
                                <label class="input-group-text" for="in_stock">
                                    <i class="fa-solid fa-warehouse" style="margin-right: 5px;"></i> Total Purchased Quantity&nbsp;<i>*Required</i>
                                </label>
                                <input id="in_stock" type="text" class="form-control @error('in_stock') is-invalid @enderror" name="in_stock" value="{{ old('in_stock') }}" pattern="^\d{1,6}$" required>
                                @error('in_stock')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div> --}}

                            {{-- <div class="col-md-6">
                                <label class="input-group-text" for="reorder_level">
                                    <i class="fa-solid fa-warehouse" style="margin-right: 5px;"></i> Reorder Level&nbsp;<i>*Required</i>
                                </label>
                                <input id="reorder_level" type="text" class="form-control @error('reorder_level') is-invalid @enderror" name="reorder_level" value="{{ old('reorder_level') }}" pattern="^\d{1,6}$" required>
                                @error('reorder_level')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div> --}}
                        </div>

                         <!-- Stockroom Details -->
                         <div class="row mb-3">
                            {{-- <div class="col-md-4">
                                <label class="input-group-text" for="aisle_number">
                                    <i class="fa-solid fa-warehouse" style="margin-right: 5px;"></i> Aisle Number&nbsp;<i>*Required</i>
                                </label>
                                <div class="custom-select">
                                    <select name="aisle_number" id="aisle_number" class="form-control @error('aisle_number') is-invalid @enderror" required>
                                        <option value="1">1</option>
                                        <option value="2">2</option>
                                        <option value="3">3</option>
                                        <option value="4">4</option>
                                    </select>
                                </div>
                                @error('aisle_number')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="input-group-text" for="cabinet_level">
                                    <i class="fa-solid fa-warehouse" style="margin-right: 5px;"></i> Cabinet Level&nbsp;<i>*Required</i>
                                </label>
                                <div class="custom-select">
                                    <select name="cabinet_level" id="cabinet_level" class="form-control @error('cabinet_level') is-invalid @enderror" required>
                                        <option value="1">1</option>
                                        <option value="2">2</option>
                                        <option value="3">3</option>
                                        <option value="4">4</option>
                                        <option value="5">5</option>
                                    </select>
                                </div>
                                @error('cabinet_level')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div> --}}
                            {{-- <div class="col-md-3">
                                <label class="input-group-text" for="product_quantity">
                                    <i class="fa-solid fa-boxes" style="margin-right: 5px;"></i>Stock Stored&nbsp;<i>*Required</i>
                                </label>
                                <input id="product_quantity" type="number" class="form-control @error('product_quantity') is-invalid @enderror" name="product_quantity" value="{{ old('product_quantity') }}" min="1" required>
                                @error('product_quantity')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div> --}}

                            <!-- Dropdown for existing suppliers -->
                            {{-- <div class="col-md-4">
                                <label class="input-group-text" for="supplier_dropdown">
                                    <i class="fa-solid fa-industry" style="margin-right: 5px;"></i> Select Supplier
                                </label>
                                <div class="custom-select">
                                    <select id="supplier_dropdown" class="form-select" name="supplier_dropdown">
                                        <option value="">Select available supplier...</option>
                                        @foreach($suppliers as $supplier)
                                            <option value="{{ $supplier->supplier_id }}">{{ $supplier->company_name }}</option>
                                        @endforeach
                                        <option value="add-new">Add New Supplier</option>
                                    </select>
                                </div>
                            </div> --}}
                         </div>

                        <!-- Supplier Details (Hidden by Default) -->
                        <div id="supplier-details" class="d-none">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="input-group-text" for="company_name">
                                        <i class="fa-solid fa-industry" style="margin-right: 5px;"></i> Supplier&nbsp;<i>*Required</i>
                                    </label>
                                    <input id="company_name" type="text" class="form-control" name="company_name">
                                </div>
                                <div class="col-md-6">
                                    <label class="input-group-text" for="contact_person">
                                        <i class="fa-solid fa-industry" style="margin-right: 5px;"></i> Contact Person&nbsp;<i>*Required</i>
                                    </label>
                                    <input id="contact_person" type="text" class="form-control" name="contact_person">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="input-group-text" for="email">
                                        <i class="fa-solid fa-industry" style="margin-right: 5px;"></i> Email&nbsp;<i>*Required</i>
                                    </label>
                                    <input id="email" type="email" class="form-control" name="email">
                                </div>
                                <div class="col-md-6">
                                    <label class="input-group-text" for="mobile_number">
                                        <i class="fa-solid fa-industry" style="margin-right: 5px;"></i> Mobile Number&nbsp;<i>*Required</i>
                                    </label>
                                    <input id="mobile_number" type="text" class="form-control" name="mobile_number">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label class="input-group-text" for="address">
                                        <i class="fa-solid fa-industry" style="margin-right: 5px;"></i> Address&nbsp;<i>*Required</i>
                                    </label>
                                    <input id="address" type="text" class="form-control" name="address">
                                </div>
                            </div>
                        </div>  

                        

                        <div class="row mb-0">
                            <div class="col-md-12 text-end">
                                <button type="submit" name="create" class="btn btn-primary">
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

    // supplier dropdown
    document.getElementById('supplier_dropdown').addEventListener('change', function () {
        const supplierId = this.value;
        const supplierDetails = document.getElementById('supplier-details');

        if (supplierId === 'add-new') {
            // Show supplier details and make fields required
            supplierDetails.classList.remove('d-none');
            document.querySelectorAll('#supplier-details input').forEach(input => {
                input.setAttribute('required', 'required');
                input.value = ''; // Clear fields
            });
        } else {
            // Hide supplier details and remove required attributes
            supplierDetails.classList.add('d-none');
            document.querySelectorAll('#supplier-details input').forEach(input => {
                input.removeAttribute('required');
                input.value = ''; // Clear fields
            });

            if (supplierId) {
                // Fetch and populate supplier details
                fetch('/supplier/details', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    },
                    body: JSON.stringify({ supplier_id: supplierId }),
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data) {
                            document.getElementById('company_name').value = data.company_name;
                            document.getElementById('contact_person').value = data.contact_person;
                            document.getElementById('email').value = data.email;
                            document.getElementById('mobile_number').value = data.mobile_number;
                            document.getElementById('address').value = data.address;
                        }
                    })
                    .catch(error => console.error('Error:', error));
            }
        }
    });
</script>

@endsection
