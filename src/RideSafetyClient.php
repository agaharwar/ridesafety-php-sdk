<?php

declare(strict_types=1);

namespace RideSafety;

final class RideSafetyException extends \RuntimeException
{
    public int $status;
    public mixed $details;

    public function __construct(int $status, string $message, mixed $details = null)
    {
        parent::__construct($message, $status);
        $this->status = $status;
        $this->details = $details;
    }
}

final class RideSafetyClient
{
    private string $apiKey;
    private string $baseUrl;
    private int $timeout;
    private string $authMode;

    public function __construct(
        string $apiKey,
        string $baseUrl = 'https://ridesafety.app',
        int $timeout = 20,
        string $authMode = 'bearer'
    ) {
        $this->apiKey = $apiKey;
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->timeout = $timeout;
        $this->authMode = $authMode;
    }

    public function getRecalls(array $params): array
    {
        return $this->request('GET', '/api/v1/recalls', $params, null);
    }

    public function runDiagnostics(array $payload): array
    {
        return $this->request('POST', '/api/v1/diagnostics', null, $payload);
    }

    public function getSafetyRatings(string $make, string $model, string|int $year): array
    {
        return $this->request('GET', '/api/v1/safety-ratings', [
            'make' => $make,
            'model' => $model,
            'year' => (string) $year,
        ], null);
    }

    private function request(string $method, string $path, ?array $query, ?array $body): array
    {
        $url = $this->baseUrl . $path;
        if ($query && count($query) > 0) {
            $url .= '?' . http_build_query(array_filter($query, fn ($value) => $value !== null && $value !== ''));
        }

        $ch = curl_init($url);
        $headers = [
            'Accept: application/json',
            'Content-Type: application/json',
        ];

        if ($this->authMode === 'x-api-key') {
            $headers[] = 'x-api-key: ' . $this->apiKey;
        } else {
            $headers[] = 'Authorization: Bearer ' . $this->apiKey;
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        if ($body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        }

        $response = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new RideSafetyException(0, $error ?: 'Request failed');
        }

        curl_close($ch);

        $decoded = json_decode($response, true);
        $data = is_array($decoded) ? $decoded : $response;

        if ($status >= 400) {
            $message = is_array($data) && isset($data['error']) ? (string) $data['error'] : 'Request failed';
            throw new RideSafetyException($status, $message, $data);
        }

        return is_array($data) ? $data : ['raw' => $data];
    }
}
