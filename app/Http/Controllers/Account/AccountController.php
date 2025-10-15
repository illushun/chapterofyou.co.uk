<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

use App\Models\Address;
use App\Models\User;
use App\Models\Address\Lookup;

class AccountController extends Controller
{
    /**
     * Show the main account settings view.
     * Fetches user and associated addresses.
     * @return \Inertia\Response
     */
    public function index()
    {
        $user = Auth::user();
        return Inertia::render('account/View', [
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
            ],
            'addresses' => $user->addresses()->orderByDesc('is_default')->get(),
        ]);
    }

    /**
     * Update the user's general profile information (name and email).
     */
    public function updateProfile(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore(Auth::id())],
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
            'password' => ['required', 'min:8', 'confirmed'], // 'confirmed' checks against password_confirmation
        ]);

        Auth::user()->update([
            'password' => Hash::make($request->password),
        ]);
        return redirect()->back()->with('success', 'Password updated successfully.');
    }

    /**
     * Look up addresses for a given query, using cache first.
     */
    public function lookupAddress(Request $request)
    {
        $request->validate([
            'query' => ['required', 'string', 'min:3', 'max:255'],
        ]);

        // Normalise the query for consistent caching
        $rawQuery = trim($request->query);

        // Check database cache
        $cachedLookup = AddressLookup::where('query', $rawQuery)->first();

        if ($cachedLookup) {
            // Return cached data
            return response()->json(['addresses' => $cachedLookup->data]);
        }

        // Cache miss, call API
        $apiKey = env('GETADDRESS_IO_API_KEY');

        // Using the /find endpoint for free-text search
        $response = Http::get("https://api.getaddress.io/find/{$rawQuery}", [
            'api-key' => $apiKey,
            'top' => 100
        ]);

        if ($response->successful()) {
            $data = $response->json();
            // The /find endpoint
            $addresses = $data['suggestions'] ?? $data['addresses'] ?? [];

            // Store results in cache
            AddressLookup::create([
                'query' => $rawQuery,
                'data' => $addresses,
            ]);
            return response()->json(['addresses' => $addresses]);
        }

        // Handle API error response
        return response()->json([
            'message' => 'Address lookup failed or nothing matched your search.',
            'errors' => $response->json() ?? ['api' => 'External API error.'],
        ], $response->status() ?: 500);
    }

    /**
     * Store a new address.
     */
    public function storeAddress(Request $request)
    {
        $request->validate([
            'type' => ['required', Rule::in(['shipping', 'billing', 'home'])],
            'is_default' => ['sometimes', 'boolean'],
            'line_1' => ['required', 'string', 'max:255'],
            'line_2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'county' => ['nullable', 'string', 'max:255'],
            'postcode' => ['required', 'string', 'max:20'],
            'country' => ['required', 'string', 'max:255'],
        ]);

        // Logic to handle setting a new default address (unsetting old one if necessary)
        if ($request->input('is_default')) {
            Auth::user()->addresses()->where('is_default', true)->update(['is_default' => false]);
        }
        Auth::user()->addresses()->create($request->all());

        return redirect()->back()->with('success', 'Address added successfully.');
    }

    /**
     * Update an existing address.
     */
    public function updateAddress(Request $request, Address $address)
    {
        if ($address->user_id !== Auth::id()) {
            abort(403, 'Unauthorised action.');
        }

        $request->validate([
            'type' => ['required', Rule::in(['shipping', 'billing', 'home'])],
            'is_default' => ['sometimes', 'boolean'],
            'line_1' => ['required', 'string', 'max:255'],
            'line_2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'county' => ['nullable', 'string', 'max:255'],
            'postcode' => ['required', 'string', 'max:20'],
            'country' => ['required', 'string', 'max:255'],
        ]);

        // Logic to handle setting a new default address
        if ($request->input('is_default')) {
            Auth::user()->addresses()->where('is_default', true)->update(['is_default' => false]);
        }
        $address->update($request->all());

        return redirect()->back()->with('success', 'Address updated successfully.');
    }

    /**
     * Remove an address.
     */
    public function destroyAddress(Address $address)
    {
        if ($address->user_id !== Auth::id()) {
            abort(403, 'Unauthorised action.');
        }
        $address->delete();

        return redirect()->back()->with('success', 'Address removed successfully.');
    }
}
