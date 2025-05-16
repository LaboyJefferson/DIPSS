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
        background-color: #565656; /* Light background for contrast */
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

    .modal-content{
        background-color:#565656 !important;
        color: #fff !important;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5); 
    }

    /*close button in modal*/
    .custom-close {
        background-color: transparent; /* Make background transparent */
        color: white; /* Keep text color white */
        border: none; /* Remove border */
        font-size: 24px; /* Adjust size if needed */
        cursor: pointer; /* Change cursor to pointer */
        padding: 0; /* Remove padding */
        outline: none; /* Remove outline on focus */
    }

    .custom-close:hover {
        color: #ccc; /* Optional: change color on hover */
    }

    /* Positioning for the notification circle inside <th> */
        .filterBtn {
        position: relative; /* Make <th> the reference point for absolute positioning */
    }

    /* Notification Circle Style */
    .notification-circle {
        position: absolute;
        top: -1em; /* Adjust the top position if necessary */
        right: -1em; /* Adjust the right position if necessary */
        background-color: #dc3545; /* Red background for the circle */
        color: white;
        width: 20px; /* Circle width */
        height: 20px; /* Circle height */
        border-radius: 50%; /* Make it a circle */
        text-align: center;
        font-size: 12px;
        line-height: 20px; /* Center the number inside the circle */
    }


    /* Dropdown Styles */
    .dropdown-menu {
        min-width: 200px;
    }

    /* Flexbox styling for the filter button and banner */
    .d-flex {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

</style>

@section('content')
@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css">
@endpush
@if(Auth::user()->role == "Administrator") <!-- Check if user is an administrator -->
    <div class="container-fluid">
        <div class="row">
            <!-- Main Content -->
            <main class="col-md-9 ml-sm-auto col-lg-10 px-md-4">
                <div class="main-content">
                    @include('common.alert')
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center">
                        <h1 class="h2">Account Management</h1>
                    </div>

                     <!-- Dropdown with Buttons -->
                     <div class="row d-flex justify-content-end"> <!-- Align the row to the right -->
                        <div class="col-auto">
                            <a type="button" class="btn btn-success mb-2" href="{{ route('accounts_table') }}">
                                Display All
                            </a>
                        </div>
                        <div class="col-auto">
                            <a type="button" class="btn filterBtn btn-success mb-2" href="{{ route('accounts_table.resend_link_filter') }}">
                                Resend Verification Link
                                @if($pendingResendLinkCount >= 0)
                                    <div class="notification-circle">
                                        {{ $pendingResendLinkCount }}
                                    </div>
                                @endif
                            </a>
                        </div>
                        <div class="col-auto">
                            <a type="button" class="btn filterBtn btn-success mb-2" href="{{ route('accounts_table.confirm_reject_filter') }}">
                                Confirm/Reject Account
                                @if($pendingConfirmRejectCount >= 0)
                                    <div class="notification-circle">
                                        {{ $pendingConfirmRejectCount }}
                                    </div>
                                @endif    
                            </a>
                        </div>
                    </div>

                    
                    

                    <!-- Table Section -->
                    <table class="table table-responsive table-hover">
                        
                        <thead>
                            <tr>
                                <th>First Name</th>
                                <th>Last Name</th>
                                <th>Email</th>
                                <th>Mobile Number</th>
                                <th>User Role</th>
                                <th>Email Verified At</th>
                                <th>
                                    Resend Link
                                </th>
                                <th colspan="2">
                                    Confirm User Account
                                </th>
                                <th colspan="2">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(count($userSQL) > 0) <!-- Check if userSQL is not null and not empty -->
                                @foreach($userSQL as $data)
                                    <tr>
                                        <td>{{ $data->first_name }}</td>
                                        <td>{{ $data->last_name }}</td>
                                        <td>{{ $data->email }}</td>
                                        <td>{{ $data->mobile_number ?? 'Unassigned' }}</td>
                                        <td>{{ $data->user_roles ?? 'Unassigned' }}</td>
                                        @if($data->email_verified_at !=null)
                                            <td>{{ $data->email_verified_at }}</td>
                                            <td><button type="button" class="btn btn-primary" disabled>Resend Link</button></td>
                                        @else
                                            <td>Not Yet Verified</td>
                                            <td>
                                                <form action="{{ route('resend_confirmation_email', $data->user_id) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="btn btn-primary">Resend Link</button>
                                                </form>
                                            </td>
                                        @endif
                                        @if($data->user_roles === null && $data->email_verified_at !=null)
                                            <td><button type="button" class="btn btn-primary" data-toggle="modal" data-target="#confirmModal{{ $data->user_id }}">Confirm</button></td>
                                            <td><button type="button" class="btn btn-danger" data-toggle="modal" data-target="#rejectModal{{ $data->user_id }}">Reject</button></td>
                                        @else
                                            <td><button type="button" class="btn btn-primary" disabled>Confirm</button></td>
                                            <td><button type="button" class="btn btn-danger" disabled>Reject</button></td>
                                        @endif
                                        <td>
                                            <!-- Trigger the update modal with a button -->
                                            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#updateModal{{ $data->user_id }}" title="Update">
                                                <i class="fa-solid fa-user-pen"></i>
                                            </button>
                                             <!-- Trigger the delete modal with a button -->
                                             <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#deleteModal{{ $data->user_id }}" title="Delete">
                                                <i class="fa-solid fa-user-minus"></i>
                                            </button>
                                        </td>

                                        {{-- reject modal --}}
                                        <div id="rejectModal{{ $data->user_id }}" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="rejectModalLabel" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h4 class="modal-title">Reject User Account</h4>
                                                        <button type="button" class="close custom-close" data-dismiss="modal">&times;</button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <form action="{{ route('reject_account', $data->user_id) }}" method="POST">
                                                            @csrf
                                                            <input type="hidden" name="user_id" value="{{ $data->user_id }}">
                                                            <input type="hidden" name="action" value="reject">
                                                            
                                                            <!-- Admin Password Input -->
                                                            <div class="form-group">
                                                                <label for="reject_admin_password">Current Password <i>*Required</i></label>
                                                                <input type="password" class="form-control @error('admin_password') is-invalid @enderror" 
                                                                       id="reject_admin_password_{{ $data->user_id }}" name="admin_password" required>
                                                                <small class="form-text text-light mt-2">
                                                                    Note: Please enter your current password for confirmation.
                                                                </small>
                                                                @error('reject_admin_password')
                                                                    <div class="alert alert-danger">{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                            
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                                                <button type="submit" class="btn btn-danger">Reject User Login</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        

                                        {{-- confirm madal --}}
                                        <div id="confirmModal{{ $data->user_id }}" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="confirmModalLabel" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h4 class="modal-title">Confirm User Account</h4>
                                                        <button type="button" class="close custom-close" data-dismiss="modal">&times;</button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <form action="{{ route('confirm_account', $data->user_id) }}" method="POST">
                                                            @csrf
                                                            <input type="hidden" name="user_id" value="{{ $data->user_id }}">
                                                            <input type="hidden" name="action" value="confirm">
                                                            
                                                            <!-- Admin Password Input -->
                                                            <div class="form-group mb-4">
                                                                <label for="confirm_admin_password">Current Password <i>*Required</i></label>
                                                                <input type="password" class="form-control @error('admin_password') is-invalid @enderror" 
                                                                       id="confirm_admin_password_{{ $data->user_id }}" name="admin_password" required>
                                                                <small class="form-text text-light mt-2">
                                                                    Note: Please enter your current password for confirmation.
                                                                </small>
                                                                @error('confirm_admin_password')
                                                                    <div class="alert alert-danger">{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                            
                                                            <!-- Roles Selection -->
                                                            <div class="form-group mb-4">
                                                                <label>Select User Roles <i>*Required</i>: </label>
                                                                <div class="form-check">
                                                                    <input id="confirm_role_admin_{{ $data->user_id }}" type="checkbox" class="form-check-input @error('roles') is-invalid @enderror" 
                                                                           name="roles[]" value="Administrator">
                                                                    <label for="confirm_role_admin_{{ $data->user_id }}" class="form-check-label">Administrator</label>
                                                                </div>
                                                                <div class="form-check">
                                                                    <input id="confirm_role_purchase_manager_{{ $data->user_id }}" type="checkbox" class="form-check-input @error('roles') is-invalid @enderror" 
                                                                           name="roles[]" value="Purchase Manager">
                                                                    <label for="confirm_role_purchase_manager{{ $data->user_id }}" class="form-check-label">Purchase Manager</label>
                                                                </div>
                                                                <div class="form-check">
                                                                    <input id="confirm_role_inventory_manager_{{ $data->user_id }}" type="checkbox" class="form-check-input @error('roles') is-invalid @enderror" 
                                                                           name="roles[]" value="Inventory Manager">
                                                                    <label for="confirm_role_inventory_manager{{ $data->user_id }}" class="form-check-label">Inventory Manager</label>
                                                                </div>
                                                                <div class="form-check">
                                                                    <input id="confirm_role_auditor_{{ $data->user_id }}" type="checkbox" class="form-check-input @error('roles') is-invalid @enderror" 
                                                                           name="roles[]" value="Auditor">
                                                                    <label for="confirm_role_auditor_{{ $data->user_id }}" class="form-check-label">Auditor</label>
                                                                </div>
                                                                <div class="form-check">
                                                                    <input id="confirm_role_salesperson_{{ $data->user_id }}" type="checkbox" class="form-check-input @error('roles') is-invalid @enderror" 
                                                                           name="roles[]" value="Salesperson">
                                                                    <label for="confirm_role_salesperson_{{ $data->user_id }}" class="form-check-label">Salesperson</label>
                                                                </div>
                                                                @error('roles')
                                                                    <div class="alert alert-danger">{{ $message }}</div>
                                                                @enderror
                                                                <small class="form-text text-light mt-2">
                                                                    Note: You can select single or multiple roles.
                                                                </small>
                                                            </div>
                                                            
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                                                <button type="submit" class="btn btn-primary">Confirm User Account</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>


                                         {{-- Update Modal --}}
                                         <div id="updateModal{{ $data->user_id }}" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="updateModalLabel" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h4 class="modal-title">Update User Role</h4>
                                                        <button type="button" class="close custom-close" data-dismiss="modal">&times;</button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <form action="{{ route('update_role', $data->user_id) }}" method="POST">
                                                            @csrf
                                                            
                                                            <input type="hidden" name="user_id" value="{{ $data->user_id }}">
                                                            <input type="hidden" name="action" value="update">
                                                            
                                                            <!-- Admin Password Input -->
                                                            <div class="form-group mb-4">
                                                                <label for="update_admin_password">Current Password <i>*Required</i></label>
                                                                <input type="password" class="form-control @error('admin_password') is-invalid @enderror" 
                                                                       id="update_admin_password_{{ $data->user_id }}" name="admin_password" required>
                                                                <small class="form-text text-light mt-2">
                                                                    Note: Please enter your current password for confirmation.
                                                                </small>
                                                                @error('update_admin_password')
                                                                    <div class="alert alert-danger">{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                            
                                                            <!-- Roles Selection -->
                                                            <div class="form-group mb-4">
                                                                <label>Select User Roles <i>*Required</i>: </label>
                                                                <div class="form-check">
                                                                    <input id="update_role_admin_{{ $data->user_id }}" type="checkbox" class="form-check-input @error('roles') is-invalid @enderror" 
                                                                           name="roles[]" value="Administrator">
                                                                    <label for="update_role_admin_{{ $data->user_id }}" class="form-check-label">Administrator</label>
                                                                </div>
                                                                <div class="form-check">
                                                                    <input id="update_role_purchase_manager_{{ $data->user_id }}" type="checkbox" class="form-check-input @error('roles') is-invalid @enderror" 
                                                                           name="roles[]" value="Purchase Manager">
                                                                    <label for="update_role_purchase_manager_{{ $data->user_id }}" class="form-check-label">Purchase Manager</label>
                                                                </div>
                                                                <div class="form-check">
                                                                    <input id="update_role_inventory_manager_{{ $data->user_id }}" type="checkbox" class="form-check-input @error('roles') is-invalid @enderror" 
                                                                           name="roles[]" value="Inventory Manager">
                                                                    <label for="update_role_inventory_manager_{{ $data->user_id }}" class="form-check-label">Inventory Manager</label>
                                                                </div>
                                                                <div class="form-check">
                                                                    <input id="update_role_auditor_{{ $data->user_id }}" type="checkbox" class="form-check-input @error('roles') is-invalid @enderror" 
                                                                           name="roles[]" value="Auditor">
                                                                    <label for="update_role_auditor_{{ $data->user_id }}" class="form-check-label">Auditor</label>
                                                                </div>
                                                                <div class="form-check">
                                                                    <input id="update_role_salesperson_{{ $data->user_id }}" type="checkbox" class="form-check-input @error('roles') is-invalid @enderror" 
                                                                           name="roles[]" value="Salesperson">
                                                                    <label for="update_role_salesperson_{{ $data->user_id }}" class="form-check-label">Salesperson</label>
                                                                </div>
                                                                @error('roles')
                                                                    <div class="alert alert-danger">{{ $message }}</div>
                                                                @enderror
                                                                <small class="form-text text-light mt-2">
                                                                    Note: You can select single or multiple roles.
                                                                </small>
                                                            </div>
                                                            
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                                                <button type="submit" class="btn btn-primary">Confirm User Update</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        

                                        <!-- Delete Modal -->
                                        <div id="deleteModal{{ $data->user_id }}" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h4 class="modal-title">Confirm Deletion</h4>
                                                        <button type="button" class="close custom-close" data-dismiss="modal">&times;</button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <form action="{{ route('account_management.destroy', $data->user_id) }}" method="POST">
                                                            @csrf
                                                            @method('DELETE')
                                                            
                                                            <input type="hidden" name="user_id" value="{{ $data->user_id }}">
                                                            <input type="hidden" name="action" value="delete">
                                                            
                                                            <!-- Admin Password Input -->
                                                            <div class="form-group">
                                                                <label for="delete_admin_password">Current Password <i>*Required</i></label>
                                                                <input type="password" class="form-control @error('admin_password') is-invalid @enderror" 
                                                                       id="delete_admin_password_{{ $data->user_id }}" name="admin_password" required>
                                                                <small class="form-text text-light mt-2">
                                                                    Note: Please enter your current password for confirmation.
                                                                </small>
                                                                @error('delete_admin_password')
                                                                    <div class="alert alert-danger">{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                            
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                                                <button type="submit" class="btn btn-danger">Confirm Delete</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="11" class="text-center">No user found.</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </main>
        </div>
    </div>
@else
    <h1 class="alert alert-danger mt-2">Sorry, you do not have access to this page. Please go <button onclick="window.history.back()" class="btn btn-secondary">← Back</button>.</h1>
@endif

<script>
    $(document).ready(function () {
        // Dynamically open the correct modal based on validation errors
        @if ($errors->any())
            let userId = '{{ old("user_id") }}'; // Retrieve the user_id from the old input
            let action = '{{ old("action") }}'; // Retrieve the action from the old input

            if (action === 'reject') {
                $('#rejectModal' + userId).modal('show');
            } else if (action === 'confirm') {
                $('#confirmModal' + userId).modal('show');
            } else if (action === 'update') {
                $('#updateModal' + userId).modal('show');
            } else if (action === 'delete') {
                $('#deleteModal' + userId).modal('show');
            }
        @endif
    });
</script>


@endsection