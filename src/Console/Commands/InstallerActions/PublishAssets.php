<?php

namespace Mcms\Mailchimp\Console\Commands\InstallerActions;


use Illuminate\Console\Command;

/**
 * Class PublishAssets
 * @package Mcms\Mailchimp\Console\Commands\InstallerActions
 */
class PublishAssets
{
    /**
     * @param Command $command
     */
    public function handle(Command $command)
    {
        $command->call('vendor:publish', [
            '--provider' => 'Mcms\Mailchimp\McmsMailchimpServiceProvider',
            '--tag' => ['public'],
        ]);

        $command->call('vendor:publish', [
            '--provider' => 'Mcms\Mailchimp\McmsMailchimpServiceProvider',
            '--tag' => ['assets'],
        ]);

        $command->call('vendor:publish', [
            '--provider' => 'Mcms\Mailchimp\McmsMailchimpServiceProvider',
            '--tag' => ['admin-package'],
        ]);

        $command->comment('* Assets published');
    }
}