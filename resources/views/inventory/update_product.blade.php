{{-- @extends('layouts.app')
@include('common.navbar')

@section('content')
<style>
    .card {
        background-color: #34495e; /* Darker card background */
        border: none; /* Remove border */
        border-radius: 8px; /* Rounded corners */
    }
    .input-group-text {
        background-color: #74e39a; /* Input group background */
        border: none; /* Remove borders */
        color: #0f5132; /* Dark text */
    }
    .btn-primary {
        background-color: #74e39a; /* Green button */
        color: black;
        border: none; /* Remove button borders */
    }
    .btn-primary:hover {
        background-color: #0f5132; /* Darker green on hover */
    }
    .form-control {
        background-color: #fff; /* White input background */
        color: black; /* Black text */
        border: 1px solid #444; /* Subtle border */
    }
    .form-control:focus {
        border-color: #28a745; /* Green border on focus */
        box-shadow: none; /* Remove default shadow */
    }
    .progress {
        height: 20px; /* Height of the progress bar */
    }
</style>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header text-center text-white">{{ __('Update Product') }}</div>
                <div class="card-body">
                    <!-- Alert Messages -->
                    @include('common.alert')

                    <!-- Progress Bar -->
                    <div class="mb-4">
                        <div class="progress">
                            <div class="progress-bar bg-success" role="progressbar" style="width: 0%;" id="progressBar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                        </div>
                    </div>

                    <form method="POST" action="{{ url('update_product/' . $product->product_id) }}" enctype="multipart/form-data" id="productForm">
                        @csrf
                        @method('PUT')
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="input-group-text" for="product_name">
                                    <i class="fa-solid fa-box-open"></i> Product Name
                                </label>
                                <input id="product_name" type="text" class="form-control @error('product_name') is-invalid @enderror" name="product_name" value="{{ $product->product_name ?? '' }}" required>
                                @error('product_name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="input-group-text" for="category_name">
                                    <i class="fa fa-table-list"></i> Category
                                </label>
                                <input id="category_name" type="text" class="form-control @error('category_name') is-invalid @enderror" name="category_name" value="{{ $product->category->category_name ?? '' }}" required>
                                @error('category_name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="input-group-text" for="color">
                                    <i class="fa-solid fa-paintbrush"></i> Color
                                </label>
                                <input id="color" type="text" class="form-control @error('color') is-invalid @enderror" name="color" value="{{ old('color', isset($descriptionArray['color']) ? $descriptionArray['color'] : '') }}" required>
                                @error('color')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        
                            <div class="col-md-4">
                                <label class="input-group-text" for="size">
                                    <i class="fa-solid fa-ruler"></i> Size
                                </label>
                                <input id="size" type="text" class="form-control @error('size') is-invalid @enderror" name="size" value="{{ old('size', isset($descriptionArray['size']) ? $descriptionArray['size'] : '') }}" required>
                                @error('size')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        
                            <div class="col-md-4">
                                <label class="input-group-text" for="description">
                                    <i class="fa-solid fa-pen-to-square"></i> Description
                                </label>
                                <input id="description" type="text" class="form-control @error('description') is-invalid @enderror" name="description" value="{{ old('description', isset($descriptionArray['description']) ? $descriptionArray['description'] : '') }}" required>
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
                                    <i class="fa-solid fa-pen-to-square"></i> Purchase Price Per Unit
                                </label>
                                <input id="purchase_price_per_unit" type="text" class="form-control @error('purchase_price_per_unit') is-invalid @enderror" name="purchase_price_per_unit" value="{{ $inventory->purchase_price_per_unit ?? '' }}" required>
                                @error('purchase_price_per_unit')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="input-group-text" for="sale_price_per_unit">
                                    <i class="fa-solid fa-tag"></i> Sale Price Per Unit
                                </label>
                                <input id="sale_price_per_unit" type="text" class="form-control @error('sale_price_per_unit') is-invalid @enderror" name="sale_price_per_unit" value="{{ $inventory->sale_price_per_unit ?? '' }}" required>
                                <small class="form-text text-info mt-2">Note: Please enter a whole number like 5999.</small>
                                @error('sale_price_per_unit')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="input-group-text" for="unit_of_measure">
                                    <i class="fa-solid fa-scale-balanced"></i> Unit of Measure
                                </label>
                                <input id="unit_of_measure" type="text" class="form-control @error('unit_of_measure') is-invalid @enderror" name="unit_of_measure" value="{{ $inventory->unit_of_measure ?? '' }}" required>
                                @error('unit_of_measure')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="input-group-text" for="in_stock">
                                    <i class="fa-solid fa-warehouse"></i> Quantity in Stock
                                </label>
                                <input id="in_stock" type="text" class="form-control @error('in_stock') is-invalid @enderror" name="in_stock" value="{{ $inventory->in_stock ?? '' }}" required>
                                @error('in_stock')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="input-group-text" for="reorder_level">
                                    <i class="fa-solid fa-warehouse"></i> Reorder Level
                                </label>
                                <input id="reorder_level" type="text" class="form-control @error('reorder_level') is-invalid @enderror" name="reorder_level" value="{{ $inventory->reorder_level ?? '' }}" required>
                                @error('reorder_level')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="input-group-text" for="company_name">
                                    <i class="fa-solid fa-industry"></i> Company
                                </label>
                                <input id="company_name" type="text" class="form-control @error('company_name') is-invalid @enderror" name="company_name" value="{{ $supplier->company_name ?? '' }}" required>
                                @error('company_name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="input-group-text" for="contact_person">
                                    <i class="fa-solid fa-industry"></i> Contact Person
                                </label>
                                <input id="contact_person" type="text" class="form-control @error('ccontact_person') is-invalid @enderror" name="contact_person" value="{{ $supplier->contact_person ?? '' }}" required>
                                @error('contact_person')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="input-group-text" for="email">
                                    <i class="fa-solid fa-industry"></i> Email
                                </label>
                                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ $supplier->email ?? '' }}" required>
                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="input-group-text" for="mobile_number">
                                    <i class="fa-solid fa-industry"></i> Mobile Number
                                </label>
                                <input id="mobile_number" type="text" class="form-control @error('mobile_number') is-invalid @enderror" name="mobile_number" value="{{ $supplier->mobile_number ?? '' }}" required>
                                @error('mobile_number')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label class="input-group-text" for="address">
                                    <i class="fa-solid fa-industry"></i> Address
                                </label>
                                <input id="address" type="text" class="form-control @error('address') is-invalid @enderror" name="address" value="{{ $supplier->address ?? '' }}" required>
                                @error('address')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12 text-center">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa-solid fa-check"></i> Update Product
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('productForm').addEventListener('input', function() {
        const totalFields = 12; // Change this to the actual number of required fields
        let filledFields = 0;

        const inputs = this.querySelectorAll('input[required]');
        inputs.forEach(input => {
            if (input.value.trim() !== '') {
                filledFields++;
            }
        });

        const progressPercentage = (filledFields / totalFields) * 100;
        document.getElementById('progressBar').style.width = progressPercentage + '%';
        document.getElementById('progressBar').setAttribute('aria-valuenow', progressPercentage);
        document.getElementById('progressBar').innerText = Math.round(progressPercentage) + '%';
    });
</script>
@endsection --}}
