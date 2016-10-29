<?php

namespace Mcms\Mailchimp\StartUp;



use Mcms\Mailchimp\Middleware\PublishPage;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

/**
 * Class RegisterMiddleware
 * @package Mcms\Mailchimp\StartUp
 */
class RegisterMiddleware
{

    /**
     * Register all your middleware here
     * @param ServiceProvider $serviceProvider
     * @param Router $router
     */
    public function handle(ServiceProvider $serviceProvider, Router $router)
    {

    }
}