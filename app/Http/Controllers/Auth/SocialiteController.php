<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use App\Http\Controllers\Controller;
use Exception;

use App\Models\User;

class SocialiteController extends Controller
{
    /**
     * Redirect the user to the provider's authentication page.
     *
     * @param string $provider
     * @return \Illuminate\Http\RedirectResponse
     */
    public function redirectToProvider(string $provider)
    {
        return Socialite::driver($provider)->stateless()->redirect();
    }

    /**
     * Obtain the user information from the provider and log the user in.
     *
     * @param string $provider
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handleProviderCallback(string $provider)
    {
        try {
            // Get the user information from the provider
            $socialiteUser = Socialite::driver($provider)->stateless()->user();

        } catch (Exception $e) {
            // Redirect back to login with an error message
            return redirect('/login')->withErrors(['socialite' => 'Could not authenticate with ' . ucfirst($provider) . '. Please try again.']);
        }

        // Check if a user with this provider ID already exists
        $user = User::where('provider_id', $socialiteUser->getId())
                    ->where('provider', $provider)
                    ->first();

        // If the user does not exist check if an account with that email exists
        if (!$user) {
            // Check for existing user by email
            $user = User::where('email', $socialiteUser->getEmail())->first();

            if ($user) {
                // If user exists by email link the social account
                $user->provider = $provider;
                $user->provider_id = $socialiteUser->getId();
                $user->save();
            } else {
                // Otherwise create a new user account
                $user = User::create([
                    'name' => $socialiteUser->getName(),
                    // Use a fallback name if the provider doesn't give one
                    'email' => $socialiteUser->getEmail() ?? $socialiteUser->getId() . '@' . $provider . '.local',
                    'provider' => $provider,
                    'provider_id' => $socialiteUser->getId(),
                    // Socialite users don't need a real password so Hash a random string
                    'password' => Hash::make(rand(100000, 999999)),
                ]);
            }
        }

        // Log the user in
        Auth::login($user, remember: true);

        // Redirect to the home page
        return redirect()->intended('/');
    }
}
