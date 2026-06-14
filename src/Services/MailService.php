<?php

namespace Reloop\Services;

use GuzzleHttp\RequestOptions;
use Reloop\ReloopClient;
use Reloop\SendMailResponse;
use Reloop\Support\Parameters;
use Reloop\Support\ResourceFactory;

class MailService
{
    public function __construct(private ReloopClient $client)
    {
    }

    public function send(array $parameters): SendMailResponse
    {
        $data = $this->client->request('POST', '/api/mail/v1/send', [
            RequestOptions::JSON => Parameters::forSnakeRequest($parameters),
        ]);

        return ResourceFactory::sendMail($data);
    }
}
