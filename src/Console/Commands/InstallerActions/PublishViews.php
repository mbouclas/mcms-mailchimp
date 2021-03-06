<?php

namespace Mcms\Mailchimp\Console\Commands\InstallerActions;


use Illuminate\Console\Command;

/**
 * Class PublishViews
 * @package Mcms\Mailchimp\Console\Commands\InstallerActions
 */
class PublishViews
{
    /**
     * @param Command $command
     */
    public function handle(Command $command)
    {
        $command->call('vendor:publish', [
            '--provider' => 'Mcms\Mailchimp\McmsMailchimpServiceProvider',
            '--tag' => ['views'],
        ]);
        
        $command->comment('* Views published');
    }
}