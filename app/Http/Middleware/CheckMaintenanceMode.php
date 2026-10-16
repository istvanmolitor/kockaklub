<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckMaintenanceMode
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Setting::get('maintenance_mode') !== '1') {
            return $next($request);
        }

        return response()
            ->view('errors.503', ['message' => Setting::get('maintenance_message')], 503)
            ->header('Retry-After', 3600);
    }
}
