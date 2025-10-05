<?php

// app/Http/Controllers/WaitlistController.php

namespace App\Http\Controllers;

use App\Models\WaitlistEntry;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class WaitlistController extends Controller
{
    /**
     * Handle the incoming request to save a new waitlist email.
     */
    public function __invoke(Request $request)
    {
        // 1. Validation
        $validated = $request->validate([
            'email' => [
                'required',
                'email',
                'max:255',
                // Ensures the email is unique in the 'waitlist_entries' table
                Rule::unique('waitlist_entries', 'email'), 
            ],
        ]);

        // 2. Save to Database
        WaitlistEntry::create($validated);

        // 3. Return a successful JSON response
        return response()->json([
            'message' => 'Thank you! You have been added to the waitlist.',
        ], 201); // 201 Created
    }
}
