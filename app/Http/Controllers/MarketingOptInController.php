<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;

class MarketingOptInController extends Controller
{
    /**
     * Update the authenticated user's marketing preference from account settings.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'marketing_opt_in' => ['required', 'boolean'],
        ]);

        $user = Auth::user();
        $optin = (bool) $validated['marketing_opt_in'];

        $user->marketing_opt_in = $optin;
        $user->opted_in_at  = $optin ? now() : $user->opted_in_at;
        $user->opted_out_at = !$optin ? now() : $user->opted_out_at;
        $user->save();

        $message = $optin
            ? 'You have opted in to marketing emails.'
            : 'You have been unsubscribed from marketing emails.';

        return back()->with('success', $message);
    }

    /**
     * Show the unsubscribe confirmation page.
     * Uses a signed URL so no login is required — safe to include in email links.
     */
    public function unsubscribeShow(Request $request, User $user)
    {
        if (!$request->hasValidSignature()) {
            abort(403, 'This unsubscribe link is invalid or has expired.');
        }

        return inertia('account/Unsubscribe', [
            'user'       => $user->only('name', 'email'),
            'alreadyOut' => !$user->marketing_opt_in,
        ]);
    }

    /**
     * Process the unsubscribe — hit by the confirm button on the unsubscribe page.
     * Also uses a signed URL.
     */
    public function unsubscribeConfirm(Request $request, User $user)
    {
        if (!$request->hasValidSignature()) {
            abort(403, 'This unsubscribe link is invalid or has expired.');
        }

        if ($user->marketing_opt_in) {
            $user->marketing_opt_in = false;
            $user->opted_out_at     = now();
            $user->save();
        }

        return inertia('account/Unsubscribe', [
            'user'      => $user->only('name', 'email'),
            'confirmed' => true,
        ]);
    }

    /**
     * Generate a signed unsubscribe URL for a given user.
     * Called from BroadcastMail when building the email.
     */
    public static function signedUrl(User $user): string
    {
        return URL::signedRoute('unsubscribe.show', ['user' => $user->id]);
    }
}
