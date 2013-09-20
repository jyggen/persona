<?php
namespace Mozilla\Persona\Provider\Laravel;

use App;
use Config;
use DB;
use Exception;
use Illuminate\Auth\UserInterface;
use Illuminate\Auth\UserProviderInterface;

class PersonaUserProvider implements UserProviderInterface
{

    const EVENT_LOGIN    = 'persona.login';
    const EVENT_REGISTER = 'persona.register';

    protected $identity;

    public function retrieveById($email)
    {

        $event = App::make('events');

        if ($event->hasListeners(self::EVENT_LOGIN)) {

            $user = $event->fire(self::EVENT_LOGIN, array($email));
            $user = $user[0];

            if (($user instanceof UserInterface) === false and $user !== null) {
                $type = (is_object($user)) ? get_class($user) : gettype($user);
                throw new Exception(
                    self::EVENT_LOGIN.' expected an instance of Illuminate\\Auth\\UserInterface, '.$type.' received.'
                );
            }

        } else {
            $user = DB::table(Config::get('auth.table'))->where('email', $email)->first();
            $user = ($user !== null) ? new PersonaUser((array) $user) : $user;
        }

        return $user;

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

            $user = $this->retrieveById($identity->getEmail());

            if ($user === null) {
                $user = $this->createUser($identity->getEmail());
            }

            $this->identity = $identity;

            return $user;

        }

        return null;

    }

    public function validateCredentials(UserInterface $user, array $credentials)
    {

        unset($credentials);

        if ($this->identity === null) {
            throw new Exception('You should not call PersonaUserProvider::validateCredentials() directly.');
        }

        return ($user->getAuthIdentifier() === $this->identity->getEmail());

    }

    protected function createUser($email)
    {

        $event = App::make('events');

        if ($event->hasListeners(self::EVENT_REGISTER)) {

            $user = $event->fire(self::EVENT_REGISTER, array($email));
            $user = $user[0];

            if (($user instanceof UserInterface) === false and $user !== null) {
                $type = (is_object($user[0])) ? get_class($user[0]) : gettype($user[0]);
                throw new Exception(
                    self::EVENT_LOGIN.' expected an instance of Illuminate\\Auth\\UserInterface, '.$type.' received.'
                );
            }

        } else {
            DB::table(Config::get('auth.table'))->insert(array('email' => $email));
            $user = $this->retrieveById($email);
        }

        return $user;

    }
}
