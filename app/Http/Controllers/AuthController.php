<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login()
    {
        if (Auth::check()) {
            $user = Auth::user();

            return redirect($user->must_change_password ? '/ganti-password' : ($user->isAdmin() ? '/admin' : '/dashboard'));
        }

        return view('auth.login');
    }

    public function doLogin(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', strtolower(trim($credentials['email'])))->first();

        if (! $user || ! $user->aktif || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => 'Email atau password salah.',
            ]);
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        if ($user->must_change_password) {
            return redirect('/ganti-password')->with('wajib_ganti', true);
        }

        return redirect()->intended($user->isAdmin() ? '/admin' : '/dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    public function changePassword(Request $request)
    {
        $user = $request->user();

        return view('auth.change-password', [
            'user' => $user,
            'wajib' => $user->must_change_password,
        ]);
    }

    public function doChangePassword(Request $request)
    {
        $request->validate([
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $user = $request->user();
        $user->password = $request->password;
        $user->must_change_password = false;
        $user->save();

        return redirect($user->isAdmin() ? '/admin' : '/dashboard')
            ->with('ok', 'Password berhasil diganti.');
    }

    public function resetRequest()
    {
        return view('auth.reset');
    }

    public function resetDemo()
    {
        abort_unless(app()->environment('local'), 404);

        Auth::logout();
        \Illuminate\Support\Facades\Artisan::call('migrate:fresh', ['--seed' => true, '--force' => true]);

        return redirect('/login')->with('ok', 'Data demo direset. Login: guru@sma.id / admin@sma.id, password: password.');
    }
}
