@extends('layouts.app')
@include('common.navbar')

@section('content')

<style>
    body {
        background-image: url('/storage/images/bg-photo.jpeg');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
    }

    .main-content {
        background: #565656;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5);
        color: #fff;
    }

    h1.h2 {
        font-size: 2rem;
        font-weight: bold;
        color: #fff;
    }

    .card {
        background-color: #3a3a3a;
        color: #fff;
        border: none;
        border-radius: 10px;
    }

    .card-header {
        background-color: #1abc9c;
        color: #fff;
        font-weight: bold;
        text-align: center;
        border-radius: 10px 10px 0 0;
    }

    .card-body {
        padding: 1.5rem;
    }

    .profile-pic {
        width: 120px;
        height: 120px;
        object-fit: cover;
        border-radius: 50%;
        border: 3px solid #1abc9c;
        margin-bottom: 15px;
    }

    .list-group-item {
        background-color: transparent;
        color: #fff;
        border: none;
    }

    .iconBtn {
        margin-left: 10px;
        color: #1abc9c;
        border: none;
        background: none;
        cursor: pointer;
    }

    .iconBtn:hover {
        color: #16a085;
    }

    @media (max-width: 768px) {
        .main-content {
            padding: 15px;
        }
        .profile-pic {
            width: 100px;
            height: 100px;
        }
    }
</style>

