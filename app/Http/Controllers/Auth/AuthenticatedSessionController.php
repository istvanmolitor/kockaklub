<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function __construct(private readonly CartService $cartService) {}

    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => 'A megadott adatokkal nem található fiók.',
            ]);
        }

        $request->session()->regenerate();

        $user = $request->user();
        $customer = $user->customer ?: Customer::firstOrCreate(
            ['email' => $user->email],
            ['name' => $user->name, 'user_id' => $user->id]
        );

        if ($customer->user_id === null) {
            $customer->update(['user_id' => $user->id]);
        }

        $this->cartService->mergeGuestCartIntoCustomer($request, $customer);

        if ($request->input('redirect') === 'checkout') {
            return redirect()->route('checkout.create');
        }

        return redirect()->intended(route('home'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        $customer = $request->user()?->customer;

        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($customer) {
            $this->cartService->moveCustomerCartToSession($customer);
        }

        return redirect()->route('home');
    }
}
