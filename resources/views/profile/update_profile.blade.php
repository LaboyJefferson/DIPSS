{{-- @extends('layouts.app')
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
            <div class="card">
                <div class="text card-header text-center" style="background-color: #3a8f66">{{ __('Update User Account') }}</div>
                <div class="card-body">
                    {{-- error handling alert message --}}
          {{--}}          @include('common.alert')
                    @if(session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    
                    <form method="POST" action="{{ url('update_profile/' . $user->user_id) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <!-- Profile Picture -->
                        <div class="form-group mb-3">
                            <label class="text" for="">{{ __('Choose Profile Picture') }}</label>
                            <input type="file" name="image_url" class="form-control" accept="image/*">
                        </div>

                        <!-- First and Last Name -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <span class="input-group-text">
                                    <i class="fa fa-user fa-lg"></i><label class="ms-2">First Name</label>
                                </span>
                                <input id="first_name" type="text" class="form-control" name="first_name" pattern="^[A-Z]{1}[a-z]*$"  value="{{ $user->first_name }}">
                            </div>

                            <div class="col-md-6">
                                <span class="input-group-text">
                                    <i class="fa fa-user fa-lg"></i><label class="ms-2">Last Name</label>
                                </span>
                                <input id="last_name" type="text" class="form-control" name="last_name" pattern="^[A-Z]{1}[a-z]*$" value="{{ $user->last_name }}">
                            </div>
                        </div>

                        <!-- Email and Mobile Number -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <span class="input-group-text">
                                    <i class="fa-solid fa-envelope fa-lg"></i><label class="ms-2">Email Address</label>
                                </span>
                                <input id="email" type="email" class="form-control" name="email" value="{{ $user->email ?? '' }}">
                            </div>

                            <div class="col-md-6">
                                <span class="input-group-text">
                                    <i class="fa-solid fa-sim-card fa-lg"></i><label class="ms-2">Mobile Number</label>
                                </span>
                                <input id="mobile_number" type="number" class="form-control" name="mobile_number" pattern="^09\d{9}$" value="{{ $user->mobile_number ?? '' }}">
                            </div>
                        </div>

                        <!-- Username -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <span class="input-group-text">
                                    <i class="fa fa-user fa-lg"></i><label class="ms-2">Username</label>
                                </span>
                                <input id="username" type="text" class="form-control" name="username" pattern="^[A-Za-z0-9]*" value="{{ $user->username ?? '' }}">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <span class="input-group-text">
                                    <i class="fa fa-key fa-lg"></i><label class="ms-2">New Password</label>
                                </span>
                                <input id="new_password" type="password" class="form-control @error('new_password') is-invalid @enderror" name="new_password" pattern="^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*_\-\\\.\+]).{8,}$" placeholder="Must be a strong password" pattern="^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*_\-\\\.\+]).{8,}$">
                                <small class="form-text text-danger mt-2" style="color: red">
                                    <p class="text">
                                        Note: Please enter at least 8 characters with a number, symbol, capital letter, and small letter.
                                    </p>
                                </small>
                                @error('new_password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <span class="input-group-text">
                                    <i class="fa fa-key fa-lg"></i><label class="ms-2">Confirm New Password</label>
                                </span>
                                <input id="new_password_confirmation" type="password" class="form-control" name="new_password_confirmation" placeholder="Confirm new password">
                            </div>
                        </div>

                        <div class="row mb-3">
                                <span class="input-group-text">
                                    <i class="fa fa-key fa-lg"></i><label class="ms-2">Confirm Update</label>
                                </span>
                            <div class="col">
                                    <div class="form-group">
                                        <label class="text" for="username">Confirm Username</label>
                                        <input type="text" class="form-control" id="username_{{ $user->user_id }}" placeholder="Enter current username" name="confirm_username" pattern="^[A-Za-z0-9]*" required>
                                    </div>
                            </div>
                            <div class="col">
                                    <div class="form-group">
                                        <label class="text" for="password">Confirm Password</label>
                                        <input type="password" class="form-control" id="password_{{ $user->user_id }}" placeholder="Enter current password" name="confirm_password" pattern="^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*_\-\\\.\+]).{8,}$" required>
                                    </div>
                            </div>
                        </div>

                        <div class="row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary" name="update">
                                    {{ __('Update Profile') }}
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
