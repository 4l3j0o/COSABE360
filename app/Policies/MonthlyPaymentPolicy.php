<?php

namespace App\Policies;

use App\Models\MonthlyPayment;
use App\Models\User;
use App\Support\AccessScope;

class MonthlyPaymentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->puede('pagos.ver');
    }

    public function view(User $user, MonthlyPayment $payment): bool
    {
        return AccessScope::puedeVerPago($user, $payment);
    }
}
