<?php

namespace App\Http\Controllers;

use App\Mail\WelcomeDiscountMail;
use App\Models\Voucher;
use App\Models\WaitlistEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class WaitlistController extends Controller
{
    public function __invoke(Request $request)
    {
        $validated = $request->validate([
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('waitlist_entries', 'email'),
            ],
        ]);

        WaitlistEntry::create($validated);

        // Generate a unique one-time voucher code for this signup
        do {
            $code = 'WELCOME' . strtoupper(Str::random(6));
        } while (Voucher::where('code', $code)->exists());

        Voucher::create([
            'code'                    => $code,
            'description'             => "10% welcome discount for {$validated['email']}",
            'type'                    => 'percentage',
            'value'                   => 10.00,
            'applies_to_all_products' => true,
            'stackable'               => false,
            'new_customers_only'      => false,
            'single_use_per_user'     => true,
            'max_uses'                => 1,
            'uses_count'              => 0,
            'valid_until'             => now()->addDays(30),
            'is_active'               => true,
        ]);

        Mail::to($validated['email'])->queue(new WelcomeDiscountMail($code));

        return response()->json(['message' => 'Thank you!'], 201);
    }
}
