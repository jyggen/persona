<?php
namespace Mozilla\Persona;

use Exception;

class Verifier
{
    const ENDPOINT = 'https://verifier.login.persona.org/verify';

    protected $audience;
    protected $endpoint;

    public function __construct($audience, $endpoint = self::ENDPOINT)
    {
        $this->audience = $audience;
        $this->endpoint = $endpoint;
    }

    public function getAudience()
    {
        return $this->audience;
    }

    public function getEndpoint()
    {
        return $this->endpoint;
    }

    public function verify(Identity $identity)
    {

        $curl     = curl_init($this->endpoint);
        $jsonData = json_encode(array('assertion' => $identity->getAssertion(), 'audience' => $this->audience));
        $headers  = array('Content-type: application/json', 'Content-length: '.strlen($jsonData));

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 0);

        $response = curl_exec($curl);

        if ($response === false) {
            throw new Exception(curl_error($curl));
        }

        curl_close($curl);

        $json = json_decode($response);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Persona returned unexpected data.');
        }

        $identity->parse($json);

    }
}
