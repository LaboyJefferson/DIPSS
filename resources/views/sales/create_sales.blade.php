@extends('layouts.app')
@include('common.navbar')

@section('content')
<style>
    body {
        background-image: url('/storage/images/bg-photo.jpeg');
        background-size: cover; /* Cover the entire viewport */
        background-position: center; /* Center the background image */
        background-repeat: no-repeat; /* Prevent the image from repeating */
    }

    .card {
        background-color: #565656; /* Card background */
        border: none; /* Remove border */
        border-radius: 8px; /* Rounded corners */
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

    .table th, td {
        background-color: #565656 !important; /* Set background color for all table headers */
        color: #ffffff !important;
    }

    
</style>

<div class="col-md-9 ml-sm-auto col-lg-10 px-md-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="text card-header text-center" style="background-color: #3a8f66; color: #fff; font-weight: bold;">{{ __('Sale Transaction') }}</div>
                <div class="card-body">
                    @include('common.alert')

                    <form id="sales-form" method="POST" action="{{ url('sales') }}">
                        @csrf
                        <div id="product-fields">
                            <div class="product-entry">
                                <div class="row mt-3">
                                    <div class="col-3">
                                        <label for="product_id" class="form-label" style="color: #fff;">{{ __('Product No.') }} <i>*Required</i></label>
                                        <input type="text" class="form-control product_id" name="product_id[]" oninput="checkProductExistence(this)" required>
                                    </div>
                                    <div class="col-3">
                                        <label for="quantity" class="form-label" style="color: #fff;">{{ __('Quantity') }} <i>*Required</i></label>
                                        <input type="number" class="form-control quantity" name="quantity[]" required>
                                    </div>
                                    <div class="col-3">
                                        <label for="total_amount" class="form-label" style="color: #fff;">{{ __('Amount') }} <i>*Read-Only</i></label>
                                        <input type="text" class="form-control total_amount" name="total_amount[]" readonly>
                                    </div>
                                    <div class="col-3" style="padding-top: 2em;">
                                        <button type="button" class="btn btn-light more-info-btn" onclick="showProductDetails(this)" disabled>
                                            <strong>more info.</strong>
                                        </button>
                                    </div>
                                </div>
                                <hr>
                            </div>
                        </div>
                        
                        <label for="grand_total_amount" class="form-label" style="color: #fff;">{{ __('Total Amount') }} <i>*Read-Only</i></label>
                        <input type="text" class="form-control" id="grand_total_amount" name="grand_total_amount" readonly>

                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <button type="button" id="add-another-product" class="btn btn-secondary me-2">{{ __('Add Another Product') }}</button>
                            <button type="submit" class="btn btn-success">{{ __('Submit') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Check if product exists in the database and enable/disable the "More Info" button
    function checkProductExistence(input) {
        const productId = input.value;

        // Only proceed if the product ID is not empty and is 8 digits long
        if (productId) {
            fetchProductExistence(productId, input);
        } else {
            disableMoreInfoButton(input);
        }
    }

    // Fetch product existence from the server
    function fetchProductExistence(productId, input) {
        fetch('{{ route('fetch.product') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ product_id: productId })
        })
        .then(response => response.json())
        .then(data => {
            const productEntry = input.closest('.product-entry');
            const moreInfoButton = productEntry.querySelector('.more-info-btn');
            const productIdField = productEntry.querySelector('.product_id');
            
            if (data.success) {
                // Product exists, enable the "More Info" button
                moreInfoButton.disabled = false;
                showInvalidProductMessage(input, '', false);
            } else {
                // Product does not exist, show the error message
                moreInfoButton.disabled = true;
                showInvalidProductMessage(input, 'Product not found in the database.');
            }
        })
        .catch(error => {
            console.error('Error fetching product:', error);
            const productEntry = input.closest('.product-entry');
            const moreInfoButton = productEntry.querySelector('.more-info-btn');
            moreInfoButton.disabled = true;
            showInvalidProductMessage(input, 'Error fetching product data. Please try again.');
        });
    }

    // Disable the "More Info" button if product ID is invalid or product is not found
    function disableMoreInfoButton(input) {
        const productEntry = input.closest('.product-entry');
        const moreInfoButton = productEntry.querySelector('.more-info-btn');
        moreInfoButton.disabled = true;
    }

    // Show or hide the invalid product message
    function showInvalidProductMessage(input, message, isError = true) {
        const productEntry = input.closest('.product-entry');
        let invalidMessage = productEntry.querySelector('.invalid-feedback');
        
        // If no message element exists, create one
        if (!invalidMessage) {
            invalidMessage = document.createElement('span');
            invalidMessage.classList.add('invalid-feedback');
            invalidMessage.style.color = 'white';
            productEntry.querySelector('.col-3').appendChild(invalidMessage);
        }

        // Display error or clear message
        if (message) {
            invalidMessage.style.display = 'inline';
            invalidMessage.textContent = message;
        } else {
            invalidMessage.style.display = 'none';
        }
    }

    // Event listener for the "More Info" button click
    function showProductDetails(button) {
        const productId = button.closest('.product-entry').querySelector('.product_id').value;
        if (productId) {
            fetchProductDetails(productId, button);
        }
    }

    // Fetch product details function
    function fetchProductDetails(productId, button) {
        fetch('{{ route('fetch.product') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ product_id: productId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const product = data.product;
                const seller = data.seller;

                let storeStock = data.product.in_stock - data.product.product_quantity;
                // Create product details string for SweetAlert
                const productDetails = `
                    <p><strong>Seller:</strong> ${seller || 'N/A'}</p>
                    <p><strong>Product Name:</strong> ${product.product_name || 'N/A'}</p>
                    <p><strong>Color:</strong> ${product.descriptionArray.color || 'N/A'}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong>Size:</strong> ${product.descriptionArray.size || 'N/A'}</p>
                    <p><strong>Description:</strong> ${product.descriptionArray.description || 'N/A'}</p>
                    <p><strong>Category:</strong> ${product.category_name || 'N/A'}</p>
                    <p><strong>In Stock:</strong> ${product.in_stock || 'Out of Stock'}</p>
                    <p><strong>Store Stock:</strong> ${storeStock || 'Out of Stock'}</p>
                    <p><strong>In Stock:</strong> ${product.product_quantity || 'Out of Stock'}</p>
                    <p><strong>Price per Unit: ₱</strong> ${product.sale_price_per_unit}/${product.unit_of_measure}</p>
                `;

                // Display SweetAlert with product details
                Swal.fire({
                    title: `${product.product_name || 'Product Info'}`,
                    html: productDetails,
                    icon: 'info',
                    // showCancelButton: true,
                    confirmButtonText: 'Close',
                });

                // Show price and unit details for the selected product
                const price = product.sale_price_per_unit;
                const productEntry = button.closest('.product-entry');
                const quantityInput = productEntry.querySelector('.quantity');
                const totalAmountInput = productEntry.querySelector('.total_amount');

                // Recalculate total amount when quantity is entered or changed
                quantityInput.addEventListener('input', function() {
                    totalAmountInput.value = (this.value * price).toFixed(2);
                    calculateGrandTotal();
                });

                // Initial calculation for the product
                totalAmountInput.value = (quantityInput.value * price).toFixed(2);
                calculateGrandTotal();
            } else {
                alert(data.message);
            }
        });
    }

    // Calculate grand total for all products
    function calculateGrandTotal() {
        const totalAmountFields = document.querySelectorAll('.total_amount');
        let grandTotal = 0;
        totalAmountFields.forEach(field => {
            grandTotal += parseFloat(field.value) || 0;
        });
        document.getElementById('grand_total_amount').value = grandTotal.toFixed(2);
    }

    // For adding a new product entry
    document.getElementById('add-another-product').addEventListener('click', function() {
        const newProductEntry = document.createElement('div');
        newProductEntry.className = 'product-entry';
        newProductEntry.innerHTML = `
            <div class="row mt-3">
                <div style="align-items:left">
                    <button type="button" id="cancel-button" class="btn btn-danger me-2 mb-4">{{ __('Cancel') }}</button>
                </div>
                <div class="col-3">
                    <label for="product_id" class="form-label" style="color: #fff;">{{ __('Product No.') }} <i>*Required</i></label>
                    <input type="text" class="form-control product_id" name="product_id[]" oninput="checkProductExistence(this)" required oninput="checkProductExistence(this)">
                </div>
                <div class="col-3">
                    <label for="quantity" class="form-label" style="color: #fff;">{{ __('Quantity') }} <i>*Required</i></label>
                    <input type="number" class="form-control quantity" name="quantity[]" required>
                </div>
                <div class="col-3">
                    <label for="total_amount" class="form-label" style="color: #fff;">{{ __('Amount') }} <i>*Read-Only</i></label>
                    <input type="text" class="form-control total_amount" name="total_amount[]" readonly>
                </div>
                <div class="col-3" style="padding-top: 2em;">
                    <button type="button" class="btn btn-light more-info-btn" onclick="showProductDetails(this)" disabled>
                        <strong>more info.</strong>
                    </button>
                </div>
            </div>
            <hr>
        `;
        
        // Append the new product entry to the product fields container
        document.getElementById('product-fields').appendChild(newProductEntry);
    });

    // For removing new product entry
    document.getElementById('product-fields').addEventListener('click', function(e) {
        if (e.target && e.target.id === 'cancel-button') {
            // Find the closest product entry and remove it
            const productEntry = e.target.closest('.product-entry');
            if (productEntry) {
                productEntry.remove();
                calculateGrandTotal(); // Recalculate grand total after removal
            }
        }
    });

    // Ensure that the amount field updates automatically when quantity is entered
    document.getElementById('product-fields').addEventListener('input', function(e) {
        if (e.target && e.target.classList.contains('quantity')) {
            const productEntry = e.target.closest('.product-entry');
            const quantity = e.target.value;
            const productId = productEntry.querySelector('.product_id').value;
            if (productId && quantity) {
                const totalAmountInput = productEntry.querySelector('.total_amount');
                fetchProductDetailsForAmount(productId, quantity, totalAmountInput);
            }
        }
    });

    // Fetch product price and calculate amount based on quantity
    function fetchProductDetailsForAmount(productId, quantity, totalAmountInput) {
        fetch('{{ route('fetch.product') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ product_id: productId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const price = data.product.sale_price_per_unit;
                totalAmountInput.value = (quantity * price).toFixed(2);
                calculateGrandTotal();
            } else {
                alert(data.message);
            }
        });
    }
</script>






@endsection