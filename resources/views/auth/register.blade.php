{{-- @extends('layouts.app')

@section('content')
<style>
    body {
        background-color: #1a1a1a; /* Dark background */
        color: #f8f9fa; /* Light text color */
    }
    .card {
        background-color: #d3d6d3; /* Card background */
        border: none; /* Remove border */
        border-radius: 8px; /* Rounded corners */
    }
    .input-group-text {
        background-color: #74e39a; /* input group background */
        border: none; /* Remove borders */
        color: #0f5132; /* White text */
    }
    .btn-primary {
        background-color: #74e39a; /* Green button */
        color: black;
        border: none; /* Remove button borders */
    }
    .btn-primary:hover {
        background-color: #0f5132; /* Darker green on hover */
    }
    .btn-secondary {
        background-color: #74e39a; /* Dark background for role selection */
        color: #0f5132;
        border: none;
    }
    .btn-secondary:hover {
        background-color: #0f5132; /* Green on hover */
    }
    .form-control {
        background-color: white; /* Darker input background */
        color: black; /* White text */
        border: 1px solid #444; /* Subtle border */
    }
    .form-control:focus {
        background-color: white; /* Focus background */
        color: black;
        border-color: #28a745; /* Green border on focus */
        box-shadow: none; /* Remove default shadow */
    }
</style>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header text-center">{{ __('Register') }}</div>
                <div class="card-body">
                    <!-- Alert Messages -->
                    @include('common.alert')
                    <form method="POST" action="{{ route('register') }}" enctype="multipart/form-data">
                        @csrf

                        <div class="form-group mb-3">
                            <label for="">{{ __('Choose Profile Picture') }}</label>
                            <input type="file" name="image_url" class="form-control"  accept="image/*" required>
                        </div> 

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <span class="input-group-text">
                                    <i class="fa fa-user fa-lg"></i><label class="ms-2">First Name</label>
                                </span>
                                <input id="first_name" type="text" class="form-control @error('first_name') is-invalid @enderror" name="first_name" placeholder="Format Sample: Gabriel" value="{{ old('first_name') }}" required>

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

                                @error('mobile_number')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="role" class="col-md-4 col-form-label text-md-end">{{ __('Select User Role:') }}</label>
                            <div class="col-md-6">
                                <div class="form-check form-check-inline">
                                    <input id="inventory_manager" type="radio" class="btn-check form-check-input @error('role') is-invalid @enderror" name="role" value="Inventory Manager">
                                    <label for="inventory_manager" class="form-check-label btn btn-secondary">{{ __('Inventory Manager') }}</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input id="auditor" type="radio" class="btn-check form-check-input @error('role') is-invalid @enderror" name="role" value="Auditor">
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
                                <small class="form-text text-danger mt-2" style="color: red">
                                    Note: Please enter at least 8 characters with a number, symbol, capital letter, and small letter.
                                </small>
                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <span class="input-group-text">
                                    <i class="fa fa-key fa-lg"></i><label class="ms-2">Confirm Password</label>
                                </span>
                                <input id="password-confirm" type="password" class="form-control" name="password_confirmation" placeholder="Entered password should match." required>
                            </div>
                        </div>

                        <div class="row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary">
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
