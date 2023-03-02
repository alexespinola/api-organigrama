<?php

namespace apiOrganigrama;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

class ApiOrganigramaServiceProvider extends ServiceProvider
{
  
    public function register()
    {
        // $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'loginCuentas');
    }

    public function boot()
    {
        // $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        // $this->loadViewsFrom(__DIR__.'/../resources/views', 'loginCuentas');
        // $router = $this->app->make(Router::class);
    }

}