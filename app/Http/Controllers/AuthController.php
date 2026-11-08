<?php

namespace App\Http\Controllers;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Show the login form for guests.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Alias for create to support showLoginForm convention.
     */
    public function showLoginForm(): View
    {
        return $this->create();
    }

    /**
     * Handle an authentication attempt with rate limiting and session regeneration.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $credentials['email'] = Str::lower(trim($credentials['email']));

        $throttleKey = $this->throttleKey($request);

        // Maximum 5 login attempts per minute
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            event(new Lockout($request));

            $seconds = RateLimiter::availableIn($throttleKey);

            throw ValidationException::withMessages([
                'email' => trans('auth.throttle', [
                    'seconds' => $seconds,
                    'minutes' => ceil($seconds / 60),
                ]),
            ]);
        }

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::hit($throttleKey, 60);

            // Generic error message without revealing whether the email exists
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        RateLimiter::clear($throttleKey);

        $request->session()->regenerate();

        return redirect()->intended(route('catalog.index'));
    }

    /**
     * Alias for store to support login convention.
     *
     * @throws ValidationException
     */
    public function login(Request $request): RedirectResponse
    {
        return $this->store($request);
    }

    /**
     * Log the user out of the application, invalidate session, and regenerate CSRF token.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /**
     * Alias for destroy to support logout convention.
     */
    public function logout(Request $request): RedirectResponse
    {
        return $this->destroy($request);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    protected function throttleKey(Request $request): string
    {
        return Str::transliterate(Str::lower(trim((string) $request->input('email'))).'|'.$request->ip());
    }
}
