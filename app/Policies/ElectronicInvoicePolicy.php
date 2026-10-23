<?php

namespace App\Policies;

use App\Models\ElectronicInvoice;
use App\Models\User;
use App\Support\AccessScope;

class ElectronicInvoicePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['Administrador', 'Secretaría', 'Director']);
    }

    public function view(User $user, ElectronicInvoice $invoice): bool
    {
        return AccessScope::puedeVerFactura($user, $invoice);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['Administrador', 'Secretaría', 'Director']);
    }

    public function update(User $user, ElectronicInvoice $invoice): bool
    {
        return $user->hasAnyRole(['Administrador', 'Secretaría'])
            || ($user->hasRole('Director') && AccessScope::puedeVerFactura($user, $invoice));
    }

    public function delete(User $user, ElectronicInvoice $invoice): bool
    {
        return $user->hasAnyRole(['Administrador', 'Secretaría'])
            || ($user->hasRole('Director') && AccessScope::puedeVerFactura($user, $invoice));
    }
}
