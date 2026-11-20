<?php

namespace App\Support;

use App\Models\ElectronicInvoice;
use App\Models\MonthlyPayment;
use App\Models\Section;
use App\Models\SectionSubject;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * HU-83 Sprint 1A: alcance de acceso reutilizable para Policies.
 */
class AccessScope
{
    public static function esAdmin(User $user): bool
    {
        return $user->hasRole('Administrador');
    }

    public static function esDirector(User $user): bool
    {
        return $user->hasRole('Director');
    }

    public static function esSecretaria(User $user): bool
    {
        return $user->hasRole('Secretaría');
    }

    public static function esDocente(User $user): bool
    {
        return $user->hasRole('Docente');
    }

    public static function esEstudiante(User $user): bool
    {
        return $user->hasRole('Estudiante');
    }

    public static function esRegente(User $user): bool
    {
        return $user->hasRole('Regente');
    }

    /** Niveles a cargo del Director (vacío = sin restricción, convención del sistema). */
    public static function nivelesDirector(User $user): Collection
    {
        if (! self::esDirector($user)) {
            return collect();
        }

        return $user->nivelesACargo()->pluck('levels.id');
    }

    /** IDs de secciones donde el docente dicta o es tutor/encargado. */
    public static function seccionesDocente(User $user): Collection
    {
        if (! self::esDocente($user) || ! $user->teacher) {
            return collect();
        }

        $porMateria = SectionSubject::where('teacher_id', $user->teacher->id)->pluck('section_id');
        $porTutoria = Section::where('teacher_id', $user->teacher->id)->pluck('id');

        return $porMateria->merge($porTutoria)->unique()->values();
    }

    /** IDs de secciones visibles para el Director según sus niveles. */
    public static function seccionesDirector(User $user): Collection
    {
        if (! self::esDirector($user)) {
            return collect();
        }

        $niveles = self::nivelesDirector($user);

        if ($niveles->isEmpty()) {
            return Section::pluck('id');
        }

        return Section::whereHas('grade', fn ($q) => $q->whereIn('level_id', $niveles))
            ->pluck('id');
    }

    public static function puedeVerSeccion(User $user, Section $section): bool
    {
        if (self::esAdmin($user) || self::esSecretaria($user) || self::esRegente($user)) {
            return true;
        }

        if (self::esDirector($user)) {
            return self::seccionesDirector($user)->contains($section->id);
        }

        if (self::esDocente($user)) {
            return self::seccionesDocente($user)->contains($section->id);
        }

        if (self::esEstudiante($user) && $user->student) {
            return (int) $user->student->activeSection()?->id === (int) $section->id;
        }

        return false;
    }

    /** Aplica el alcance del usuario a una consulta de estudiantes. */
    public static function aplicarEstudiantes(Builder $query, User $user): Builder
    {
        if (self::esAdmin($user) || self::esSecretaria($user) || self::esRegente($user)) {
            return $query;
        }

        if (self::esDirector($user)) {
            return self::aplicarEstudiantesPorSecciones($query, self::seccionesDirector($user));
        }

        if (self::esDocente($user)) {
            return self::aplicarEstudiantesPorSecciones($query, self::seccionesDocente($user));
        }

        if (self::esEstudiante($user) && $user->student) {
            return $query->whereKey($user->student->id);
        }

        return $query->whereRaw('1 = 0');
    }

    private static function aplicarEstudiantesPorSecciones(Builder $query, Collection $sectionIds): Builder
    {
        if ($sectionIds->isEmpty()) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereHas('registrations', function (Builder $q) use ($sectionIds) {
            $q->where('registrations.estado', 'ACTIVA')
                ->whereIn('registrations.section_id', $sectionIds);
        });
    }

    public static function puedeVerEstudiante(User $user, Student $student): bool
    {
        if (self::esAdmin($user) || self::esSecretaria($user) || self::esRegente($user)) {
            return true;
        }

        if (self::esEstudiante($user) && $user->student) {
            return (int) $user->student->id === (int) $student->id;
        }

        $sectionIds = self::esDirector($user)
            ? self::seccionesDirector($user)
            : (self::esDocente($user) ? self::seccionesDocente($user) : collect());

        if ($sectionIds->isEmpty()) {
            return false;
        }

        return $student->registrations()
            ->where('registrations.estado', 'ACTIVA')
            ->whereIn('registrations.section_id', $sectionIds)
            ->exists();
    }

    /** Admin/Secretaría y Director dentro de su nivel pueden registrar/ver pagos. */
    public static function puedeGestionarPagos(User $user, Student $student): bool
    {
        if (self::esAdmin($user) || self::esSecretaria($user)) {
            return true;
        }

        return self::esDirector($user) && self::puedeVerEstudiante($user, $student);
    }

    public static function puedeVerPago(User $user, MonthlyPayment $pago): bool
    {
        if (self::esAdmin($user) || self::esSecretaria($user)) {
            return true;
        }

        $student = $pago->registration?->enrollment?->estudiante;

        if (! $student) {
            return false;
        }

        if (self::esDirector($user)) {
            return self::puedeVerEstudiante($user, $student);
        }

        if (self::esEstudiante($user) && $user->student) {
            return (int) $user->student->id === (int) $student->id;
        }

        return false;
    }

    public static function aplicarPagos(Builder $query, User $user): Builder
    {
        if (self::esAdmin($user) || self::esSecretaria($user)) {
            return $query;
        }

        if (self::esDirector($user)) {
            $sectionIds = self::seccionesDirector($user);

            if ($sectionIds->isEmpty()) {
                return $query->whereRaw('1 = 0');
            }

            return $query->whereHas('registration', fn (Builder $q) => $q->whereIn('section_id', $sectionIds));
        }

        if (self::esEstudiante($user) && $user->student) {
            return $query->whereHas('registration.enrollment', fn (Builder $q) => $q->where('student_id', $user->student->id));
        }

        return $query->whereRaw('1 = 0');
    }

    public static function puedeVerFactura(User $user, ElectronicInvoice $invoice): bool
    {
        if (self::esAdmin($user) || self::esSecretaria($user)) {
            return true;
        }

        $pago = MonthlyPayment::where('electronic_invoice_id', $invoice->id)->first();

        if (! $pago) {
            return false;
        }

        return self::puedeVerPago($user, $pago);
    }

    public static function aplicarFacturas(Builder $query, User $user): Builder
    {
        if (self::esAdmin($user) || self::esSecretaria($user)) {
            return $query;
        }

        if (self::esDirector($user)) {
            $niveles = self::nivelesDirector($user);

            if ($niveles->isEmpty()) {
                return $query;
            }

            return $query->whereHas('monthlyPayments.registration.section.grade', fn (Builder $q) => $q->whereIn('level_id', $niveles));
        }

        if (self::esEstudiante($user) && $user->student) {
            return $query->whereHas('monthlyPayments.registration.enrollment', fn (Builder $q) => $q->where('student_id', $user->student->id));
        }

        return $query->whereRaw('1 = 0');
    }
}
