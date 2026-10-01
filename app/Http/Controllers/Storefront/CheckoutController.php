<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Mail\OrderConfirmationMail;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(private readonly CartService $cartService) {}

    public function create(Request $request): View|RedirectResponse
    {
        $cart = $this->cartService->currentCart($request);
        $cart->load('items.product');

        if ($cart->items->isEmpty()) {
            return redirect()->route('cart.show')->with('status', 'A kosarad üres.');
        }

        $customer = $request->user()?->customer;

        return view('storefront.checkout.create', [
            'cart' => $cart,
            'customer' => $customer,
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

        if ($request->user()) {
            $customer = $request->user()->customer;
        } else {
            $customer = Customer::firstOrCreate(
                ['email' => $validated['email']],
                ['name' => $validated['name']]
            );
        }

        try {
            $order = DB::transaction(function () use ($cart, $customer, $validated) {
                $subtotal = 0;
                $lineData = [];

                foreach ($cart->items as $item) {
                    $product = Product::whereKey($item->product_id)->lockForUpdate()->first();

                    if (! $product || $product->stock < $item->quantity) {
                        throw ValidationException::withMessages([
                            'quantity' => sprintf('"%s" termékből nincs elég készleten.', $item->product->name),
                        ]);
                    }

                    $lineTotal = $product->price * $item->quantity;
                    $subtotal += $lineTotal;

                    $lineData[] = [
                        'product' => $product,
                        'quantity' => $item->quantity,
                        'unit_price' => $product->price,
                        'line_total' => $lineTotal,
                    ];
                }

                $order = Order::create([
                    'customer_id' => $customer->id,
                    'order_status_id' => OrderStatus::default()->id,
                    'order_number' => $this->generateOrderNumber(),
                    'shipping_name' => $validated['shipping_name'],
                    'shipping_phone' => $validated['shipping_phone'],
                    'shipping_address' => $validated['shipping_address'],
                    'payment_method' => $validated['payment_method'],
                    'subtotal' => $subtotal,
                    'total' => $subtotal,
                ]);

                foreach ($lineData as $line) {
                    $order->items()->create([
                        'product_id' => $line['product']->id,
                        'product_name' => $line['product']->name,
                        'unit_price' => $line['unit_price'],
                        'quantity' => $line['quantity'],
                        'line_total' => $line['line_total'],
                    ]);

                    $line['product']->decrement('stock', $line['quantity']);
                }

                $cart->items()->delete();
                $cart->delete();

                return $order;
            });
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors())->withInput();
        }

        Mail::to($customer->email)->queue(new OrderConfirmationMail($order));

        return redirect()->route('checkout.confirmation', $order);
    }

    public function confirmation(Order $order): View
    {
        $order->load('items', 'orderStatus');

        return view('storefront.checkout.confirmation', ['order' => $order]);
    }

    private function generateOrderNumber(): string
    {
        return 'ORD-'.now()->format('Ymd').'-'.strtoupper(Str::random(6));
    }
}
