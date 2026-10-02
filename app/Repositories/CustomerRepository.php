<?php

namespace App\Repositories;

use App\Models\Customer;
use App\Models\User;

class CustomerRepository
{
    public function firstOrCreateByEmail(string $email, string $name): Customer
    {
        return Customer::firstOrCreate(
            ['email' => $email],
            ['name' => $name],
        );
    }

    public function firstOrCreateForUser(User $user): Customer
    {
        return $user->customer ?: Customer::create([
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ]);
    }
}
