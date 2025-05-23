@extends('layouts.app')

<!-- Wrap navbar in a div with a specific class for print -->
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
        color: #000
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
                            Report generated on {{ \Carbon\Carbon::now()->format('F j, Y') }}
                        </p>
                    </div>
                </div>
            </div>


            <div class="d-flex justify-content-end">
                <button type="button" class="btn btn-primary ms-2 me-2 printButton" id="printButton" onclick="checkSignatureAndPrint();">
                    <i class="fa-solid fa-print"></i> Print Report
                </button>
            </div>

            <!-- Inventory Table -->
            <table class="table table-bordered">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center mt-4">
                    <h5>Purchased Product Details</h5> 
                </div>
                <thead>
                    <tr>
                        <th>Order No.</th>
                        <th>Product No.</th>
                        <th>Name</th>
                        <th>Payment Method</th>
                        <th>Purchased Price</th>
                        <th>Quantity</th>
                        <th>Supplier</th>
                        <th>Date Ordered</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $data)
                        <tr>
                            <td>{{ $data->purchase_order_id }}</td>
                            <td>{{ $data->product_id }}</td>
                            <td>{{ $data->product_name }}</td>
                            <td>{{ $data->payment_method }}</td>
                            <td>{{ number_format($data->price, 2) }}</td>
                            <td>{{ $data->quantity }}</td>
                            <td>{{ $data->company_name }}</td>
                            <td>{{ $data->created_at }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="14" class="text-center">No data available for the selected period.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <!-- Signature Upload Section -->
            <div id="signature-upload" class="signature-section">
                <form id="signature-form" enctype="multipart/form-data" class="signature-form">
                    <h3 id="signature-upload-title">Upload Signature</h3>
                    <label for="signature">Choose a signature image:</label>
                    <input type="file" name="signature" id="signature" accept="image/*" required>
                    <button type="submit" class="btn btn-primary">Upload Signature</button>
                </form>
            </div>

            <!-- Footer: Prepared By -->
            <div class="prepared-by-container">
                    <div id="signature-preview"  class="signature"></div>

                <div class="prepared-by">
                    <?php $user = auth()->user(); ?>
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
