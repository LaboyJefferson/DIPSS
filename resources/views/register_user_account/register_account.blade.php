@extends('layouts.app')

@section('content')
<style>
    body {
        background-image: url('/storage/images/bg-photo.jpeg');
        background-size: cover; /* Cover the entire viewport */
        background-position: center; /* Center the background image */
        background-repeat: no-repeat; /* Prevent the image from repeating */
        background-color: #1a1a1a; /* Dark background */
        color: #fff; /* Light text color */
        height: 100vh; /* Full viewport height */
        display: flex; /* Enable flexbox */
        justify-content: center; /* Center horizontally */
        align-items: center; /* Center vertically */
        margin: 0; /* Remove default margin */
    }
    .card {
        background-color: #565656; /* Card background */
        border: none; /* Remove border */
        border-radius: 8px; /* Rounded corners */
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5); 
        width: 100%;
        max-width: 700px; /* Constrain the card width */
    }
    .input-group-text {
        background-color: #3a8f66; /* Input group background */
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
</style>

<div class="card">
    <div class="card-header text-center" style="background-color:#3a8f66; color:#fff; font-weight: bold;">{{ __('Register') }}</div>
    <div class="card-body">
        <!-- Alert Messages -->
        @include('common.alert')
        <form method="POST" action="{{ url('register_account') }}" enctype="multipart/form-data">
            @csrf

            <!-- Input fields -->
            <div class="row">
                <div class="col mb-4">
                    <span class="input-group-text">
                        <i class="fa fa-user fa-lg"></i><label class="ms-2">First Name <i>*Required</i></label>
                    </span>
                    <input id="first_name" type="text" class="form-control @error('first_name') is-invalid @enderror" name="first_name" placeholder="Format Sample: Gabriel" value="{{ old('first_name') }}" pattern="^[A-Z]{1}[a-z]*$" required>
                    <small class="text form-text text-light mt-2">
                        Note: Please enter the value starting with an uppercase letter, followed by lowercase letters only.
                    </small>
                    @error('first_name')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="col mb-4">
                    <span class="input-group-text">
                        <i class="fa fa-user fa-lg"></i><label class="ms-2">Last Name <i>*Required</i></label>
                    </span>
                    <input id="last_name" type="text" class="form-control @error('last_name') is-invalid @enderror" name="last_name" placeholder="Format Sample: Madriago" value="{{ old('last_name') }}" pattern="^[A-Z]{1}[a-z]*$" required>
                    <small class="text form-text text-light mt-2">
                        Note: Please enter the value starting with an uppercase letter, followed by lowercase letters only.
                    </small>
                    @error('last_name')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
            </div>

            <div class="row">
                <div class="col mb-4">
                    <span class="input-group-text">
                        <i class="fa-solid fa-envelope fa-lg"></i><label class="ms-2">Email Address <i>*Required</i></label>
                    </span>
                    <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" placeholder="Email Address should be valid" value="{{ old('email') }}" required>
                    <small class="text form-text text-light mt-2">
                        Note: Please enter a verified email address.
                    </small>
                    @error('email')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="row">
                    <div class="col mb-4">
                        <span class="input-group-text">
                            <i class="fa fa-key fa-lg"></i><label class="ms-2">Password <i>*Required</i></label>
                        </span>
                        <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" placeholder="Must be a strong password" pattern="^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*_\-\\\.\+]).{8,}$" required>
                        <small class="text form-text text-light mt-2">
                            Note: Please enter at least 8 characters with a number, symbol, capital letter, and small letter.
                        </small>
                        @error('password')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="col mb-4">
                        <span class="input-group-text">
                            <i class="fa fa-key fa-lg"></i><label class="ms-2">Confirm Password <i>*Required</i></label>
                        </span>
                        <input id="password-confirm" type="password" class="form-control" name="password_confirmation" placeholder="Entered password should match." required>
                    </div>
                </div>
            </div>

            <div class="row mb-0">
                <div class="col text-center">
                    <button type="submit" name="create" class="btn btn-primary">
                        {{ __('Register') }}
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
