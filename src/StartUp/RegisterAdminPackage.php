<?php

namespace Mcms\Mailchimp\StartUp;

use Illuminate\Support\ServiceProvider;
use ModuleRegistry;

class RegisterAdminPackage
{
    public function handle(ServiceProvider $serviceProvider)
    {
        ModuleRegistry::registerModule($serviceProvider->packageName . '/admin.package.json');

    }
}