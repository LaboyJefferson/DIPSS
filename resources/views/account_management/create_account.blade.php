{{-- @extends('layouts.app')
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
    
</style>

<div class="col-md-9 ml-sm-auto col-lg-10 px-md-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="main-content">
                
            </div>
            <div class="card">
                <div class="card-header text-center" style="background-color:#3a8f66; color:#fff; font-weight: bold;">{{ __('Register') }}</div>
                <div class="card-body">
                    <!-- Alert Messages -->
                    @include('common.alert')
                    <form method="POST" action="{{ url('account_management') }}" enctype="multipart/form-data">
                        @csrf

                        <div class="form-group mb-3">
                            <label class="text" for="">{{ __('Choose Profile Picture') }}</label>
                            <input type="file" name="image_url" class="form-control"  accept="image/*" required>
                        </div> 

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <span class="input-group-text">
                                    <i class="fa fa-user fa-lg"></i><label class="ms-2">First Name</label>
                                </span>
                                <input id="first_name" type="text" class="form-control @error('first_name') is-invalid @enderror" name="first_name" placeholder="Format Sample: Gabriel" value="{{ old('first_name') }}" pattern="^[A-Z]{1}[a-z]*$" required>
                                <small class="text form-text text-danger mt-2">
                                    <p class="text">
                                        Note: Please enter the value starting with an uppercase letter, followed by lowercase letters only.
                                    </p>
                                </small>
                                @error('first_name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <span class="input-group-text">
                                    <i class="fa fa-user fa-lg"></i><label class="ms-2">Last Name</label>
                                </span>
                                <input id="last_name" type="text" class="form-control @error('last_name') is-invalid @enderror" name="last_name" placeholder="Format Sample: Madriago" value="{{ old('last_name') }}" pattern="^[A-Z]{1}[a-z]*$" required>
                                <small class="text form-text text-danger mt-2">
                                    <p class="text">
                                        Note: Please enter the value starting with an uppercase letter, followed by lowercase letters only.
                                    </p>
                                </small>
                                @error('last_name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <span class="input-group-text">
                                    <i class="fa-solid fa-envelope fa-lg"></i><label class="ms-2">Email Address</label>
                                </span>
                                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" placeholder="Email Address should be valid" value="{{ old('email') }}" required>
                                <small class="text form-text text-danger mt-2">
                                    <p class="text">
                                        Note: Please enter a verified email address.
                                    </p>
                                </small>
                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <span class="input-group-text">
                                    <i class="fa-solid fa-sim-card fa-lg"></i><label class="ms-2">Mobile Number</label>
                                </span>
                                <input id="cp_number" type="number" class="form-control @error('mobile_number') is-invalid @enderror" name="mobile_number" placeholder="Format Sample: 09123456789 and is 11 digits" value="{{ old('mobile_number') }}" pattern="^09\d{9}$" required>
                                <small class="text form-text text-danger mt-2">
                                    <p class="text">
                                        Note: Please enter a PH mobile number starting with '09' followed by 9 digits.
                                    </p>
                                </small>
                                @error('mobile_number')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <div class="below">
                            <div class="row mb-3">
                                <label class="text" for="role" class="col-md-4 col-form-label text-md-end">{{ __('Select User Role:') }}</label>
                                <div class="col-md-6">
                                    <div class="form-check form-check-inline">
                                        <input id="inventory_manager" type="radio" class="btn-check form-check-input @error('role') is-invalid @enderror" name="role" value="Inventory Manager" required>
                                        <label for="inventory_manager" class="form-check-label btn btn-secondary">{{ __('Inventory Manager') }}</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input id="auditor" type="radio" class="btn-check form-check-input @error('role') is-invalid @enderror" name="role" value="Auditor" required>
                                        <label for="auditor" class="form-check-label btn btn-secondary">{{ __('Auditor') }}</label>
                                    </div>

                                    @error('role')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <span class="input-group-text">
                                        <i class="fa fa-user fa-lg"></i><label class="ms-2">Username</label>
                                    </span>
                                    <input id="username" type="text" class="form-control @error('username') is-invalid @enderror" name="username" placeholder="Username must be unique" value="{{ old('username') }}" pattern="^[A-Za-z0-9]*" required>
                                    <small class="text form-text mt-2">
                                        <p class="text">
                                            Note for User: <ul>
                                                <li>The input must contain only alphanumeric characters (letters and numbers).</li>
                                                <li>Please ensure that this value is unique. It cannot be the same as any other existing entry.</li>
                                                </ul>
                                        </p>
                                    </small>
                                    @error('username')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <span class="input-group-text">
                                        <i class="fa fa-key fa-lg"></i><label class="ms-2">Password</label>
                                    </span>
                                    <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" placeholder="Must be a strong password" pattern="^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*_\-\\\.\+]).{8,}$" required>
                                    <small class="text form-text text-danger mt-2">
                                        <p class="text">
                                            Note: Please enter at least 8 characters with a number, symbol, capital letter, and small letter.
                                        </p>
                                    </small>
                                    @error('password')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <span class="input-group-text">
                                        <i class="fa fa-key fa-lg"></i><label class="ms-2">Confirm Password</label>
                                    </span>
                                    <input id="password-confirm" type="password" class="form-control" name="password_confirmation" placeholder="Entered password should match." required>
                                </div>
                            </div>
                        </div>
                        

                        <div class="row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" name="create" class="btn btn-primary">
                                    {{ __('Register') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection --}}
