<?php

namespace Mcms\Mailchimp\StartUp;


use App;
use Illuminate\Support\ServiceProvider;

/**
 * Register your Facades/aliases here
 * Class RegisterFacades
 * @package Mcms\Mailchimp\StartUp
 */
class RegisterFacades
{
    /**
     * @param ServiceProvider $serviceProvider
     */
    public function handle(ServiceProvider $serviceProvider)
    {

        /**
         * Register Facades
         */
        $facades = \Illuminate\Foundation\AliasLoader::getInstance();
//        $facades->alias('ModuleRegistry', \Mcms\Mailchimp\Facades\ModuleRegistryFacade::class);
        
    }
}