<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Exception;

class RevolutService
{
    protected string $baseUrl;
    protected string $apiKey;

    public function __construct()
    {
        $mode = config('services.revolut.mode', 'sandbox');
        $this->apiKey = config('services.revolut.key');

        if (empty($this->apiKey)) {
            // Log warning or handle appropriately in logic
        }

        // TRYING NEW API URL STRUCTURE (No /1.0 versioning)
        if ($mode === 'sandbox') {
            $this->baseUrl = 'https://sandbox-merchant.revolut.com/api';
        } else {
            $this->baseUrl = 'https://merchant.revolut.com/api';
        }
    }

    /**
     * Create an order in Revolut.
     * https://developer.revolut.com/docs/merchant/create-order
     *
     * @param float $amount Amount in major units (e.g. 10.50)
     * @param string $currency Currency code (e.g. "EUR")
     * @param string $description Optional description
     * @return array The full response from Revolut (including 'token')
     * @throws Exception
     */
    public function createOrder(float $amount, string $currency = 'GBP', string $description = ''): array
    {
        // REVERT TO EUR (Assuming URL fix solves the issue)
        // Revolut expects amount in minor units (cents) as integer.
        $amountInCents = (int) round($amount * 100);

        $payload = [
            'amount' => $amountInCents,
            'currency' => $currency,
            'description' => $description, // Description is allowed
            // 'capture_mode' => 'automatic', // Try removing capture_mode if issues persist, but it's standard.
        ];

        \Illuminate\Support\Facades\Log::info('Revolut Request:', [
            'url' => "{$this->baseUrl}/orders",
            'headers' => [
                'Authorization' => 'Bearer ' . substr($this->apiKey ?? '', 0, 5) . '...',
                'Content-Type' => 'application/json',
                'User-Agent' => 'WendyDiner/1.0',
            ],
            'payload' => $payload
        ]);

        $response = Http::withToken($this->apiKey)
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'User-Agent' => 'WendyDiner/1.0',
                'Revolut-Api-Version' => '2024-09-01', // Required by new API
            ])
            ->post("{$this->baseUrl}/orders", $payload);

                if ($response->failed()) {
                    $statusCode = $response->status();
                    $url = "{$this->baseUrl}/orders";
                    // Log essential info for debugging without exposing full key in logs if possible,
                    // but user requested to see errors.
                    throw new Exception("Revolut API Error [{$statusCode}] (URL: {$url}): " . $response->body());
                }

                return $response->json();
            }
        }
