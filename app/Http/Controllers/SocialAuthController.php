<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;

class SocialAuthController extends Controller
{
    public function redirectToProvider($provider)
    {
        //redirect google or facebook
        return Socialite::driver($provider)->redirect();
    }
    public function handleProviderCallback($provider)
    {
        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (\Exception $e) {
            return redirect('/login')->with('error', 'failed to login by' . $provider . ': ' . $e->getMessage());
        }
        $email = $socialUser->getEmail();
        $name = $socialUser->getName() ?? $socialUser->getNickname();
        $providerId = $socialUser->getId();

        $user = User::where(function ($q) use ($provider, $providerId, $email) {
            $q->where('provider', $provider)->where('provider_id', $providerId);
        })->orWhere('email', $email)->first();
        if (!$user) {
            $user = User::create([
                'name' => $name ?? 'user_' . Str::random(6),
                'email' => $email ?? Str::random(8) . '@nomail.com',
                'password' => bcrypt(Str::random(16)),
                'provider' => $provider,
                'provider_id' => $providerId,
            ]);
        } else {
            // لو لقي مستخدم بالـ email لكن بدون تفاصيل provider نحدّثه
            if (!$user->provider) {
                $user->update([
                    'provider' => $provider,
                    'provider_id' => $providerId,
                ]);
            }
        }

        Auth::login($user, true);
        return redirect('/welcome');
    }
}
