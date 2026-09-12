<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class GoogleController extends Controller
{
    /**
     * Redirect the customer to Google for authentication.
     */
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle Google's OAuth callback.
     */
    public function callback(): RedirectResponse
    {
    try {
    $googleUser = Socialite::driver('google')->user();


        $email = $googleUser->getEmail();

        if (!$email) {
            return redirect()
                ->route('login')
                ->with('error', 'Google did not provide an email address. Please try again.');
        }

        $user = User::where('google_id', $googleUser->getId())
            ->orWhere('email', $email)
            ->first();

        if ($user) {
            if (!$user->google_id) {
                $user->google_id = $googleUser->getId();
            }

            if (!$user->email_verified_at) {
                $user->email_verified_at = now();
            }

            $user->save();
        } else {
            $user = User::create([
                'name' => $googleUser->getName()
                    ?: $googleUser->getNickname()
                    ?: 'Google User',
                'email' => $email,
                'google_id' => $googleUser->getId(),
                'email_verified_at' => now(),
                'password' => null,
            ]);
        }

        Auth::login($user);

        request()->session()->regenerate();

        if ($user->isAdmin()) {
            request()->session()->forget('admin_expiry_reminder_dismissed');

            return redirect()->route('admin.dashboard');
        }

        return redirect()->route('home');

    } catch (Throwable $e) {
        report($e);

        return redirect()
            ->route('login')
            ->with('error', 'Unable to sign in with Google right now. Please try again.');
    }

    }

}