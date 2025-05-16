<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\ConfirmRegistration;

class ProfileController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        // Check if the user is logged in
        if (!Auth::check()) {
            // If the user is not logged in, redirect to login
            return redirect('/login')->withErrors('You must be logged in.');
        }

        $user = Auth::user();
        return view('profile.show_profile', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        // Get the authenticated user
        $userAuth = Auth::user();
        
        // Find the user by the provided ID
        $user = User::find($id);
        
        // Check if the user is logged in
        if (!Auth::check()) {

            // Update roles
            $user->update([
                'role' => null,
            ]);

            return redirect('/login')->with('error', 'Unauthorized Page');
        }

        // Pass user data to the view
        return view('profile.update_profile', [
            'user' => $user,
            'userAuth' => $userAuth,
        ]);
    }

    public function update(Request $request, $field)
    {
        /** @var User $user */
        $user = Auth::user(); // Explicitly cast Auth::user() as an instance of the User model

        // update user_roles separately
        if ($field === 'roles[]') {
            $request->validate([
                'roles' => ['required', 'array'],
                'roles.*' => ['string', 'in:Administrator,Inventory Manager,Auditor'], // Ensure valid roles
            ]);
    
            // Update roles
            $user->update([
                'user_roles' => implode(', ', $request->roles), // Save roles as a comma-separated string
                'role' => null,
            ]);

            //for logout
            Auth::logout(); // Log out the user
            $request->session()->invalidate(); // Invalidate the session
            $request->session()->regenerateToken(); // Regenerate CSRF token
    
            return redirect('/login')->with('success', 'Roles updated successfully!');
        }

        // Handle email update separately
        if ($field === 'email') {
            // Validate the email
            $request->validate([
                'email' => ['required', 'string', 'email', 'max:30', 'unique:user'],
            ]);

            // Update the email
            $user->update([
                'email' => $request['email'], // Hash the new password before storing
                'email_verified_at' => null,
                'email_verification_sent_at' => now(),
                'role' => null,
            ]);

            // Send confirmation email
            Mail::to($request['email'])->send(new ConfirmRegistration($user));

            //for logout
            Auth::logout(); // Log out the user
            $request->session()->invalidate(); // Invalidate the session
            $request->session()->regenerateToken(); // Regenerate CSRF token

            return redirect('/login')->with('success', 'Email updated successfully! A confirmation email has been sent to your email address.');
        }

        // Handle password update separately
        if ($field === 'password') {
            // Validate the new password and confirm new password
            $request->validate([
                'current_password' => 'required|string',
                'new_password' => 'required|string|min:8|confirmed', // Ensure passwords match
            ]);

            // Check if the current password is correct
            if (!Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'Error: Current password is incorrect.']);
            }

            // Check if the new password is similar to current password
            if ($request->current_password === $request->new_password) {
                return back()->withErrors(['new_password' => 'Error: New password should not be similar to your current password.']);
            }

            // Update the password
            $user->update([
                'password' => Hash::make($request->new_password), // Hash the new password before storing
                'role' => null,
            ]);

            //for logout
            Auth::logout(); // Log out the user
            $request->session()->invalidate(); // Invalidate the session
            $request->session()->regenerateToken(); // Regenerate CSRF token

            return redirect('/login')->with('success', 'Password updated successfully!');
        }

        // Handle the image field separately
        if ($field === 'image_url' && $request->hasFile('image_url')) {

            $request->validate([
                'image_url' => ['image'],
            ]);

            $fileNameWithExt = $request->file('image_url')->getClientOriginalName();
            $fileName = pathinfo($fileNameWithExt, PATHINFO_FILENAME);
            $extension = $request->file('image_url')->getClientOriginalExtension();
            $fileNameToStore = $fileName . '_' . time() . '.' . $extension;
            $request->file('image_url')->storeAs('public/userImage', $fileNameToStore);

            $user->update(['image_url' => $fileNameToStore]);
        } else {

            // Validate the input based on the field being updated
            $request->validate([
                $field => 'required|string|max:255',
            ]);

            // Update other fields
            $user->update([$field => $request->$field]);
        }

        return redirect()->back()->with('success', ucfirst($field) . ' updated successfully!');
    }
}
