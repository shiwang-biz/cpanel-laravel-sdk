<?php

namespace Shiwang\CpanelLaravelSdk;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Shiwang\CpanelLaravelSdk\Exceptions\WhmRequestException;

class WhmClient
{
    public function __construct(
        protected string $host,
        protected int $port,
        protected string $username,
        protected string $password,
        protected bool $verifySsl = true,
        protected int $timeout = 30,
    ) {
    }

    /**
     * Call a WHM API 1 function.
     *
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    public function request(string $function, array $params = [], string $method = 'GET'): array
    {
        $url = sprintf('https://%s:%d/json-api/%s', $this->host, $this->port, $function);

        $params = array_merge(['api.version' => 1], $params);

        $request = $this->pendingRequest();

        $response = strtoupper($method) === 'POST'
            ? $request->asForm()->post($url, $params)
            : $request->get($url, $params);

        if ($response->failed()) {
            throw new WhmRequestException(
                "WHM API request to [{$function}] failed with HTTP status {$response->status()}.",
                (array) $response->json(),
                $response->status(),
            );
        }

        $data = (array) $response->json();

        $this->assertSuccessful($function, $data, $response->status());

        return $data;
    }

    protected function pendingRequest(): PendingRequest
    {
        return Http::withBasicAuth($this->username, $this->password)
            ->withOptions(['verify' => $this->verifySsl])
            ->timeout($this->timeout)
            ->acceptJson();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function assertSuccessful(string $function, array $data, int $httpStatus): void
    {
        // Modern WHM API 1 functions wrap the result in `metadata.result`.
        // Some legacy functions instead return a `result[0].status` envelope.
        $result = $data['metadata']['result'] ?? $data['result'][0]['status'] ?? null;

        if ($result === null) {
            return;
        }

        if ((int) $result !== 1) {
            $reason = $data['metadata']['reason']
                ?? $data['result'][0]['statusmsg']
                ?? 'Unknown WHM API error.';

            throw new WhmRequestException(
                "WHM API call [{$function}] failed: {$reason}",
                $data,
                $httpStatus,
            );
        }
    }
}
