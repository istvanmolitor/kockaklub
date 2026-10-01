<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMessageRequest;
use App\Repositories\CustomerRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function __construct(private readonly CustomerRepository $customers) {}

    public function create(): View
    {
        return view('storefront.contact.create');
    }

    public function store(StoreMessageRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        if ($request->user()) {
            $customer = $request->user()->customer;
        } else {
            $customer = $this->customers->firstOrCreateByEmail($validated['email'], $validated['name']);
        }

        $customer->messages()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'message' => $validated['message'],
        ]);

        return redirect()->route('contact.create')->with('status', 'Köszönjük az üzeneted! Hamarosan válaszolunk.');
    }
}
