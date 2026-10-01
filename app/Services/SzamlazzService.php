<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Omisai\Szamlazzhu\Buyer;
use Omisai\Szamlazzhu\Currency;
use Omisai\Szamlazzhu\Document\Invoice\Invoice;
use Omisai\Szamlazzhu\Header\InvoiceHeader;
use Omisai\Szamlazzhu\Item\InvoiceItem;
use Omisai\Szamlazzhu\Language;
use Omisai\Szamlazzhu\PaymentMethod as SzamlazzPaymentMethod;
use Omisai\Szamlazzhu\Seller;
use Omisai\Szamlazzhu\SzamlaAgent;
use Omisai\Szamlazzhu\SzamlaAgentException;
use Omisai\Szamlazzhu\TaxPayer;

class SzamlazzService
{
    /**
     * Shipping and payment surcharge lines have no per-line VAT rate stored on the
     * order, so they follow the general Hungarian VAT rate.
     */
    private const SURCHARGE_VAT_RATE = 27;

    /**
     * @throws SzamlaAgentException
     */
    public function issueInvoice(Order $order): Order
    {
        $apiKey = setting('szamlazz_api_key');

        if (blank($apiKey)) {
            throw new SzamlaAgentException('Nincs beállítva a Számlázz.hu Agent kulcs (Beállítások > Számlázz.hu Agent kulcs).');
        }

        $order->loadMissing(['items', 'customer', 'paymentMethod', 'shippingMethod']);

        $invoice = new Invoice(Invoice::INVOICE_TYPE_E_INVOICE);
        $invoice->setHeader($this->buildHeader($order));
        $invoice->setBuyer($this->buildBuyer($order));
        $invoice->setItems($this->buildItems($order));

        if ($seller = $this->buildSeller()) {
            $invoice->setSeller($seller);
        }

        $agent = SzamlaAgent::createWithAPIkey($apiKey, true);
        $response = $agent->generateInvoice($invoice);

        $pdfPath = null;

        if ($response->getPdfFile() !== '') {
            $pdfPath = sprintf('invoices/%s.pdf', $order->order_number);
            Storage::disk('local')->put($pdfPath, $response->getPdfFile());
        }

        $order->update([
            'invoice_number' => $response->getInvoiceNumber(),
            'invoiced_at' => now(),
            'invoice_pdf_path' => $pdfPath,
        ]);

        return $order;
    }

    private function buildHeader(Order $order): InvoiceHeader
    {
        $header = new InvoiceHeader(Invoice::INVOICE_TYPE_E_INVOICE);

        return $header
            ->setIssueDate(now())
            ->setFulfillment($order->created_at)
            ->setPaymentDue($this->paymentDue($order))
            ->setPaymentMethod($this->mapPaymentMethod($order->paymentMethod?->name))
            ->setCurrency(Currency::HUF)
            ->setLanguage(Language::HU)
            ->setOrderNumber($order->order_number);
    }

    private function paymentDue(Order $order): Carbon
    {
        return $this->isCashOnDelivery($order->paymentMethod?->name) ? now() : now()->addDays(8);
    }

    private function isCashOnDelivery(?string $paymentMethodName): bool
    {
        return str_contains(mb_strtolower($paymentMethodName ?? ''), 'utánvét');
    }

    private function mapPaymentMethod(?string $paymentMethodName): SzamlazzPaymentMethod
    {
        $name = mb_strtolower($paymentMethodName ?? '');

        return match (true) {
            str_contains($name, 'utánvét') => SzamlazzPaymentMethod::PAYMENT_METHOD_CASH_ON_DELIVERY,
            str_contains($name, 'kártya') => SzamlazzPaymentMethod::PAYMENT_METHOD_BANKCARD,
            str_contains($name, 'készpénz') => SzamlazzPaymentMethod::PAYMENT_METHOD_CASH,
            str_contains($name, 'paypal') => SzamlazzPaymentMethod::PAYMENT_METHOD_PAYPAL,
            default => SzamlazzPaymentMethod::PAYMENT_METHOD_TRANSFER,
        };
    }

    private function buildBuyer(Order $order): Buyer
    {
        $buyer = (new Buyer)
            ->setName($order->billing_name)
            ->setCountry($order->billing_country)
            ->setZipCode($order->billing_zip)
            ->setCity($order->billing_city)
            ->setAddress($order->billing_address)
            ->setSendEmailState(true);

        if (filled($order->customer?->email)) {
            $buyer->setEmail($order->customer->email);
        }

        if (filled($order->billing_tax_number)) {
            $buyer->setTaxNumber($order->billing_tax_number)
                ->setTaxPayer(TaxPayer::TAXPAYER_HAS_TAXNUMBER);
        } else {
            $buyer->setTaxPayer(TaxPayer::TAXPAYER_NO_TAXNUMBER);
        }

        return $buyer;
    }

    /**
     * @return InvoiceItem[]
     */
    private function buildItems(Order $order): array
    {
        $items = $order->items->map(fn (OrderItem $item) => $this->buildLineItem(
            name: $item->product_name,
            quantity: (float) $item->quantity,
            netUnitPrice: (float) $item->unit_price,
            vatRate: $item->vat_rate,
        ))->all();

        if ($order->shipping_cost > 0) {
            $items[] = $this->buildLineItem(
                name: sprintf('Szállítási díj (%s)', $order->shippingMethod?->name ?? 'szállítás'),
                quantity: 1.0,
                netUnitPrice: (float) $order->shipping_cost,
                vatRate: self::SURCHARGE_VAT_RATE,
            );
        }

        if ($order->payment_cost > 0) {
            $items[] = $this->buildLineItem(
                name: sprintf('Fizetési mód díja (%s)', $order->paymentMethod?->name ?? 'fizetés'),
                quantity: 1.0,
                netUnitPrice: (float) $order->payment_cost,
                vatRate: self::SURCHARGE_VAT_RATE,
            );
        }

        return $items;
    }

    private function buildLineItem(string $name, float $quantity, float $netUnitPrice, int $vatRate): InvoiceItem
    {
        $netPrice = $netUnitPrice * $quantity;
        $vatAmount = round($netPrice * $vatRate / 100, 2);
        $grossAmount = $netPrice + $vatAmount;

        return (new InvoiceItem)
            ->setName($name)
            ->setQuantity($quantity)
            ->setQuantityUnit('db')
            ->setNetUnitPrice($netUnitPrice)
            ->setVat((string) $vatRate)
            ->setNetPrice($netPrice)
            ->setVatAmount($vatAmount)
            ->setGrossAmount($grossAmount);
    }

    private function buildSeller(): ?Seller
    {
        $bankName = setting('szamlazz_bank_name');
        $bankAccount = setting('szamlazz_bank_account_number');

        if (blank($bankName) && blank($bankAccount)) {
            return null;
        }

        $seller = new Seller;

        if (filled($bankName)) {
            $seller->setBank($bankName);
        }

        if (filled($bankAccount)) {
            $seller->setBankAccount($bankAccount);
        }

        return $seller;
    }
}
