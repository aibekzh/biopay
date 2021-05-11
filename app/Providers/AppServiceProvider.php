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



https://tk.adata.kz/api/email/verify/96?
//expires=1620734573&
//hash=67285701f42df20469b7947e7410b5ab03fde8ce&
//refresh_token=def50200a9d26fe23fc29b407bb140c490f95811f257d79904f5dc44d343010f56b7c368e2eb4c22d5d2a72ff016c4b1695aa05d08bb1c0c568a8fec1c543547277c5aea999ee4750372480cbe4a03f3c10989e0a6d3b65cfd8190374f7b37e5d2775c7f94bc285aa2b8b01de5fe0064401238dd3d2d6c8ff6b1f09a894d298e9f22906d7bce55e18bd05633623c9b3d6397ef9d98d56456452364a38cdadd4417249de8f4618371b2d3198d8d40be2c39c1f38aeae4e268db1432ba4358612234480f1c2bf122978895d70592bab1683a242034b414afd9c70801fd2119deecd60e896e1b80f64fc52fb91071b19713dc8a8adf554d89f2623581870018327df9ee017562026208b27f86e521c1913201f3c7d09d9c42a4dccd501f604589c943a05be5c485307bb9f467eaaabaefef0bd1ec9cdf22d296deab1f9370c6a45bfed039130a8f2504561616c0a5ea3b3067c349c141c0272040f247b8ed44e826b9ad&signature=05f0893bd8b1f74e9893606ea79cd1f9f1e1c50fdb4c897e5d2f1225c06791c7
