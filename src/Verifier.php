<?php
namespace Boo\Persona;

use Boo\Persona\Exceptions\JsonException;
use Boo\Persona\Exceptions\PersonaException;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;

class Verifier
{
    const ENDPOINT = 'https://verifier.login.persona.org/verify';
    const STATUS_FAILURE = 'failure';
    const STATUS_SUCCESS = 'okay';

    protected $audience;
    protected $client;
    protected $endpoint;

    public function __construct($audience, ClientInterface $client = null)
    {
        $this->audience = $audience;
        $this->client   = $client ?: new Client;
        $this->endpoint = self::ENDPOINT;
    }

    public function getAudience()
    {
        return $this->audience;
    }

    public function getClient()
    {
        return $this->client;
    }

    public function getEndpoint()
    {
        return $this->endpoint;
    }

    public function verify($assertion)
    {
        $response = $this->client->post($this->endpoint, [
            'json' => [
                'assertion' => $assertion,
                'audience'  => $this->audience,
            ],
        ]);

        $json = json_decode($response->getBody());

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new JsonException(json_last_error_msg());
        }

        switch ($json->status) {
            case self::STATUS_SUCCESS:
                if ($json->audience !== $this->audience) {
                    throw new PersonaException('Audience does not match server.');
                }

                return new Identity([
                    'audience' => $json->audience,
                    'email'    => $json->email,
                    'expires'  => (int) floor($json->expires / 1000),
                    'issuer'   => $json->issuer,
                ]);
            case self::STATUS_FAILURE:
                throw new PersonaException($json->reason);
        }
    }

    public function setAudience($audience)
    {
        $this->audience = $audience;
    }

    public function setClient($client)
    {
        $this->client = $client;
    }

    public function setEndpoint($endpoint)
    {
        $this->endpoint = $endpoint;
    }
}
