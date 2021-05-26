<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
//        $this->app->bind("League\OAuth2\Server\Repositories\ClientRepositoryInterface", "App\Classes\Authorization");
        $this->app->bind(ExternalApiHelper::class, function() {
            return new ExternalApiHelper('Hello, app!');
        });
    }

    public function boot()
    {
            $this->app['request']->server->set('HTTPS', true);
            \URL::forceScheme('https');
    }
}
