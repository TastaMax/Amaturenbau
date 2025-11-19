<?php

namespace App\Http\Controllers\NEU;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ShopWareSyncPropertyOption
{
    protected ShopWareAPIController $api;
    protected ShopWareHelper $swHelper;

    public function __construct()
    {
        $this->api = new ShopWareAPIController();
        $this->swHelper = new ShopWareHelper();
    }

    /**
     * Holt eine Liste von Produkten aus ShopWare
     */
    public function getProperties()
    {
        // Check ob die Daten schon im Cache sind
        $cachedProperties = cache()->get('shopware_properties');

        if ($cachedProperties) {
            return $cachedProperties;  // Falls ja, sofort zurückgeben
        }

        // Falls nicht, vom ShopWare API holen
        $properties = $this->api->makeRequest('GET', '/api/property-group'); // Beispiel-Endpunkt, je nach deinem ShopWare Setup

        // Auftrennen
        $properties = $properties['data'];

        // Speichern der Properties im Cache für z. B. 24 Stunden
        cache()->put('shopware_properties', $properties, 10);  // Cache für 10 Minuten

        return $properties;
    }

    /**
     * Holt Optionen für eine bestimmte Property-Gruppe
     */
    public function getOptionsForPropertyGroup($groupId)
    {
        // Check ob die Optionen für diese Gruppe schon im Cache sind
        $cachedOptions = cache()->get('shopware_property_group_' . $groupId . '_options');

        if ($cachedOptions) {
            return $cachedOptions;  // Falls ja, sofort zurückgeben
        }

        // Falls nicht, vom ShopWare API holen
        $options = $this->api->makeRequest('GET', '/api/property-group-option', [
            'query' => ['filter[groupId]' => $groupId]
        ]);

        // Auftrennen
        $options = $options['data'] ?? [];

        // Speichern der Optionen im Cache für z. B. 24 Stunden
        cache()->put('shopware_property_group_' . $groupId . '_options', $options, 10);  // Cache für 10 Minuten

        return $options;
    }


    /**
     * Synchronizes property groups and their options with ShopWare.
     *
     * This method checks for existing property groups and options in the
     * ShopWare system and creates new ones if they do not exist. It uses
     * cached data unless forceSync is set to true, in which case it fetches
     * the latest data directly from the ShopWare API.
     *
     * @param array $propertyData An associative array where keys are property
     *                            names and values are arrays containing option
     *                            data with German and English translations.
     * @param bool $forceSync     If set to true, forces a sync with the ShopWare
     *                            API, bypassing the cache.
     *
     * @return array The API response indicating success or failure of the sync
     *               operation, including any messages and data returned.
     */
    public function bulkCreatePropertyGroupWithValues(array $propertyData, bool $forceSync = false): array
    {
        // Properties aus dem Cache holen, es sei denn, forceSync ist wahr
        $properties = $forceSync ? $this->api->makeRequest('GET', '/api/property-group')['data'] : $this->getProperties();

        $groupPayload = [];
        $optionPayload = [];

        foreach ($propertyData as $propertyName => $values) {
            // Suche nach der Property mit dem angegebenen Namen
            $existingProperty = collect($properties)->firstWhere(function ($property) use ($propertyName) {
                return isset($property['attributes']['name']) && $property['attributes']['name'] === $propertyName;
            });

            // Wenn sie nicht existiert, dann eine neue UUID erstellen
            $groupId = $existingProperty ? $existingProperty['id'] : $this->swHelper->generateUUID();

            // Falls die Gruppe noch nicht existiert, dann die Gruppe in das Payload einfügen
            if (!$existingProperty || $forceSync) {
                $groupPayload[] = [
                    'id' => $groupId,
                    'name' => $propertyName,
                    'filterable' => true,
                    'sortingType' => 'alphanumeric',
                    'displayType' => 'text',
                    'translations' => [
                        'en-GB' => [
                            'name' => $propertyName,  // Englische Übersetzung
                        ],
                    ]
                ];
            }

            // Hole bestehende Optionen für diese Property-Gruppe, wenn nicht gecached und forceSync nicht aktiv
            $existingOptions = $forceSync ? $this->api->makeRequest('GET', '/api/property-group-option', [
                'query' => ['filter[groupId]' => $groupId]
            ])['data'] : $this->getOptionsForPropertyGroup($groupId);

            // Optionen hochladen, aber nur wenn sie noch nicht existieren oder bei forceSync
            foreach ($values['de'] as $index => $deValue) {
                // Den englischen Wert anhand des gleichen Indexes holen
                $enValue = $values['en'][$index]['en'] ?? null;
                $deValue = $deValue['de'] ?? null;

                // Prüfen, ob der deutsche Wert vorhanden ist
                if ($deValue) {

                    // Prüfen, ob die Option bereits existiert, basierend auf dem deutschen Wert
                    $existingOption = collect($existingOptions)->firstWhere(function ($option) use ($deValue) {
                        return $option['attributes']['name'] === $deValue;  // Deutsch prüfen
                    });

                    // Wenn sie nicht existiert, dann eine neue UUID erstellen
                    $valueId = $existingOption ? $existingOption['id'] : $this->swHelper->generateUUID();

                    // Wenn die Option nicht existiert oder forceSync aktiv ist, füge sie zur Payload hinzu
                    if (!$existingOption || $forceSync) {
                        $optionPayload[] = [
                            'id' => $valueId,
                            'groupId' => $groupId,
                            'name' => $deValue,  // Hier wird der deutsche Wert verwendet
                            'translations' => [
                                'en-GB' => [
                                    'name' => $enValue,  // Englische Übersetzung
                                ],
                            ]
                        ];
                    }
                }
            }
        }

        // Nur Payload senden, wenn es neue Gruppen oder Optionen gibt
        if (empty($groupPayload) && empty($optionPayload)) {
            return ['success' => true, 'message' => 'Alle Gruppen und Optionen aktuell!', 'data' => []];  // Keine neuen Gruppen oder Optionen, also nichts tun
        }

        $syncPayload = [
            'property_group' => [
                'entity' => 'property_group',
                'action' => 'upsert',
                'payload' => $groupPayload
            ],
            'property_group_option' => [
                'entity' => 'property_group_option',
                'action' => 'upsert',
                'payload' => $optionPayload
            ]
        ];

        // Aufruf der API und Überprüfung der Antwort
        $response = $this->api->makeRequest('POST', '/api/_action/sync', [
            'json' => $syncPayload
        ]);

        return $response;
    }
}
