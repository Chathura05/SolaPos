<?php

namespace App\Policies;

use App\Models\Sale;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SalePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('Cashier');
    }

    public function view(User $user, Sale $sale): bool
    {
        if ($user->hasRole('Cashier')) {
            return $sale->showroom_id === $user->showroom_id;
        }
        return false;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('Cashier');
    }

    public function update(User $user, Sale $sale): bool
    {
        return false;
    }

    public function delete(User $user, Sale $sale): bool
    {
        return false;
    }

    public function restore(User $user, Sale $sale): bool
    {
        return false;
    }

    public function forceDelete(User $user, Sale $sale): bool
    {
        return false;
    }
}
