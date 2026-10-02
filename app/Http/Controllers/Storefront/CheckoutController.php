<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Mail\OrderConfirmationMail;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ShippingMethod;
use App\Models\Site;
use App\Repositories\CustomerRepository;
use App\Repositories\ShippingMethodRepository;
use App\Services\CartService;
use App\Services\ProductInterestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly CartService $cartService,
        private readonly ProductInterestService $productInterestService,
        private readonly CustomerRepository $customers,
        private readonly ShippingMethodRepository $shippingMethods,
    ) {}

    public function gate(Request $request): View|RedirectResponse
    {
        $cart = $this->cartService->currentCart($request);
        $cart->load('items');

        if ($cart->items->isEmpty()) {
            return redirect()->route('cart.show')->with('status', 'A kosarad üres.');
        }

        if ($request->user()) {
            return redirect()->route('checkout.create');
        }

        return view('storefront.checkout.gate');
    }

    public function create(Request $request): View|RedirectResponse
    {
        $cart = $this->cartService->currentCart($request);
        $cart->load('items.product');

        if ($cart->items->isEmpty()) {
            return redirect()->route('cart.show')->with('status', 'A kosarad üres.');
        }

        $customer = $request->user()?->customer;

        $shippingMethods = $this->shippingMethods->activeWithAvailablePaymentMethods();

        if ($shippingMethods->isEmpty()) {
            return redirect()->route('cart.show')->with('status', 'Jelenleg nem érhető el szállítási mód.');
        }

        if (! Site::main()) {
            return redirect()->route('cart.show')->with('status', 'Jelenleg nem fogadható rendelés.');
        }

        return view('storefront.checkout.create', [
            'cart' => $cart,
            'customer' => $customer,
            'shippingMethods' => $shippingMethods,
        ]);
    }

    public function store(StoreOrderRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $cart = $this->cartService->currentCart($request);
        $cart->load('items.product');

        if ($cart->items->isEmpty()) {
            return redirect()->route('cart.show')->with('status', 'A kosarad üres.');
        }

        $mainSite = Site::main();

        if (! $mainSite) {
            return redirect()->route('cart.show')->with('status', 'Jelenleg nem fogadható rendelés.');
        }

        if ($request->user()) {
            $customer = $this->customers->firstOrCreateForUser($request->user());
        } else {
            $customer = $this->customers->firstOrCreateByEmail($validated['email'], $validated['name']);
        }

        $shippingMethod = ShippingMethod::findOrFail($validated['shipping_method_id']);
        $paymentMethod = PaymentMethod::findOrFail($validated['payment_method_id']);

        $billingSameAsShipping = (bool) ($validated['billing_same_as_shipping'] ?? false);

        $billing = $billingSameAsShipping
            ? [
                'billing_name' => $validated['shipping_name'],
                'billing_country' => $validated['shipping_country'],
                'billing_city' => $validated['shipping_city'],
                'billing_zip' => $validated['shipping_zip'],
                'billing_address' => $validated['shipping_address'],
            ]
            : [
                'billing_name' => $validated['billing_name'],
                'billing_country' => $validated['billing_country'],
                'billing_city' => $validated['billing_city'],
                'billing_zip' => $validated['billing_zip'],
                'billing_address' => $validated['billing_address'],
            ];

        try {
            $order = DB::transaction(function () use ($cart, $customer, $validated, $billing, $shippingMethod, $paymentMethod, $mainSite) {
                $subtotal = 0;
                $lineData = [];

                foreach ($cart->items as $item) {
                    $product = Product::whereKey($item->product_id)->first();

                    if (! $product) {
                        throw ValidationException::withMessages([
                            'quantity' => sprintf('"%s" termék már nem elérhető.', $item->product->name),
                        ]);
                    }

                    $lineTotal = $product->price * $item->quantity;
                    $subtotal += $lineTotal;

                    $lineData[] = [
                        'product' => $product,
                        'quantity' => $item->quantity,
                        'unit_price' => $product->price,
                        'vat_rate' => $product->vat_rate,
                        'line_total' => $lineTotal,
                    ];
                }

                $order = Order::create([
                    'site_id' => $mainSite->id,
                    'customer_id' => $customer->id,
                    'order_status_id' => OrderStatus::default()->id,
                    'order_number' => Order::generateOrderNumber(),
                    'shipping_name' => $validated['shipping_name'],
                    'shipping_phone' => $validated['shipping_phone'],
                    'shipping_country' => $validated['shipping_country'],
                    'shipping_city' => $validated['shipping_city'],
                    'shipping_zip' => $validated['shipping_zip'],
                    'shipping_address' => $validated['shipping_address'],
                    ...$billing,
                    'billing_tax_number' => $validated['billing_tax_number'] ?? null,
                    'shipping_method_id' => $shippingMethod->id,
                    'payment_method_id' => $paymentMethod->id,
                    'subtotal' => $subtotal,
                    'shipping_cost' => $shippingMethod->cost,
                    'payment_cost' => $paymentMethod->cost,
                    'total' => $subtotal + $shippingMethod->cost + $paymentMethod->cost,
                ]);

                foreach ($lineData as $line) {
                    $order->items()->create([
                        'product_id' => $line['product']->id,
                        'product_name' => $line['product']->name,
                        'unit_price' => $line['unit_price'],
                        'vat_rate' => $line['vat_rate'],
                        'quantity' => $line['quantity'],
                        'line_total' => $line['line_total'],
                    ]);

                    // Stock is not automatically decremented on checkout (out of scope —
                    // see the Leltár/StockMovement resource for manual stock management).
                }

                $cart->items()->delete();
                $cart->delete();

                return $order;
            });
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors())->withInput();
        }

        if ($request->user()) {
            foreach ($order->load('items.product')->items as $item) {
                $this->productInterestService->recordOrder($request->user(), $item->product);
            }
        }

        if (! $customer->hasShippingDetails()) {
            $customer->fill([
                'shipping_name' => $validated['shipping_name'],
                'shipping_country' => $validated['shipping_country'],
                'shipping_city' => $validated['shipping_city'],
                'shipping_zip' => $validated['shipping_zip'],
                'shipping_address' => $validated['shipping_address'],
            ]);
        }

        if (! $customer->hasBillingDetails()) {
            $customer->fill($billing);
        }

        if (blank($customer->billing_tax_number) && filled($validated['billing_tax_number'] ?? null)) {
            $customer->billing_tax_number = $validated['billing_tax_number'];
        }

        if ($customer->isDirty()) {
            $customer->save();
        }

        Mail::to($customer->email)->queue(new OrderConfirmationMail($order));

        return redirect()->route('checkout.confirmation', $order);
    }

    public function confirmation(Order $order): View
    {
        $order->load('items.product.defaultImage', 'orderStatus', 'shippingMethod', 'paymentMethod');

        return view('storefront.checkout.confirmation', ['order' => $order]);
    }
}
