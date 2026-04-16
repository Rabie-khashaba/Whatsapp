<?php

namespace App\Services;

use App\Interfaces\PaymentGatewayInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class PaymobPaymentService extends BasePaymentService implements PaymentGatewayInterface
{
    protected string $apiKey;
    protected string $publicKey;
    protected string $secretKey;

    /**
     * @var int[]
     */
    protected array $integrationIds;

    protected string $currency;

    public function __construct()
    {
        $base = (string) config('paymob.base_url', 'https://accept.paymob.com');
        $base = rtrim($base, '/');
        $this->baseUrl = preg_replace('~/api/?$~', '', $base) ?: $base;

        $this->apiKey = (string) config('paymob.api_key', env('BAYMOB_API_KEY'));
        $this->publicKey = (string) config('paymob.public_key', env('BAYMOB_PUBLIC_KEY'));
        $this->secretKey = (string) config('paymob.secret_key', env('BAYMOB_SECRET_KEY'));
        $this->currency = (string) config('paymob.currency', 'EGP');

        $rawIntegrationId = config('paymob.integration_id', null);
        if (is_string($rawIntegrationId) && str_contains($rawIntegrationId, ',')) {
            $this->integrationIds = array_values(array_filter(array_map('intval', array_map('trim', explode(',', $rawIntegrationId)))));
        } else {
            $this->integrationIds = array_values(array_filter([is_numeric($rawIntegrationId) ? (int) $rawIntegrationId : null]));
        }

        if ($this->integrationIds === []) {
            $this->integrationIds = [5481966];
        }

        $this->headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
    }

    protected function generateToken(): string
    {
        $response = $this->request('POST', '/api/auth/tokens', ['api_key' => $this->apiKey]);
        $data = $response->json();

        return (string) Arr::get($data, 'token', '');
    }

    protected function fetchTransaction(string $transactionId): array
    {
        $token = $this->generateToken();
        if ($token === '') {
            return [];
        }

        $this->headers['Authorization'] = 'Bearer ' . $token;

        $response = $this->request('GET', '/api/acceptance/transactions/' . $transactionId);
        $payload = $response->json();

        return is_array($payload) ? $payload : [];
    }

    protected function normalizeAmount(array $data): array
    {
        if (isset($data['amount'])) {
            $data['amount_cents'] = (int) round(((float) $data['amount']) * 100);
            unset($data['amount']);
        } elseif (isset($data['amount_cents'])) {
            $data['amount_cents'] = (int) round(((float) $data['amount_cents']) * 100);
        }

        return $data;
    }

    public function sendPayment(Request $request): array
    {
        if ($this->publicKey === '' || $this->secretKey === '') {
            return [
                'success' => false,
                'message' => 'Paymob public key and secret key are required for Flash Checkout.',
            ];
        }

        $data = $this->normalizeAmount($request->all());
        $data['currency'] = $data['currency'] ?? $this->currency;
        $data['merchant_order_id'] = (string) ($data['merchant_order_id'] ?? '');
        if ($data['merchant_order_id'] === '') {
            $data['merchant_order_id'] = 'PAY-' . now()->format('YmdHis');
        }
        $data['merchant_order_id'] = $this->makeUniqueMerchantOrderId($data['merchant_order_id']);

        $payload = [
            'amount' => (int) ($data['amount_cents'] ?? 0),
            'currency' => (string) $data['currency'],
            'payment_methods' => $this->integrationIds,
            'items' => is_array($data['items'] ?? null) ? $data['items'] : [],
            'billing_data' => $this->buildBillingData($data),
            'special_reference' => $data['merchant_order_id'],
            'notification_url' => route('payments.paymob.callback'),
            'redirection_url' => route('payments.paymob.callback'),
        ];

        $this->headers['Authorization'] = 'Token ' . $this->secretKey;
        $response = $this->request('POST', '/v1/intention/', $payload);
        $payload = $response->json();

        if ($response->successful()) {
            $clientSecret = (string) Arr::get($payload, 'client_secret', Arr::get($payload, 'cs', ''));
            $providerOrderId = Arr::get($payload, 'id', Arr::get($payload, 'intention_order_id'));

            if ($clientSecret !== '') {
                return [
                    'success' => true,
                    'url' => $this->buildCheckoutUrl($clientSecret),
                    'provider_order_id' => $providerOrderId !== null ? (string) $providerOrderId : null,
                    'raw' => $payload,
                ];
            }
        }

        return [
            'success' => false,
            'message' => 'Failed to create Paymob payment link.',
            'raw' => $payload,
        ];
    }

    public function callBack(Request $request): array
    {
        $raw = $request->all();

        $successValue = $request->input('success', Arr::get($raw, 'obj.success', null));
        $success = $successValue === true || $successValue === 'true' || $successValue === 1 || $successValue === '1';

        $merchantOrderId = (string) ($request->input('merchant_order_id')
            ?? Arr::get($raw, 'merchant_order_id')
            ?? Arr::get($raw, 'obj.order.merchant_order_id')
            ?? Arr::get($raw, 'order.merchant_order_id')
            ?? '');

        $providerOrderId = $request->input('order')
            ?? $request->input('order_id')
            ?? Arr::get($raw, 'order')
            ?? Arr::get($raw, 'order_id')
            ?? Arr::get($raw, 'obj.order.id')
            ?? Arr::get($raw, 'obj.order')
            ?? null;

        $providerTransactionId = $request->input('id')
            ?? Arr::get($raw, 'id')
            ?? Arr::get($raw, 'obj.id')
            ?? Arr::get($raw, 'obj.transaction.id')
            ?? null;

        if ($merchantOrderId === '' && $providerTransactionId !== null) {
            $tx = $this->fetchTransaction((string) $providerTransactionId);
            $merchantOrderId = (string) Arr::get($tx, 'order.merchant_order_id', '');

            if ($providerOrderId === null) {
                $providerOrderId = Arr::get($tx, 'order.id', null);
            }
        }

        $orderId = null;
        if (preg_match('/\\bPAY-(\\d+)\\b/i', $merchantOrderId, $m)) {
            $orderId = (int) $m[1];
        } elseif (preg_match('/\\bORDER-(\\d+)\\b/i', $merchantOrderId, $m)) {
            $orderId = (int) $m[1];
        }

        $declineReason = $this->extractDeclineReason($raw);

        return [
            'success' => $success,
            'order_id' => $orderId,
            'merchant_order_id' => $merchantOrderId !== '' ? $merchantOrderId : null,
            'provider_order_id' => $providerOrderId !== null ? (string) $providerOrderId : null,
            'provider_transaction_id' => $providerTransactionId !== null ? (string) $providerTransactionId : null,
            'decline_reason' => $declineReason,
            'raw' => $raw,
        ];
    }

    protected function extractDeclineReason(array $raw): ?string
    {
        $candidates = [
            Arr::get($raw, 'message'),
            Arr::get($raw, 'error'),
            Arr::get($raw, 'detail'),
            Arr::get($raw, 'obj.message'),
            Arr::get($raw, 'obj.error_occured'),
            Arr::get($raw, 'obj.txn_response_code'),
            Arr::get($raw, 'obj.data.message'),
            Arr::get($raw, 'data.message'),
        ];

        foreach ($candidates as $candidate) {
            if (is_bool($candidate)) {
                $candidate = $candidate ? 'true' : 'false';
            }

            if (is_scalar($candidate)) {
                $value = trim((string) $candidate);
                if ($value !== '' && !in_array(strtolower($value), ['false', 'null'], true)) {
                    return $value;
                }
            }
        }

        return null;
    }

    protected function buildCheckoutUrl(string $clientSecret): string
    {
        return rtrim($this->baseUrl, '/') . '/unifiedcheckout/?publicKey=' . urlencode($this->publicKey) . '&clientSecret=' . urlencode($clientSecret);
    }

    protected function makeUniqueMerchantOrderId(string $merchantOrderId): string
    {
        return trim($merchantOrderId) . '-' . now()->format('YmdHisv');
    }

    protected function buildBillingData(array $data): array
    {
        $shipping = $data['shipping_data'] ?? [];
        if (!is_array($shipping)) {
            $shipping = [];
        }

        return [
            'first_name' => (string) Arr::get($shipping, 'first_name', 'Customer'),
            'last_name' => (string) Arr::get($shipping, 'last_name', 'User'),
            'email' => (string) Arr::get($shipping, 'email', 'customer@example.com'),
            'phone_number' => (string) Arr::get($shipping, 'phone_number', '+201000000000'),
        ];
    }
}