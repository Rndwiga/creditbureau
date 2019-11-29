<?php

namespace Rndwiga\CreditBureau\Providers;

use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Rndwiga\CreditBureau\Console\InstallCommand;
use Rndwiga\CreditBureau\Console\MigrateCommand;


class ModuleServiceProvider extends ServiceProvider
{
    protected static $packageName = 'CreditBureau';

    protected $providers = [];

    protected $aliases = [];

    protected $commands = [
        InstallCommand::class,
        MigrateCommand::class
    ];

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/../Resources/Views', self::$packageName);
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerServiceProviders();
        $this->registerAliases();
        $this->registerCommands();
        $this->registerConfigs();
        $this->registerRoutes();
        $this->registerPublishable();
    }


    private function registerServiceProviders()
    {
        foreach ($this->providers as $provider)
        {
            $this->app->register($provider);
        }
    }
    private function registerAliases()
    {
        $loader = AliasLoader::getInstance();
        foreach ($this->aliases as $key => $alias)
        {
            $loader->alias($key, $alias);
        }
    }
    private function registerConfigs()
    {
        $this->mergeConfigFrom(
            __DIR__."/../Config/".self::$packageName.".php", self::$packageName
        );
    }
    private function registerCommands(){
        $this->commands($this->commands);
    }

    private function registerRoutes(){

        Route::namespace('Rndwiga\CreditBureau\Api\Http\Controllers')
            ->middleware(['api'])
            ->group(function () {
                $this->loadRoutesFrom(__DIR__.'/../Routes/api.php');
            });
    }

    private function registerPublishable(){

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__."/../Config/".self::$packageName.".php" => config_path(self::$packageName.'.php'),
            ], self::$packageName.'-config');
        }
    }
}
