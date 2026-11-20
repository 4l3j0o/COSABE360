<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\SecurityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;

class AuthController extends Controller
{
    public const MAX_INTENTOS = 5;

    public const MINUTOS_BLOQUEO = 15;

    /** Dominio Google Workspace permitido (HU-36). */
    public const GOOGLE_HD = 'cosabe.edu.bo';

    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        // HU-38: el acceso por contraseña queda restringido a la cuenta de emergencia.
        $emergencyEmail = (string) config('app.emergency_admin_email');
        $emergencyEnabled = (bool) config('app.emergency_login_enabled', false);

        if (! $emergencyEnabled || strtolower((string) $request->input('email')) !== strtolower($emergencyEmail)) {
            return redirect()->route('login.google')
                ->withErrors(['email' => 'El acceso por contraseña está deshabilitado. Inicie sesión con Google.']);
        }

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

        if (! Auth::attempt($credenciales, $request->filled('remember'))) {
            if ($user) {
                $this->registrarIntentoFallido($user);
            }

            SecurityLog::event('login.failed', [
                'email' => $credenciales['email'],
                'metodo' => 'password',
            ]);

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

        SecurityLog::event('login.success', ['metodo' => 'password']);

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /**
     * HU-36: redirige a Google mostrando el selector de cuentas.
     * No se restringe el dominio aquí: el usuario puede elegir cualquiera de
     * sus cuentas (Gmail, Workspace, etc.) y el callback valida que sea del
     * dominio permitido y que esté registrada en el sistema.
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')
            ->redirectUrl(config('services.google.redirect'))
            ->with(['prompt' => 'select_account'])
            ->redirect();
    }

    /**
     * HU-36: procesa el callback de Google.
     * - Solo admite el dominio cosabe.edu.bo.
     * - Vincula por google_id o por email existente (conserva rol y estado).
     * - Los emails sin cuenta registrada se rechazan (sin auto-creación).
     */
    public function handleGoogleCallback(Request $request)
    {
        if (! $request->has('code')) {
            Log::warning('HU-36 callback sin code', ['query' => $request->except('code')]);

            $mensaje = $request->filled('error')
                ? 'Google rechazó el acceso. '.$request->input('error_description', '')
                : 'Acceso con Google cancelado o incompleto. Intente nuevamente.';

            return redirect()->route('login')
                ->withErrors(['email' => $mensaje]);
        }

        try {
            $googleUser = Socialite::driver('google')
                ->redirectUrl(config('services.google.redirect'))
                ->user();
        } catch (InvalidStateException $e) {
            Log::error('HU-36 InvalidState (sesión/host)', ['query' => $request->except('code')]);

            return redirect()->route('login')
                ->withErrors(['email' => 'La sesión expiró o cambiaste de host. Abre la página de nuevo y reintenta.']);
        } catch (\Exception $e) {
            Log::error('HU-36 Google callback fallido', [
                'clase' => get_class($e),
                'mensaje' => $e->getMessage(),
                'codigo' => $e->getCode(),
                'query' => $request->except('code'),
                'tiene_code' => $request->has('code'),
                'origen' => $e->getFile().':'.$e->getLine(),
            ]);
            SecurityLog::event('login.google.error', [
                'clase' => get_class($e),
                'codigo' => $e->getCode(),
            ]);

            return redirect()->route('login')
                ->withErrors(['email' => 'No se pudo completar el acceso con Google. Intente nuevamente.']);
        }

        $email = strtolower((string) $googleUser->getEmail());
        $dominio = substr(strrchr($email, '@'), 1);

        if ($dominio !== self::GOOGLE_HD) {
            SecurityLog::event('login.google.rejected_domain', ['email' => $email]);

            return redirect()->route('login')
                ->withErrors(['email' => 'Solo se permiten cuentas del dominio @'.self::GOOGLE_HD.'.']);
        }

        $user = User::where('google_id', $googleUser->getId())->first()
            ?? User::where('email', $email)->first();

        if (! $user) {
            Log::warning('HU-36 login email no registrado', ['email' => $email, 'google_id' => $googleUser->getId()]);
            SecurityLog::event('login.google.unregistered', ['email' => $email]);

            return redirect()->route('login')
                ->withErrors(['email' => 'Su cuenta Google no está registrada en el sistema. Contacte al administrador.']);
        }

        if ($user->estado !== 'ACTIVO') {
            SecurityLog::event('login.google.inactive', ['user_id' => $user->id]);

            return redirect()->route('login')
                ->withErrors(['email' => 'Su cuenta se encuentra inactiva. Contacte al administrador.']);
        }

        Auth::login($user, true);
        $request->session()->regenerate();

        $user->forceFill([
            'google_id' => $googleUser->getId(),
            'avatar' => $googleUser->getAvatar(),
            'ultimo_acceso' => now(),
            'intentos_fallidos' => 0,
            'bloqueado_hasta' => null,
        ])->save();

        SecurityLog::event('login.google.success', ['metodo' => 'google']);

        return redirect()->intended(route('dashboard'));
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
