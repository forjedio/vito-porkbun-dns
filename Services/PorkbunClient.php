<?php

namespace App\Vito\Plugins\Forjedio\VitoPorkbunDns\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class PorkbunClient
{
    private const string API_BASE_URL = 'https://api.porkbun.com/api/json/v3/';

    public function __construct(
        private readonly string $apiKey,
        private readonly string $secretApiKey,
    ) {}

    public static function fromCredentials(array $credentials): self
    {
        return new self(
            apiKey: $credentials['api_key'],
            secretApiKey: $credentials['secret_api_key'],
        );
    }

    public function post(string $endpoint, array $data = []): Response
    {
        return $this->client()->post($endpoint, array_merge($this->authBody(), $data));
    }

    public function isSuccessful(Response $response): bool
    {
        return $response->successful() && $response->json('status') === 'SUCCESS';
    }

    private function client(): PendingRequest
    {
        return Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->baseUrl(self::API_BASE_URL);
    }

    /**
     * @return array<string, string>
     */
    private function authBody(): array
    {
        return [
            'apikey' => $this->apiKey,
            'secretapikey' => $this->secretApiKey,
        ];
    }
}
