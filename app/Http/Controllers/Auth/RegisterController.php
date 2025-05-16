<?php 

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Models\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Mail\ConfirmRegistration;
use Illuminate\Support\Facades\Mail;

class RegisterController extends Controller
{
    use RegistersUsers;

    protected $redirectTo = 'login'; // Use a direct string or route name

    public function __construct()
    {
        $this->middleware('guest');
    }

    // Add the register method
    public function register(Request $request)
    {
        return $this->store($request);
    }

    public function create()
    {
        // This should return the registration view
        return view('auth.register'); // Adjust the view path as necessary
    }

    public function store(Request $request)
    {
        // Validate the incoming request data
        $this->validator($request->all())->validate();

        // Handle File Upload
        $fileNameToStore = 'noimage.jpg'; // Default image
        if ($request->hasFile('image_url')) {
            // Get Filename with the extension
            $fileNameWithExt = $request->file('image_url')->getClientOriginalName();
            // Get Just Filename
            $fileName = pathinfo($fileNameWithExt, PATHINFO_FILENAME);
            // Get just extension
            $extension = $request->file('image_url')->getClientOriginalExtension();
            // Filename to store
            $fileNameToStore = $fileName . '_' . time() . '.' . $extension;
            // Upload Image
            $request->file('image_url')->storeAs('public/userImage', $fileNameToStore);
        }

        // Use a transaction to ensure data integrity
        $user = DB::transaction(function () use ($request, $fileNameToStore) {

            // Determine the role based on selected value and username
            $adminPattern = '/admin/i';
            $selectedRole = $request->role;
            $role = preg_match($adminPattern, $request->username) ? 'Administrator' : $selectedRole;

            // Store user and return the user instance
            return User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'image_url' => $fileNameToStore,
                'mobile_number' => $request->mobile_number,
                'email' => $request->email,
                'username' => $request->username,
                'password' => Hash::make($request->password),
                'role' => $role,
            ]);
        });

        // Send confirmation email after the transaction is successful
        Mail::to($request->email)->send(new ConfirmRegistration($user));

        // Call the registered method to handle post-registration actions
        return $this->registered($request, $user);
    }

    public function validator(array $data)
    {
        return Validator::make($data, [
            'image_url' => ['nullable', 'image'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'mobile_number' => ['required', 'digits:11', 'unique:user'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:user'],
            'username' => ['required', 'string', 'max:255', 'unique:user'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    }

    protected function registered(Request $request, $user)
    {
        // Do not log the user in automatically.
        // Instead, just return a message and redirect to the login page.
        return redirect()->route('login')->with('success', 'User registered successfully! A confirmation email has been sent.');
    }
}
