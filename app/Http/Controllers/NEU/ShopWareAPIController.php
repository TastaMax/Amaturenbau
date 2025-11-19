<?php

namespace App\Http\Controllers\NEU;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class ShopWareAPIController
{
    protected string $baseUrl;
    protected string $apiId;
    protected string $apiKey;

    protected Client $client;

    public function __construct()
    {
        $this->baseUrl = config('app.shopware_base_url');
        $this->apiId = config('app.shopware_client_id');
        $this->apiKey = config('app.shopware_api_key');

        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'headers' => [
                'Accept' => 'application/vnd.api+json, application/json',
                'Content-Type' => 'application/json',
            ],
        ]);
    }

    /**
     * Versucht, ein Access Token zu holen.
     *
     * @param callable|null $onSuccess Callback bei Erfolg, erhält das Token
     * @param callable|null $onError Callback bei Fehler, erhält die Fehlermeldung/Exception
     * @return string|false
     */
    public function getAccessToken(callable $onSuccess = null, callable $onError = null): string|false
    {
        try {
            $response = $this->client->post('api/oauth/token', [
                'json' => [
                    'grant_type' => 'client_credentials',
                    'client_id' => $this->apiId,
                    'client_secret' => $this->apiKey,
                ],
            ]);

            $data = json_decode($response->getBody(), true);
            $token = $data['access_token'] ?? null;

            if ($token && $onSuccess) {
                $onSuccess($token);
            }

            return $token;
        } catch (GuzzleException $e) {
            $errorMsg = $e->getMessage();
            Log::error('ShopWare Auth Error', [
                'message' => $errorMsg,
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            if ($onError) {
                $onError($e);
            }

            return false;
        }
    }

    public function makeRequest($method, $url, $options = [])
    {
        try {
            // Holen des Access-Tokens
            $accessToken = $this->getAccessToken();

            if (!$accessToken) {
                return [
                    'status' => 'error',
                    'message' => 'Kein gültiger Access-Token gefunden'
                ];
            }

            // Die Anfrage an die API senden, den Token im Header hinzufügen
            $response = $this->client->request($method, $url, array_merge($options, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Accept' => 'application/vnd.api+json, application/json',
                    'Content-Type' => 'application/json',
                ]
            ]));

            // Rückgabe des Response-Body als Array (oder JSON, falls erforderlich)
            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            // Fehlerbehandlung: Überprüfen, ob der Fehler eine Antwort enthält
            if ($e->hasResponse()) {
                $response = $e->getResponse();
                return [
                    'status' => 'error',
                    'message' => $response->getBody()->getContents()
                ];
            }

            return [
                'status' => 'error',
                'message' => 'Unbekannter Fehler beim Abrufen der API-Daten'
            ];
        }
    }



}
