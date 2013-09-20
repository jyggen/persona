<?php
namespace Mozilla\Persona\Test;

use Mockery;
use Mozilla\Persona\Verifier as Verifier;
use PHPUnit_Framework_TestCase as TestCase;

class VerifierTest extends TestCase
{
    public function tearDown()
    {
        Mockery::close();
    }

    public function testConstructor()
    {
        $verifier = new Verifier('http://example.com');
        $this->assertInstanceof('Mozilla\\Persona\\Verifier', $verifier);
    }

    /**
     * @expectedException PHPUnit_Framework_Error
     */
    public function testConstructorWithoutAudience()
    {
        new Verifier();
    }

    public function testVerify()
    {

        $identity = Mockery::mock('Mozilla\\Persona\\Identity');
        $identity->shouldReceive('getAssertion')->times(1)->andReturn('assertion');
        $identity->shouldReceive('parse')->with('stdClass')->times(1);

        $verifier = new Verifier('http://example.com');
        $verifier->verify($identity);

    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Persona returned unexpected data.
     */
    public function testVerifyWithInvalidResponse()
    {

        $identity = Mockery::mock('Mozilla\\Persona\\Identity');
        $identity->shouldReceive('getAssertion')->times(1)->andReturn('assertion');

        $verifier = new Verifier('http://example.com', 'http://example.com');
        $verifier->verify($identity);

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
        $verifier = new Verifier('http://example.com', 'http://example.com');
        $this->assertSame('http://example.com', $verifier->getEndpoint());
    }
}