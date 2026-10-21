<?php

namespace App\Repositories;

use App\Models\PostalCode;

class PostalCodeRepository
{
    public function findCity(?int $countryId, ?string $code): ?string
    {
        if (! $countryId || ! $code) {
            return null;
        }

        return PostalCode::query()
            ->where('country_id', $countryId)
            ->where('code', $code)
            ->value('city');
    }
}
