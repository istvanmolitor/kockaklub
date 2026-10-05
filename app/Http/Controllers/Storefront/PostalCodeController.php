<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Country;
use Illuminate\Http\JsonResponse;

class PostalCodeController extends Controller
{
    public function index(Country $country): JsonResponse
    {
        return response()->json(
            $country->postalCodes()->pluck('city', 'code')
        );
    }
}
