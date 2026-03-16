<?php

namespace App\Vito\Plugins\Forjedio\VitoPorkbunDns\Actions;

use App\Vito\Plugins\Forjedio\VitoPorkbunDns\Services\PorkbunClient;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Throwable;

class ManageRecords
{
    private const int DEFAULT_TTL = 600;

    public function __construct(private readonly PorkbunClient $client) {}

    /**
     * @return array<string, mixed>
     *
     * @throws \RuntimeException
     */
    public function list(string $domainId): array
    {
        $response = $this->client->post("dns/retrieve/{$domainId}");

        if (! $this->client->isSuccessful($response)) {
            Log::error('Failed to fetch Porkbun DNS records', [
                'domainId' => $domainId,
                'response' => $response->json(),
            ]);

            throw new \RuntimeException(
                'Failed to fetch DNS records: '
                .($response->json('message') ?? 'Unknown error. Ensure API access is enabled for this domain in Porkbun.')
            );
        }

        return collect($response->json('records'))->map(fn (array $record) => [
            'id' => $record['id'],
            'type' => $record['type'],
            'name' => $record['name'],
            'content' => $record['content'],
            'ttl' => $record['ttl'],
            'proxied' => false,
            'priority' => isset($record['prio']) && $record['prio'] !== '' ? (int) $record['prio'] : null,
            'created_on' => null,
            'modified_on' => null,
        ])->toArray();
    }

    /**
     * @return array<string, mixed>
     *
     * @throws ValidationException
     */
    public function create(string $domainId, array $input): array
    {
        try {
            $response = $this->client->post(
                "dns/create/{$domainId}",
                $this->buildRecordData($input),
            );

            $this->ensureSuccessful($response, 'create', [
                'domainId' => $domainId,
                'input' => $input,
            ]);

            return [
                'id' => $response->json('id'),
                'type' => $input['type'],
                'name' => $input['name'],
                'content' => $input['content'],
                'ttl' => $input['ttl'] ?? self::DEFAULT_TTL,
                'proxied' => false,
                'priority' => $input['priority'] ?? null,
            ];
        } catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            Log::error('Porkbun createRecord exception', ['error' => $e->getMessage()]);
            throw ValidationException::withMessages(['record' => 'Failed to create DNS record: '.$e->getMessage()]);
        }
    }

    /**
     * @return array<string, mixed>
     *
     * @throws ValidationException
     */
    public function update(string $domainId, string $recordId, array $input): array
    {
        try {
            $response = $this->client->post(
                "dns/edit/{$domainId}/{$recordId}",
                $this->buildRecordData($input),
            );

            $this->ensureSuccessful($response, 'update', [
                'domainId' => $domainId,
                'recordId' => $recordId,
                'input' => $input,
            ]);

            return [
                'id' => $recordId,
                'type' => $input['type'],
                'name' => $input['name'],
                'content' => $input['content'],
                'ttl' => $input['ttl'] ?? self::DEFAULT_TTL,
                'proxied' => false,
                'priority' => $input['priority'] ?? null,
            ];
        } catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            Log::error('Porkbun updateRecord exception', ['error' => $e->getMessage()]);
            throw ValidationException::withMessages(['record' => 'Failed to update DNS record: '.$e->getMessage()]);
        }
    }

    public function delete(string $domainId, string $recordId): bool
    {
        try {
            $response = $this->client->post("dns/delete/{$domainId}/{$recordId}");

            if (! $this->client->isSuccessful($response)) {
                Log::error('Failed to delete Porkbun DNS record', [
                    'domainId' => $domainId,
                    'recordId' => $recordId,
                    'response' => $response->json(),
                ]);

                return false;
            }

            return true;
        } catch (Throwable $e) {
            Log::error('Porkbun deleteRecord exception', ['error' => $e->getMessage()]);

            return false;
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function buildRecordData(array $input): array
    {
        $data = [
            'type' => $input['type'],
            'name' => $input['name'],
            'content' => $input['content'],
            'ttl' => $input['ttl'] ?? self::DEFAULT_TTL,
        ];

        if (isset($input['priority'])) {
            $data['prio'] = $input['priority'];
        }

        return $data;
    }

    /**
     * @throws ValidationException
     */
    private function ensureSuccessful(Response $response, string $operation, array $context): void
    {
        if (! $this->client->isSuccessful($response)) {
            Log::error("Failed to {$operation} Porkbun DNS record", array_merge($context, [
                'response' => $response->json(),
            ]));

            throw ValidationException::withMessages([
                'record' => "Failed to {$operation} DNS record: ".($response->json('message') ?? 'Unknown error'),
            ]);
        }
    }
}
