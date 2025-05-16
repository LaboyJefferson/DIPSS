<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;
use App\Models\Contact_Details;
use App\Models\User;
use App\Models\Credentials; // Ensure you import the Credentials model
use Illuminate\Support\Facades\Hash;

class ResetPasswordController extends Controller
{
    use ResetsPasswords;

    /**
     * Where to redirect users after resetting their password.
     *
     * @var string
     */
    protected $redirectTo = '/login';

    /**
     * Display the password reset form.
     */
    public function showResetForm(Request $request, $token = null)
    {
        $email = $request->email;
        return view('auth.passwords.reset')->with([
            'token' => $token,
            'email' => $email,
        ]);
    }

    /**
     * Handle resetting the user's password.
     */
    public function reset(Request $request)
    {
        // Validate the incoming request data
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Find the contact details by email
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors(['email' => trans('User not found.')]);
        }

        // Update the password in the credentials table
        $user->password = Hash::make($request->password);
        $user->save();

        // Redirect with success message
        return redirect($this->redirectTo)->with('success', trans('Password has been reset.'));
    }
}
