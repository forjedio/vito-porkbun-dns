<?php

namespace App\Vito\Plugins\Forjedio\VitoPorkbunDns\Actions;

use App\Vito\Plugins\Forjedio\VitoPorkbunDns\Services\PorkbunClient;
use Illuminate\Support\Facades\Log;
use Throwable;

class TestConnection
{
    public function test(array $credentials): bool
    {
        try {
            $client = PorkbunClient::fromCredentials($credentials);
            $response = $client->post('ping');

            if ($client->isSuccessful($response)) {
                return true;
            }

            Log::error('Porkbun connection failed', ['response' => $response->json()]);

            return false;
        } catch (Throwable $e) {
            Log::error('Porkbun connection exception', ['error' => $e->getMessage()]);

            return false;
        }
    }
}
