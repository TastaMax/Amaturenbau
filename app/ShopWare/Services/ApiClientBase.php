<?php

namespace App\ShopWare\Services;

use App\Models\CustomeLog;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

abstract class ApiClientBase
{
    protected Client $client;
    protected array $config;
    protected ?string $cachedToken = null;

    // Konfiguration
    protected int $tokenCacheDuration = 3300; // 55 Minuten
    protected bool $enableDetailedLogging = true;
    protected bool $enablePerformanceLogging = true;
    protected string $logSystem = 'API Client';
    protected int $maxRetries = 3;
    protected array $retryDelays = [1, 2, 4]; // Sekunden zwischen Versuchen

    public function __construct(array $config = [])
    {
        $this->config = array_merge($this->getDefaultConfig(), $config);
        $this->applyConfig();
        $this->initializeHttpClient();

        $this->logInfo('API Client initialisiert', [
            'base_url' => $this->config['base_url'] ?? 'nicht gesetzt',
            'client_class' => static::class
        ]);
    }

    abstract protected function getDefaultConfig(): array;
    abstract protected function authenticate(): ?string;
    abstract protected function getCacheKey(): string;

    protected function applyConfig(): void
    {
        $this->enableDetailedLogging = $this->config['detailed_logging'] ?? true;
        $this->enablePerformanceLogging = $this->config['performance_logging'] ?? true;
        $this->maxRetries = $this->config['max_retries'] ?? 3;
        $this->tokenCacheDuration = $this->config['token_cache_duration'] ?? 3300;
    }

    protected function initializeHttpClient(): void
    {
        $this->client = new Client([
            'base_uri' => $this->config['base_url'],
            'timeout' => $this->config['timeout'] ?? 30,
            'connect_timeout' => $this->config['connect_timeout'] ?? 10,
            'headers' => array_merge([
                'Accept' => 'application/vnd.api+json',
                'Content-Type' => 'application/vnd.api+json',
                'User-Agent' => $this->config['user_agent'] ?? 'Laravel-API-Client/2.0'
            ], $this->config['default_headers'] ?? []),
            'http_errors' => false
        ]);
    }

    /**
     * Holt einen Access Token mit Caching-Mechanismus
     */
    public function getAccessToken(bool $forceRefresh = false): ?string
    {
        $startTime = microtime(true);

        if (!$forceRefresh && $this->cachedToken) {
            return $this->cachedToken;
        }

        $cacheKey = $this->getCacheKey();

        if (!$forceRefresh && Cache::has($cacheKey)) {
            $this->cachedToken = Cache::get($cacheKey);
            $this->logDebug('Token aus Cache geladen');
            return $this->cachedToken;
        }

        try {
            $this->logInfo('Neuen Access Token anfordern');

            $token = $this->authenticate();

            if ($token) {
                $this->cachedToken = $token;
                Cache::put($cacheKey, $token, $this->tokenCacheDuration);

                $this->logPerformance('Token erfolgreich erhalten', $startTime);
                return $token;
            }

            $this->logError('Token-Anfrage fehlgeschlagen - kein Token erhalten');
            return null;

        } catch (\Throwable $e) {
            $this->logException('Token-Anfrage Exception', $e);
            return null;
        }
    }

