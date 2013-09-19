<?php
namespace Mozilla\Persona\Test;

use Mockery;
use Mozilla\Persona\Identity as Identity;
use PHPUnit_Framework_TestCase as TestCase;
use stdClass;

class IdentityTest extends TestCase
{
    public function tearDown()
    {
        Mockery::close();
    }

    public function testConstructor()
    {
        $identity = new Identity('assertion');
        $this->assertInstanceof('Mozilla\\Persona\\Identity', $identity);
    }

    /**
     * @expectedException PHPUnit_Framework_Error
     */
    public function testConstructorWithoutAssertion()
    {
        new Identity();
    }

    public function testIsVerified()
    {
        $identity = new Identity('assertion');
        $this->assertFalse($identity->isVerified());
    }

    public function testGetAssertion()
    {
        $verifier = new Identity('assertion');
        $this->assertSame('assertion', $verifier->getAssertion());
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage This identity has not yet been verified.
     */
    public function testGetAudienceBeforeVerify()
    {
        $identity = new Identity('assertion');
        $identity->getAudience();
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage This identity has not yet been verified.
     */
    public function testGetEmailBeforeVerify()
    {
        $identity = new Identity('assertion');
        $identity->getEmail();
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage This identity has not yet been verified.
     */
    public function testGetExpiresBeforeVerify()
    {
        $identity = new Identity('assertion');
        $identity->getExpires();
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage This identity has not yet been verified.
     */
    public function testGetIssuerBeforeVerify()
    {
        $identity = new Identity('assertion');
        $identity->getIssuer();
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage This identity has not yet been verified.
     */
    public function testGetReasonBeforeVerify()
    {
        $identity = new Identity('assertion');
        $identity->getReason();
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage This identity has not yet been verified.
     */
    public function testGetStatusBeforeVerify()
    {
        $identity = new Identity('assertion');
        $identity->getStatus();
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage This identity has not yet been verified.
     */
    public function testIsInvalidBeforeVerify()
    {
        $identity = new Identity('assertion');
        $identity->isInvalid();
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage This identity has not yet been verified.
     */
    public function testIsValidBeforeVerify()
    {
        $identity = new Identity('assertion');
        $identity->isValid();
    }

    public function testParseFailure()
    {
        $identity = new Identity('assertion');
        $identity->parse($this->getFailureIdentity());
        $this->assertTrue($identity->isVerified());
        $this->assertFalse($identity->isValid());
        $this->assertTrue($identity->isInvalid());
        $this->assertSame(Identity::STATUS_FAILURE, $identity->getStatus());
        $this->assertSame('assertion has expired', $identity->getReason());
    }

    public function testparseSuccess()
    {
        $identity = new Identity('assertion');
        $identity->parse($this->getSuccessIdentity());
        $this->assertTrue($identity->isVerified());
        $this->assertTrue($identity->isValid());
        $this->assertFalse($identity->isInvalid());
        $this->assertSame(Identity::STATUS_SUCCESS, $identity->getStatus());
        $this->assertSame('example.com', $identity->getAudience());
        $this->assertSame('example@example.com', $identity->getEmail());
        $this->assertSame(1379591414, $identity->getExpires());
        $this->assertSame('example.login.persona.org', $identity->getIssuer());
    }

    protected function getFailureIdentity()
    {
        $identity = new StdClass;
        $identity->status = Identity::STATUS_FAILURE;
        $identity->reason = 'assertion has expired';
        return $identity;
    }

    protected function getSuccessIdentity()
    {
        $identity = new StdClass;
        $identity->status   = Identity::STATUS_SUCCESS;
        $identity->audience = 'example.com';
        $identity->email    = 'example@example.com';
        $identity->expires  = 1379591414580;
        $identity->issuer   = 'example.login.persona.org';
        return $identity;
    }
}
