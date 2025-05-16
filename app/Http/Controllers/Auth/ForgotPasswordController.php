<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
    use SendsPasswordResetEmails;

    /**
     * Send a reset link to the given user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function sendResetLinkEmail(Request $request)
    {
        // Validate the email address
        $request->validate(['email' => 'required|email']);

        // Check if the user exists
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors(['email' => trans('User not found.')]);
        }

        // Send the reset link
        $sent = Password::sendResetLink($request->only('email'));

        if ($sent === Password::RESET_LINK_SENT) {
            return back()->with('status', trans('A password reset link has been sent to your email.'));
        } else {
            return back()->withErrors(['email' => trans('Failed to send password reset link.')]);
        }
    }

    /**
     * Display the form to request a password reset link.
     *
     * @return \Illuminate\View\View
     */
    public function showLinkRequestForm()
    {
        return view('auth.passwords.email');
    }
}