    /**
     * Universelle API-Anfrage-Methode mit verbesserter Fehlerbehandlung
     */
    public function request(string $method, string $endpoint, array $options = []): array
    {
        $startTime = microtime(true);
        $requestId = uniqid('req_');

        $this->logInfo("API Anfrage gestartet [{$requestId}]", [
            'method' => $method,
            'endpoint' => $endpoint,
            'options_preview' => $this->sanitizeForLogging($options)
        ]);

        for ($attempt = 1; $attempt <= $this->maxRetries; $attempt++) {
            try {
                $token = $this->getAccessToken($attempt > 1);

                if (!$token) {
                    return $this->createErrorResponse('Kein gültiger Access Token verfügbar', 401, $requestId);
                }

                // Request-Optionen vorbereiten
                $requestOptions = $this->prepareRequestOptions($options, $token, $requestId);

                $response = $this->client->request($method, $endpoint, $requestOptions);
                $statusCode = $response->getStatusCode();
                $responseBody = $response->getBody()->getContents();

                // Erfolgreiche Antwort
                if ($statusCode >= 200 && $statusCode < 300) {
                    $result = $this->createSuccessResponse($responseBody, $statusCode, $requestId);

                    $this->logPerformance("API Anfrage erfolgreich [{$requestId}]", $startTime, [
                        'method' => $method,
                        'endpoint' => $endpoint,
                        'status_code' => $statusCode,
                        'attempt' => $attempt
                    ]);

                    return $result;
                }

                // Fehlerbehandlung basierend auf Status Code
                if ($statusCode === 401 && $attempt < $this->maxRetries) {
                    $this->logWarning("Unauthorized - Token refresh erforderlich [{$requestId}]", [
                        'attempt' => $attempt,
                        'status_code' => $statusCode
                    ]);
                    Cache::forget($this->getCacheKey());
                    $this->cachedToken = null;
                    sleep($this->retryDelays[$attempt - 1] ?? 1);
                    continue;
                }

                if ($statusCode >= 500 && $attempt < $this->maxRetries) {
                    $this->logWarning("Server Error - Wiederholung [{$requestId}]", [
                        'attempt' => $attempt,
                        'status_code' => $statusCode,
                        'delay' => $this->retryDelays[$attempt - 1] ?? 1
                    ]);
                    sleep($this->retryDelays[$attempt - 1] ?? 1);
                    continue;
                }

                // Endgültiger Fehler
                return $this->createErrorResponse(
                    "HTTP {$statusCode}: " . $this->getStatusMessage($statusCode),
                    $statusCode,
                    $requestId,
                    $responseBody
                );

            } catch (GuzzleException $e) {
                if ($attempt < $this->maxRetries && $this->isRetryableException($e)) {
                    $this->logWarning("Retryable Exception - Wiederholung [{$requestId}]", [
                        'attempt' => $attempt,
                        'exception' => get_class($e),
                        'message' => $e->getMessage(),
                        'delay' => $this->retryDelays[$attempt - 1] ?? 1
                    ]);
                    sleep($this->retryDelays[$attempt - 1] ?? 1);
                    continue;
                }

                return $this->createErrorResponse(
                    'Network Error: ' . $e->getMessage(),
                    $e->getCode() ?: 500,
                    $requestId,
                    null,
                    $e
                );
            }
        }

        return $this->createErrorResponse('Max retries erreicht', 500, $requestId);
    }

    protected function prepareRequestOptions(array $options, string $token, string $requestId): array
    {
        return array_merge($options, [
            'headers' => array_merge($options['headers'] ?? [], [
                'Authorization' => 'Bearer ' . $token,
                'X-Request-ID' => $requestId
            ])
        ]);
    }

    protected function createSuccessResponse(string $responseBody, int $statusCode, string $requestId): array
    {
        $data = json_decode($responseBody, true);

        return [
            'success' => true,
            'data' => $data,
            'status_code' => $statusCode,
            'request_id' => $requestId,
            'timestamp' => now()->toISOString()
        ];
    }

    protected function createErrorResponse(string $message, int $statusCode, string $requestId, ?string $responseBody = null, ?\Throwable $exception = null): array
    {
        $errorData = [
            'success' => false,
            'error' => [
                'message' => $message,
                'code' => $statusCode,
                'request_id' => $requestId,
                'timestamp' => now()->toISOString()
            ]
        ];

        if ($responseBody) {
            $parsedResponse = json_decode($responseBody, true);
            $errorData['error']['response'] = $parsedResponse ?: $responseBody;
        }

        $this->logError("API Request fehlgeschlagen [{$requestId}]", [
            'message' => $message,
            'status_code' => $statusCode,
            'response_body' => $responseBody,
            'exception' => $exception ? [
                'class' => get_class($exception),
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine()
            ] : null
        ]);

        return $errorData;
    }

