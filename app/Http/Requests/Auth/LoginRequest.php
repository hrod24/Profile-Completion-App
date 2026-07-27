<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use App\Models\employee_details;
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
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'employee_id' => [
                'required',
                'string',
                'max:20',
            ],
            'password' => [
                'required',
                'string',
            ],
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

        $employeeId = trim(
            (string) $this->input('employee_id')
        );

        $authenticated = Auth::attempt(
            [
                'employee_id' => $employeeId,
                'password' => $this->input('password'),
            ],
            $this->boolean('remember')
        );

        if (!$authenticated) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'employee_id' =>
                'Employee ID atau password tidak valid.',
            ]);
        }

        $user = Auth::user();

        /*
     * Admin dan OD tidak perlu mempunyai record
     * pada employee_details.
     */
        if ($user?->role === 'employee') {
            $isActiveEmployee = employee_details::query()
                ->where(
                    'employee_id',
                    trim((string) $user->employee_id)
                )
                ->where('active', 1)
                ->exists();

            if (!$isActiveEmployee) {
                Auth::guard('web')->logout();

                RateLimiter::hit($this->throttleKey());

                throw ValidationException::withMessages([
                    'employee_id' =>
                    'Your employee account is no longer active. / Akun employee Anda sudah tidak aktif.',
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
        if (
            ! RateLimiter::tooManyAttempts(
                $this->throttleKey(),
                5
            )
        ) {
            return;
        }

        event(
            new Lockout($this)
        );

        $seconds = RateLimiter::availableIn(
            $this->throttleKey()
        );

        throw ValidationException::withMessages([
            'employee_id' =>
            trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil(
                    $seconds / 60
                ),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(
            Str::lower(
                trim((string) $this->input('employee_id'))
            ) . '|' . $this->ip()
        );
    }
}
