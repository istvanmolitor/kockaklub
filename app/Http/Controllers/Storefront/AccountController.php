<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function show(Request $request): View
    {
        $customer = $request->user()->customer;

        return view('storefront.account.show', [
            'user' => $request->user(),
            'customer' => $customer,
        ]);
    }

    public function orders(Request $request): View
    {
        $customer = $request->user()->customer;

        $orders = $customer
            ? $customer->orders()->with('orderStatus')->latest()->get()
            : collect();

        return view('storefront.account.orders', [
            'orders' => $orders,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'shipping_name' => ['nullable', 'string', 'max:255'],
            'shipping_country' => ['nullable', 'string', 'max:255'],
            'shipping_city' => ['nullable', 'string', 'max:255'],
            'shipping_zip' => ['nullable', 'string', 'max:20'],
            'shipping_address' => ['nullable', 'string', 'max:2000'],
            'billing_name' => ['nullable', 'string', 'max:255'],
            'billing_country' => ['nullable', 'string', 'max:255'],
            'billing_city' => ['nullable', 'string', 'max:255'],
            'billing_zip' => ['nullable', 'string', 'max:20'],
            'billing_address' => ['nullable', 'string', 'max:2000'],
            'billing_tax_number' => ['nullable', 'string', 'max:20'],
        ]);

        $user = $request->user();

        $customer = $user->customer ?: Customer::create([
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ]);

        $customer->update($validated);

        return redirect()->route('account.show')->with('status', 'Az adataid frissítve lettek.');
    }
}
