<?php
namespace Jyggen\Persona\Test;

use App;
use DB;
use Event;
use Schema;
use Jyggen\Persona\Provider\Laravel\PersonaUser;
use Jyggen\Persona\Provider\Laravel\PersonaUserProvider;
use Orchestra\Testbench\TestCase;

class ProviderLaravelPersonaUserProviderTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        Schema::create(
            'users',
            function ($table) {
                $table->increments('id');
                $table->string('email');
            }
        );
    }

    public function tearDown()
    {
        Schema::drop('users');
        parent::tearDown();
    }

    public function testConstruct()
    {
        $provider = new PersonaUserProvider;
        $this->assertInstanceof('Jyggen\\Persona\\Provider\\Laravel\\PersonaUserProvider', $provider);
        $this->assertInstanceof('Illuminate\\Auth\\UserProviderInterface', $provider);
    }

    public function testRetrieveById()
    {
        $provider = new PersonaUserProvider;
        $user = $provider->retrieveById('test@example.com');
        $this->assertSame(null, $user);
        DB::table('users')->insert(array('email' => 'test@example.com'));
        $user = $provider->retrieveById('test@example.com');
        $this->assertInstanceof('Illuminate\\Auth\\UserInterface', $user);
        $this->assertSame('test@example.com', $user->email);
    }

    public function testRetrieveByIdCustomEvent()
    {
        Event::listen(
            'persona.login',
            function () {
                return new PersonaUser(array('email' => 'another@example.com'));
            }
        );
        $provider = new PersonaUserProvider;
        $user = $provider->retrieveById('test@example.com');
        $this->assertInstanceof('Illuminate\\Auth\\UserInterface', $user);
        $this->assertSame('another@example.com', $user->email);
    }

    public function testRetrieveByIdCustomEventNotFound()
    {
        Event::listen(
            'persona.login',
            function () {
                return null;
            }
        );
        $provider = new PersonaUserProvider;
        $user = $provider->retrieveById('test@example.com');
        $this->assertSame(null, $user);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Illuminate\Auth\UserInterface
     */
    public function testRetrieveByIdCustomEventInvalidTypeReturned()
    {
        Event::listen(
            'persona.login',
            function () {
                return array();
            }
        );
        $provider = new PersonaUserProvider;
        $provider->retrieveById('test@example.com');
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage validateCredentials()
     */
    public function testRetrieveValidateCredentialsDirectly()
    {

        $provider = new PersonaUserProvider;
        $user     = new PersonaUser(array('email' => 'test@example.com'));
        $provider->validateCredentials($user, array('assertion' => 'assertion'));

    }

    protected function getEnvironmentSetUp($app)
    {
        restore_error_handler();
        $app->make('config')->set('database.default', 'test');
        $app->make('config')->set(
            'database.connections.test',
            array(
                'driver'   => 'sqlite',
                'database' => ':memory:',
                'prefix'   => '',
            )
        );
    }
}
