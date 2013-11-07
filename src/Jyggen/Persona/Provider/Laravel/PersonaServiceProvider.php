<?php
namespace Jyggen\Persona\Provider\Laravel;

use Auth;
use Illuminate\Auth\Guard;
use Illuminate\Support\ServiceProvider;
use Jyggen\Persona\Identity;
use Jyggen\Persona\Verifier;
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
        $this->package('jyggen/persona');
        Auth::extend('persona', function ($app) {
            return new Guard(new PersonaUserProvider, $app->make('session')->driver());
        });
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('persona.identity', function ($app, $assertion) {
            $app->log->debug('Identity using assertion "'.$assertion.'" created.');
            return new Identity($assertion);
        });

        $this->app->singleton('persona.verifier', function ($app, $endpoint) {
            $audience = sprintf('%s://%s:%u', Request::getScheme(), Request::getHost(), Request::getPort());
            $app->log->debug('Verifier using audience "'.$audience.'" created.');
            return (empty($endpoint)) ? new Verifier($audience) : new Verifier($audience, $endpoint);
        });

        $this->app->bind('persona.user', function ($app, $attributes) {
            $app->log->debug('New PersonaUser created.');
            return new PersonaUser($attributes);
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array('persona.identity', 'persona.verifier', 'persona.user');
    }
}
