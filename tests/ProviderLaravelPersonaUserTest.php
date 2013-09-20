<?php
namespace Mozilla\Persona\Test;

use Mozilla\Persona\Provider\Laravel\PersonaUser;
use Orchestra\Testbench\TestCase;

class ProviderLaravelPersonaUserTest extends TestCase
{
    public function testConstruct()
    {
        $user = new PersonaUser(array('email' => 'test@example.com'));
        $this->assertInstanceof('Mozilla\\Persona\\Provider\\Laravel\\PersonaUser', $user);
        $this->assertInstanceof('Illuminate\\Auth\\UserInterface', $user);
    }

    public function testGetAuthIdentifier()
    {
        $user = new PersonaUser(array('email' => 'test@example.com'));
        $this->assertSame('test@example.com', $user->getAuthIdentifier());
    }

    protected function getEnvironmentSetUp($app)
    {
        restore_error_handler();
    }
}
