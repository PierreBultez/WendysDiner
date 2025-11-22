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

        if ($mode === 'sandbox') {
            $this->baseUrl = 'https://sandbox-merchant.revolut.com/api/1.0';
        } else {
            $this->baseUrl = 'https://merchant.revolut.com/api/1.0';
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
    public function createOrder(float $amount, string $currency = 'EUR', string $description = ''): array
    {
        // Revolut expects amount in minor units (cents) as integer.
        // e.g. 10.50 EUR -> 1050
        $amountInCents = (int) round($amount * 100);

        $payload = [
            'amount' => $amountInCents,
            'currency' => $currency,
            'description' => $description,
            'capture_mode' => 'automatic', // Default per docs
        ];

        $response = Http::withToken($this->apiKey)
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'User-Agent' => 'WendyDiner/1.0', // Good practice
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
