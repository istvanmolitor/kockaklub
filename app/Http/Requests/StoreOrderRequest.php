<?php

namespace App\Http\Requests;

use App\Models\ShippingMethod;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'shipping_name' => ['required', 'string', 'max:255'],
            'shipping_phone' => ['required', 'string', 'max:255'],
            'shipping_address' => ['required', 'string', 'max:2000'],
            'shipping_method_id' => [
                'required',
                'integer',
                Rule::exists('shipping_methods', 'id')->where('is_active', true),
            ],
            'payment_method_id' => [
                'required',
                'integer',
                Rule::exists('payment_methods', 'id')->where('is_active', true),
            ],
        ];

        if (! $this->user()) {
            $rules['name'] = ['required', 'string', 'max:255'];
            $rules['email'] = ['required', 'email', 'max:255'];
        }

        return $rules;
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $shippingMethod = ShippingMethod::find($this->input('shipping_method_id'));

            if (! $shippingMethod) {
                return;
            }

            $isValidPaymentMethod = $shippingMethod->paymentMethods()
                ->where('is_active', true)
                ->whereKey($this->input('payment_method_id'))
                ->exists();

            if (! $isValidPaymentMethod) {
                $validator->errors()->add('payment_method_id', 'A kiválasztott fizetési mód nem érhető el ehhez a szállítási módhoz.');
            }
        });
    }
}
