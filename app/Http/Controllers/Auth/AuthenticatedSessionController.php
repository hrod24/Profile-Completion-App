<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
{
    $request->authenticate();

    $request->session()->regenerate();

    /*
     * Hapus tujuan lama yang tersimpan sebelum login.
     * Misalnya /form ketika sekarang login sebagai admin.
     */
    $request->session()->forget('url.intended');

    $user = $request->user();

    if (in_array($user->role, ['admin', 'od'], true)) {
        return redirect()->route('dashboard');
    }

    if ($user->role === 'employee') {
        return redirect()->route('employee.form');
    }

    /*
     * Jangan pertahankan session untuk akun
     * yang tidak memiliki role valid.
     */
    Auth::guard('web')->logout();

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()
        ->route('login')
        ->withErrors([
            'employee_id' =>
                'Akun belum memiliki role yang valid.',
        ]);
}

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
