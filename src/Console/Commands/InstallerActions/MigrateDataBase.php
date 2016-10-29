<?php

namespace Mcms\Mailchimp\Console\Commands\InstallerActions;


use Illuminate\Console\Command;

class MigrateDataBase
{
    public function handle(Command $command)
    {
        $command->call('vendor:publish', [
            '--provider' => 'Mcms\Mailchimp\McmsMailchimpServiceProvider',
            '--tag' => ['migrations'],
        ]);

        $command->call('migrate');

        $command->comment('* Database migration complete');
    }
}