@if(Auth::user()->role == 'Administrator' || Auth::user()->role == 'Purchase Manager' || Auth::user()->role == 'Inventory Manager' || Auth::user()->role == 'Auditor' || Auth::user()->role == 'Salesperson') 
<div class="col-md-9 ml-sm-auto col-lg-10 px-md-4">
    <div class="main-content">
        @include('common.alert')

        {{-- @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif --}}

        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center mb-4">
            <h1 class="h2">Profile Management</h1>
        </div>

        <div class="row">
            <!-- User Profile Section -->
            <div class="col-md-4">
                <div class="card text-center mb-4">
                    <div class="card-body">
                        <img src="{{ asset('storage/userImage/' . Auth::user()->image_url) }}" alt="Profile Picture" class="profile-pic">
                        <h5 class="mt-3">{{ Auth::user()->first_name }} {{ Auth::user()->last_name }}</h5>
                        <!-- Hidden Form for Editing -->
                        <form id="image_url-form" style="display: none;" method="POST" action="{{ route('profile.update', ['field' => 'image_url']) }}" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            <input type="file" name="image_url" class="form-control" accept="image/*">
                            <button type="submit" class="btn btn-sm btn-success mt-2">Update</button>
                            <button type="button" class="btn btn-sm btn-danger mt-2" onclick="toggleEdit('image_url')">Cancel</button>
                        </form>

                        <button class="iconBtn" id="image_url-edit-btn" onclick="toggleEdit('image_url')">
                            <i class="fa-solid fa-pen-to-square"></i> Update Profile Picture
                        </button>
                    </div>
                </div>
            </div>

            <!-- Credentials and Contact Details -->
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header">My Credentials</div>
                    <div class="card-body">
                        <ul class="list-group">
                            <li class="list-group-item">
                                Role: <strong>{{ Auth::user()->user_roles }}</strong>

                                @if(str_contains(Auth::user()->user_roles, 'Administrator'))
                                    <!-- Hidden Form for Editing -->
                                    <form id="roles-form" style="display: none;" method="POST" action="{{ route('profile.update', ['field' => 'roles[]']) }}">
                                        @csrf
                                        @method('PUT')
                                        <div class="form-group mb-4">
                                            <label>Select User Roles <i>*Required</i>: </label>
                                            <div class="form-check">
                                                <input id="Administrator" type="checkbox" class="form-check-input @error('roles') is-invalid @enderror" 
                                                    name="roles[]" value="Administrator">
                                                <label for="Administrator" class="form-check-label">Administrator</label>
                                            </div>
                                            <div class="form-check">
                                                <input id="inventory_manager" type="checkbox" class="form-check-input @error('roles') is-invalid @enderror" 
                                                    name="roles[]" value="Inventory Manager">
                                                <label for="inventory_manager" class="form-check-label">Inventory Manager</label>
                                            </div>
                                            <div class="form-check">
                                                <input id="auditor" type="checkbox" class="form-check-input @error('roles') is-invalid @enderror" 
                                                    name="roles[]" value="Auditor">
                                                <label for="auditor" class="form-check-label">Auditor</label>
                                            </div>
                                            @error('roles')
                                                <div class="alert alert-danger">{{ $message }}</div>
                                            @enderror
                                            <small class="form-text text-light mt-2">
                                                Note: You can select single or multiple roles.
                                            </small>
                                        </div>
                                        <button type="submit" class="btn btn-sm btn-success mt-2">Update</button>
                                        <button type="button" class="btn btn-sm btn-danger mt-2" onclick="toggleEdit('roles')">Cancel</button>
                                    </form>
                                    <button class="iconBtn" id="roles-edit-btn" onclick="toggleEdit('roles')">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                @endif
                            </li>


                            <li class="list-group-item">Email: <strong>{{ Auth::user()->email }}</strong>
                                <!-- Hidden Form for Editing -->
                                <form id="email-form" style="display: none;" method="POST" action="{{ route('profile.update', ['field' => 'email']) }}">
                                    @csrf
                                    @method('PUT')
                                    <input type="email" name="email" class="form-control" value="{{ Auth::user()->email }}">
                                    <small class="text form-text text-light mt-2">
                                        <p class="text">
                                            Note: Please enter a verified email address.
                                        </p>
                                    </small>
                                    <button type="submit" class="btn btn-sm btn-success mt-2">Update</button>
                                    <button type="button" class="btn btn-sm btn-danger mt-2" onclick="toggleEdit('email')">Cancel</button>
                                </form>

                                <button class="iconBtn" id="email-edit-btn" onclick="toggleEdit('email')">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>

                                @error('email')
                                    <div style="color: red">{{ $message }}</div>
                                @enderror
                            </li>
                            <li class="list-group-item">Password: <strong>********</strong>
                                <button class="iconBtn" type="button" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                            </li>                            
                        </ul>
                    </div>
                </div>

                <!-- Modal -->
                <div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content" style="color: #000;">
                            <div class="modal-header">
                                <h5 class="modal-title" id="changePasswordModalLabel">Change Password</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form action="{{ route('profile.update', ['field' => 'password']) }}" method="POST">
                                    @csrf
                                    @method('PUT')

                                    @if (isset($errors->getMessages()['current_password']) || isset($errors->getMessages()['new_password']) || isset($errors->getMessages()['new_password_confirmation']))
                                        <div class="alert alert-danger">
                                            <ul>
                                                 @foreach ($errors->all() as $error)
                                                    <li>{{ $error }}</li>
                                                @endforeach
                                            </ul>
                                         </div>
                                        <script>
                                            $(document).ready(function() {
                                                $('#changePasswordModal').modal('show');
                                            });
                                        </script>
                                    @endif

                                    <!-- Current Password -->
                                    <div class="mb-3">
                                        <label for="current_password" class="form-label">Current Password <i>*Required</i></label>
                                        <input type="password" name="current_password" class="form-control" id="current_password" required>
                                        <small class="form-text text-danger mt-2" style="color: red">
                                            <p class="text">
                                                Note: Please enter yur current password for validation.
                                            </p>
                                        </small>
                                    </div>

                                    <!-- New Password -->
                                    <div class="mb-3">
                                        <label for="new_password" class="form-label">New Password <i>*Required</i></label>
                                        <input type="password" name="new_password" class="form-control" id="new_password" pattern="^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*_\-\\\.\+]).{8,}$" required>
                                        <small class="form-text text-danger mt-2" style="color: red">
                                            <p class="text">
                                                Note: Please enter at least 8 characters with a number, symbol, capital letter, and small letter.
                                            </p>
                                        </small>
                                    </div>

                                    <!-- Confirm New Password -->
                                    <div class="mb-3">
                                        <label for="new_password_confirmation" class="form-label">Confirm New Password <i>*Required</i></label>
                                        <input type="password" name="new_password_confirmation" class="form-control" id="new_password_confirmation" required>
                                    </div>

                                    <button type="submit" class="btn btn-primary">Change Password</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="card mb-4">
                    <div class="card-header">Contact Details</div>
                    <div class="card-body">
                        <ul class="list-group">
                            <li class="list-group-item">Mobile Number: <strong>{{ Auth::user()->mobile_number ?? 'Not yet set' }}</strong>
                                 <!-- Hidden Form for Editing -->
                                 <form id="mobile-form" style="display: none;" method="POST" action="{{ route('profile.update', ['field' => 'mobile_number']) }}">
                                    @csrf
                                    @method('PUT')
                                    <input type="text" name="mobile_number" class="form-control" value="{{ Auth::user()->mobile_number }}" pattern="^09\d{9}$">
                                    <small class="text form-text text-light mt-2">
                                        <p class="text">
                                            Note: Please enter a PH mobile number starting with '09' followed by 9 digits.
                                        </p>
                                    </small>
                                    <button type="submit" class="btn btn-sm btn-success mt-2">Update</button>
                                    <button type="button" class="btn btn-sm btn-danger mt-2" onclick="toggleEdit('mobile')">Cancel</button>
                                </form>

                                <button class="iconBtn" id="mobile-edit-btn" onclick="toggleEdit('mobile')">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                            </li>
                            <li class="list-group-item">Permanent Address: <strong>{{ Auth::user()->permanent_address ?? 'Not yet set' }}</strong>
                                <!-- Hidden Form for Editing -->
                                <form id="permanent_address-form" style="display: none;" method="POST" action="{{ route('profile.update', ['field' => 'permanent_address']) }}">
                                    @csrf
                                    @method('PUT')
                                    <input type="text" name="permanent_address" class="form-control" value="{{ Auth::user()->permanent_address }}">
                                    <button type="submit" class="btn btn-sm btn-success mt-2">Update</button>
                                    <button type="button" class="btn btn-sm btn-danger mt-2" onclick="toggleEdit('permanent_address')">Cancel</button>
                                </form>

                                <button class="iconBtn" id="permanent_address-edit-btn" onclick="toggleEdit('permanent_address')">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                            </li>
                            <li class="list-group-item">Current Address: <strong>{{ Auth::user()->current_address ?? 'Not yet set' }}</strong>
                                <!-- Hidden Form for Editing -->
                                <form id="current_address-form" style="display: none;" method="POST" action="{{ route('profile.update', ['field' => 'current_address']) }}">
                                    @csrf
                                    @method('PUT')
                                    <input type="text" name="current_address" class="form-control" value="{{ Auth::user()->current_address }}">
                                    <button type="submit" class="btn btn-sm btn-success mt-2">Update</button>
                                    <button type="button" class="btn btn-sm btn-danger mt-2" onclick="toggleEdit('current_address')">Cancel</button>
                                </form>

                                <button class="iconBtn" id="current_address-edit-btn" onclick="toggleEdit('current_address')">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">Emergency Contact</div>
                    <div class="card-body">
                        <ul class="list-group">
                            <li class="list-group-item">Contact Person: <strong>{{ Auth::user()->emergency_contact ?? 'Not yet set' }}</strong>
                                <!-- Hidden Form for Editing -->
                                <form id="emergency_contact-form" style="display: none;" method="POST" action="{{ route('profile.update', ['field' => 'emergency_contact']) }}">
                                    @csrf
                                    @method('PUT')
                                    <input type="text" name="emergency_contact" class="form-control" value="{{ Auth::user()->emergency_contact }}" pattern="^[A-Z][a-z]+ [A-Z][a-z]+$">
                                    <small class="text form-text text-light mt-2">
                                        <p class="text">
                                            Note: Please enter in a format of: Firstname Lastname (e.g. John Doe).
                                        </p>
                                    </small>
                                    <button type="submit" class="btn btn-sm btn-success mt-2">Update</button>
                                    <button type="button" class="btn btn-sm btn-danger mt-2" onclick="toggleEdit('emergency_contact')">Cancel</button>
                                </form>

                                <button class="iconBtn" id="emergency_contact-edit-btn" onclick="toggleEdit('emergency_contact')">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                            </li>
                            <li class="list-group-item">Number: <strong>{{ Auth::user()->emergency_contact_number ?? 'Not yet set' }}</strong>
                                <!-- Hidden Form for Editing -->
                                <form id="emergency_contact_number-form" style="display: none;" method="POST" action="{{ route('profile.update', ['field' => 'emergency_contact_number']) }}">
                                    @csrf
                                    @method('PUT')
                                    <input type="text" name="emergency_contact_number" class="form-control" value="{{ Auth::user()->emergency_contact_number }}" pattern="^09\d{9}$">
                                    <small class="text form-text text-light mt-2">
                                        <p class="text">
                                            Note: Please enter a PH mobile number starting with '09' followed by 9 digits.
                                        </p>
                                    </small>
                                    <button type="submit" class="btn btn-sm btn-success mt-2">Update</button>
                                    <button type="button" class="btn btn-sm btn-danger mt-2" onclick="toggleEdit('emergency_contact_number')">Cancel</button>
                                </form>

                                <button class="iconBtn" id="emergency_contact_number-edit-btn" onclick="toggleEdit('emergency_contact_number')">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@else
    <h1 class="alert alert-danger mt-2">Sorry, you do not have access to this page. Please go <button onclick="window.history.back()" class="btn btn-secondary">← Back</button>.</h1>
@endif


<script>
   function toggleEdit(field) {
        const displayElement = document.querySelector(`#${field}-display`);
        const formElement = document.querySelector(`#${field}-form`);
        const editButton = document.querySelector(`#${field}-edit-btn`);

        if (formElement.style.display === "none") {
            formElement.style.display = "block";
            if (displayElement) displayElement.style.display = "none";
            if (editButton) editButton.style.display = "none"; // Hide the edit button
        } else {
            formElement.style.display = "none";
            if (displayElement) displayElement.style.display = "block";
            if (editButton) editButton.style.display = "inline-block"; // Show the edit button again
        }
    }

</script>

{{-- <a href="{{ url('edit_profile/'. Auth::user()->user_id) }}" class="btn" style="background-color: #3a8f66; color: #fff;">Edit Profile</a> --}}
@endsection
