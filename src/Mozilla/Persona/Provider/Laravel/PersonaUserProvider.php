<?php
namespace Mozilla\Persona\Provider\Laravel;

use App;
use Exception;
use Illuminate\Auth\UserInterface;
use Illuminate\Auth\UserProviderInterface;

class PersonaUserProvider implements UserProviderInterface
{

    protected $identity;

    public function retrieveById($identifier)
    {
        dd('retrieveById', $identifier);
    }

    public function retrieveByCredentials(array $credentials)
    {
        if (isset($credentials['assertion']) === false) {
            throw new Exception('Missing assertion.');
        }

        $identity = App::make('persona.identity', $credentials['assertion']);
        $verifier = App::make('persona.verifier');

        $verifier->verify($identity);

        if ($identity->isValid()) {

            $user = App::make('events')->fire('persona.login', array($identity));

            if (isset($user[0]) === false) {
                throw new Exception(
                    'You must register an observer of the event "persona.login" to use Mozilla Persona authentication.'
                );
            } elseif (($user[0] instanceof UserInterface) === false) {
                $type = (is_object($user[0])) ? get_class($user[0]) : gettype($user[0]);
                throw new Exception(
                    '"persona.login" expected an instance of Illuminate\\Auth\\UserInterface, '.$type.' received.'
                );
            }

            $this->identity = $identity;

            return $user[0];

        }

        return null;

    }

    public function validateCredentials(UserInterface $user, array $credentials)
    {
        return ($user->getAuthIdentifier() === $this->identity->getEmail());
    }
}
