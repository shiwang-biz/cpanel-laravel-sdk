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

    /**
     * Proxy a call through WHM to a specific cPanel account's classic API 2
     * (or UAPI, apiVersion 3) module/function. Used for account-scoped
     * operations — e.g. addon/parked/subdomains — that have no direct WHM
     * API 1 equivalent and must be run in the context of a cPanel user.
     *
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    public function cpanelApiRequest(string $user, string $module, string $func, array $params = [], string $method = 'GET', int $apiVersion = 2): array
    {
        return $this->request('cpanel', array_merge($params, [
            'cpanel_jsonapi_user' => $user,
            'cpanel_jsonapi_apiversion' => $apiVersion,
            'cpanel_jsonapi_module' => $module,
            'cpanel_jsonapi_func' => $func,
        ]), $method);
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
        // Calls proxied through `cpanel` (classic API 2 / UAPI) wrap it in
        // `cpanelresult.event.result` instead.
        $result = $data['metadata']['result']
            ?? $data['result'][0]['status']
            ?? $data['cpanelresult']['event']['result']
            ?? null;

        if ($result === null) {
            return;
        }

        if ((int) $result !== 1) {
            $reason = $data['metadata']['reason']
                ?? $data['result'][0]['statusmsg']
                ?? $this->cpanelResultErrorReason($data)
                ?? 'Unknown WHM API error.';

            throw new WhmRequestException(
                "WHM API call [{$function}] failed: {$reason}",
                $data,
                $httpStatus,
            );
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function cpanelResultErrorReason(array $data): ?string
    {
        $errors = $data['cpanelresult']['event']['errors'] ?? null;

        return is_array($errors) ? implode('; ', $errors) : $errors;
    }
}
