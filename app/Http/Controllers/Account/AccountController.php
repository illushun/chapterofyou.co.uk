<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AccountController extends Controller
{
    /**
     * Show the main account settings view.
     * @return \Inertia\Response
     */
    public function index()
    {
        $user = Auth::user();
        $addresses = $user->addresses()->get();

        return Inertia::render('account/View', [
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
            ],
            'addresses' => $addresses,
        ]);
    }

    /**
     * Update the user's general profile information (name and email).
     */
    public function updateProfile(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . Auth::id()],
        ]);

        $user = Auth::user();
        $user->forceFill([
            'name' => $request->name,
            'email' => $request->email,
        ])->save();
        return redirect()->back()->with('success', 'Profile updated successfully.');
    }

    /**
     * Update the user's password.
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'min:8', 'confirmed'],
        ]);

        // 2. Update logic here...
        // Auth::user()->update([
        //     'password' => Hash::make($request->password),
        // ]);

        return redirect()->back()->with('success', 'Password updated successfully.');
    }

    /**
     * Store a new address for the authenticated user.
     */
    public function storeAddress(Request $request)
    {
        $request->validate([
            'type' => 'required|in:shipping,billing,home',
            'line_1' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'postcode' => 'required|string|max:20',
            // ... other fields
        ]);

        Auth::user()->addresses()->create($request->all());
        return redirect()->back()->with('success', 'Address added successfully.');
    }

    /**
     * Update an existing address.
     */
    public function updateAddress(Request $request, Address $address)
    {
        if ($address->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'type' => 'required|in:shipping,billing,home',
            'line_1' => 'required|string|max:255',
            // ... other fields
        ]);

        $address->update($request->all());
        return redirect()->back()->with('success', 'Address updated successfully.');
    }

    /**
     * Remove an address.
     */
    public function destroyAddress(Address $address)
    {
        if ($address->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }
        $address->delete();
        return redirect()->back()->with('success', 'Address removed successfully.');
    }
}
