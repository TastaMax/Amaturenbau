<?php

namespace App\Services;

use App\Models\CustomeLog;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;

class ShopwareApiClient
{
    protected mixed $baseUrl;
    protected mixed $apiId;
    protected mixed $apiKey;

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

    public function getAccessToken()
    {
        try {
            $response = $this->client->post('api/oauth/token', [
                'json' => [
                    'grant_type' => 'client_credentials',
                    'client_id' => $this->apiId,
                    'client_secret' => $this->apiKey,
                ],
            ]);

            return json_decode($response->getBody(), true)['access_token'];
        } catch (GuzzleException $e) {
            return false;
        }
    }

    public function callShopwareApi($endpoint, $method = 'GET', $params = [], $json = true)
    {
        try {
            $accessToken = $this->getAccessToken();


            if($json)
            {
                $response = $this->client->request($method, $endpoint, [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $accessToken,
                    ],
                    'json' => $params,
                ]);
            }else{
                $response = $this->client->request($method, $endpoint, [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $accessToken,
                    ],
                    'body' => $params,
                ]);
            }


            return [
                'body' => json_decode($response->getBody(), true),
                'code' => $response->getStatusCode(),
            ];
        } catch (GuzzleException $e) {
            $log = new CustomeLog();
            $log->importance = 10;
            $log->system = 'ShopWare Sync';
            $log->message = 'API Problem bei Endpunkt '.$endpoint.'!';
            $log->debug = json_encode(['StatusCode' => $e->getCode(), 'Message' => json_decode($e->getResponse()->getBody()->getContents())], );
            $log->save();

            return [$e->getCode(), $e->getResponse()->getBody()->getContents()];
        }
    }

    public function checkConnection(): bool
    {
        try {
            $response = $this->client->get('/');
            if ($response->getStatusCode() === 200) {
                return true; // Die Verbindung zum Shop und der Token sind erfolgreich
            }
        } catch (RequestException $e) {
            return false; // Fehler beim Verbindungsversuch oder ungültiger Token
        } catch (GuzzleException $e) {
            return false;
        }

        return false; // Standardmäßig wird false zurückgegeben, falls kein erfolgreicher Statuscode erhalten wurde
    }

    public function checkTokenValidity(): bool
    {
        try {
            $accessToken = $this->getAccessToken();

            if (!$accessToken) {
                return false;
            }

        } catch (RequestException $e) {
            return false;
        }

        return true;
    }

    public function sync($type, $entity, $action, $payload)
    {
        $accessToken = $this->getAccessToken();
        try {
            $request = $this->client->post('/api/_action/sync', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                ],
                'body' => json_encode([
                    'write-'.$type => [
                        'entity' => $entity,
                        'action' => $action,
                        'payload' => $payload
                    ]
                ])
            ]);

            $log = new CustomeLog();
            $log->importance = 0;
            $log->system = 'ShopWare Sync';
            $log->message = $type.' wurden synchronisiert!';
            $log->debug = json_encode(['StatusCode' => $request->getStatusCode(), 'Message' => json_decode($request->getBody()->getContents())]);
            $log->save();

            return [$request->getStatusCode(), $request->getBody()->getContents()];
        } catch (ClientException $e) {

            $log = new CustomeLog();
            $log->importance = 10;
            $log->system = 'ShopWare Sync';
            $log->message = $type.' konnten nicht synchronisiert werden!';
            $log->debug = json_encode(['StatusCode' => $e->getCode(), 'Message' => json_decode($e->getResponse()->getBody()->getContents())], );
            $log->save();

            return [$e->getCode(), $e->getResponse()->getBody()->getContents()];
        }  catch (GuzzleException $e) {

            $log = new CustomeLog();
            $log->importance = 10;
            $log->system = 'ShopWare Sync';
            $log->message = $type . ' konnten nicht synchronisiert werden!';
            $log->debug = json_encode(['StatusCode' => $e->getCode(), 'Message' => json_decode($e->getResponse()->getBody()->getContents())],);
            $log->save();

            return [$e->getCode(), $e->getResponse()->getBody()->getContents()];
        }

    }

    public function searchPropertyGroupIdByName($propertyGroupName): false|array
    {
        if(is_null($propertyGroupName) || !isset($propertyGroupName))
        {
            return false;
        }
        return $this->callShopwareApi('/api/search/property-group', 'POST', json_encode([
            "filter" => [
                [
                    "type" => "equals",
                    "field"=> "name",
                    "value" => $propertyGroupName
                ]
            ]
        ], true), false);
    }

    public function searchPropertyGroupOptions($propertyGroupId, $propertyName)
    {
        return $this->callShopwareApi('/api/search/property-group/'.$propertyGroupId.'/options', 'POST', json_encode([
            "filter" => [
                [
                    "type" => "equals",
                    "field"=> "name",
                    "value" => $propertyName
                ]
            ]
        ], true), false);
    }

    public function createPropertyGroupIdByName($propertyGroupName, $propertyGroupNameEng, $id)
    {
        return $this->callShopwareApi('/api/property-group', 'POST', json_encode([
            "name" => $propertyGroupName,
            "id" => $id,
            "translations" => [
                "en-GB" => [
                    "name" => $propertyGroupNameEng
                ]
            ]
        ], true), false);
    }

    public function createPropertyGroupOption($propertyGroupId, $propertyGroupOptionName, $propertyGroupOptionNameEng, $id)
    {
        return $this->callShopwareApi('/api/property-group-option/', 'POST', json_encode([
            "name" => $propertyGroupOptionName,
            "id" => $id,
            "groupId" => $propertyGroupId,
            "position" => intval($propertyGroupOptionName),
            "translations" => [
                "en-GB" => [
                    "name" => $propertyGroupOptionNameEng
                ]
            ]
        ], true), false);
    }

    public function getProducts()
    {
        return $this->callShopwareApi('/api/product');
    }

    public function getCustomers()
    {
        return $this->callShopwareApi('/api/customer');
    }

    public function getLanguages()
    {
        return $this->callShopwareApi('/api/locale');
    }

    public function getSalesChannel()
    {
        return $this->callShopwareApi('/api/sales-channel');
    }

    public function getCategorys()
    {
        return $this->callShopwareApi('api/category');
    }

    public function getCategory($id)
    {
        return $this->callShopwareApi('api/category/'.$id);
    }

    public function postCategory($category)
    {
        return $this->callShopwareApi('api/category', 'POST', [
            'name' => $category->name,
            'parentId' => $category->parentId,
            'active' => $category->active
        ]);
    }

    public function updateSalesChannel($id, $data)
    {
        return $this->callShopwareApi('api/sales-channel/'.$id, 'POST', [
            $data
        ]);
    }

    // Weitere Methoden für andere API-Funktionen

}
