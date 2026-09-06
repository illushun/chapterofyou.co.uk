<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;

class MarketingOptInController extends Controller
{
    public function update(Request $request)
    {
        $validated = $request->validate([
            'marketing_opt_in' => ['required', 'boolean'],
        ]);

        $user = Auth::user();
        $optin = (bool) $validated['marketing_opt_in'];

        $user->marketing_opt_in = $optin;
        $user->opted_in_at = $optin ? now() : $user->opted_in_at;
        $user->opted_out_at = ! $optin ? now() : $user->opted_out_at;
        $user->save();

        $message = $optin
            ? 'You have opted in to marketing emails.'
            : 'You have been unsubscribed from marketing emails.';

        return back()->with('success', $message);
    }

    public function unsubscribeShow(Request $request, User $user)
    {
        if (! $request->hasValidSignature()) {
            abort(403, 'This unsubscribe link is invalid or has expired.');
        }

        return inertia('account/Unsubscribe', [
            'user' => $user->only('name', 'email'),
            'alreadyOut' => ! $user->marketing_opt_in,
        ]);
    }

    public function unsubscribeConfirm(Request $request, User $user)
    {
        if (! $request->hasValidSignature()) {
            abort(403, 'This unsubscribe link is invalid or has expired.');
        }

        if ($user->marketing_opt_in) {
            $user->marketing_opt_in = false;
            $user->opted_out_at = now();
            $user->save();
        }

        return inertia('account/Unsubscribe', [
            'user' => $user->only('name', 'email'),
            'confirmed' => true,
        ]);
    }

    public static function signedUrl(User $user): string
    {
        return URL::signedRoute('unsubscribe.show', ['user' => $user->id]);
    }
}