    // Convenience-Methoden
    public function get(string $endpoint, array $params = []): array
    {
        $options = empty($params) ? [] : ['query' => $params];
        return $this->request('GET', $endpoint, $options);
    }

    public function post(string $endpoint, array $data = []): array
    {
        return $this->request('POST', $endpoint, ['json' => $data]);
    }

    public function patch(string $endpoint, array $data = []): array
    {
        return $this->request('PATCH', $endpoint, ['json' => $data]);
    }

    public function put(string $endpoint, array $data = []): array
    {
        return $this->request('PUT', $endpoint, ['json' => $data]);
    }

    public function delete(string $endpoint): array
    {
        return $this->request('DELETE', $endpoint);
    }

    // Helper-Methoden
    protected function isRetryableException(\Throwable $e): bool
    {
        return $e instanceof RequestException &&
               ($e->getCode() >= 500 || $e->getCode() === 0);
    }

    protected function getStatusMessage(int $statusCode): string
    {
        $messages = [
            400 => 'Bad Request - Ungültige Anfrage',
            401 => 'Unauthorized - Authentifizierung fehlgeschlagen',
            403 => 'Forbidden - Zugriff verweigert',
            404 => 'Not Found - Ressource nicht gefunden',
            429 => 'Too Many Requests - Rate Limit erreicht',
            500 => 'Internal Server Error - Server Fehler',
            502 => 'Bad Gateway - Gateway Fehler',
            503 => 'Service Unavailable - Service nicht verfügbar'
        ];

        return $messages[$statusCode] ?? 'Unbekannter HTTP Status';
    }

    protected function sanitizeForLogging(array $data): array
    {
        $sensitiveKeys = ['password', 'token', 'secret', 'key', 'client_secret', 'api_key'];

        return $this->sanitizeArray($data, $sensitiveKeys);
    }

    private function sanitizeArray(array $data, array $sensitiveKeys): array
    {
        $sanitized = [];

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $sanitized[$key] = $this->sanitizeArray($value, $sensitiveKeys);
            } elseif (in_array(strtolower($key), $sensitiveKeys, true)) {
                $sanitized[$key] = '***';
            } else {
                $sanitized[$key] = $value;
            }
        }

        return $sanitized;
    }

    // Logging-Methoden
    protected function logInfo(string $message, array $context = []): void
    {
        if ($this->enableDetailedLogging) {
            $this->writeToCustomLog($message, 0, $context);
        }
        Log::info("[{$this->logSystem}] {$message}", $context);
    }

    protected function logWarning(string $message, array $context = []): void
    {
        $this->writeToCustomLog($message, 5, $context);
        Log::warning("[{$this->logSystem}] {$message}", $context);
    }

    protected function logError(string $message, array $context = []): void
    {
        $this->writeToCustomLog($message, 10, $context);
        Log::error("[{$this->logSystem}] {$message}", $context);
    }

    protected function logDebug(string $message, array $context = []): void
    {
        if ($this->enableDetailedLogging) {
            Log::debug("[{$this->logSystem}] {$message}", $context);
        }
    }

    protected function logPerformance(string $message, float $startTime, array $context = []): void
    {
        if ($this->enablePerformanceLogging) {
            $duration = round((microtime(true) - $startTime) * 1000, 2);
            $context['duration_ms'] = $duration;
            $this->logInfo("{$message} (Dauer: {$duration}ms)", $context);
        }
    }

    protected function logException(string $message, \Throwable $e, array $context = []): void
    {
        $context['exception'] = [
            'class' => get_class($e),
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ];

        $this->logError($message, $context);
    }

    protected function writeToCustomLog(string $message, int $importance, array $context = []): void
    {
        try {
            CustomeLog::create([
                'importance' => $importance,
                'system' => $this->logSystem,
                'message' => $message,
                'debug' => json_encode($context, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
            ]);
        } catch (\Throwable $e) {
            Log::error("Fehler beim Schreiben in Custom Log: " . $e->getMessage());
        }
    }
}
