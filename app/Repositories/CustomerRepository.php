<?php

namespace App\Repositories;

use App\Models\Customer;

class CustomerRepository
{
    public function firstOrCreateByEmail(string $email, string $name): Customer
    {
        return Customer::firstOrCreate(
            ['email' => $email],
            ['name' => $name],
        );
    }
}
