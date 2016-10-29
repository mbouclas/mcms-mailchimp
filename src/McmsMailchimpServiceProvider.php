<?php

namespace Mcms\Mailchimp;


use Mcms\Mailchimp\Service\MailchimpListCollection;
use Mcms\Mailchimp\Service\MailchimpService;
use Mcms\Mailchimp\StartUp\RegisterAdminPackage;
use Mcms\Mailchimp\StartUp\RegisterEvents;
use Mcms\Mailchimp\StartUp\RegisterFacades;
use Mcms\Mailchimp\StartUp\RegisterMiddleware;
use Mcms\Mailchimp\StartUp\RegisterServiceProviders;
use Mcms\Mailchimp\StartUp\RegisterSettingsManager;
use Mcms\Mailchimp\StartUp\RegisterWidgets;
use Illuminate\Support\ServiceProvider;
use DrewM\MailChimp\MailChimp;
use \Installer, \Widget;
use Illuminate\Contracts\Events\Dispatcher as DispatcherContract;
use Illuminate\Contracts\Auth\Access\Gate as GateContract;
use Illuminate\Routing\Router;

class McmsMailchimpServiceProvider extends ServiceProvider
{
    /**
     * @var array
     */
    protected $commands = [
        \Mcms\Mailchimp\Console\Commands\Install::class,
        \Mcms\Mailchimp\Console\Commands\RefreshAssets::class,
    ];

    public $packageName = 'mcms-mailchimp';

    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot(DispatcherContract $mailchimp, GateContract $gate, Router $router)
    {
        $this->publishes([
            __DIR__ . '/../config/config.php' => config_path('mcmsMailchimp.php'),
        ], 'config');

        $this->publishes([
            __DIR__ . '/../database/migrations/' => database_path('migrations')
        ], 'migrations');

        $this->publishes([
            __DIR__ . '/../database/seeds/' => database_path('seeds')
        ], 'seeds');

        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/mcms/mailchimp'),
        ], 'views');

        $this->publishes([
            __DIR__ . '/../resources/lang' => resource_path('lang'),
        ], 'lang');

        $this->publishes([
            __DIR__ . '/../resources/public' => public_path('vendor/mcms/mailchimp'),
        ], 'public');

        $this->publishes([
            __DIR__ . '/../resources/assets' => public_path('vendor/mcms/mailchimp'),
        ], 'assets');

        $this->publishes([
            __DIR__ . '/../config/admin.package.json' => storage_path('app/mcms/mailchimp/admin.package.json'),
        ], 'admin-package');


        if (!$this->app->routesAreCached()) {
            $router->group([
                'middleware' => 'web',
            ], function ($router) {
                require __DIR__.'/Http/routes.php';
            });

            $this->loadViewsFrom(__DIR__ . '/../resources/views', 'mcmsMailchimp');
        }

        /**
         * Register any widgets
         */
        (new RegisterWidgets())->handle();

        /**
         * Register Events
         */
//        parent::boot($mailchimp);
        (new RegisterEvents())->handle($this, $mailchimp);

        /*
         * Register dependencies
        */
        (new RegisterServiceProviders())->handle();

        /*
         * Register middleware
        */
        (new RegisterMiddleware())->handle($this, $router);


        /**
         * Register admin package
         */
        (new RegisterAdminPackage())->handle($this);

        (new RegisterSettingsManager())->handle($this);
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        /*
        * Register Commands
        */
        $this->commands($this->commands);

        /**
         * Register Facades
         */
        (new RegisterFacades())->handle($this);

        /**
         * Register installer
         */
        Installer::register(\Mcms\Mailchimp\Installer\Install::class);

    }
}
