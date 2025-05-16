@extends('layouts.app')

@section('content')
<style>
    body {
        background-image: url('/storage/images/bg-photo.jpeg'); 
        background-size: cover; 
        background-position: center; 
        background-repeat: no-repeat; 
        height: 100vh; 
        margin: 0; 
        display: flex; 
        justify-content: center; 
        align-items: center; 
    }

    .card {
        border: none; 
        min-height: 400px;
        background-color: #565656; 
        backdrop-filter: blur(10px); 
        width: 1500px; 
        max-width: 90%; 
        height: 100%; 
        border-radius: 15px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5); 
    }

    .card-body {
        display: flex; 
        align-items: stretch; 
        justify-content: space-between;
        padding: 40px;
        border-radius: 15px;
    }

    .loginIMG {
        width: 40%; 
        height: 100%; 
        object-fit: cover;  
        border-radius: 10px;
    }

    .alert {
        margin-bottom: 1.5rem;
        text-align: center;
        font-weight: bold;
    }

    .btn {
        transition: background-color 0.3s ease; 
    }

    .btn-login {
        background-color: #3a8f66; 
        color: white; 
        border: none;
    }

    .btn-login:hover {
        background-color: #2f6b5a; 
    }

    .btn-register {
        background-color: #231f20; 
        color: white; 
        border: none;
        transition: background-color 0.3s ease; 
    }

    .btn-register:hover {
        background-color: #0d0d0d;
    }

    .forgot-password {
        margin-right: 0px;
    }

    @media (max-width: 768px) {
        .card-body {
            flex-direction: column; 
            align-items: center; 
        }

        .loginIMG {
            width: 100%; 
            margin-bottom: 20px; 
        }

        .login-form {
            width: 100%; 
        }
    }

    .input-group .toggle-password {
        width: 40px;
        display: flex;
        justify-content: center;
        align-items: center;
    }
