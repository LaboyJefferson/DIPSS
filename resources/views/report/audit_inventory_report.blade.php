@extends('layouts.app')

<!-- Wrap navbar for print view -->
<div class="navbar-print-hide">
    @include('common.navbar')
</div>

@section('content')
<style>
/* General Print Styles */
@media print {
    .navbar-print-hide, .printButton, .signature-form {
        display: none; /* Hide non-essential elements */
    }

    body {
        margin: 0;
        padding: 0;
        font-size: 10pt;
        line-height: 1.6;
        background: none;
    }

    .container-fluid {
        max-width: 100%;
        padding: 0;
    }

    #main-content {
        padding: 40px 30px;
        background-color: transparent;
        color: #000;
        box-shadow: none;
    }

    .report-header img {
            width: 13em; /* Adjust logo size */
            height: auto;
        }

    /* Table Styles for Print */
    .table {
        width: 100%;
        border-collapse: collapse;
        font-size: 9pt;
    }

    .table th {
        /* background-color: #3a8f66; */
        color: #000;
        font-weight: bold;
        text-align: center;
    }

    .table th, .table td {
        padding: 6px;
        border: 1px solid #dee2e6;
    }

    .prepared-by-container {
        margin-top: 40px;
        text-align: left;
    }

    #signature-preview img {
        max-width: 120px;
        max-height: 60px;
    }
}

/* Normal View Styles */
body {
    font-family: Arial, sans-serif;
}

.container-fluid, .main-content {
    margin-top: 0;
    padding: 0;
}

#main-content {
    background-color: #fff;
    border-radius: 10px;
}

.table {
    margin-top: 30px;
    width: 100%;
    border: 1px solid #dee2e6;
    font-size: 10pt;
    border-radius: 5px;
}

.table th {
        /* background-color: #3a8f66; */
        color: #000;
        font-weight: bold;
    }

.table th, .table td {
    padding: 8px;
    text-align: left;
}

.signature-section {
    margin-top: 40px;
    text-align: left;
}

#signature-preview {
    margin-top: 20px;
    text-align: left;
}

#signature-preview img {
    max-width: 150px;
    max-height: 80px;
}

/* Print Button */
.printButton {
    margin-top: 20px;
    text-align: right;
}

.printButton button {
    background-color: #007bff;
    color: #fff;
    border: none;
    padding: 10px 20px;
    cursor: pointer;
    border-radius: 5px;
}

.report-header img {
            width: 13em; /* Adjust logo size */
            height: auto;
        }
</style>

<div class="container-fluid">
    <main class="col-md-9 ml-sm-auto col-lg-10 px-md-4">
        <div class="main-content" id="main-content">
            <!-- Alert Messages -->
            @include('common.alert')

            <!-- Report Header -->
            <div class="report-header py-3 border-bottom">
                <div class="align-items-center">
                    <!-- Logo Section -->
                    <div class="text-center">
                        <img src="{{ asset('storage/logo/logo.png') }}" alt="Logo" class="img-fluid mb-2">
                    </div>
                    <!-- Title Section -->
                    <div class="text-center">
                        <h3 class="mb-1">{{ $reportTitle }}</h3>
                        <p class="text-muted mb-0">
                            Report generated on {{ now()->format('F j, Y') }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Print Button -->
            <div class="d-flex justify-content-end">
                <button type="button" class="btn btn-primary ms-2 me-2 printButton" id="printButton" onclick="checkSignatureAndPrint();">
                    <i class="fa-solid fa-print"></i> Print Report
                </button>
            </div>

            <!-- Audit Logs Table -->
            <table class="table table-bordered">
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

            <!-- Signature Section -->
            <div  id="signature-upload"  class="signature-section">
                <form id="signature-form" enctype="multipart/form-data" class="signature-form">
                    <h3>Upload Signature</h3>
                    <label>Choose a signature image:</label>
                    <input type="file" name="signature" id="signature" accept="image/*">
                    <button type="submit" class="btn btn-primary">Upload Signature</button>
                </form>
            </div>

            <!-- Footer -->
            <div class="prepared-by-container">
                <div id="signature-preview" class="signature"></div>
                <div>
                    <label>Prepared By: {{ auth()->user()->first_name }} {{ auth()->user()->last_name }}</label>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
    function checkSignatureAndPrint() {
        var signaturePreview = document.getElementById('signature-preview').innerHTML;
        
        if (!signaturePreview) {
            alert('You must upload a signature before printing the report.');
            // Scroll to the signature upload section
            document.getElementById('signature-upload').scrollIntoView({behavior: 'smooth'});
        } else {
            window.print();
        }
    }

    // jQuery to handle signature upload and preview
    $(document).ready(function() {
        $('#signature-form').on('submit', function(e) {
            e.preventDefault(); // Prevent the form from submitting the traditional way

            var formData = new FormData(this);

            $.ajax({
                url: "{{ route('upload.signature') }}",  // Adjust the route as necessary
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.status == 'success') {
                        $('#signature-preview').html('<img src="' + response.signature_url + '" alt="Signature" style="max-width: 200px;">');
                    } else {
                        alert('Failed to upload signature. Please try again.');
                    }
                },
                error: function() {
                    alert('An error occurred. Please try again.');
                }
            });
        });
    });
</script>
@endsection
