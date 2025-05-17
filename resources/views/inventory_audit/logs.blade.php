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

    .table th, td {
        background-color: #565656 !important; /* Set background color for all table headers */
        color: #ffffff !important;
    }

    .table th, td {
        background-color: #f8f9fa !important; /* Set background color for all table headers */
    }
    
    /*Icon*/
    .circle-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 30px; /* Set the width of the circle */
        height: 30px; /* Set the height of the circle */
        border-radius: 50%; /* Makes it a circle */
        background-color: #dc3545; /* Light red background */
        color: white; /* Icon color */
        font-size: 1.5rem; /* Adjust icon size */
        transition: background-color 0.3s; /* Smooth transition for background color */
    }

    .circle-icon:hover {
        background-color: #c82333; /* Darker red on hover */
    }

    /*Date Picker*/
    .input-group .form-control {
        background-color: #212529; /* Grey background for input */
        color: white; /* White text */
        border-radius: 5px; /* Rounded corners */
        border: none;
    }

    .input-group .form-control:focus {
        background-color: #212529; /* Maintain grey background on focus */
        color: white; /* White text */
        outline: none; /* Remove default outline */
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
        color: black !important; /* This will apply to all text in the modal */
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

    .custom-date-picker {
        appearance: none; /* Removes the default appearance */
        -webkit-appearance: none; /* For Safari */
        position: relative;
        padding: 10px 40px 10px 10px; /* Adds padding to make room for the icon */
        background-color: #000; /* Ensures the input's background matches */
        color: #fff; /* White text color */
        border: 1px solid #fff; /* White border */
        border-radius: 5px;
        width: 28em;
        }

    /* This makes the original calendar icon invisible while keeping it clickable */
    .custom-date-picker::-webkit-calendar-picker-indicator {
        opacity: 0;
        display: block;
        position: absolute;
        right: 10px;
        width: 20px;
        height: 100%;
        cursor: pointer;
    }

    /* Custom white icon overlay */
    .custom-date-picker:before {
        content: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="white" viewBox="0 0 24 24"><path d="M19 4h-1V2h-2v2H8V2H6v2H5c-1.1 0-1.99.9-1.99 2L3 20c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V10h14v10zM7 12h5v5H7z"/></svg>');
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        pointer-events: none; /* Makes the icon non-clickable but allows the input's functionality */
    }
</style>    

@section('content')
    <div class="container-fluid">
        <main class="col-md-9 ml-sm-auto col-lg-10 px-md-4">
            <div class="main-content">
                <ul class="nav nav-tabs mb-4" id="auditTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="logs-tab" data-bs-toggle="tab" data-bs-target="#logs" type="button" role="tab" aria-controls="logs" aria-selected="true">
                        Product Discrepancy Logs
                    </button>
                </li>
                @if(Auth::user()->role == 'Inventory Manager') 
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="create-tab" data-bs-toggle="tab" data-bs-target="#create" type="button" role="tab" aria-controls="create" aria-selected="false">
                            Find Product Discrepancies
                        </button>
                    </li>
                @endif
            </ul>

            <div class="tab-content" id="auditTabsContent">

                <!-- Alert Messages -->
                @include('common.alert')
                <div id="alertContainer"></div> <!-- Error message placeholder -->

                <div class="tab-pane fade show active" id="logs" role="tabpanel" aria-labelledby="logs-tab">
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center">
                        <h1 class="h2 mb-4">Product Discrepancy Logs</h1>
                    </div>
                <form method="POST" action="{{ route('generate_audit_filter_report') }}" enctype="multipart/form-data" class="mb-4 report-form" id="reportForm">
                    @csrf
                    <div class="input-group mb-3">
                        <!-- Pass values directly as arrays -->
                        <input type="hidden" name="user_ids" value="{{ implode(',', request('user_ids', [])) }}">
                        <input type="hidden" name="dates" value="{{ implode(',', request('dates', [])) }}">
                        
                        <div class="d-flex w-100 align-items-end justify-content-between">
                            <div class="text-start" style="min-width: 300px">
                                <p><strong>Interpretation of Discrepancy Values:</strong></p>
                                <ul>
                                    <li>A <strong>negative discrepancy (-)</strong> means the physical stock count is less than the system's recorded stock.</li>
                                    <li>A <strong>positive discrepancy (+)</strong> means the physical stock count is greater than the system's recorded stock.</li>
                                </ul>
                            </div>

                            <div class="ms-auto">
                                <button type="submit" class="btn btn-success">
                                    <i class="fa-solid fa-print"></i> Generate Report
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
                <!-- Dropdown with Buttons -->
                    <div class="row d-flex justify-content-end">
                        <div class="col-auto">
                            <div class="dropdown">

                                <!-- Display all -->
                                <div class="btn-group">
                                    <a type="button" class="btn btn-success mb-2" href="{{ route('inventory.audit.logs') }}">Display All</a>
                                </div>
                            
                                
                                <!-- Date Audited Dropdown -->
                                <div class="btn-group">
                                    <button 
                                        class="btn btn-success dropdown-toggle mb-2" 
                                        type="button" 
                                        id="dateAuditedDropdown" 
                                        data-bs-toggle="dropdown" 
                                        aria-expanded="false">
                                        <i class="fas fa-calendar-alt"></i> Select Date(s)
                                    </button>
                                    <ul class="dropdown-menu p-4 shadow" aria-labelledby="dateAuditedDropdown" style="min-width: 300px;">
                                        <form id="dateAuditedFilterForm" method="GET" action="{{ route('filter_date_audited') }}">
                                            
                                            <!-- Filter Button Above the Calendar -->
                                            <div class="d-flex justify-content-between mb-3">
                                                <button type="submit" class="btn btn-success btn-sm w-100">Apply Filter</button>
                                            </div>
                                
                                            <!-- Date Picker Input -->
                                            <div class="mb-3">
                                                <label for="datePicker" class="form-label fw-bold">Select Date(s) to Filter</label>
                                                <input 
                                                    type="text" 
                                                    id="datePicker" 
                                                    class="form-control border-success" 
                                                    name="dates[]" 
                                                    placeholder="Pick date(s)" 
                                                    readonly 
                                                    style="cursor: pointer;">
                                            </div>
                                        </form>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <table class="table table-bordered table-responsive">
                    <thead>
                        <tr>
                            <th rowspan="2" class="text-center align-middle">Product Name</th>
                            <th colspan="2" class="text-center">Physical Count</th>
                            <th colspan="2" class="text-center">System Record</th>
                            <th colspan="2" class="text-center">Discrepancies</th>
                            <th rowspan="2" class="text-center align-middle">Total Discrepancies</th>
                            <th rowspan="2" class="text-center align-middle">Discrepancy Reason</th>
                            <th rowspan="2" class="text-center align-middle">Date Created</th>
                        </tr>
                        <tr>
                            <th>Store Stock</th>
                            <th>Stockroom Stock</th>
                            <th>Store Stock</th>
                            <th>Stockroom Stock</th>
                            <th>Store Stock</th>
                            <th>Stockroom Stock</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($auditLogs as $log)
                            <div>
                                <tr>
                                    <td>{{ $log->inventory->product->product_name }}</td>
                                    <td>{{ $log->physical_storestock_count }}</td>
                                    <td>{{ $log->physical_stockroom_count }}</td>
                                    <td>{{ $log->system_storestock_record }}</td>
                                    <td>{{ $log->system_stockroom_record }}</td>
                                    <td>{{ $log->storestock_discrepancies }}</td>
                                    <td>{{ $log->stockroom_discrepancies }}</td>
                                    <td>{{ $log->storestock_discrepancies + $log->stockroom_discrepancies}}</td>
                                    <td>{{ $log->discrepancy_reason }}</td>
                                    <td>{{ $log->audit_date }}</td>
                                </tr>
                            </div>
                            @empty
                            <tr>
                                <td colspan="10" class="text-center">
                                    <strong>There are no data currently.</strong>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            </div>
            <div class="tab-pane fade" id="create" role="tabpanel" aria-labelledby="create-tab">
                <h2 class="h4 mt-3">Find Discrepancies</h2>
                <form method="POST" action="{{ route('find_discrepancies') }}">
                @csrf
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Product Name</th>
                                <th>Physical Store Stock Count *Required</th>
                                <th>System Store Stock Record</th>
                                <th>Store Stock Discrepancy</th>
                                <th>Physical Stockroom Stock Count *Required</th>
                                <th>System Stockroom Stock Record</th>
                                <th>Stockroom Stock Discrepancy</th>
                                <th>Discrepancy Reason *Required</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($inventoryJoined as $inventory)
                            <tr>
                                <td>{{ $inventory->product_name }}</td>
                                <td>
                                    <input type="number" name="store_physical[{{ $inventory->inventory_id }}]" placeholder="Enter a Number" class="form-control store-physical" required autocomplete="off" value="">
                                </td>
                                <td>
                                    <input type="number" name="system_store[{{ $inventory->inventory_id }}]" class="form-control" value="{{ $inventory->in_stock - $inventory->product_quantity }}" readonly>
                                </td>
                                <td>
                                    <input type="text" name="store_discrepancy[{{ $inventory->inventory_id }}]" class="form-control store-discrepancy" readonly value="">
                                </td>
                                <td>
                                    <input type="number" name="stockroom_physical[{{ $inventory->inventory_id }}]" placeholder="Enter a Number" class="form-control stockroom-physical" required autocomplete="off" value="">
                                </td>
                                <td>
                                    <input type="number" name="system_stockroom[{{ $inventory->inventory_id }}]" class="form-control" value="{{ $inventory->product_quantity }}" readonly>
                                </td>
                                <td>
                                    <input type="text" name="stockroom_discrepancy[{{ $inventory->inventory_id }}]" class="form-control stockroom-discrepancy" readonly value="">
                                </td>
                                <td>
                                    <input type="text" name="reason[{{ $inventory->inventory_id }}]" placeholder="e.g. Human Error" id="reason-{{ $inventory->inventory_id }}" disabled autocomplete="off" value="">
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <button type="submit" class="btn btn-success mt-3">Submit Audit</button>
            </form>

            </div>

        </main>
    </div>
@endsection

<script>

    // sweetalerts for store
    function showStore(prevStock, newStock, prevInStock, newInStock) {
        const storeDetails = `
            <strong>Previous Store Stock:</strong> ${prevStock}<br>
            <strong>New Store Stock:</strong> ${newStock}<br>
            <strong>Previous Quantity on Hand:</strong> ${prevInStock}<br>
            <strong>New Quantity on Hand:</strong> ${newInStock}<br>
        `;

        Swal.fire({
            title: 'Store Stock Details',
            html: storeDetails,
            icon: 'info',
            confirmButtonText: 'Close'
        });
    }

    // sweetalerts for stockroom
    function showStockroom(prevStock, newStock, prevInStock, newInStock) {
        const stockroomDetails = `
            <strong>Previous Stockroom Stock:</strong> ${prevStock}<br>
            <strong>New Stockroom Stock:</strong> ${newStock}<br>
            <strong>Previous Quantity on Hand:</strong> ${prevInStock}<br>
            <strong>New Quantity on Hand:</strong> ${newInStock}<br>
        `;

        Swal.fire({
            title: 'Stockroom Stock Details',
            html: stockroomDetails,
            icon: 'info',
            confirmButtonText: 'Close'
        });
    }

     // sweetalerts for resolve steps
     function showResolution(reason, resolveSteps, dateAudited) {
        const resolutionDetails = `
            <strong>Date Audited:</strong> ${dateAudited}<br>
            <strong>Discrepancy Reason:</strong> ${reason}<br>
            <strong>Resolve Discrepancy Steps:</strong> ${resolveSteps}<br>
        `;

        Swal.fire({
            title: 'Resolution Details',
            html: resolutionDetails,
            icon: 'info',
            confirmButtonText: 'Close'
        });
    }

    // date filter
    document.addEventListener('DOMContentLoaded', function () {
        flatpickr('#datePicker', {
            mode: 'multiple', // Allow selecting multiple dates
            dateFormat: 'Y-m-d', // Format for the dates
            altInput: true, // Show a user-friendly display
            altFormat: 'F j, Y', // Display format
        });
    });

//for finding discrepancies
document.addEventListener('DOMContentLoaded', function () {
    const rows = document.querySelectorAll('tbody tr');

    rows.forEach(row => {
        // Get relevant inputs
        const storeInput = row.querySelector('.store-physical');
        const stockroomInput = row.querySelector('.stockroom-physical');

        const storeDiscrepancy = row.querySelector('.store-discrepancy');
        const stockroomDiscrepancy = row.querySelector('.stockroom-discrepancy');

        const reasonInput = row.querySelector('input[name^="reason"]');

        if (!storeInput || !stockroomInput || !storeDiscrepancy || !stockroomDiscrepancy || !reasonInput) return;

        const getInt = (val) => {
            const num = parseInt(val);
            return isNaN(num) ? 0 : num;
        };

        // Function to update discrepancies and reason field state
        function updateDiscrepancies() {
    const physicalStore = getInt(storeInput.value);
    const systemStore = getInt(row.querySelector('td:nth-child(3) input').value);

    const physicalStockroom = getInt(stockroomInput.value);
    const systemStockroom = getInt(row.querySelector('td:nth-child(6) input').value);

    // Calculate discrepancies only if storeInput or stockroomInput has some input
    // Otherwise, keep discrepancy fields empty
    if (storeInput.value.trim() === '') {
        storeDiscrepancy.value = '';
    } else {
        storeDiscrepancy.value = physicalStore - systemStore;
    }

    if (stockroomInput.value.trim() === '') {
        stockroomDiscrepancy.value = '';
    } else {
        stockroomDiscrepancy.value = physicalStockroom - systemStockroom;
    }

    // Enable reason input only if discrepancy fields have non-zero numeric value
    const storeDiff = parseInt(storeDiscrepancy.value) || 0;
    const stockroomDiff = parseInt(stockroomDiscrepancy.value) || 0;

    if (storeDiff !== 0 || stockroomDiff !== 0) {
        reasonInput.removeAttribute('disabled');
    } else {
        reasonInput.value = '';
        reasonInput.setAttribute('disabled', 'disabled');
    }
}


        // Initialize discrepancies and reason input state on page load
        updateDiscrepancies();

        // Update on user input
        storeInput.addEventListener('input', updateDiscrepancies);
        stockroomInput.addEventListener('input', updateDiscrepancies);
    });
});

</script>
