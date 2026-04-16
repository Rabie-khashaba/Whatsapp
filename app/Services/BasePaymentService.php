<?php

namespace App\Services;

use Exception;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class BasePaymentService
{
    protected string $baseUrl = '';

    /**
     * @var array<string,string>
     */
    protected array $headers = [];

    protected function request(string $method, string $url, array $data = [], string $type = 'json'): Response
    {
        try {
            return Http::withHeaders($this->headers)->send($method, rtrim($this->baseUrl, '/') . $url, [
                $type => $data,
            ]);
        } catch (Exception $e) {
            throw $e;
        }
    }
}
