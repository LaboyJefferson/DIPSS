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
                <div class="col-md-10">
                    <div class="card">
                        <div class="card-header text-center text-white" style="background-color: #3a8f66; color:#fff; font-weight: bold; ">{{ __('Edit Order') }}</div>
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

                            <form method="POST" action="{{ route('update_order', ['id' => $order->purchase_order_id]) }}" enctype="multipart/form-data">
                                @csrf
                                
                                <!-- Order Details 1 - Payment Method Field, Order Type Field -->
                                <div class="row mb-2">
                                    <div class="col-md-4">
                                        <label class="input-group-text" for="payment_method">
                                            <i class="fa-solid fa-cash-register" style="margin-right: 5px;"></i>Payment Method&nbsp;<i>*Required</i>
                                        </label>
                                        <div class="custom-select">
                                            <select id="payment_method" class="form-select" name="payment_method" required>
                                                <option value="" disabled>Select payment method...</option>
                                                <option value="Cash on Delivery (COD)" {{ $order->payment_method == 'Cash on Delivery (COD)' ? 'selected' : '' }}>Cash on Delivery (COD)</option>
                                                <option value="Credit/Debit Card" {{ $order->payment_method == 'Credit/Debit Card' ? 'selected' : '' }}>Credit/Debit Card</option>
                                                <option value="Paypal" {{ $order->payment_method == 'Paypal' ? 'selected' : '' }}>PayPal</option>
                                                <option value="GCash" {{ $order->payment_method == 'GCash' ? 'selected' : '' }}>GCash</option>
                                            </select>
                                        </div>
                                        @error('payment_method')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>

                                    <div class="col-md-4">
                                        <label class="input-group-text" for="order_type">
                                            <i class="fa-solid fa-user" style="margin-right: 5px;"></i> Select Order Type &nbsp;<i>*Required</i>
                                        </label>
                                        <div class="custom-select">
                                            <select id="order_type" class="form-select" name="order_type" required>
                                                <option value="" disabled>Select order type...</option>
                                                <option value="Purchasing Order" {{ $order->type == 'Purchasing Order' ? 'selected' : '' }}>Purchase Order</option>
                                                <option value="Backorder" {{ $order->type == 'Backorder' ? 'selected' : '' }}>Backorder</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Order Details 2: Billing address, Shipping address-->
                                <div class="row mb-2">
                                    {{-- <div class="col-md-6">
                                        <label class="input-group-text" for="billing_address">
                                            <i class="fa-solid fa-file-invoice" style="margin-right: 5px;"></i>Billing Address&nbsp;<i>*Required</i>
                                        </label>
                                        <p id="billing-address" class="form-control" style="border: 1px solid #ced4da; padding: 10px; border-radius: 5px;"></p>
                                        @error('billing_address')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span> 
                                        @enderror
                                    </div> --}}
                                    
                                    <div class="col-md-6">
                                        <label class="input-group-text" for="shipping_address">
                                            <i class="fa-solid fa-map-marker-alt" style="margin-right: 5px;"></i>Shipping Address&nbsp;<i>*Required</i>
                                        </label>
                                        <div class="custom-select">
                                            <select id="address_dropdown" class="form-select" name="address_dropdown">
                                                <option value="">Select a shipping address..</option>
                                                @foreach($addresses as $address)
                                                    <option value="{{ $address->address }}" {{ $address->address == $order->shipping_address ? 'selected' : '' }}>{{ $address->address }}</option>
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

                                    <div class="col-md-4">
                                        <label class="input-group-text" for="total_price">
                                            <i class="fa-solid fa-box-open" style="margin-right: 5px;"></i>Total Price&nbsp;<i>*read-only</i>
                                        </label>
                                        <input id="total_price" style="color: white; pointer-events: none;"  type="number" value="0" class="form-control @error('total_price') is-invalid @enderror">
                                        <input type="hidden" name="total_price" id="total_price_hidden">
                                        @error('total_price')
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
                                    <!-- Dropdown for existing suppliers -->
                                    {{-- <div class="col-md-4">
                                        <label class="input-group-text" for="supplier_dropdown">
                                            <i class="fa-solid fa-user" style="margin-right: 5px;"></i> Select Supplier &nbsp;<i>*Required</i>
                                        </label>
                                        <div class="custom-select">
                                            <select id="supplier_dropdown" class="form-select" name="supplier_dropdown" required>
                                                <option value="">Select available supplier...</option>
                                                @foreach($suppliers as $supplier)
                                                    <option value="{{ $supplier->supplier_id }}"  {{ $supplier->supplier_id == $order->supplier_id ? 'selected' : '' }}>{{ $supplier->company_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div> --}}
                                </div>
                                
                                <!-- Table for showing products for the selected supplier -->
                                <div id="product-table-container" >
                                    <h4 class="h2">Products to Add</h4>
                                    <table id="product-table" class="table table-responsive mt-4">
                                        <thead>
                                            <tr>
                                                <th>Supplier</th>
                                                <th>Product</th>
                                                <th>Quantity</th>
                                                <th>Price <span style="font-weight: normal;"><i>*read-only</i></span></th>
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

                                <!-- Hidden inputs -->
                                <input type="hidden" name="user_id" value="{{ Auth::user()->user_id }}">
                                <input type="hidden" name="billing_address" id="billing_address_hidden" value="{{ $order->billing_address }}">

                                <div class="row mb-3">
                                    <div class="col-md-6 d-flex justify-content-end">
                                        <a class="btn btn-success" href="{{ route('purchase_order') }}">Go Back</a>
                                    </div>
                                    <div class="col-md-6 d-flex justify-content-start">
                                        <button type="submit" class="btn btn-primary">
                                            {{ __('Update Order') }}
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
        const products = @json($products);  // Available products
        const suppliers = @json($suppliers);  // Available suppliers
        const orderItems = @json($order->order_items);  // Order items from the existing order
        const supplierDetails = @json($order->suppliers); // Supplier details for the order
        const orderSuppliers = @json($order_suppliers);
        const supplierProductMap = @json($supplierProductMap);
        
        // Address dropdown (same as create page)
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

        // Function to populate the products and suppliers based on existing order items
        function populateOrderItems() {
            const tableBody = document.getElementById('product-table-body');

            orderItems.forEach((orderItem, index) => {
                const row = document.createElement('tr');

                // Supplier dropdown (first column)
                const supplierCell = document.createElement('td');
                const supplierContainer = document.createElement('div');
                supplierContainer.className = 'custom-select';
                const selectSupplier = document.createElement('select');
                selectSupplier.className = 'form-select supplier-dropdown';
                selectSupplier.name = 'supplier_id[]';
                selectSupplier.required = false;
                selectSupplier.innerHTML = `<option value="" selected>Select Supplier...</option>`; // Initially empty, selected value

                // Populate the select options with available suppliers
                suppliers.forEach(supplier => {
                    const option = document.createElement('option');
                    option.value = supplier.supplier_id;
                    option.textContent = supplier.company_name;

                    // Check if this supplier is linked to the current order item via order_supplier
                    const linkedSupplier = orderSuppliers.find(orderSupplier => orderSupplier.purchase_order_id === orderItem.purchase_order_id && orderSupplier.supplier_id === supplier.supplier_id);
                    
                    // If linked, mark this supplier as selected
                    if (linkedSupplier) {
                        option.selected = true;
                    }

                    selectSupplier.appendChild(option);
                });

                // Add the "Add New" option at the end
                const addNewOption = document.createElement('option');
                addNewOption.value = 'add-new';
                addNewOption.textContent = 'Add New Supplier';
                selectSupplier.appendChild(addNewOption);

                supplierContainer.appendChild(selectSupplier);
                supplierCell.appendChild(supplierContainer);

                // Product dropdown (second column)
                const productCell = document.createElement('td');
                const productContainer = document.createElement('div');
                productContainer.className = 'custom-select';
                const selectProduct = document.createElement('select');
                selectProduct.className = 'form-select product-dropdown';
                selectProduct.name = 'product_id[]';
                selectProduct.required = true;
                selectProduct.innerHTML = `<option value="" selected>Select Product...</option>`; // Initially empty, selected value

                // Populate the select options with available products
                products.forEach(product => {
                    const option = document.createElement('option');
                    option.value = product.product_id;
                    option.textContent = product.product_name;
                    if (product.product_id === orderItem.product_id) {
                        option.selected = true; // Mark as selected for this order item
                    }
                    selectProduct.appendChild(option);
                });

                productContainer.appendChild(selectProduct);
                productCell.appendChild(productContainer);

                // Function to populate products based on selected supplier for this row
                function populateProductsForSupplier(supplierId, selectedProductId = null) {
                    selectProduct.innerHTML = '<option value="" selected>Select Product...</option>';

                    const productsForSupplier = supplierProductMap[supplierId] || [];

                    productsForSupplier.forEach(product => {
                        const option = document.createElement('option');
                        option.value = product.product_id;
                        option.textContent = product.product_name;
                        if (product.product_id === selectedProductId) {
                            option.selected = true;
                        }
                        selectProduct.appendChild(option);
                    });
                }

                // Populate products if supplier already selected
                const initialSupplierId = selectSupplier.value;
                if (initialSupplierId && initialSupplierId !== 'add-new') {
                    populateProductsForSupplier(initialSupplierId, orderItem.product_id);
                }

                // When supplier changes, re-populate the product dropdown
                selectSupplier.addEventListener('change', function () {
                    if (this.value && this.value !== 'add-new') {
                        populateProductsForSupplier(this.value);
                    } else {
                        selectProduct.innerHTML = '<option value="" selected>Select Product...</option>';
                    }
                });

                // Quantity input field (third column)
                const quantityCell = document.createElement('td');
                const quantityInput = document.createElement('input');
                quantityInput.type = 'number';
                quantityInput.name = 'product_quantity[]';
                quantityInput.className = 'form-control';
                quantityInput.min = 1;
                quantityInput.value = orderItem.quantity;  // Pre-fill with the quantity from orderItem
                quantityInput.required = true;
                quantityInput.style.width = '100px';
                quantityCell.appendChild(quantityInput);

                // Price input field (fourth column) - assuming you have a price for each product
                const priceCell = document.createElement('td');
                const priceInput = document.createElement('input');
                priceInput.type = 'number';
                priceInput.name = 'product_price[]';
                priceInput.className = 'form-control';
                priceInput.value = orderItem.price || 0; // Pre-fill with the price from orderItem
                priceInput.style.pointerEvents = "none"; // If the price should be read-only
                priceInput.style.width = '150px';
                priceCell.appendChild(priceInput);

                // Delete button (fifth column)
                const actionCell = document.createElement('td');
                const deleteBtn = document.createElement('button');
                deleteBtn.type = 'button';
                deleteBtn.className = 'btn btn-danger';
                deleteBtn.textContent = 'Remove';
                deleteBtn.addEventListener('click', function () {
                    row.remove(); // Remove the row when delete is clicked
                    updateTotalPrice();
                });
                actionCell.appendChild(deleteBtn);

                let unitPrice = parseFloat(orderItem.price / orderItem.quantity); // Estimate unit price

                selectProduct.addEventListener('change', function () {
                    const supplierId = selectSupplier.value;
                    const selectedProductId = this.value;
                    const productList = supplierProductMap[supplierId] || [];
                    const selectedProduct = productList.find(p => p.product_id == selectedProductId);
                    
                    unitPrice = selectedProduct ? parseFloat(selectedProduct.price) : 0;

                    const quantity = parseFloat(quantityInput.value || 0);
                    priceInput.value = (unitPrice * quantity).toFixed(2);
                    updateTotalPrice();
                });

                quantityInput.addEventListener('input', function () {
                    const quantity = parseFloat(this.value || 0);
                    priceInput.value = (unitPrice * quantity).toFixed(2);
                    updateTotalPrice();
                });

                // Append cells to row
                row.appendChild(supplierCell);
                row.appendChild(productCell);
                row.appendChild(quantityCell);
                row.appendChild(priceCell);
                row.appendChild(actionCell);

                // Append the row to the table body
                tableBody.appendChild(row);
            });
        }

        function createEmptyRow() {
            const row = document.createElement('tr');

            // --- Supplier cell ---
            const supplierCell = document.createElement('td');
            const supplierDIV = document.createElement('div');
            supplierDIV.className = 'custom-select';
            const supplierSelect = document.createElement('select');
            supplierSelect.className = 'form-select supplier-dropdown';
            supplierSelect.name = 'supplier_id[]';
            supplierSelect.required = true;
            supplierSelect.innerHTML = '<option value="" selected>Select Supplier...</option>';
            suppliers.forEach(supplier => {
                const option = document.createElement('option');
                option.value = supplier.supplier_id;
                option.textContent = supplier.company_name;
                supplierSelect.appendChild(option);
            });
            supplierDIV.appendChild(supplierSelect);
            supplierCell.appendChild(supplierDIV);

            // --- Product cell ---
            const productCell = document.createElement('td');
            const productDIV = document.createElement('div');
            productDIV.className = 'custom-select';
            const productSelect = document.createElement('select');
            productSelect.className = 'form-select product-dropdown';
            productSelect.name = 'product_id[]';
            productSelect.required = true;
            productSelect.innerHTML = '<option value="" selected>Select Product...</option>';
            productDIV.appendChild(productSelect);
            productCell.appendChild(productDIV);

            // --- Quantity cell ---
            const quantityCell = document.createElement('td');
            const quantityInput = document.createElement('input');
            quantityInput.type = 'number';
            quantityInput.name = 'product_quantity[]';
            quantityInput.className = 'form-control';
            quantityInput.min = 1;
            quantityInput.value = 1;
            quantityInput.required = true;
            quantityInput.style.width = '100px';
            quantityCell.appendChild(quantityInput);

            // --- Price cell ---
            const priceCell = document.createElement('td');
            const priceInput = document.createElement('input');
            priceInput.type = 'number';
            priceInput.name = 'product_price[]';
            priceInput.className = 'form-control';
            priceInput.readOnly = true;
            priceInput.style.pointerEvents = "none";
            priceInput.style.width = '150px';
            priceCell.appendChild(priceInput);

            // --- Actions cell ---
            const actionCell = document.createElement('td');
            const deleteBtn = document.createElement('button');
            deleteBtn.type = 'button';
            deleteBtn.className = 'btn btn-danger';
            deleteBtn.textContent = 'Remove';
            deleteBtn.addEventListener('click', function () {
                row.remove();
                updateTotalPrice?.();
            });
            actionCell.appendChild(deleteBtn);

            // --- Supplier change logic ---
            supplierSelect.addEventListener('change', function () {
                const supplierId = this.value;
                productSelect.innerHTML = '<option value="" selected>Select Product...</option>';
                if (supplierId && supplierId !== 'add-new') {
                    const productsForSupplier = supplierProductMap[supplierId] || [];
                    productsForSupplier.forEach(product => {
                        const option = document.createElement('option');
                        option.value = product.product_id;
                        option.textContent = product.product_name;
                        productSelect.appendChild(option);
                    });
                }
            });

            let unitPrice = 0;

            // --- Product change logic (update price) ---
            productSelect.addEventListener('change', function () {
                const supplierId = supplierSelect.value;
                const selectedProductId = this.value;
                const productList = supplierProductMap[supplierId] || [];
                const selectedProduct = productList.find(p => p.product_id == selectedProductId);
                
                unitPrice = selectedProduct ? parseFloat(selectedProduct.price) : 0;

                // Recalculate price based on quantity
                const quantity = parseFloat(quantityInput.value || 0);
                priceInput.value = (unitPrice * quantity).toFixed(2);

                updateTotalPrice();
            });

            // When quantity changes
            quantityInput.addEventListener('input', function () {
                const quantity = parseFloat(this.value || 0);
                priceInput.value = (unitPrice * quantity).toFixed(2);
                updateTotalPrice();
            });

            // When product changes (price updates too)
            productSelect.addEventListener('change', function () {
                const supplierId = supplierSelect.value;
                const selectedProductId = this.value;
                const productList = supplierProductMap[supplierId] || [];
                const selectedProduct = productList.find(p => p.product_id == selectedProductId);
                priceInput.value = selectedProduct ? selectedProduct.price : '';
                updateTotalPrice(); // <- Add this
            });

            // Append cells to row
            row.appendChild(supplierCell);
            row.appendChild(productCell);
            row.appendChild(quantityCell);
            row.appendChild(priceCell);
            row.appendChild(actionCell);

            return row;
        }

        document.getElementById('add-product-btn').addEventListener('click', function () {
            const newRow = createEmptyRow();
            document.getElementById('product-table-body').appendChild(newRow);
        });

        function updateTotalPrice() {
            let total = 0;

            // Loop through all product rows
            const rows = document.querySelectorAll('#product-table-body tr');
            rows.forEach(row => {
                const quantityInput = row.querySelector('input[name="product_quantity[]"]');
                const priceInput = row.querySelector('input[name="product_price[]"]');

                const quantity = parseFloat(quantityInput?.value || 0);
                const price = parseFloat(priceInput?.value || 0);

                if (!isNaN(quantity) && !isNaN(price)) {
                    total += price;
                }
            });

            // Update the visible total and hidden input
            document.getElementById('total_price').value = total.toFixed(2);
            document.getElementById('total_price_hidden').value = total.toFixed(2);
        }

        // Initialize the order items when the page loads
        window.onload = function () {
            populateOrderItems(); // Populate the product rows with existing data
            updateTotalPrice();
        };

    </script>
@endsection