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
        background-color: #565656 !important; /* Set background color for all table headers */
        color: #ffffff !important;
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
@if(Auth::user()->role === 'Salesperson')
    <div class="container-fluid">
        <main class="col-md-9 ml-sm-auto col-lg-10 px-md-4">
            <form action="{{ route('dispose_product') }}" method="POST" id="disposeForm">
                @csrf
                <div class="main-content">
                    <!-- Alert Messages -->
                    @include('common.alert')
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center">
                        <h1 class="h2 mb-4">Returned Product</h1>
                    </div>

                    <!-- Table Section -->
                    <table class="table table-responsive">
                        <thead>
                            <tr>
                                <th>
                                    <input type="checkbox" id="selectAll"> Select All
                                </th>
                                <th>Ref. No.</th>
                                <th>Seller</th>
                                <th>Product Name</th>
                                <th>Returned Quantity</th>
                                <th>Total Returned Amount</th>
                                <th>Returned Reason</th>
                                <th>Returned Timestamp</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($returnProductJoined as $data)
                                <tr>
                                    <td>
                                        <input type="checkbox" name="selected_products[]" value="{{ json_encode(['return_product_id' => $data->return_product_id, 'return_quantity' => $data->return_quantity]) }}">
                                        Select
                                    </td>
                                    <td>{{ $data->sales_id }}</td>
                                    <td>{{ $data->first_name }} {{ $data->last_name }}</td>
                                    <td>{{ $data->product_name }}</td>
                                    <td>{{ $data->return_quantity }}</td>
                                    <td>{{ $data->total_return_amount }}</td>
                                    <td>{{ $data->return_reason }}</td>
                                    <td>{{ $data->return_date }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center">No returned products found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
            
                    <!-- Batch Disposal Button -->
                    <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#confirmDisposeModal" disabled>
                        Dispose Selected
                    </button>
                </div>
            
                <!-- Confirm Disposal Modal -->
                <div class="modal fade" id="confirmDisposeModal" tabindex="-1" role="dialog" aria-labelledby="confirmDisposeModalLabel" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="confirmDisposeModalLabel">Confirm Disposal</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <div class="form-group">
                                    <label for="confirm_password">Confirm Password <i>*Required</i></label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                    <small class="form-text text-danger mt-2" style="color: red">
                                        <p class="text">
                                            Note: Please enter your current password here for confirmation.
                                        </p>
                                    </small>
                                </div>

                                <!-- Modal Validation Error Alert Message-->
                                @if ($errors->any())
                                    <div class="alert alert-danger mt-2" style="height: 4em;">
                                        <ul>
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                    <script>
                                        $(document).ready(function() {
                                            $('#confirmDisposeModal').modal('show');
                                        });
                                    </script>
                                @endif

                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-danger">Confirm Dispose</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
            
        </main>
    </div>
@else
    <h1 class="alert alert-danger mt-2">Sorry, you do not have access to this page. Please go <button onclick="window.history.back()" class="btn btn-secondary">← Back</button>.</h1>
@endif
@endsection

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectAllCheckbox = document.getElementById('selectAll');
        const productCheckboxes = document.querySelectorAll('input[name="selected_products[]"]');
        const disposeButton = document.querySelector('.btn.btn-danger[data-target="#confirmDisposeModal"]');

        // Function to update dispose button state
        function updateDisposeButtonState() {
            const anyChecked = Array.from(productCheckboxes).some(checkbox => checkbox.checked);
            disposeButton.disabled = !anyChecked;
        }

        // Function to handle "Select All" toggle
        selectAllCheckbox.addEventListener('change', function() {
            productCheckboxes.forEach(checkbox => {
                checkbox.checked = selectAllCheckbox.checked;
            });
            updateDisposeButtonState();
        });

        // Update "Select All" state when individual checkboxes change
        productCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                // Check if all are selected, if not, uncheck "Select All"
                selectAllCheckbox.checked = Array.from(productCheckboxes).every(cb => cb.checked);
                updateDisposeButtonState();
            });
        });

        // Initialize button state on page load
        updateDisposeButtonState();
    });
</script>

