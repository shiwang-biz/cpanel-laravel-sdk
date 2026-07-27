<?php

namespace Shiwang\CpanelLaravelSdk\Modules;

use Shiwang\CpanelLaravelSdk\WhmClient;

class PackageManager
{
    public function __construct(protected WhmClient $client)
    {
    }

    /**
     * Create a new hosting package.
     *
     * Required keys: name. Common optional keys: quota, bwlimit, maxftp,
     * maxsql, maxpop, maxlists, maxsub, maxpark, maxaddon, ip, cgi,
     * hasshell, cpmod, featurelist, language, maxemailsperday.
     *
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    public function create(array $params): array
    {
        return $this->client->request('addpkg', $params, 'POST');
    }

    /**
     * Update an existing hosting package's limits/settings.
     *
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    public function update(string $name, array $params): array
    {
        return $this->client->request('editpkg', array_merge($params, [
            'name' => $name,
        ]), 'POST');
    }

    /**
     * @return array<string, mixed>
     */
    public function delete(string $name): array
    {
        return $this->client->request('killpkg', [
            'pkg' => $name,
        ], 'POST');
    }

    /**
     * List hosting packages.
     *
     * @return array<string, mixed>
     */
    public function list(): array
    {
        return $this->client->request('listpkgs');
    }
}
