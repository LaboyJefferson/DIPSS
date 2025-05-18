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
                    <div class="card-header text-center text-white" style="background-color: #3a8f66; color:#fff; font-weight: bold; ">Edit Supplier - {{ $supplier->company_name }}</div>
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

                        <form method="POST" action="{{ route('update_supplier', $supplier->supplier_id) }}" enctype="multipart/form-data">
                            @csrf

                            <div id="supplier-details">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="input-group-text" for="company_name">
                                            <i class="fa-solid fa-industry" style="margin-right: 5px;"></i> Supplier&nbsp;<i>*Required</i>
                                        </label>
                                        <input id="company_name" type="text" class="form-control" name="company_name" value="{{ old('company_name', $supplier->company_name) }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="input-group-text" for="contact_person">
                                            <i class="fa-solid fa-industry" style="margin-right: 5px;"></i> Contact Person&nbsp;<i>*Required</i>
                                        </label>
                                        <input id="contact_person" type="text" class="form-control" name="contact_person" value="{{ old('contact_person', $supplier->contact_person) }}">
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="input-group-text" for="email">
                                            <i class="fa-solid fa-industry" style="margin-right: 5px;"></i> Email&nbsp;<i>*Required</i>
                                        </label>
                                        <input id="email" type="email" class="form-control" name="email" value="{{ old('email', $supplier->email) }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="input-group-text" for="mobile_number">
                                            <i class="fa-solid fa-industry" style="margin-right: 5px;"></i> Mobile Number&nbsp;<i>*Required</i>
                                        </label>
                                        <input id="mobile_number" type="text" class="form-control" name="mobile_number" value="{{ old('mobile_number', $supplier->mobile_number) }}">
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <label class="input-group-text" for="address">
                                            <i class="fa-solid fa-industry" style="margin-right: 5px;"></i> Address&nbsp;<i>*Required</i>
                                        </label>
                                        <input id="address" type="text" class="form-control" name="address" value="{{ old('address', $supplier->address) }}">
                                    </div>
                                </div>
                            </div>  

                            <div class="row mb-3">
                                <div class="col-md-6 d-flex justify-content-end">
                                    <a class="btn btn-success" href="{{ route('supplier_list') }}">Go Back</a>
                                </div>
                                <div class="col-md-6 d-flex justify-content-start">
                                    <button type="submit" class="btn btn-primary">
                                        {{ __('Update Supplier') }}
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
        
    </script>

@endsection