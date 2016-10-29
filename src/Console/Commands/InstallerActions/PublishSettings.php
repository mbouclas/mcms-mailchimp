<?php

namespace Mcms\Mailchimp\Console\Commands\InstallerActions;


use Illuminate\Console\Command;


/**
 * @example php artisan vendor:publish --provider="Mcms\Mailchimp\McmsMailchimpServiceProvider" --tag=config
 * Class PublishSettings
 * @package Mcms\Mailchimp\Console\Commands\InstallerActions
 */
class PublishSettings
{
    /**
     * @param Command $command
     */
    public function handle(Command $command)
    {
        $command->call('vendor:publish', [
            '--provider' => 'Mcms\Mailchimp\McmsMailchimpServiceProvider',
            '--tag' => ['config'],
        ]);

        $command->comment('* Settings published');
    }
}