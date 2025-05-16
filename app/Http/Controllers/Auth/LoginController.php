<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User; // User Model
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Support\Str;

class LoginController extends Controller
{
    /**
     * Handle login requests to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    use ThrottlesLogins;

    protected function throttleKey()
    {
        return strtolower($this->username()) . '|' . request()->ip();
    }

    protected function sendLockoutResponse(Request $request)
    {
        $seconds = $this->limiter()->availableIn(
            $this->throttleKey($request)
        );

        return redirect()->back()
            ->withInput($request->only('email'))
            ->withErrors([
                'email' => "Too many attempts. Try again in {$seconds} seconds.",
            ])
            ->with('lockout_seconds', $seconds); // 🔥 Send to frontend
    }

    public function username()
    {
        return 'email';
    }

    public function login(Request $request)
{
    // Validate the form data
    $credentials = $request->validate([
        'role' => 'nullable|string',
        'email' => 'required|string|email',
        'password' => 'required|string',
    ]);

    // 🔒 Throttle check
    if ($this->hasTooManyLoginAttempts($request)) {
        $this->fireLockoutEvent($request);
        return $this->sendLockoutResponse($request);
    }

    // Fetch the user by email
    $credential = User::where('email', $credentials['email'])->first();

    // Check if user exists
    if ($credential) {
        if (Hash::check($credentials['password'], $credential->password)) {

            // Fetch the user and check if the user has valid email verification
            $user = User::where('user_id', $credential->user_id)->first();
            $userFKey = DB::table('user')
                ->select('user.*')
                ->where('user_id', '=',  $credential->user_id)
                ->first();

            if ($userFKey) {
                
                // Check if the email is verified
                if ($credential->email_verified_at !== null) {
                    if (!empty($credential->user_roles)) {
                        // Log the user in
                        Auth::login($user);
                        
                        // Get all roles associated with the user
                        $roles = explode(', ', $user->user_roles); // Converts the comma-separated string back into an array

                        // If the user has only one role, log them in directly
                        if (count($roles) === 1) {

                            // Perform the role update within a transaction
                            DB::transaction(function () use ($user, $roles) {
                                // Prepare the update data
                                $updateData = [
                                    'role' => $roles[0], //update the role fieild if the user's user_role is only 1
                                ];
                        
                                // Update the user's role
                                User::where('user_id', $user->user_id)->update($updateData);
                            });

                            // Auth::login($user);
                            $this->clearLoginAttempts($request);
                            return redirect('/dashboard')->with('success', "Successfully logged in as {$roles[0]}.");
                        }

                        //If user have multiple user_roles, update the role selected by the user in the login page
                            DB::transaction(function () use ($request, $user) {
                                // Prepare the update data
                                $updateData = [
                                    'role' => $request->role,
                                ];
                        
                                // Update the user's role
                                User::where('user_id', $user->user_id)->update($updateData);
                            });

                            $this->clearLoginAttempts($request);
                            return redirect('/dashboard')->with('success', "Successfully Logged in.");
                    } else {
                        // Increment Failed login attempts
                        $this->incrementLoginAttempts($request);
                        return back()->with('error', 'Your account is not verified yet.');
                    }
                } else {
                    $this->incrementLoginAttempts($request);
                    return back()->with('error', 'Your email is not verified yet. Please check your inbox.');
                }
            } else {
                $this->incrementLoginAttempts($request);
                return back()->with('error', 'The provided credentials do not match our records.');
            }
        } else {
            $this->incrementLoginAttempts($request);
            return back()->with('error', 'Incorrect Entered Password. Please login again.');
        }
    } else {
        $this->incrementLoginAttempts($request);
        // If login fails, redirect back with an error message
        return back()->with('error', 'Incorrect Entered Email. The user does not exist.');
    }
}
    public function showLoginForm()
    {
        return view('auth.login', ['roles' => []]);
    }

    public function logout(Request $request)
    {
        $user = Auth::user();

        // Perform the role update within a transaction
        DB::transaction(function () use ($user) {
            // Prepare the update data
            $updateData = [
                'role' => null,
            ];
    
            // Update the user's role
            User::where('user_id', $user->user_id)->update($updateData);
        });

        Auth::logout(); // Log out the user

        $request->session()->invalidate(); // Invalidate the session
        $request->session()->regenerateToken(); // Regenerate CSRF token

        return redirect('/login'); // Redirect to login or any desired route
    }

    // This fetch roles based on the entered email. This method will handle the AJAX request:
    public function getUserRoles(Request $request)
    {
        $email = $request->input('email');
    
        // Fetch the user by email
        $user = User::where('email', $email)->first();
    
        if ($user && $user->user_roles) {
            // Convert roles to an array
            $roles = explode(', ', $user->user_roles);
            return response()->json(['roles' => $roles]);
        }
    
        // If no user or roles found, return an empty array
        return response()->json(['roles' => []]);
    }

    // Optional: Customize the max login attempts and lockout time
    protected function maxAttempts()
    {
        return 2; // max 5 attempts
    }

    protected function decayMinutes()
    {
        return 1; // lockout for 2 minutes
    }

}
