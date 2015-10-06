<?php
namespace Boo\Persona;

class Identity
{
    public $audience;
    public $email;
    public $expires;
    public $issuer;

    public function __construct(array $data)
    {
        $this->audience = $data['audience'];
        $this->email    = $data['email'];
        $this->expires  = $data['expires'];
        $this->issuer   = $data['issuer'];
    }

    public function isExpired()
    {
        return $this->expires <= time();
    }
}
