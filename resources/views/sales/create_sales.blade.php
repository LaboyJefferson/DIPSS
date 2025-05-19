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
    .table-responsive {
        max-height: 400px;
        overflow-y: auto;
    }
    .totals-box {
        background: #444;
        padding: 15px;
        border-radius: 5px;
        margin-top: 20px;
    }

    input::-webkit-outer-spin-button,
    input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    input[type=number] {
        -moz-appearance: textfield;
    }
</style>

<div class="col-md-9 ml-sm-auto col-lg-10 px-md-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="text card-header text-center" style="background-color: #3a8f66; color: #fff; font-weight: bold;">{{ __('Sale Transaction') }}</div>
                <div class="card-body">
                    @include('common.alert')

                    <form id="search-form" class="search-box">
                        @csrf
                        <input type="text" id="stock-search" class="form-control" 
                               placeholder="Stock Search..." autocomplete="off">
                        <button type="submit" class="btn btn-secondary">Search</button>
                    </form>

                    <form id="sales-form" method="POST" action="{{ route('sales.store') }}">
                        @csrf

                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Qty</th>
                                        <th>Name</th>
                                        <th>Unit</th>
                                        <th>Tax</th>
                                        <th>Price</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody id="items-table">
                                    <!-- Dynamic items will be added here -->
                                </tbody>
                            </table>
                        </div>

                        <div class="totals-box">
                            <div class="row text-white">
                                <div class="col-md-6">
                                    <div class="d-flex justify-content-between">
                                        <span>Subtotal:</span>
                                        <span id="subtotal">₱0.00</span>
                                        <input type="hidden" id="subtotal_amnt" name="subtotal_amnt">
                                    </div>
                                <div class="d-flex justify-content-between">
                                    <span>Discount:</span>
                                    <div class="d-inline-flex">
                                        <div class="input-group" style="max-width: 70px;">
                                            <input type="number" class="form-control" style="padding:3 5" id="discount-input" 
                                                min="0" max="100" value="0">
                                            <span class="input-group-text" style="padding:0 5;margin:0;background-color: #212529;border:0;color:white;">%</span>
                                        </div>
                                        <span id="discount-amount" style="margin:auto;">(₱0.00)</span>
                                        <input type="hidden" id="discount_amnt" name="discount_amnt">
                                    </div>
                                </div>
                                    <div class="d-flex justify-content-between">
                                        <span>Tax:</span>
                                        <span id="tax">₱0.00</span>
                                        <input type="hidden" id="tax_amnt" name="tax_amnt">
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <strong>Total:</strong>
                                        <strong id="total">₱0.00</strong>
                                        <input type="hidden" id="total_amnt" name="total_amnt">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Customer Email</label>
                                        <input type="email" class="form-control" name="customer_email">
                                    </div>
                                    <div class="form-group">
                                        <label>Notes</label>
                                        <textarea class="form-control" name="notes"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <div>
                                <button type="button" class="btn btn-secondary me-2">Cancel</button>
                                <button type="button" class="btn btn-success" id="process-btn">Process</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="checkoutModal" tabindex="-1" aria-labelledby="checkoutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background-color: #565656; color: white;">
            <div class="modal-header" style="border-bottom: 1px solid #3a8f66;">
                <h5 class="modal-title" id="checkoutModalLabel">CHECKOUT</h5>
                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close" style="filter: invert(1);"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-6"><strong>Total:</strong></div>
                    <div class="col-6 text-end"><span id="modal-total">₱0.00</span></div>
                </div>

                <!-- Payment Method Selection -->
                <div class="row mb-3">
                    <div class="col-12">
                        <div class="btn-group w-100" role="group">
                            <button type="button" class="btn btn-outline-light payment-method" data-method="cash">Cash</button>
                            <button type="button" class="btn btn-outline-light payment-method" data-method="gcash">GCASH</button>
                            <!-- <button type="button" class="btn btn-outline-light payment-method" data-method="eftpos">EFTPOS</button>
                            <button type="button" class="btn btn-outline-light payment-method" data-method="cheque">Cheque</button> -->
                        </div>
                        <input type="hidden" id="selected-payment-method" name="payment_method">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-6"><strong>Balance:</strong></div>
                    <div class="col-6 text-end"><span id="modal-balance">₱0.00</span></div>
                </div>

                <div class="row mb-3">
                    <div class="col-6"><strong>Tendered:</strong></div>
                    <div class="col-6">
                        <div class="input-group">
                            <span class="input-group-text">₱</span>
                            <input type="number" class="form-control" id="tendered-amount" 
                                   step="0.01" min="0" value="0.00">
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-6"><strong>Payments:</strong></div>
                    <div class="col-6 text-end"><span id="modal-payments">₱0.00</span></div>
                </div>

                <div class="row mb-3">
                    <div class="col-6"><strong>Change:</strong></div>
                    <div class="col-6 text-end"><span id="modal-change">₱0.00</span></div>
                </div>

                <div class="row mt-3">
                    <div class="col-12">
                        <div class="d-grid gap-2 d-flex justify-content-end">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                            <button type="button" id="complete-sale" class="btn btn-success">Complete</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded');
    const searchForm = document.getElementById('search-form');
    const stockSearch = document.getElementById('stock-search');
    const itemsTable = document.getElementById('items-table');
    const discountInput= document.getElementById('discount-input');
    const discountAmt= document.getElementById('discount-amount');
    let committedDiscount = 0;
    let items = [];

    const processBtn = document.getElementById('process-btn');
    const checkoutModal = new bootstrap.Modal(document.getElementById('checkoutModal'));
    const modalTotal = document.getElementById('modal-total');
    const tenderedInput = document.getElementById('tendered-amount');
    const modalChange = document.getElementById('modal-change');
    const completeBtn = document.getElementById('complete-sale');

    const paymentMethods = document.querySelectorAll('.payment-method');
    const selectedMethod = document.getElementById('selected-payment-method');
    const modalBalance = document.getElementById('modal-balance');
    const modalPayments = document.getElementById('modal-payments');

    // Handle search form submission
    searchForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const searchTerm = stockSearch.value.trim();
        if (searchTerm.length >= 2) {
            fetchProduct(searchTerm);
        }
    });

    discountInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            updateDiscount();
        }
    });
    discountInput.addEventListener('blur', () => updateDiscount());

    processBtn.addEventListener('click', function(e) {
        e.preventDefault();
        const totalAmount = parseFloat(document.getElementById('total').textContent.replace('₱', ''));
        
        // Reset modal state
        paymentMethods.forEach(m => m.classList.remove('active'));
        selectedMethod.value = '';
        tenderedInput.value = totalAmount.toFixed(2);
        
        modalTotal.textContent = `₱${totalAmount.toFixed(2)}`;
        updatePaymentCalculations();
        checkoutModal.show();
    });

    paymentMethods.forEach(method => {
        method.addEventListener('click', function() {
            paymentMethods.forEach(m => m.classList.remove('active'));
            this.classList.add('active');
            selectedMethod.value = this.dataset.method;
            updatePaymentCalculations();
        });
    });

    // Handle tendered amount changes
    tenderedInput.addEventListener('input', updatePaymentCalculations);

    function updateDiscount() {
        committedDiscount = parseFloat(discountInput.value) || 0;
        if (committedDiscount < 0) committedDiscount = 0;
        if (committedDiscount > 100) committedDiscount = 100;
        discountInput.value = committedDiscount; // Ensure valid value
        updateTotals();
    }

    async function fetchProduct(searchTerm) {
        try {
            const response = await fetch('{{ route('fetch.product') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ search: searchTerm })
            });
            
            const data = await response.json();
            if (data.products && data.products.length > 0) {
                // Show all matches in console for debugging
                console.log('Found products:', data.products);
                // Add first matching product to table
                addProductToTable(data.products[0]);
                stockSearch.value = '';
            } else {
                console.log('No products found');
            }
        } catch (error) {
            console.error('Error:', error);
        }
    }

    function addProductToTable(product) {
        const existingRow = itemsTable.querySelector(`tr[data-product-id="${product.id}"]`);
        const itemCount = document.querySelectorAll('#items-table tr').length;
        
        // Calculate prices
        const basePrice = product.price;
        const taxRate = product.tax_rate;
        const taxAmount = basePrice * taxRate;
        const unitPrice = basePrice + taxAmount;

        if (existingRow) {
            const qtyInput = existingRow.querySelector('.quantity');
            qtyInput.value = parseInt(qtyInput.value) + 1;
            updateItem(existingRow);
            return;
        }

        const row = document.createElement('tr');
        row.dataset.productId = product.id;
        row.innerHTML = `
            <td>
                <div class="quantity-wrapper">
                    <input type="number" class="form-control quantity" value="1" min="1">
                    <button class="btn btn-sm btn-success confirm-qty" style="display: none;">✓</button>
                </div>
            </td>
            <td>${product.name}</td>
            <td class="base-price">₱${basePrice.toFixed(2)}</td>
            <td>${taxRate > 0 ? (taxRate*100 + '%') : 'No Tax'}</td>
            <td class="unit-price">₱${unitPrice.toFixed(2)}</td>
            <td><button class="btn btn-danger btn-sm remove-item">X</button></td>
            <input type="hidden" name="items[${itemCount}][product_id]" value="${product.id}">
            <input type="hidden" name="items[${itemCount}][inventory_id]" value="${product.hidden_id}">
            <input type="hidden" name="items[${itemCount}][price]" value="${product.price}">
            <input type="hidden" name="items[${itemCount}][quantity]" value="1">
            <input type="hidden" name="items[${itemCount}][tax_rate]" value="${product.tax_rate}">
        `;

        const qtyInput = row.querySelector('.quantity');
        
        qtyInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                updateItem(row);
            }
        });

        // Update on input blur
        qtyInput.addEventListener('blur', () => updateItem(row));

        row.querySelector('.remove-item').addEventListener('click', () => {
            row.remove();
            reindexItems();
            updateTotals();
        });

        itemsTable.appendChild(row);
        updateTotals();
    }

    // New function to reindex items after removal
    function reindexItems() {
        document.querySelectorAll('#items-table tr').forEach((row, index) => {
            row.querySelectorAll('input').forEach(input => {
                input.name = input.name.replace(/items\[\d+\]/g, `items[${index}]`);
            });
        });
    }

    function updateItem(row) {
        const qtyInput = row.querySelector('.quantity');
        const qty = parseInt(qtyInput.value) || 0;

        
        // Update hidden fields
        row.querySelector('input[name$="[quantity]"]').value = qty;
        
        // Recalculate prices
        const basePrice = parseFloat(row.querySelector('.base-price').textContent.replace('₱', ''));
        const taxRate = parseFloat(row.querySelector('input[name$="[tax_rate]"]').value);
        
        const taxAmount = basePrice * qty * taxRate;
        const unitPrice = (basePrice * qty) + taxAmount;
        
        row.querySelector('.unit-price').textContent = `₱${unitPrice.toFixed(2)}`;
        
        updateTotals();
    }

    function updateTotals() {
        let subtotal = 0;
        let tax = 0;

        itemsTable.querySelectorAll('tr').forEach(row => {
            const basePrice = parseFloat(row.querySelector('.base-price').textContent.replace('₱', ''));
            const qty = parseInt(row.querySelector('input[name$="[quantity]"]').value) || 0;
            const taxRate = parseFloat(row.querySelector('input[name$="[tax_rate]"]').value);
            
            subtotal += basePrice * qty;
            tax += basePrice * qty * taxRate;
        });

        const discountAmount = subtotal * (committedDiscount / 100);
        const total = (subtotal - discountAmount) + tax;
        
        document.getElementById('subtotal').textContent = `₱${subtotal.toFixed(2)}`;
        document.getElementById('discount-amount').textContent = `(₱${discountAmount.toFixed(2)})`;
        document.getElementById('tax').textContent = `₱${tax.toFixed(2)}`;
        document.getElementById('total').textContent = `₱${total.toFixed(2)}`;

        document.getElementById('subtotal_amnt').value = subtotal.toFixed(2);
        document.getElementById('discount_amnt').value = discountAmount.toFixed(2);
        document.getElementById('tax_amnt').value = tax.toFixed(2);
        document.getElementById('total_amnt').value = total.toFixed(2);
    }

    function debounce(func, timeout = 300) {
        let timer;
        return (...args) => {
            clearTimeout(timer);
            timer = setTimeout(() => func.apply(this, args), timeout);
        };
    }

    function updatePaymentCalculations() {
        const total = parseFloat(modalTotal.textContent.replace('₱', ''));
        const tendered = parseFloat(tenderedInput.value) || 0;
        const payments = Math.min(tendered, total);
        const balance = total - payments;
        const change = Math.max(tendered - total, 0);

        modalPayments.textContent = `₱${payments.toFixed(2)}`;
        modalBalance.textContent = `₱${balance.toFixed(2)}`;
        modalChange.textContent = `₱${change.toFixed(2)}`;
    }

    // Handle complete sale
    completeBtn.addEventListener('click', function() {
        const total = parseFloat(modalTotal.textContent.replace('₱', ''));
        const tendered = parseFloat(tenderedInput.value);
        
        if (tendered < total) {
            alert('Tendered amount must be at least equal to total');
            return;
        }

        const balance = parseFloat(modalBalance.textContent.replace('$', ''));
        if (balance > 0) {
            if (!confirm('Balance not fully paid. Continue anyway?')) return;
        }

        // Add payment method to form data
        const paymentInput = document.createElement('input');
        paymentInput.type = 'hidden';
        paymentInput.name = 'payment_method';
        paymentInput.value = selectedMethod.value;
        document.getElementById('sales-form').appendChild(paymentInput);

        // Add payment amount to form data
        const amountInput = document.createElement('input');
        amountInput.type = 'hidden';
        amountInput.name = 'payment_amount';
        amountInput.value = parseFloat(tenderedInput.value).toFixed(2);
        document.getElementById('sales-form').appendChild(amountInput);

        document.getElementById('sales-form').submit();
        checkoutModal.hide();
    });
});
</script>
@endsection