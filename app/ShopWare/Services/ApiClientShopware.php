<?php

namespace App\ShopWare\Services;

use Illuminate\Support\Str;

class ApiClientShopware extends ApiClientBase
{
    protected string $logSystem = 'ShopWare API Client';

    protected function getDefaultConfig(): array
    {
        return [
            'base_url' => config('app.shopware_base_url'),
            'api_id' => config('app.shopware_client_id'),
            'api_key' => config('app.shopware_api_key'),
            'default_headers' => [
                'Accept' => 'application/vnd.api+json',
                'Content-Type' => 'application/vnd.api+json',
            ],
            'user_agent' => 'Laravel-ShopWare-Client/2.0'
        ];
    }

    protected function authenticate(): ?string
    {
        $response = $this->client->post('api/oauth/token', [
            'json' => [
                'grant_type' => 'client_credentials',
                'client_id' => $this->config['api_id'],
                'client_secret' => $this->config['api_key'],
            ],
        ]);

        if ($response->getStatusCode() === 200) {
            $data = json_decode($response->getBody()->getContents(), true);
            return $data['access_token'] ?? null;
        }

        return null;
    }

    protected function getCacheKey(): string
    {
        return 'shopware_token_' . md5($this->config['api_id']);
    }

    // Verbindungstest
    public function checkConnection(): bool
    {
        $result = $this->get('/');
        return $result['success'] ?? false;
    }

    public function checkTokenValidity(): bool
    {
        $result = $this->get('api/search/currency', ['limit' => 1]);
        return $result['success'] ?? false;
    }

    // ================== BULK-OPERATIONEN ==================

    public function bulk(array $operations): array
    {
        return $this->post('api/_action/sync', $operations);
    }

    public function bulkUpsert(string $entity, array $payload): array
    {
        $writeKey = 'write-' . Str::kebab($entity);

        return $this->bulk([
            $writeKey => [
                'entity' => $entity,
                'action' => 'upsert',
                'payload' => $payload
            ]
        ]);
    }

    public function bulkDelete(string $entity, array $ids): array
    {
        $writeKey = 'delete-' . Str::kebab($entity);
        $payload = array_map(fn($id) => ['id' => $id], $ids);

        return $this->bulk([
            $writeKey => [
                'entity' => $entity,
                'action' => 'delete',
                'payload' => $payload
            ]
        ]);
    }
}
