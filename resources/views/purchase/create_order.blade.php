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

        h4.h2 {
            color: #fff;

        }

        .table th {
            font-size: 0.95em;
        }

        .table th, td {
            background-color: #565656 !important; /* Set background color for all table headers */
            color: #ffffff !important;
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
                    <div class="card-header text-center text-white" style="background-color: #3a8f66; color:#fff; font-weight: bold; ">{{ __('Create Order') }}</div>
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

                        <form method="POST" action="{{ route('store_order') }}" enctype="multipart/form-data">
                            @csrf
                            
                            <!-- Order Details 1 - Payment Method Field, Order Type Field -->
                            <div class="row mb-2">
                                <div class="col-md-4">
                                    <label class="input-group-text" for="payment_method">
                                        <i class="fa-solid fa-cash-register" style="margin-right: 5px;"></i>Payment Method&nbsp;<i>*Required</i>
                                    </label>
                                    <div class="custom-select">
                                        <select id="payment_method" class="form-select" name="payment_method" required>
                                            <option value="" selected>Select payment method...</option>
                                            <option value="Cash on Delivery (COD)">Cash on Delivery (COD)</option>
                                            <option value="Credit/Debit Card">Credit/Debit Card</option>
                                            <option value="Paypal">PayPal</option>
                                            <option value="GCash">GCash</option>
                                        </select>
                                    </div>
                                    @error('payment_method')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{$message}}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="input-group-text" for="order_type">
                                        <i class="fa-solid fa-user" style="margin-right: 5px;"></i> Select Order Type &nbsp;<i>*Required</i>
                                    </label>
                                    <div class="custom-select">
                                        <select id="order_type" class="form-select" name="order_type" required>
                                            <option value="" selected>Select order type...</option>
                                            <option value="Purchase Order">Purchase Order</option>
                                            <option value="Backorder">Backorder</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Order Details 2: Billing address, Shipping address-->
                            <div class="row mb-2">
                                <div class="col-md-6">
                                    <label class="input-group-text" for="billing_address">
                                        <i class="fa-solid fa-file-invoice" style="margin-right: 5px;"></i>Billing Address&nbsp;<i>*Required</i>
                                    </label>
                                    <p id="billing-address" class="form-control" style="border: 1px solid #ced4da; padding: 10px; border-radius: 5px;"></p>
                                    @error('billing_address')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                                

                                <div class="col-md-6">
                                    <label class="input-group-text" for="shipping_address">
                                        <i class="fa-solid fa-map-marker-alt" style="margin-right: 5px;"></i>Shipping Address&nbsp;<i>*Required</i>
                                    </label>
                                    <div class="custom-select">
                                        <select id="address_dropdown" class="form-select" name="address_dropdown">
                                            <option value="">Select a shipping address..</option>
                                            @foreach($addresses as $address)
                                                <option value="{{ $address->address }}">{{ $address->address }}</option>
                                            @endforeach
                                            <option value="add-new">Add New Address</option>
                                        </select>
                                    </div>
                                    @error('shipping_address')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <!-- Add new Address Details (Hidden by Default) -->
                            <div id="address-details" class="d-none">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="input-group-text" for="address">
                                            <i class="fa-solid fa-industry" style="margin-right: 5px;"></i> Address&nbsp;<i>*Required</i>
                                        </label>
                                        <input id="address" type="text" class="form-control" name="address">
                                    </div>
                                
                                </div>
                            </div>

                            <!-- Order Details 3: Supplier, Total price -->
                            <div class="row mb-2">
                                <div class="col-md-4">
                                    <label class="input-group-text" for="supplier_dropdown">
                                        <i class="fa-solid fa-user" style="margin-right: 5px;"></i> Select Supplier &nbsp;<i>*Required</i>
                                    </label>
                                    <div class="custom-select">
                                        <select id="supplier_dropdown" class="form-select" name="supplier_dropdown" required>
                                            <option value="">Select available supplier...</option>
                                            @foreach($suppliers as $supplier)
                                                <option value="{{ $supplier->supplier_id }}">{{ $supplier->company_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                {{-- <div class="col-md-4">
                                    <label class="input-group-text" for="total_price">
                                        <i class="fa-solid fa-box-open" style="margin-right: 5px;"></i>Total Price&nbsp;<i>*Required</i>
                                    </label>
                                    <input id="total_price" style="color: black;" type="number" value="0" class="form-control @error('total_price') is-invalid @enderror" disabled>
                                    <input type="hidden" name="total_price" id="total_price_hidden">
                                    @error('total_price')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div> --}}
                            </div>
                            
                            <!-- Table for showing products for the selected supplier -->
                            <div id="product-table-container">
                                <h4 class="h2">Products to Add</h4>
                                <table id="product-table" class="table table-responsive mt-4">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th>Quantity</th>
                                            <th>Price</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="product-table-body">
                                        <!-- Product rows will be added here -->
                                    </tbody>
                                </table>

                                <!-- Button to add a product -->
                                <button type="button" id="add-product-btn" class="btn btn-success">
                                    Add Product
                                </button>
                            </div>

                            <!-- Hidden inputs-->
                            <input type="hidden" name="supplier_id" id="supplier_id_hidden" value="">
                            <input type="hidden" name="user_id" value="{{ Auth::user()->user_id }}">
                            <input type="hidden" name="billing_address" id="billing_address_hidden" value="">

                            <div class="row mb-0">
                                <div class="col-md-12 text-end">
                                    <button type="submit" class="btn btn-primary">
                                        {{ __('Create Order') }}
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
    // address dropdown
    document.getElementById('address_dropdown').addEventListener('change', function () {
        const addressId = this.value;
        const addressDetails = document.getElementById('address-details');

        if (addressId === 'add-new') {
            // Show address details and make fields required
            addressDetails.classList.remove('d-none');
            document.querySelectorAll('#address-details input').forEach(input => {
                input.setAttribute('required', 'required');
                input.value = ''; // Clear fields
            });
        } else {
            // Hide address details and remove required attributes
            addressDetails.classList.add('d-none');
            document.querySelectorAll('#address-details input').forEach(input => {
                input.removeAttribute('required');
                input.value = ''; // Clear fields
            });
        }
    });

    // supplier dropdown event
    document.getElementById('supplier_dropdown').addEventListener('change', function () {
        const supplierId = this.value;
        document.getElementById('supplier_id_hidden').value = supplierId;

        const productTable = document.getElementById('product-table-container');
        const tableBody = document.getElementById('product-table-body');
        const billingAddressElement = document.getElementById('billing-address');
        const hiddenBillingAddressElement = document.getElementById('billing_address_hidden');

        tableBody.innerHTML = ''; // Clears all rows everytime user change a supplier
        billingAddressElement.textContent = '';

        // Remove any existing error message
        let existingErrorMessage = document.querySelector('.error-message');
        if (existingErrorMessage) {
            existingErrorMessage.remove();
        }

        if (supplierId !== '') {
            productTable.classList.remove('d-none');
            
            // Fetch products for the selected supplier
            fetch(`/supplier/${supplierId}/products`)
                .then(response => response.json())
                .then(data => {
                    // Store products globally for later use
                    window.availableProducts = data;

                    // Populate the product dropdown
                    populateProductDropdown();
                })
                .catch(error => {
                    console.error('Error fetching products:', error);
                });

                // Fetch the supplier data (this should return an object with the supplier details, including the address)
            fetch(`/supplier/${supplierId}`)
                .then(response => response.json())
                .then(data => {
                    if (data && data.address) {
                        // Populate the billing address with the supplier's address
                        billingAddressElement.textContent = data.address;
                        hiddenBillingAddressElement.value = data.address;
                    } else {
                        // Handle case if address is not found
                        billingAddressElement.textContent = 'No address found for this supplier.';
                    }
                })
                .catch(error => {
                    console.error('Error fetching supplier address:', error);
                    billingAddressElement.textContent = 'Error fetching address.';
                });
        } else {
            productTable.classList.add('d-none');
        }
    });

    // Populate product dropdowns based on available products
    function populateProductDropdown() {
        // Select all product dropdowns
        const productDropdowns = document.querySelectorAll('.product-dropdown');
        
        // Loop through all the dropdowns and populate them
        productDropdowns.forEach(select => {
            // Reset current options
            select.innerHTML = `<option value="" selected>Select Product...</option>`;

            // Check if we have products available
            if (window.availableProducts && window.availableProducts.length > 0) {
                window.availableProducts.forEach(product => {
                    const option = document.createElement('option');
                    option.value = product.product_id; // Set the product ID as the value
                    option.textContent = product.product_name; // Display the product name
                    select.appendChild(option);
                });
            } else {
                // If no products are available, show a message in the dropdown
                const option = document.createElement('option');
                option.value = '';
                option.textContent = 'No products available';
                select.appendChild(option);
            }
        });
    }

    // Add product row on button click
    document.getElementById('add-product-btn').addEventListener('click', function () {
        // Check if products are available
        if (typeof window.availableProducts === 'undefined' || window.availableProducts.length === 0) {
            // Check if an error message already exists
            let existingErrorMessage = document.querySelector('.error-message');
            if (existingErrorMessage) {
                existingErrorMessage.remove();
            }

            // Create a new error message
            const errorMessage = document.createElement('p');
            errorMessage.textContent = "No products available.";
            errorMessage.style.color = "#721c24"; // Brighter red text
            errorMessage.style.backgroundColor = "#f5c6cb"; // More visible light red background
            errorMessage.style.padding = "10px"; // Some padding around the message
            errorMessage.style.borderRadius = "5px"; // Rounded corners for the message box
            errorMessage.classList.add('error-message'); // Add a class for easy selection later

            // Get the "Add Product" button
            const addProductBtn = document.getElementById('add-product-btn');

            // Insert the error message before the button
            addProductBtn.parentNode.insertBefore(errorMessage, addProductBtn);
            return;
        }

        const tableBody = document.getElementById('product-table-body');
        const row = document.createElement('tr');

        let rowIndex = 1;
        // Product dropdown
        const productCell = document.createElement('td');
        const divContainer = document.createElement('div');
        divContainer.className = 'custom-select';
        const select = document.createElement('select');
        select.className = 'form-select product-dropdown';
        select.name = 'product_id[]';
        select.required = true;
        select.innerHTML = `<option value="" selected>Select Product...</option>`; // Initially empty, selected value

        // Populate the select options with available products
        if (window.availableProducts && window.availableProducts.length > 0) {
            window.availableProducts.forEach(product => {
                const option = document.createElement('option');
                option.value = product.product_id; // Set the product ID as value
                option.textContent = product.product_name; // Set the product name as display text
                option.setAttribute('data-purchase-price', product.purchase_price_per_unit); // Add the data-purchase-price attribute

                select.appendChild(option);
            });
        }

        // Append the select to div container and the container to product cell
        divContainer.appendChild(select);
        productCell.appendChild(divContainer);

        // Quantity input field
        const quantityCell = document.createElement('td');
        const quantityInput = document.createElement('input');
        quantityInput.type = 'number';
        quantityInput.name = 'product_quantity[]';
        quantityInput.className = 'form-control';
        quantityInput.min = 1;
        quantityInput.value = 1;
        quantityInput.required = true;
        quantityCell.appendChild(quantityInput);

        // Get combined price of product
        const priceCell = document.createElement('td');
        const priceInput = document.createElement('input');
        priceInput.type = 'number';
        priceInput.name = 'product_combined_price[]';
        priceInput.className = 'form-control';
        priceInput.style.color = 'black';
        priceInput.disabled = true;

        // Hidden input for form submission
        const hiddenPriceInput = document.createElement('input');
        hiddenPriceInput.type = 'hidden';
        hiddenPriceInput.name = 'product_combined_price[]';
        hiddenPriceInput.value = 0.00;

        priceInput.value = 0.00;
        priceCell.appendChild(priceInput);
        priceCell.appendChild(hiddenPriceInput);
        

        // Function to update the combined price
        function updatePrice() { //product price is saved from inventory database
            const selectedOption = select.options[select.selectedIndex];
            const price = parseFloat(selectedOption.getAttribute('data-purchase-price')) || 0;
            const quantity = parseInt(quantityInput.value, 10) || 1;
            const combinedPrice = (price * quantity).toFixed(2);

            priceInput.value = combinedPrice;
            hiddenPriceInput.value = combinedPrice;
            updateTotalPrice();
        }

        // // Call updatePrice when product or quantity changes
        select.addEventListener('change', updatePrice);
        quantityInput.addEventListener('input', updatePrice);

        // Delete button
        const actionCell = document.createElement('td');
        const deleteBtn = document.createElement('button');
        deleteBtn.type = 'button';
        deleteBtn.className = 'btn btn-danger';
        deleteBtn.textContent = 'Delete';
        deleteBtn.addEventListener('click', function () {
            row.remove(); // Remove the row when delete is clicked
            updateTotalPrice();
        });
        actionCell.appendChild(deleteBtn);

        // Append cells to row
        row.appendChild(productCell);
        row.appendChild(quantityCell);
        row.appendChild(priceCell);
        row.appendChild(actionCell);

        // Append the row to the table body
        tableBody.appendChild(row);
        rowIndex++;
    });

    function updateTotalPrice() {
        const priceInputs = document.querySelectorAll('input[name="product_combined_price[]"]');
        let total = 0;

        priceInputs.forEach(input => {
            total += parseFloat(input.value) || 0;
        });

        const totalPriceField = document.getElementById('total_price');
        const hiddenField = document.getElementById('total_price_hidden'); 

        totalPriceField.value = total.toFixed(2);
        hiddenField.value = total.toFixed(2);
    }
</script>
@endsection