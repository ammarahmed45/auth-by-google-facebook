<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;

class SocialAuthController extends Controller
{
    public function redirectToProvider(Request $request, $provider)
    {
        //  تحقق من reCAPTCHA
        $request->validate([
            'g-recaptcha-response' => 'required',
        ]);

        $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => config('services.recaptcha.secret_key'),
            'response' => $request->input('g-recaptcha-response'),
        ]);

        $googleResponse = $response->json();

        if (empty($googleResponse['success']) || !$googleResponse['success']) {
            return back()->with('error', 'Please verify that you are not a robot.');
        }

        //  لو التحقق نجح، كمل تسجيل الدخول بالسوشيال
        return Socialite::driver($provider)->redirect();
    }

    public function handleProviderCallback($provider)
    {
        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (\Exception $e) {
            return redirect('/login')->with('error', 'Failed to login by ' . $provider . ': ' . $e->getMessage());
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
