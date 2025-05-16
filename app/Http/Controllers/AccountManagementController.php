<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Mail\ConfirmRegistration;
use App\Mail\ConfirmationNotice;
use App\Mail\RejectRegistration;
use App\Mail\UpdateNotice;
use Illuminate\Support\Facades\Mail;
 use Illuminate\Support\Facades\Log;
 use Exception;

class AccountManagementController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() 
    {
        // Check if the user is logged in
        if (!Auth::check()) {
            // If the user is not logged in, redirect to login
            return redirect('/login')->withErrors('You must be logged in.');
        }
    
        // Fetch the logged-in user's credentials
        $user = Auth::user();
        
        // Check if the user has credentials
        if ($user) {
            
            // Check if the logged-in user is an Administrator
            if ($user->role === "Administrator") {

                // Get the total number of users that need to be confirmed/rejected
                $pendingConfirmRejectCount = DB::table('user')
                ->whereNull('user_roles')
                ->where('email_verified_at', '!=', null)
                ->count();

                // Get the total number of users that need to be confirmed/rejected
                $pendingResendLinkCount = DB::table('user')
                ->whereNull('email_verified_at')
                ->count();

                // Join `user`, `credentials`, and `contact_details` to get user details
                $userSQL = DB::table('user')
                ->select('user.*')
                ->where('user_id', '!=', $user->user_id)
                ->whereNotNull('created_at')
                // ->orWhere('user_roles', '=', null)
                ->get();
    
                // Pass the user details to the view
                return view('account_management.accounts_table', [
                    'userSQL' => $userSQL,
                    'pendingConfirmRejectCount' => $pendingConfirmRejectCount,
                    'pendingResendLinkCount' => $pendingResendLinkCount,
                ]);
            } else {
                // If the user is not an Administrator, redirect with an error
                return redirect('/login')->withErrors('Unauthorized access.');
            }
        }
    
        // If credentials are not found, redirect with an error
        return redirect('/login')->withErrors('Unauthorized access or missing credentials.');
    }

    public function confirmRejectFilter()
    {
        // Fetch users with 'user_roles' as null
        $userSQL = DB::table('user')
            ->whereNull('user_roles')
            ->where('email_verified_at', '!=', null)
            ->get();

        // Count users with 'user_roles' as null
        $pendingConfirmRejectCount = $userSQL->count();

        // Get the total number of users that need to be confirmed/rejected
        $pendingResendLinkCount = DB::table('user')
                ->whereNull('email_verified_at')
                ->count();

        // Pass the data to the view
        return view('account_management.accounts_table', [
            'userSQL' => $userSQL,
            'pendingConfirmRejectCount' => $pendingConfirmRejectCount,
            'pendingResendLinkCount' => $pendingResendLinkCount, // Pass the count here
        ]);
    }

    public function resendLinkFilter()
    {
        // Fetch users with 'user_roles' as null
        $userSQL = DB::table('user')
            ->whereNull('email_verified_at')
            ->get();

        // Count users with 'email_verified_at' as null
        $pendingResendLinkCount = $userSQL->count();

        // Get the total number of users that need to be confirmed/rejected
        $pendingConfirmRejectCount = DB::table('user')
                ->whereNull('user_roles')
                ->where('email_verified_at', '!=', null)
                ->count();

        // Pass the data to the view
        return view('account_management.accounts_table', [
            'userSQL' => $userSQL,
            'pendingResendLinkCount' => $pendingResendLinkCount,
            'pendingConfirmRejectCount' => $pendingConfirmRejectCount, // Pass the count here
        ]);
    }


    public function confirmEmail($id)
    {
        Log::info('Email confirmation called for user ID: ' . $id); // Log the incoming ID

        try {
            // Find the user by ID
            $user = User::find($id);
            Log::info('User found: ', ['user' => $user]); // Log the found user details

            if (!$user) {
                return redirect()->route('login')->with('error', 'User not found.');
            }

            // Check if the email is already verified
            if ($user->email_verified_at) {
                return redirect()->route('login')->with('error', 'Email has already been confirmed.');
            }

            // Check if the registration time is within one hour
            $createdAt = $user->email_verification_sent_at;
            $currentTime = now();

            if ($currentTime->diffInHours($createdAt) > 24) {
                return redirect()->route('login')->with('error', 'This confirmation link has expired. Please request a new one.');
            }

            // If within an hour, proceed with the email confirmation
            $user->email_verified_at = now(); // Set the email_verified_at timestamp
            $user->save(); // Save the changes

            return redirect()->route('login')->with('success', 'Email has been confirmed!');
        } catch (Exception $e) {
            Log::error('Email confirmation error: ' . $e->getMessage());
            return redirect()->route('login')->with('error', 'There was an error confirming your email.');
        }
    }

    public function resendConfirmationEmail($id)
    {
        // Find the user by their ID
        $user = User::find($id);

        if (!$user) {
            return redirect()->route('accounts_table')->with('error', 'User not found.');
        }

        // Check if the user has already verified their email
        if ($user->email_verified_at != null) {
            return redirect()->route('accounts_table')->with('error', 'Email already verified.');
        }

        try {

            // Update the email
            $user->update([
                'email_verification_sent_at' => now(),
            ]);

            // Send the confirmation email again
            Mail::to($user->email)->send(new ConfirmRegistration($user));

            return redirect()->route('accounts_table')->with('success', 'Confirmation email resent.');
        } catch (\Exception $e) {
            Log::error('Failed to resend confirmation email for user ' . $user->user_id . ': ' . $e->getMessage());
            return redirect()->route('accounts_table')->with('error', 'Failed to resend confirmation email.');
        }
    }

    public function confirmAccount(Request $request, $id)
    {
        // Find the user by their ID
        $user = User::find($id);

        if (!$user) {
            return redirect()->route('accounts_table')->with('error', 'User not found.');
        }

        // Validate the input
        $request->validate([
            'admin_password' => 'required|string',
            'roles' => 'required|array|min:1',
            'roles.*' => 'in:Administrator,Purchase Manager,Inventory Manager,Auditor,Salesperson',
        ]);

        // Check if the admin's current password is correct
        if (!Hash::check($request->admin_password, auth()->user()->password)) {
            return back()->withErrors(['admin_password' => 'Error: Current password is incorrect.'])->withInput();
        }

        // Perform the role update within a transaction
        DB::transaction(function () use ($request, $user) {
            // Prepare the update data
            $updateData = [
                'user_roles' => implode(', ', $request->roles), // Join the roles into a comma-separated string
            ];
    
            // Update the user's role
            User::where('user_id', $user->user_id)->update($updateData);
        });

        try {
            // Send the rejection email
            Mail::to($user->email)->send(new ConfirmationNotice($user));

            return redirect()->route('accounts_table')->with('success', 'User account confirmed with roles: ' . implode(', ', $request->roles). ' & email confirmation notice has been sent to the user');
        } catch (\Exception $e) {
            Log::error('Failed to send confirmation notice to the email of the user ' . $user->id . ': ' . $e->getMessage());
            return redirect()->route('accounts_table')->with('error', 'Failed to send rejection email.');
        }
    }

    public function rejectAccount(Request $request, $id)
    {
        // Find the user by their ID
        $user = User::find($id);

        if (!$user) {
            return redirect()->route('accounts_table')->with('error', 'User not found.');
        }

        // Validate the input
        $request->validate([
            'admin_password' => 'required|string',
        ]);

        // Check if the admin's current password is correct
        if (!Hash::check($request->admin_password, auth()->user()->password)) {
            return back()->withErrors(['admin_password' => 'Error: Admin password is incorrect.'])->withInput();
        }

        try {
            // Send the rejection email
            Mail::to($user->email)->send(new RejectRegistration($user));

            // Finally, delete the user
            $user->delete();

            return redirect()->route('accounts_table')->with('success', 'Rejection email notice has been sent to the user. The account will be remove from our records');
        } catch (\Exception $e) {
            Log::error('Failed to send rejection email for user ' . $user->id . ': ' . $e->getMessage());
            return redirect()->route('accounts_table')->with('error', 'Failed to send rejection email.');
        }
    }


    public function updateRole(Request $request, $id)
    {
        // Find the user by their ID
        $user = User::find($id);

        if (!$user) {
            return redirect()->route('accounts_table')->with('error', 'User not found.');
        }

        // Validate the input
        $request->validate([
            'admin_password' => 'required|string',
            'roles' => 'required|array|min:1',
            'roles.*' => 'in:Administrator,Purchase Manager,Inventory Manager,Auditor,Salesperson',
        ]);

        // Check if the admin's current password is correct
        if (!Hash::check($request->admin_password, auth()->user()->password)) {
            return back()->withErrors(['update_admin_password' => 'Error: Current password is incorrect.'])->withInput();
        }

        // Perform the role update within a transaction
        DB::transaction(function () use ($request, $user) {
            // Prepare the update data
            $updateData = [
                'user_roles' => implode(', ', $request->roles), // Join the roles into a comma-separated string
            ];
    
            // Update the user's role
            User::where('user_id', $user->user_id)->update($updateData);
        });

        try {
            // Send the email
            Mail::to($user->email)->send(new UpdateNotice($user));

            return redirect()->route('accounts_table')->with('success', 'User account confirmed with roles: ' . implode(', ', $request->roles). ' & update notice has been sent to the user');
        } catch (\Exception $e) {
            Log::error('Failed to send confirmation notice to the email of the user ' . $user->id . ': ' . $e->getMessage());
            return redirect()->route('accounts_table')->with('error', 'Failed to send rejection email.');
        }
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        // Validate admin credentials
        $request->validate([
            'admin_password' => 'required|string',
        ]);

        // Check if the admin's current password is correct
        if (!Hash::check($request->admin_password, auth()->user()->password)) {
            return back()->withErrors(['admin_password' => 'Error: Admin password is incorrect.'])->withInput();
        }

        // Find the user to be deleted
        $user = User::find($id);

        // Check if the user exists
        if (!$user) {
            return redirect()->route('accounts_table')->with('error', 'User not found.');
        }

        // Finally, delete the user
        $user->delete();

        return redirect()->route('accounts_table')->with('success', 'User deleted successfully.');
    }

}
