<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;


class UserListController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::all();

        return view('admin.userlist', ['users' => $users]); 
    }

        /**
     * Display a listing of Suppliers.
     */
    public function indexSuppliers()
    {
        $suppliers = User::role('supplier')
            ->select('users.id', 'name', 'contact_number', DB::raw('CONCAT_WS(" ", users.street_no, users.street_name, users.barangay, users.municipality, users.province, users.region, users.zip_code) as address'), DB::raw('COUNT(products.id) as product_count'))
            ->leftJoin('products', 'users.id', '=', 'products.supplier_id')
            ->groupBy('users.id', 'name', 'contact_number', 'address')
            ->paginate(10);
     

        return view('officer.supplierlist', ['suppliers' => $suppliers]); 
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        /* 
        return view('admin.create', [
            'user' => $request->user(),
        ]); 
        */
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
         
        $user = new User();
        /**$user->name = $request->name;
        *$user->email = $request->email;
        *$user->password = $request->password;
        *$user->contact_number = $request->contact_number;
        *$user->address = $request->address;
        */
        $user->save();
        return redirect()->route('admin.userlist')->with('success', 'User Created.'); 

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
