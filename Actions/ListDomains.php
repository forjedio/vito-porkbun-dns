<?php

namespace App\Vito\Plugins\Forjedio\VitoPorkbunDns\Actions;

use App\Vito\Plugins\Forjedio\VitoPorkbunDns\Services\PorkbunClient;
use Illuminate\Support\Facades\Log;
use Throwable;

class ListDomains
{
    public function __construct(private readonly PorkbunClient $client) {}

    /**
     * @param  array<int, string>  $allowedDomains
     * @return array<string, mixed>
     */
    public function list(array $allowedDomains = []): array
    {
        try {
            $domains = $this->fetchAll();

            return collect($domains)
                ->when(count($allowedDomains) > 0, fn ($collection) => $collection->filter(
                    fn (array $domain) => in_array($domain['domain'], $allowedDomains)
                ))
                ->map(fn (array $domain) => self::mapDomain($domain))
                ->values()
                ->toArray();
        } catch (Throwable $e) {
            Log::error('Porkbun getDomains exception', ['error' => $e->getMessage()]);

            return [];
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function find(string $domainId): array
    {
        try {
            $domains = $this->fetchAll();

            $domain = collect($domains)->first(
                fn (array $domain) => $domain['domain'] === $domainId
            );

            return $domain ? self::mapDomain($domain) : [];
        } catch (Throwable $e) {
            Log::error('Porkbun getDomain exception', ['error' => $e->getMessage()]);

            return [];
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function fetchAll(): array
    {
        $response = $this->client->post('domain/listAll');

        if (! $this->client->isSuccessful($response)) {
            Log::error('Failed to fetch Porkbun domains', ['response' => $response->json()]);

            return [];
        }

        return $response->json('domains') ?? [];
    }

    /**
     * @return array<string, mixed>
     */
    private static function mapDomain(array $domain): array
    {
        return [
            'id' => $domain['domain'],
            'name' => $domain['domain'],
            'status' => $domain['status'] ?? 'active',
            'created_on' => $domain['createDate'] ?? null,
            'modified_on' => $domain['createDate'] ?? null,
        ];
    }
}
