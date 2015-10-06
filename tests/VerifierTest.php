<?php
namespace Boo\Persona;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PHPUnit_Framework_TestCase;

class VerifierTest extends PHPUnit_Framework_TestCase
{
    protected $responses = [
        'valid'              => '{"email": "foobar@example.com", "status": "okay", "audience": "http://example.com", "expires": 1308859352261, "issuer": "example.com"}',
        'invalid_json'       => 'foobar',
        'incorrect_audience' => '{"email": "foobar@example.com", "status": "okay", "audience": "incorrect"}',
        'invalid'            => '{"status":"failure","reason":"no certificates provided"}',
    ];

    public function testThatClassCanBeConstructed()
    {
        $verifier = new Verifier('http://example.com');
        $this->assertInstanceof(Verifier::class, $verifier);
    }

    /**
     * @expectedException PHPUnit_Framework_Error
     */
    public function testThatClassCanNotBeCosntructedWithoutAudience()
    {
        new Verifier;
    }

    public function testVerifyWithValidResponse()
    {
        $container = [];
        $client    = $this->getGuzzleClient($container, $this->responses['valid']);
        $verifier  = new Verifier('http://example.com', $client);

        $verifier->verify('foobar');

        $request  = $container[0]['request'];
        $body     = json_decode($request->getBody());
        $response = $container[0]['response'];

        $this->assertSame('POST', $request->getMethod());
        $this->assertSame(Verifier::ENDPOINT, (string) $request->getUri());
        $this->assertSame('foobar', $body->assertion);
        $this->assertSame('http://example.com', $body->audience);
        $this->assertSame($this->responses['valid'], (string) $response->getBody());
    }

    /**
     * @expectedException Boo\Persona\Exceptions\JsonException
     */
    public function testVerifyWithInvalidJsonResponse()
    {
        $container = [];
        $client    = $this->getGuzzleClient($container, $this->responses['invalid_json']);
        $verifier  = new Verifier('http://example.com', $client);

        $verifier->verify('foobar');
    }

    /**
     * @expectedException Boo\Persona\Exceptions\PersonaException
     */
    public function testVerifyWithIncorrectAudienceResponse()
    {
        $container = [];
        $client    = $this->getGuzzleClient($container, $this->responses['incorrect_audience']);
        $verifier  = new Verifier('http://example.com', $client);

        $verifier->verify('foobar');
    }

    /**
     * @expectedException GuzzleHttp\Exception\ServerException
     */
    public function testVerifyWithServerErrorResponse()
    {
        $container = [];
        $client    = $this->getGuzzleClient($container, '', 500);
        $verifier  = new Verifier('http://example.com', $client);

        $verifier->verify('foobar');
    }

    /**
     * @expectedException Boo\Persona\Exceptions\PersonaException
     */
    public function testVerifyWithInvalideResponse()
    {
        $container = [];
        $client    = $this->getGuzzleClient($container, $this->responses['invalid']);
        $verifier  = new Verifier('http://example.com', $client);

        $verifier->verify('foobar');
    }

    public function testGetAudience()
    {
        $verifier = new Verifier('http://example.com');
        $this->assertSame('http://example.com', $verifier->getAudience());
    }

    public function testGetEndpoint()
    {
        $verifier = new Verifier('http://example.com');
        $this->assertSame(Verifier::ENDPOINT, $verifier->getEndpoint());
    }

    protected function getGuzzleClient(&$container, $body, $code = 200)
    {
        $history  = Middleware::history($container);
        $response = new Response($code, [], $body);
        $handler  = HandlerStack::create(new MockHandler([$response]));

        $handler->push($history);

        return new Client(['handler' => $handler]);
    }
}
