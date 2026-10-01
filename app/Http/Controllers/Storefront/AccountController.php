<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function show(Request $request): View
    {
        $customer = $request->user()->customer;

        $orders = $customer
            ? $customer->orders()->with('orderStatus')->latest()->get()
            : collect();

        return view('storefront.account.show', [
            'user' => $request->user(),
            'customer' => $customer,
            'orders' => $orders,
        ]);
    }
}
