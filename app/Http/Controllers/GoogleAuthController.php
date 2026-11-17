<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class GoogleAuthController extends Controller
{
    public function redirect(Request $request): RedirectResponse
    {
        if (! config('services.google.client_id') || ! config('services.google.client_secret')) {
            return redirect()->route('login')->with('error', 'Login Google belum dikonfigurasi. Hubungi pengelola klinik.');
        }
        $state = Str::random(40);
        $verifier = Str::random(96);
        $request->session()->put(['oauth_state' => $state, 'oauth_verifier' => $verifier]);

        return redirect()->away('https://accounts.google.com/o/oauth2/v2/auth?'.http_build_query([
            'client_id' => config('services.google.client_id'), 'redirect_uri' => route('google.callback'),
            'response_type' => 'code', 'scope' => 'openid email profile', 'state' => $state,
            'code_challenge' => rtrim(strtr(base64_encode(hash('sha256', $verifier, true)), '+/', '-_'), '='),
            'code_challenge_method' => 'S256', 'prompt' => 'select_account',
        ]));
    }

    public function callback(Request $request): RedirectResponse
    {
        $state = $request->session()->pull('oauth_state');
        $verifier = $request->session()->pull('oauth_verifier');
        if (! is_string($state) || ! is_string($request->input('state')) || ! hash_equals($state, $request->input('state')) || ! $request->filled('code') || ! $verifier) {
            return redirect()->route('login')->with('error', 'Login Google dibatalkan atau sesi kedaluwarsa. Silakan coba lagi.');
        }
        try {
            $token = Http::asForm()->connectTimeout(5)->timeout(15)->post('https://oauth2.googleapis.com/token', [
                'code' => $request->string('code')->toString(), 'client_id' => config('services.google.client_id'),
                'client_secret' => config('services.google.client_secret'), 'redirect_uri' => route('google.callback'),
                'grant_type' => 'authorization_code', 'code_verifier' => $verifier,
            ])->throw()->json('access_token');
            if (! is_string($token) || $token === '') {
                return redirect()->route('login')->with('error', 'Google tidak memberikan akses. Silakan coba lagi.');
            }
            $identity = Http::withToken($token)->connectTimeout(5)->timeout(15)->get('https://openidconnect.googleapis.com/v1/userinfo')->throw()->json();
        } catch (ConnectionException|RequestException $exception) {
            report($exception);

            return redirect()->route('login')->with('error', 'Layanan Google belum dapat dihubungi. Silakan coba lagi.');
        }
        if (! ($identity['email_verified'] ?? false) || ! filter_var($identity['email'] ?? '', FILTER_VALIDATE_EMAIL) || empty($identity['sub'])) {
            throw ValidationException::withMessages(['email' => 'Google tidak memberikan email terverifikasi.']);
        }
        $user = User::where('google_id', $identity['sub'])->first();
        if (! $user && User::where('email', $identity['email'])->exists()) {
            return redirect()->route('login')->with('error', 'Email ini sudah terdaftar dengan metode masuk lain. Hubungi admin.');
        }
        $user ??= User::firstOrCreate(['google_id' => $identity['sub']], [
            'name' => $identity['name'] ?? 'Pasien', 'email' => $identity['email'],
            'google_id' => $identity['sub'], 'password' => Str::random(64), 'role' => 'patient',
        ]);
        abort_unless($user->role === 'patient', 403);
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }
}
