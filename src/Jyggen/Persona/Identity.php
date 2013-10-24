<?php
namespace Jyggen\Persona;

use Exception;
use stdClass;

class Identity
{
    const STATUS_FAILURE = 'failure';
    const STATUS_SUCCESS = 'okay';

    protected $verified = false;

    protected $assertion;
    protected $audience;
    protected $email;
    protected $expires;
    protected $issuer;
    protected $reason;
    protected $status;

    public function __construct($assertion)
    {
        $this->assertion = $assertion;
    }

    public function getAssertion()
    {
        return $this->assertion;
    }

    public function getAudience()
    {
        if ($this->verified === false) {
            throw new Exception('This identity has not yet been verified.');
        }
        return $this->audience;
    }

    public function getEmail()
    {
        if ($this->verified === false) {
            throw new Exception('This identity has not yet been verified.');
        }
        return $this->email;
    }

    public function getExpires()
    {
        if ($this->verified === false) {
            throw new Exception('This identity has not yet been verified.');
        }
        return $this->expires;
    }

    public function getIssuer()
    {
        if ($this->verified === false) {
            throw new Exception('This identity has not yet been verified.');
        }
        return $this->issuer;
    }

    public function getReason()
    {
        if ($this->verified === false) {
            throw new Exception('This identity has not yet been verified.');
        }
        return $this->reason;
    }

    public function getStatus()
    {
        if ($this->verified === false) {
            throw new Exception('This identity has not yet been verified.');
        }
        return $this->status;
    }

    public function isInvalid()
    {
        if ($this->verified === false) {
            throw new Exception('This identity has not yet been verified.');
        }
        return $this->status === self::STATUS_FAILURE;
    }

    public function isValid()
    {
        if ($this->verified === false) {
            throw new Exception('This identity has not yet been verified.');
        }
        return $this->status === self::STATUS_SUCCESS;
    }

    public function isVerified()
    {
        return $this->verified;
    }

    public function parse(stdClass $response)
    {

        $this->status = $response->status;

        switch ($this->status) {
            case self::STATUS_SUCCESS:
                $this->audience = $response->audience;
                $this->email    = $response->email;
                $this->expires  = intval(floor($response->expires / 1000));
                $this->issuer   = $response->issuer;
                break;
            case self::STATUS_FAILURE:
                $this->reason = $response->reason;
                break;
        }

        $this->verified = true;

    }
}
