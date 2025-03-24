<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        // Check if user exists and is a super admin
        $user = \App\Models\User::where('email', $this->input('email'))->first();

        if ($user && $user->is_super_admin) {
            // Check if super admin account is locked
            if ($user->locked_at) {
                throw ValidationException::withMessages([
                    'email' => 'This super admin account is locked. Please use the unlock:super-admin command to unlock it.',
                ]);
            }

            // Attempt authentication
            if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
                // Increment login attempts for super admin
                $user->login_attempts += 1;

                // Lock account and wipe password after 5 failed attempts
                if ($user->login_attempts >= 5) {
                    $user->locked_at = now();
                    $user->password = bcrypt(Str::random(64)); // Wipe password with random string
                    $user->save();

                    throw ValidationException::withMessages([
                        'email' => 'This super admin account has been locked due to too many failed login attempts. Please use the unlock:super-admin command to unlock it.',
                    ]);
                }

                $user->save();

                RateLimiter::hit($this->throttleKey());

                throw ValidationException::withMessages([
                    'email' => trans('auth.failed') . ' Remaining attempts: ' . (5 - $user->login_attempts),
                ]);
            }

            // Reset login attempts on successful login
            $user->login_attempts = 0;
            $user->save();
        } else {
            // Regular authentication for non-super admin users
            if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
                RateLimiter::hit($this->throttleKey());

                throw ValidationException::withMessages([
                    'email' => trans('auth.failed'),
                ]);
            }
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')) . '|' . $this->ip());
    }
}
