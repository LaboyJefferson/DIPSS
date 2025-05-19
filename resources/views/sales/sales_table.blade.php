@extends('layouts.app')

<!-- Include the vertical navigation bar -->
@include('common.navbar')

@section('content')

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
        color: #fff;
        background-color: #565656; 
        border-radius: 5px; /* Slightly rounded corners */
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5); 
    }

    h1.h2 {
        color: #fff; /* Change this to your desired color */
    }

    .table th, td {
        background-color: #565656; /* Set background color for all table headers */
        color: #ffffff;
    }

    /*Date Picker*/
    .form-control {
        background-color: #212529; /* White input background */
        color: #fff; /* Black text */
        border: 1px solid #212529; /* Subtle border */
    }

    .form-control:focus {
        background-color: #212529;
        color: #fff; /* Black text */
        border-color: 1px solid #3a8f66; /* Green border on focus */
        box-shadow: none; /* Remove default shadow */
    }

    .input-group .input-group-text {
        background-color: #198754; /* Background for 'to' text */
        color: white; /* Text color */
        border-radius: 5px; /* Rounded corners */
        border: none;
    }

    .btn-success {
        background-color: #28a745; /* Button color */
        border-color: #28a745; /* Border color */
    }

    .table th, td {
        background-color: #565656 !important; /* Set background color for all table headers */
        color: #ffffff !important;
    }

    .modal-content {
        color: #fff; /* This will apply to all text in the modal */
        margin: 20px 15px;
    }

    .modal-header, .modal-footer {
        margin-bottom: 15px; /* Space between header/footer and body */
    }

    .modal-body {
        margin-top: 10px; /* Space above the body content */
    }

    /* Optional: For better spacing around specific elements */
    .form-group {
        margin-bottom: 1rem; /* Space below each form group */
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

</style>

@if(Auth::user()->role == 'Salesperson') 
    <div class="container-fluid">
        <main class="col-md-9 ml-sm-auto col-lg-10 px-md-4">
            <div class="main-content">
                    <!-- Alert Messages -->
                @include('common.alert')
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center mb-3">
                    <h1 class="h2">Sales Management</h1>
                    {{-- <div class="d-flex">
                        <a class="btn btn-success" href="{{ route('sales.create') }}">+ Sale Product</a>
                        <a class="btn btn-warning ms-2" href="{{ route('return_product_table') }}" style="margin-left: -5px;">Returned Products View</a>
                    </div> --}}
                </div>

                <!-- Search Bar -->
                <div class="d-flex justify-content-between align-items-center">
                    <!-- Search Form -->
                    <form class="d-flex align-items-center" role="search" id="searchForm" style="gap: 0.5rem;">
                        <input 
                            class="form-control" 
                            type="search" 
                            placeholder="Search by Sales ID" 
                            aria-label="Search" 
                            id="searchInput" 
                            style="width: 30em; padding: 5px; font-size: 14px;"
                        >
                        <button class="btn btn-success" type="submit">Search</button>
                    </form>

                    <div class="d-flex justify-content-end">
                        <div class="ms-3">
                            <a class="btn btn-success" href="{{ route('sales.create') }}">+ Sale Product</a>
                            {{-- <a class="btn btn-warning ms-2" href="{{ route('return_product_table') }}">Returned Products View</a> --}}
                        </div>
                    </div>
                </div>

                <div id="searchResults" class="dropdown mt-2" style="display: none;">
                    <ul class="dropdown-menu" id="resultsList"></ul>
                </div>

                <!-- Table to Display Search Results -->
                <table class="table table-responsive mt-4" id="searchResultsTable" style="display: none;">
                    <thead>
                        <tr>
                            <th>Ref. No.</th>
                            <th>Product Name</th>
                            <th>Quantity</th>
                            <th>Sale Price</th>
                            <th>Amount</th>
                            <th>Total Amount</th>
                            <th>Sales Timestamp</th>
                            <th>Description</th>
                            <th>Action</th>
                            <th>Receipt</th>
                        </tr>
                    </thead>
                    <tbody id="selectedSaleDetails">
                        <!-- Selected sales details will appear here -->
                    </tbody>
                </table>

                <!-- Table Section for All Sales -->
                <table class="table table-responsive" id="allSalesTable">
                    <thead>
                        <tr>
                            <th>Ref. No.</th>
                            <th>Product Name</th>
                            <th>Quantity</th>
                            <th>Sale Price</th>
                            <th>Amount</th>
                            <th>Total Amount</th>
                            <th>Transaction Date</th>
                            <th>Description</th>
                            <th>Action</th>
                            <th>Receipt</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $currentSalesId = null; // Variable to track current sales ID
                        @endphp

                        @forelse($salesGrouped as $sales)
                            @foreach($sales as $index => $data)
                                <tr>
                                    @if($index === 0) <!-- Display this only for the first product -->
                                        <td rowspan="{{ count($sales) }}">{{ $data->sales_id }}</td> <!-- Merge cells for sales_id -->
                                        <td>{{ $data->product_name }}</td>
                                        <td>{{ $data->sales_quantity }}</td>
                                        <td>{{ number_format($data->sale_price_per_unit, 2) }}</td>
                                        <td>{{ number_format($data->amount, 2) }}</td>
                                        <td rowspan="{{ count($sales) }}">{{ number_format($data->total_amount, 2) }}</td>
                                        <td>{{ $data->sales_date }}</td>
                                        <td>
                                            <button type="button" class="btn" onclick="showDescriptionDetail('{{ $data->descriptionArray['color'] ?? 'N/A' }}', '{{ $data->descriptionArray['size'] ?? 'N/A' }}', '{{ $data->descriptionArray['description'] ?? 'N/A' }}')">
                                                <strong style="color: white; text-decoration: none; font-weight: normal;" >more info.</strong>
                                            </button>
                                        </td>
                                        <td>
                                            @if ($data->return_product_id === null && $data->sales_date > $deadline)
                                                <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#returnModal{{ $data->sales_details_id }}">
                                                    Return
                                                </button>
                                            @else
                                                <button type="button" class="btn btn-warning" disabled>
                                                    Return
                                                </button>
                                            @endif
                                        </td>
                                        <td rowspan="{{ count($sales) }}">
                                            <a href="{{ route('sales.receipt', $data->sales_id) }}" 
                                                class="btn btn-info btn-sm"
                                                target="_blank"> {{-- Add target="_blank" --}}
                                                View
                                            </a>
                                        </td>
                                    @else
                                    <td>{{ $data->product_name }}</td>
                                    <td>{{ $data->sales_quantity }}</td>
                                    <td>{{ number_format($data->sale_price_per_unit, 2) }}</td>
                                    <td>{{ number_format($data->amount, 2) }}</td>
                                    <td>{{ $data->sales_date }}</td>
                                    <td>
                                        <button type="button" class="btn" onclick="showDescriptionDetail('{{ $data->descriptionArray['color'] ?? 'N/A' }}', '{{ $data->descriptionArray['size'] ?? 'N/A' }}', '{{ $data->descriptionArray['description'] ?? 'N/A' }}')">
                                            <strong style="color: white; text-decoration: none; font-weight: normal;" >more info.</strong>
                                        </button>
                                    </td>
                                    <td>
                                        @if ($data->return_product_id === null && $data->sales_date > $deadline)
                                            <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#returnModal{{ $data->sales_details_id }}">
                                                Return
                                            </button>
                                        @else
                                            <button type="button" class="btn btn-warning" disabled>
                                                Return
                                            </button>
                                        @endif
                                    </td>
                                    @endif
                                </tr>
                            @endforeach
                            <!-- Return Modal for Each Product -->
                            @foreach ($salesGrouped as $sales)
                                @foreach($sales as $data)
                                <div class="modal fade" id="returnModal{{ $data->sales_details_id }}" tabindex="-1" role="dialog" aria-labelledby="returnModalLabel" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content" style="margin: 20px 15px; background-color:#565656;">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="returnModalLabel">Return Product</h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <form id="returnForm{{ $data->sales_details_id }}" action="{{ route('return_product.process', $data->sales_details_id) }}" method="POST">

                                                @csrf
                                                <input type="hidden" name="sales_id" value="{{ $data->sales_id }}">
                                                <input type="hidden" name="sales_details_id" value="{{ $data->sales_details_id }}">

                                                <div id="product-info" class="mb-3" style="margin-left: 1em;">
                                                    <p><strong>Sale Details:</strong></p>
                                                    <p><strong>Ref. No.:</strong> <span id="product_name">{{ $data->sales_id }}</span></p>
                                                    <p><strong>Product Name:</strong> <span id="product_name">{{ $data->product_name }}</span></p>
                                                    <p><strong>Purchase Quantity:</strong> <span id="quantity">{{ $data->sales_quantity }}</span></p>

                                                    <p id="price-info">
                                                        <strong>Sale Price per Unit: ₱</strong>
                                                        <span id="sale_price_per_unit" data-price="{{ $data->sale_price_per_unit ?? '0' }}">
                                                            {{ $data->sale_price_per_unit ?? 'N/A' }}
                                                        </span>
                                                    </p>
                                                    <p id="total-info"><strong>Total Purchase Amount: ₱</strong><span id="total_amount">{{ $data->total_amount }}</span></p>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="form-group">
                                                        <label for="return_quantity">Quantity to be Returned <i>*Required</i></label>
                                                        <input type="number" style="color:" class="form-control return-quantity" id="return_quantity_{{ $data->sales_details_id }}" name="return_quantity" pattern="^\d{1,6}$" required>
                                                    </div>
                                                    <div class="form-group mb-3">
                                                        <label for="total_return_amount_{{ $data->sales_details_id }}">Total Amount to Refund</label>
                                                        <input type="text" class="form-control total-return-amount" id="total_return_amount_{{ $data->sales_details_id }}" name="total_return_amount" placeholder="This is a readonly field" readonly>
                                                    </div>
                                                    <div class="form-group mb-3">
                                                        <label for="return_reason">Reason</label>
                                                        <input type="text" class="form-control" name="return_reason" value="Damaged Product" readonly required>
                                                    </div>
                                                </div>

                                                <!-- Modal Validation Error Alert Message-->
                                                @if ($errors->any() && old('sales_id') == $data->sales_details_id)
                                                    <div class="alert alert-danger">
                                                        <ul>
                                                            @foreach ($errors->all() as $error)
                                                                <li>{{ $error }}</li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                    <script>
                                                        $(document).ready(function() {
                                                            $('#returnModal{{ $data->sales_details_id }}').modal('show');
                                                        });
                                                    </script>
                                                @endif

                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                                    <button type="submit" class="btn btn-success" style="color: #fff">Confirm Return Product</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endforeach
                        @empty
                            <tr>
                                <td colspan="9" class="text-center">No sales found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
        </main>
    </div>
@else
    <h1 class="alert alert-danger mt-2">Sorry, you do not have access to this page. Please go <button onclick="window.history.back()" class="btn btn-secondary">← Back</button>.</h1>
@endif
@endsection

<!-- JavaScript for Supplier Details and Live Search -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

<script>
    function showDescriptionDetail(color, size, description) {
        const descriptionDetails = `
            <strong>Color:</strong> ${color}<br>
            <strong>size:</strong> ${size}<br>
            <strong>Description:</strong> ${description}<br>
        `;

        Swal.fire({
            title: 'Highlights',
            html: descriptionDetails,
            icon: 'info',
            confirmButtonText: 'Close'
        });
    }

    // Ensure the document is fully loaded before running the script
$(document).ready(function() {
    // Automatically calculate the total return amount when the return quantity is inputted
    $('.return-quantity').each(function() {
        $(this).on('input', function() {
            const saleId = $(this).attr('id').split('_')[2]; // Extract the sales_id from the input's ID
            const returnQuantity = parseFloat($(this).val()) || 0; // Ensure the quantity is a number
            const pricePerUnitElement = $(`#returnModal${saleId} #sale_price_per_unit`);
            const pricePerUnit = parseFloat(pricePerUnitElement.data('price')) || 0; // Ensure price per unit is a number

            // Calculate the total amount to be returned
            const totalReturnAmount = returnQuantity * pricePerUnit;

            // Debugging logs
            console.log("Sale ID:", saleId);
            console.log("Return Quantity:", returnQuantity);
            console.log("Price Per Unit:", pricePerUnit);

            // Update the total return amount field
            $(`#total_return_amount_${saleId}`).val(totalReturnAmount.toFixed(2));
        });
    });
});


$(document).ready(function() {
    // Handle form submission for search
    $('#searchForm').on('submit', function(event) {
        event.preventDefault(); // Prevent default form submission
        let query = $('#searchInput').val(); // Get search input

        // error handling for empty query
        let emptyQuery = $('#searchInput').val().trim(); // Get search input and remove extra spaces
        if (emptyQuery === "") {
            Swal.fire({
                title: 'Error',
                text: 'Please enter sales details to search.',
                icon: 'error',
                confirmButtonText: 'Okay'
            });
            return; // Exit the function if input is empty
        }

        $.ajax({
            url: "{{ route('sales.search') }}", // Adjust the route accordingly
            method: "GET",
            data: { query: query },
            success: function(data) {
                let tableBody = $('#selectedSaleDetails');
                tableBody.empty(); // Clear the table

                if (data.length > 0) {
                    // Show search results table and hide all sales table
                    $('#searchResultsTable').show();
                    $('#allSalesTable').hide();

                    // Loop through results and append to the table
                    $.each(data, function(index, sale) {
                        let returnButton = '';

                        // Determine if Return button should be enabled or disabled
                        if (sale.return_product_id === null && new Date(sale.sales_date) > new Date("{{ now()->subDays(7)->toDateString() }}")) {
                            returnButton = `<button type="button" class="btn btn-warning" data-toggle="modal" data-target="#returnModal${sale.sales_details_id}">
                                                Return
                                            </button>`;
                        } else {
                            returnButton = `<button type="button" class="btn btn-warning" disabled>
                                                Return
                                            </button>`;
                        }

                        // Utility function to format numbers
                        const formatNumber = (number, decimals = 2) => {
                            return new Intl.NumberFormat('en-US', {
                                minimumFractionDigits: decimals,
                                maximumFractionDigits: decimals,
                            }).format(number);
                        };

                        tableBody.append(`
                            <tr>
                                <td>${sale.sales_id}</td>
                                <td>${sale.first_name} ${sale.last_name}</td>
                                <td>${sale.product_name}</td>
                                <td>${sale.category_name}</td>
                                <td>${sale.sales_quantity}</td>
                                <td>${formatNumber(sale.sale_price_per_unit)}</td>
                                <td>${formatNumber(sale.amount)}</td>
                                <td>${formatNumber(sale.total_amount)}</td>
                                <td>${sale.sales_date}</td>
                                <td>
                                    <button type="button" class="btn" onclick="showDescriptionDetail('${sale.descriptionArray.color ?? 'N/A'}', '${sale.descriptionArray.size ?? 'N/A'}', '${sale.descriptionArray.description ?? 'N/A'}')">
                                        <p style="color: white;">more info.</p>
                                    </button>
                                </td>
                                <td>
                                    ${returnButton}
                                </td>
                                <td>
                                    <a href="{{ route('sales.receipt', $data->sales_id) }}" 
                                        class="btn btn-info btn-sm"
                                        target="_blank"> {{-- Add target="_blank" --}}
                                        View
                                    </a>
                                </td>
                            </tr>
                        `);
                    });
                } else {
                    // Handle no results case
                    Swal.fire({
                        title: 'No Results',
                        text: 'No sales match the given data. Please try again.',
                        icon: 'info',
                        confirmButtonText: 'Okay'
                    });
                    tableBody.append('<tr><td colspan="9" class="text-center">No results found.</td></tr>');
                    $('#searchResultsTable').hide();
                    $('#allSalesTable').show(); // Show all sales table again if no results
                }
            },
            error: function() {
                Swal.fire({
                    title: 'Error',
                    text: 'An error occurred while fetching sales data. Please try again later.',
                    icon: 'error',
                    confirmButtonText: 'Okay'
                });
            }
        });
    });
});

</script>
