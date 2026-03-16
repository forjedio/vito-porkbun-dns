<?php

namespace App\Vito\Plugins\Forjedio\VitoPorkbunDns;

use App\DNSProviders\AbstractDNSProvider;
use App\Vito\Plugins\Forjedio\VitoPorkbunDns\Actions\ListDomains;
use App\Vito\Plugins\Forjedio\VitoPorkbunDns\Actions\ManageRecords;
use App\Vito\Plugins\Forjedio\VitoPorkbunDns\Services\PorkbunClient;
use App\Vito\Plugins\Forjedio\VitoPorkbunDns\Actions\TestConnection;

class Porkbun extends AbstractDNSProvider
{
    public static function id(): string
    {
        return 'porkbun';
    }

    public function validationRules(array $input): array
    {
        return [
            'api_key' => 'required|string',
            'secret_api_key' => 'required|string',
            'domain_filter' => 'nullable|string',
        ];
    }

    public function credentialData(array $input): array
    {
        return [
            'api_key' => $input['api_key'],
            'secret_api_key' => $input['secret_api_key'],
            'domain_filter' => $input['domain_filter'] ?? '',
        ];
    }

    public function connect(array $credentials): bool
    {
        return app(TestConnection::class)->test($credentials);
    }

    public function getDomains(): array
    {
        return $this->domains()->list($this->getAllowedDomains());
    }

    public function getDomain(string $domainId): array
    {
        return $this->domains()->find($domainId);
    }

    public function getRecords(string $domainId): array
    {
        return $this->records()->list($domainId);
    }

    public function createRecord(string $domainId, array $recordData): array
    {
        return $this->records()->create($domainId, $recordData);
    }

    public function updateRecord(string $domainId, string $recordId, array $recordData): array
    {
        return $this->records()->update($domainId, $recordId, $recordData);
    }

    public function deleteRecord(string $domainId, string $recordId): bool
    {
        return $this->records()->delete($domainId, $recordId);
    }

    // -------------------------------------------------------------------------
    // Private Helpers
    // -------------------------------------------------------------------------

    private function client(): PorkbunClient
    {
        return PorkbunClient::fromCredentials($this->dnsProvider->credentials);
    }

    private function domains(): ListDomains
    {
        return new ListDomains($this->client());
    }

    private function records(): ManageRecords
    {
        return new ManageRecords($this->client());
    }

    /**
     * @return array<int, string>
     */
    private function getAllowedDomains(): array
    {
        $filter = $this->dnsProvider->credentials['domain_filter'] ?? '';

        if (! $filter) {
            return [];
        }

        return array_filter(array_map('trim', explode(',', $filter)));
    }
}
