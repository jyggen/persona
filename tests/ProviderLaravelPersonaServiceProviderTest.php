<?php
namespace Mozilla\Persona\Test;

use App;
use Mozilla\Persona\Provider\Laravel\PersonaServiceProvider;
use Orchestra\Testbench\TestCase;

class ProviderLaravelPersonaServiceProviderTest extends TestCase
{
    public function testConstruct()
    {
        $user = new PersonaServiceProvider(App::getFacadeRoot());
        $this->assertInstanceof('Mozilla\\Persona\\Provider\\Laravel\\PersonaServiceProvider', $user);
        $this->assertInstanceof('Illuminate\\Support\\ServiceProvider', $user);
    }

    public function testProvidesMatchRegistered()
    {

        $bindings = App::getBindings();
        $bindings = array_keys($bindings);

        $registered = array();
        foreach ($bindings as $binding) {
            if (preg_match('/^persona.([a-z]+)$/', $binding)) {
                $registered[] = $binding;
            }
        }

        $provider = new PersonaServiceProvider(App::getFacadeRoot());
        $exposed  = $provider->provides();

        sort($registered);
        sort($exposed);

        $this->assertSame($registered, $exposed);

    }

    public function testAuthDriver()
    {
        $guard = App::make('auth')->driver('persona');
        $this->assertInstanceof('Mozilla\\Persona\\Provider\\Laravel\\PersonaUserProvider', $guard->getProvider());
    }

    public function testPersonaVerifier()
    {
        $verifier = App::make('persona.verifier');
        $this->assertInstanceof('Mozilla\\Persona\\Verifier', $verifier);
        $this->assertSame('http://localhost:80', $verifier->getAudience());
    }

    public function testPersonaVerifierCustomEndpoint()
    {
        $verifier = App::make('persona.verifier', 'http://example.com');
        $this->assertInstanceof('Mozilla\\Persona\\Verifier', $verifier);
        $this->assertSame('http://localhost:80', $verifier->getAudience());
        $this->assertSame('http://example.com', $verifier->getEndpoint());
    }

    public function testPersonaIdentity()
    {
        $identity = App::make('persona.identity', 'assertion');
        $this->assertInstanceof('Mozilla\\Persona\\Identity', $identity);
        $this->assertSame('assertion', $identity->getAssertion());
    }

    public function testPersonaUser()
    {
        $user = App::make('persona.user', array('email' => 'test@example.com'));
        $this->assertInstanceof('Mozilla\\Persona\\Provider\\Laravel\\PersonaUser', $user);
        $this->assertSame('test@example.com', $user->email);
    }

    protected function getPackageProviders()
    {
        return array('Mozilla\Persona\Provider\Laravel\PersonaServiceProvider');
    }

    protected function getEnvironmentSetUp($app)
    {
        restore_error_handler();
    }
}
