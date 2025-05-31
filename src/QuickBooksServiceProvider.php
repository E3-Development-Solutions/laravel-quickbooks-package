<?php

namespace E3DevelopmentSolutions\QuickBooks;

use QuickBooksOnline\API\DataService\DataService;
use Illuminate\Support\ServiceProvider;

class QuickBooksServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        // Merge configuration
        $this->mergeConfigFrom(
            __DIR__.'/../config/quickbooks.php', 'quickbooks'
        );

        // Register the main QuickBooks service
        $this->app->singleton('quickbooks', function ($app) {
            $config = $app['config']['quickbooks'];
            
            $dataService = DataService::Configure([
                'auth_mode' => $config['auth_mode'] ?? 'oauth2',
                'ClientID' => $config['client_id'],
                'ClientSecret' => $config['client_secret'],
                'RedirectURI' => $config['redirect_uri'],
                'scope' => $config['scope'],
                'baseUrl' => $config['base_url'] ?? 'development',
            ]);

            return new QuickBooks($dataService);
        });
    }


    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // Load routes
        $this->loadRoutesFrom(__DIR__.'/../../routes/web.php');

        // Publish configuration
        $this->publishes([
            __DIR__.'/../config/quickbooks.php' => config_path('quickbooks.php'),
        ], 'config');

        // Publish migrations
        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'migrations');

        // Load migrations
        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['quickbooks'];
    }
}