</style>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <!-- Left Side: Login Image -->
                    <img src="/storage/images/loginIMG.png" class="loginIMG img-fluid" alt="login.jpg">

                    <!-- Right Side: Login Form -->
                    <div class="login-form w-50" style="color: white; margin-top: 20px;">
                        <!-- Alert Messages -->
                        @include('common.alert')

                        @if(session('lockout_seconds'))
                            <div id="lockout-message" class="alert alert-danger">
                                Too many attempts. Try again in <span id="countdown">{{ session('lockout_seconds') }}</span> seconds.
                            </div>

                            <script>
                                let seconds = {{ session('lockout_seconds') }};
                                const countdownEl = document.getElementById('countdown');

                                const interval = setInterval(() => {
                                    seconds--;
                                    countdownEl.textContent = seconds;

                                    if (seconds <= 0) {
                                        clearInterval(interval);
                                        location.reload(); // optional: reload the page to enable the form again
                                    }
                                }, 1000);
                            </script>

                        @elseif($errors->any())
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <!-- Logo Image -->
                        <div class="text-center mb-4">
                            <img src="/storage/images/DiS_Logo.png" class="img-fluid" alt="logo" style="width: 25vw; height: auto; background: transparent;">
                        </div>
                        <div style="">
                            <p class="mt-8" style="color: #fff">Login to your account</p>
                        
                            <form method="POST" action="{{ route('login') }}">
                                @csrf
                                
                                <!-- Email Input -->
                                <div class="mb-3">
                                    <div class="input-group">
                                        <span class="input-group-text" id="basic-addon1">
                                            <i class="fa fa-user fa-lg"></i>
                                        </span>
                                        <input id="email" type="email" placeholder="Email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email">
                                        {{-- <span class="input-group-text" id="basic-addon1">
                                            <i class="fa fa-user fa-lg"></i>
                                        </span> --}}
                                    </div>
                                    @error('email')
                                        <span class="invalid-feedback" role="alert" style="color: #dc3545;">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                {{-- display the user_roles associated with the entered email of the user --}}
                                <div class="mb-3" id="roles-container" style="display: none;">
                                    <label for="role">Choose a role to login <i>*Required</i>: </label>
                                    <select name="role" id="role" class="form-control">
                                        @foreach ($roles as $role)
                                            <option value="{{ $role }}">{{ ucfirst($role) }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Password Input -->
                                <div class="mb-3">
                                    <div class="input-group">
                                        <span class="input-group-text" id="basic-addon2">
                                            <i class="fa fa-key fa-lg"></i>
                                        </span>
                                        <input id="password" type="password" placeholder="Password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password" required>
                                        <span class="input-group-text toggle-password" onclick="togglePassword()" style="cursor: pointer;">
                                            <i id="toggleIcon" class="fa fa-eye-slash"></i>
                                        </span>
                                    </div>
                                    @error('password')
                                        <span class="invalid-feedback" role="alert" style="color: #dc3545;">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <!-- Forgot Password -->
                                <div class="mb-4 d-flex justify-content-between align-items-center">
                                    <div class="ms-auto"> 
                                        @if (Route::has('password.request'))
                                            <a class="forgot-password btn btn-link" href="{{ route('register_account.create') }}" style="color: #fff;">
                                                {{ __('Register') }}
                                            </a>
                                        @endif
                                    </div>
                                    <div class="ms-auto"> 
                                        <a class="createAccount btn btn-link" href="{{ route('password.request') }}" style="color: #fff;">
                                            {{ __('Forgot Password?') }}
                                        </a>
                                    </div>
                                </div>

                                <!-- Login Button -->
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-login mb-4">
                                        {{ __('Login') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>

document.getElementById("email").addEventListener("blur", function () {
    const email = this.value.trim();
    const roleDropdown = document.getElementById("role");
    const rolesContainer = document.getElementById("roles-container");

    // Clear existing options
    roleDropdown.innerHTML = "";

    if (email) {
        // Make AJAX request to fetch roles
        fetch("{{ route('get-user-roles') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content")
            },
            body: JSON.stringify({ email: email })
        })
            .then(response => {
                if (response.status === 404) {
                    throw new Error("The email address does not exist in our records.");
                }
                if (!response.ok) {
                    throw new Error("An error occurred while processing your request.");
                }
                return response.json();
            })
            .then(data => {
                if (data.roles && data.roles.length > 0) {
                    if (data.roles.length === 1) {
                        // Automatically select a single role
                        const singleRole = data.roles[0];
                        const option = document.createElement("option");
                        option.value = singleRole;
                        option.textContent = singleRole.charAt(0).toUpperCase() + singleRole.slice(1);
                        roleDropdown.appendChild(option);
                        rolesContainer.style.display = "none";
                    } else {
                        // Populate dropdown with multiple roles
                        const defaultOption = document.createElement("option");
                        defaultOption.value = "";
                        defaultOption.textContent = "Click here to select a role";
                        defaultOption.disabled = true;
                        defaultOption.selected = true;
                        roleDropdown.appendChild(defaultOption);

                        data.roles.forEach(role => {
                            const option = document.createElement("option");
                            option.value = role;
                            option.textContent = role.charAt(0).toUpperCase() + role.slice(1);
                            roleDropdown.appendChild(option);
                        });

                        rolesContainer.style.display = "block";
                        roleDropdown.setAttribute("required", "true");
                    }
                } else {
                    // Display message if no roles found
                    const option = document.createElement("option");
                    option.value = "";
                    option.textContent = "No roles available";
                    option.disabled = true;
                    roleDropdown.appendChild(option);
                }
            })
            .catch(error => {
                console.error("Error:", error.message);
                alert(error.message);
            });
    }
});

// Show/hide password
function togglePassword() {
        const passwordInput = document.getElementById("password");
        const toggleIcon = document.getElementById("toggleIcon");
        
        if (passwordInput.type === "password") {
            passwordInput.type = "text";
            toggleIcon.classList.remove("fa-eye-slash");
            toggleIcon.classList.add("fa-eye");
        } else {
            passwordInput.type = "password";
            toggleIcon.classList.remove("fa-eye");
            toggleIcon.classList.add("fa-eye-slash");
        }
    }

// Remove 'required' from the role field when hidden to prevent focusable errors
document.querySelector("form").addEventListener("submit", function () {
    const rolesContainer = document.getElementById("roles-container");
    const roleDropdown = document.getElementById("role");

    if (rolesContainer.style.display === "none" || !roleDropdown.value) {
        roleDropdown.removeAttribute("required");
    }
});

</script>

@endsection
