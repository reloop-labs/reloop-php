<?php

namespace Reloop\Services;

use GuzzleHttp\RequestOptions;
use Reloop\Domain;
use Reloop\DomainList;
use Reloop\DomainNameservers;
use Reloop\DomainStatus;
use Reloop\ForwardDnsResponse;
use Reloop\ReloopClient;
use Reloop\Support\Parameters;
use Reloop\Support\ResourceFactory;

class DomainService
{
    public function __construct(private ReloopClient $client)
    {
    }

    public function create(array $parameters): Domain
    {
        $data = $this->client->request('POST', '/api/domain/v1/create', [
            RequestOptions::JSON => Parameters::forSnakeRequest($parameters),
        ]);

        return ResourceFactory::domain($data);
    }

    public function list(array $options = []): DomainList
    {
        $data = $this->client->request('GET', '/api/domain/v1/list', [
            RequestOptions::QUERY => Parameters::forQuery($options),
        ]);

        return ResourceFactory::domainList($data);
    }

    public function get(string $domainId): Domain
    {
        $data = $this->client->request('GET', "/api/domain/v1/{$domainId}");

        return ResourceFactory::domain($data);
    }

    public function getNameservers(string $domainId): DomainNameservers
    {
        $data = $this->client->request('GET', "/api/domain/v1/nameservers/{$domainId}");

        return ResourceFactory::domainNameservers($data);
    }

    public function update(string $domainId, array $parameters): Domain
    {
        $data = $this->client->request('PATCH', "/api/domain/v1/{$domainId}", [
            RequestOptions::JSON => Parameters::forSnakeRequest($parameters),
        ]);

        return ResourceFactory::domain($data);
    }

    public function delete(string $domainId): Domain
    {
        $data = $this->client->request('DELETE', "/api/domain/v1/{$domainId}");

        return ResourceFactory::domain($data);
    }

    public function verify(string $domainId): DomainStatus
    {
        $data = $this->client->request('POST', "/api/domain/v1/verify/{$domainId}");

        return ResourceFactory::domainStatus($data);
    }

    public function forwardDns(string $domainId, array $parameters): ForwardDnsResponse
    {
        $data = $this->client->request(
            'POST',
            "/api/domain/v1/verify/{$domainId}/forward-dns",
            [
                RequestOptions::JSON => Parameters::forSnakeRequest($parameters),
            ],
        );

        return ResourceFactory::forwardDnsResponse($data);
    }
}
