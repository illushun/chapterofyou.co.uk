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
use App\Models\Address\Lookup as AddressLookup;

class AccountController extends Controller
{
    /**
     * Show the main account settings view.
     */
    public function index()
    {
        $user      = Auth::user();
        $addresses = $user->addresses()->orderByDesc('is_default')->get();

        return inertia('account/View', [
            'user'      => $user->only('name', 'email', 'marketing_opt_in'),
            'addresses' => $addresses,
        ]);
    }

    /**
     * Update the user's general profile information (name and email).
     */
    public function updateProfile(Request $request)
    {
        $validated = $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore(Auth::id())],
        ]);

        $user = Auth::user();
        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }
        $user->save();

        return back()->with('success', 'Profile updated successfully.');
    }

    /**
     * Update the user's password.
     */
    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'string', 'current_password'],
            'password'         => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = Auth::user();
        $user->password = Hash::make($validated['password']);
        $user->save();

        return back()->with('success', 'Password updated successfully.');
    }

    /**
     * Look up addresses for a given query, using cache first.
     */
    public function lookupAddress(Request $request)
    {
        $validated = $request->validate([
            'query' => ['required', 'string', 'min:3', 'max:100'],
        ]);
        $query = $validated['query'];

        $cachedLookup = AddressLookup::where('query', $query)->first();
        if ($cachedLookup) {
            return response()->json(['addresses' => json_decode($cachedLookup->data, true)]);
        }

        $apiKey = env('GETADDRESS_IO_API_KEY');
        if (!$apiKey) {
            return response()->json(['message' => 'Address API Key is not configured.', 'addresses' => []], 503);
        }

        $response = Http::get("https://api.getaddress.io/autocomplete/{$query}", [
            'api-key' => $apiKey,
            'all'     => 'true',
        ]);

        if ($response->successful()) {
            $data        = $response->json();
            $addressList = [];

            if (isset($data['suggestions']) && is_array($data['suggestions'])) {
                foreach ($data['suggestions'] as $suggestion) {
                    $addressList[] = [
                        'line_1'   => $suggestion['address'] ?? 'Address Line 1',
                        'line_2'   => null,
                        'city'     => $suggestion['locality'] ?? 'City',
                        'county'   => null,
                        'postcode' => $suggestion['postcode'] ?? 'POSTCODE',
                        'country'  => 'United Kingdom',
                    ];
                }
            }

            AddressLookup::create(['query' => $query, 'data' => json_encode($addressList)]);
            return response()->json(['addresses' => $addressList]);
        }

        return response()->json(['message' => 'Failed to connect to address lookup service.', 'addresses' => []], 500);
    }

    /**
     * Store a new address.
     */
    public function storeAddress(Request $request)
    {
        $validated = $request->validate([
            'type'     => ['required', Rule::in(['shipping', 'billing'])],
            'is_default' => ['boolean'],
            'line_1'   => ['required', 'string', 'max:255'],
            'line_2'   => ['nullable', 'string', 'max:255'],
            'city'     => ['required', 'string', 'max:255'],
            'county'   => ['nullable', 'string', 'max:255'],
            'postcode' => ['required', 'string', 'max:20'],
            'country'  => ['required', 'string', 'max:255'],
        ]);

        if ($validated['is_default'] ?? false) {
            Auth::user()->addresses()->update(['is_default' => false]);
        }

        Auth::user()->addresses()->create($validated);
        return back()->with('success', 'Address added successfully.');
    }

    /**
     * Update an existing address.
     */
    public function updateAddress(Request $request, Address $address)
    {
        if ($address->user_id !== Auth::id()) {
            abort(403, 'Unauthorised action.');
        }

        $validated = $request->validate([
            'type'     => ['required', Rule::in(['shipping', 'billing'])],
            'is_default' => ['boolean'],
            'line_1'   => ['required', 'string', 'max:255'],
            'line_2'   => ['nullable', 'string', 'max:255'],
            'city'     => ['required', 'string', 'max:255'],
            'county'   => ['nullable', 'string', 'max:255'],
            'postcode' => ['required', 'string', 'max:20'],
            'country'  => ['required', 'string', 'max:255'],
        ]);

        if ($validated['is_default'] ?? false) {
            Auth::user()->addresses()->where('id', '!=', $address->id)->update(['is_default' => false]);
        }
        $address->update($validated);
        return back()->with('success', 'Address updated successfully.');
    }

    /**
     * Remove an address.
     */
    public function destroyAddress(Address $address)
    {
        if ($address->user_id !== Auth::id()) {
            abort(403, 'Unauthorised action.');
        }

        if ($address->is_default && Auth::user()->addresses()->count() > 1) {
            Auth::user()->addresses()->where('id', '!=', $address->id)->limit(1)->update(['is_default' => true]);
        }

        $address->delete();
        return back()->with('success', 'Address deleted successfully.');
    }
}
