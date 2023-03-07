<?php

namespace apiOrganigrama;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

class ApiOrganigramaServiceProvider extends ServiceProvider
{

  public function register()
  {
    $this->mergeConfigFrom(__DIR__.'/../config/api.php', 'apiOrganigrama');
  }

  public function boot()
  {
    $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
    $this->loadViewsFrom(__DIR__.'/../resources/views', 'apiOrganigrama');
    // $router = $this->app->make(Router::class);
    $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

    if ($this->app->runningInConsole()) {
      // Publish assets
      $this->publishes([
        __DIR__.'/../resources/assets' => public_path('apiOrganigrama'),
      ], 'assets');

    }

  }

}
