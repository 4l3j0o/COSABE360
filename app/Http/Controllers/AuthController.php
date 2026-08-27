<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public const MAX_INTENTOS = 5;

    public const MINUTOS_BLOQUEO = 15;

    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credenciales = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $credenciales['email'])->first();

        if ($user && $user->estaBloqueado()) {
            $minutos = (int) ceil(now()->diffInMinutes($user->bloqueado_hasta));

            return back()
                ->withErrors(['email' => "Cuenta bloqueada temporalmente. Intente en {$minutos} minuto(s)."])
                ->onlyInput('email');
        }

        if (! Auth::attempt(array_merge($credenciales, ['remember' => $request->filled('remember')]))) {
            if ($user) {
                $this->registrarIntentoFallido($user);
            }

            return back()
                ->withErrors(['email' => 'Credenciales incorrectas.'])
                ->onlyInput('email');
        }

        /** @var User $user */
        $user = Auth::user();

        if ($user->estado !== 'ACTIVO') {
            Auth::logout();

            return back()
                ->withErrors(['email' => 'Su cuenta se encuentra inactiva. Contacte al administrador.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        $user->forceFill([
            'ultimo_acceso' => now(),
            'intentos_fallidos' => 0,
            'bloqueado_hasta' => null,
        ])->save();

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function registrarIntentoFallido(User $user): void
    {
        $intentos = $user->intentos_fallidos + 1;

        if ($intentos >= self::MAX_INTENTOS) {
            $user->forceFill([
                'bloqueado_hasta' => now()->addMinutes(self::MINUTOS_BLOQUEO),
                'intentos_fallidos' => 0,
            ])->save();

            return;
        }

        $user->forceFill(['intentos_fallidos' => $intentos])->save();
    }
}
