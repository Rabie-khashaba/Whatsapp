<?php

namespace App\Services;

use App\Interfaces\PaymentGatewayInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class FawaterkPaymentService extends BasePaymentService implements PaymentGatewayInterface
{
    protected string $apiKey;
    protected string $vendorKey;
    protected string $currency;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('fawaterk.base_url', 'https://staging.fawaterk.com'), '/');
        $this->apiKey = (string) config('fawaterk.api_key', '');
        $this->vendorKey = (string) config('fawaterk.vendor_key', '');
        $this->currency = (string) config('fawaterk.currency', 'EGP');

        $this->headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $this->apiKey,
        ];
    }

    public function paymentMethods(): array
    {
        if ($this->apiKey === '') {
            return [];
        }

        try {
            $response = $this->request('GET', '/api/v2/getPaymentmethods');
            $payload = $response->json();

            if ($response->successful() && Arr::get($payload, 'status') === 'success') {
                $methods = Arr::get($payload, 'data', []);
                return is_array($methods) ? $methods : [];
            }
        } catch (\Throwable $e) {
            Log::warning('Fawaterk payment methods request failed: ' . $e->getMessage());
        }

        return [];
    }

    public function sendPayment(Request $request): array
    {
        if ($this->apiKey === '') {
            return [
                'success' => false,
                'message' => 'Fawaterk API key is required.',
            ];
        }

        $data = $request->all();
        $shipping = is_array($data['shipping_data'] ?? null) ? $data['shipping_data'] : [];
        $amount = (float) ($data['amount'] ?? 0);
        $merchantOrderId = (string) ($data['merchant_order_id'] ?? ('PAY-' . now()->format('YmdHis')));

        $payload = [
            'payment_method_id' => (int) ($data['payment_method_id'] ?? 0),
            'cartTotal' => (string) $amount,
            'currency' => (string) ($data['currency'] ?? $this->currency),
            'invoice_number' => $merchantOrderId,
            'customer' => [
                'first_name' => (string) Arr::get($shipping, 'first_name', 'Customer'),
                'last_name' => (string) Arr::get($shipping, 'last_name', 'User'),
                'email' => (string) Arr::get($shipping, 'email', ''),
                'phone' => (string) Arr::get($shipping, 'phone_number', ''),
                'address' => (string) Arr::get($shipping, 'address', ''),
            ],
            'redirectionUrls' => [
                'successUrl' => route('payments.fawaterk.callback', ['result' => 'success']),
                'failUrl' => route('payments.fawaterk.callback', ['result' => 'fail']),
                'pendingUrl' => route('payments.fawaterk.callback', ['result' => 'pending']),
                'webhookUrl' => route('payments.fawaterk.callback'),
            ],
            'cartItems' => [
                [
                    'name' => (string) ($data['item_name'] ?? 'Subscription payment'),
                    'price' => (string) $amount,
                    'quantity' => '1',
                ],
            ],
            'payLoad' => [
                'payment_id' => $this->extractPaymentId($merchantOrderId),
                'merchant_order_id' => $merchantOrderId,
            ],
            'redirectOption' => true,
            'sendEmail' => false,
            'sendSMS' => false,
            'lang' => app()->getLocale() === 'ar' ? 'ar' : 'en',
        ];

        $response = $this->request('POST', '/api/v2/invoiceInitPay', $payload);
        $payload = $response->json();

        if ($response->successful() && Arr::get($payload, 'status') === 'success') {
            $paymentData = Arr::get($payload, 'data.payment_data', []);
            $redirectUrl = Arr::get($paymentData, 'redirectTo', Arr::get($payload, 'data.url'));

            if ($redirectUrl) {
                return [
                    'success' => true,
                    'url' => (string) $redirectUrl,
                    'provider_order_id' => Arr::get($payload, 'data.invoice_id'),
                    'provider_invoice_key' => Arr::get($payload, 'data.invoice_key'),
                    'raw' => $payload,
                ];
            }
        }

        return [
            'success' => false,
            'message' => Arr::get($payload, 'message', 'Failed to create Fawaterk payment link.'),
            'raw' => $payload,
        ];
    }

    public function callBack(Request $request): array
    {
        $raw = $request->all();
        $status = strtolower((string) (
            $request->input('invoice_status')
            ?? $request->input('status')
            ?? $request->query('result')
            ?? ''
        ));

        $paymentId = (int) Arr::get($raw, 'pay_load.payment_id', 0);
        if ($paymentId <= 0) {
            $decodedPayLoad = json_decode((string) ($raw['pay_load'] ?? ''), true);
            if (is_array($decodedPayLoad)) {
                $paymentId = (int) Arr::get($decodedPayLoad, 'payment_id', 0);
            }
        }

        $invoiceId = $request->input('invoice_id') ?? $request->input('InvoiceId');
        $invoiceKey = $request->input('invoice_key') ?? $request->input('InvoiceKey');
        $paymentMethod = $request->input('payment_method') ?? $request->input('PaymentMethod');

        return [
            'success' => in_array($status, ['paid', 'success', 'approved'], true),
            'status' => in_array($status, ['pending'], true) ? 'pending' : null,
            'hash_valid' => $this->hashIsValid($request, $invoiceId, $invoiceKey, $paymentMethod),
            'order_id' => $paymentId > 0 ? $paymentId : null,
            'provider_order_id' => $invoiceId !== null ? (string) $invoiceId : null,
            'provider_invoice_key' => $invoiceKey !== null ? (string) $invoiceKey : null,
            'provider_transaction_id' => $request->input('referenceNumber'),
            'payment_method' => $paymentMethod !== null ? (string) $paymentMethod : null,
            'decline_reason' => $request->input('errorMessage'),
            'raw' => $raw,
        ];
    }

    protected function hashIsValid(Request $request, mixed $invoiceId, mixed $invoiceKey, mixed $paymentMethod): bool
    {
        $hash = (string) $request->input('hashKey', '');
        if ($hash === '' || $this->vendorKey === '' || !$invoiceId || !$invoiceKey || !$paymentMethod) {
            return $hash === '';
        }

        $queryParam = 'InvoiceId=' . $invoiceId . '&InvoiceKey=' . $invoiceKey . '&PaymentMethod=' . $paymentMethod;
        $expected = hash_hmac('sha256', $queryParam, $this->vendorKey, false);

        return hash_equals($expected, $hash);
    }

    protected function extractPaymentId(string $merchantOrderId): ?int
    {
        if (preg_match('/\bPAY-(\d+)\b/i', $merchantOrderId, $matches)) {
            return (int) $matches[1];
        }

        return null;
    }
}
