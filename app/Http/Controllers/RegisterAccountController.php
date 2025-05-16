<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Mail\ConfirmRegistration;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Exception;

class RegisterAccountController extends Controller
{

    // Show the registration form for admin users only
    public function showRegistrationForm()
    {
        // Optionally, add logic to restrict access
        if (env('ALLOW_ADMIN_REGISTRATION', false)) {
            return view('register_user_account.admin_register');
        }

        abort(403, 'Unauthorized action.');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('register_user_account.register_account');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Validate the incoming request data
        $validatedData = $request->validate([
            'first_name' => ['required', 'string', 'max:15'],
            'last_name' => ['required', 'string', 'max:15'],
            'email' => ['required', 'string', 'email', 'max:30', 'unique:user'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);


        // Generate a custom user ID
        $userId = $this->generateUserId();

        // Use a transaction to ensure data integrity
        $user = DB::transaction(function () use ($validatedData, $userId) {

            // Create the user
            $user = User::create([
                'user_id' => $userId,
                'first_name' => $validatedData['first_name'],
                'last_name' => $validatedData['last_name'],
                'email' => $validatedData['email'],
                'password' => Hash::make($validatedData['password']),
                'email_verification_sent_at' => now(),
            ]);

            Log::info('New user created with ID: ' . $user->user_id); // Log the new user ID
            return $user; // Return the user object
        });

        // Send confirmation email
        Mail::to($validatedData['email'])->send(new ConfirmRegistration($user));
        Log::info('Sending confirmation email for user: ', $user->toArray()); // Log email sending

        return redirect('/login')->with('success', 'User registered successfully! A confirmation email has been sent to your email address.');
    }

    /**
     * Generate a custom user ID based on the current year and latest user ID.
     *
     * @return string
     */
    private function generateUserId()
    {
        $currentYear = date('Y');
        $latestUser = DB::table('user')
                        ->where('user_id', 'like', "{$currentYear}%")
                        ->orderBy('user_id', 'desc')
                        ->first();

        Log::info('Latest user found: ', (array)$latestUser); // Log latest user information

        // Initialize newIdNumber to 1
        $newIdNumber = '0000';

        if ($latestUser) {
            // Extract the last four digits and increment them
            $latestIdNumber = substr($latestUser->user_id, -4); // Get the last 4 digits of user_id
            Log::info('Latest ID Number: ' . $latestIdNumber); // Log the latest ID Number
            
            $incrementedIdNumber = (int)$latestIdNumber + 1; // Increment the ID Number
            $newIdNumber = str_pad($incrementedIdNumber, 4, '0', STR_PAD_LEFT); // Format to 4 digits
        }

        // Concatenate year with new ID number
        $generatedUserId = $currentYear . $newIdNumber; // e.g., '20240001'
        Log::info('Generated User ID: ' . $generatedUserId); // Log the generated User ID
        
        return $generatedUserId; // Return the new User ID
    }
}
