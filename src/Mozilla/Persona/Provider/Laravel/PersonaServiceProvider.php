<?php
namespace Mozilla\Persona\Provider\Laravel;

use Auth;
use Illuminate\Auth\Guard;
use Illuminate\Support\ServiceProvider;
use Mozilla\Persona\Identity;
use Mozilla\Persona\Verifier;
use Request;

class PersonaServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->package('mozilla/persona');
        Auth::extend(
            'persona',
            function () {
                return new Guard(new PersonaUserProvider, $this->app->make('session'));
            }
        );
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(
            'persona.verifier',
            function ($app, $endpoint) {
                $audience = sprintf('%s://%s:%u', Request::getScheme(), Request::getHost(), Request::getPort());
                return (empty($endpoint)) ? new Verifier($audience) : new Verifier($audience, $endpoint);
            }
        );
        $this->app->bind(
            'persona.identity',
            function ($app, $assertion) {
                return new Identity($assertion);
            }
        );
        $this->app->bind(
            'persona.user',
            function ($app, $attributes) {
                return new PersonaUser($attributes);
            }
        );
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array('persona.verifier', 'persona.identity', 'persona.user');
    }
}
