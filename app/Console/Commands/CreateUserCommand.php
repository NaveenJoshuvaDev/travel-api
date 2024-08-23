<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class CreateUserCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a new user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
        //How to ask the bash question and pass that to array as parameter
       $user['name']= $this->ask('Name of the new user');
       $user['email']= $this->ask('Email of the new user');
       User::create([$user]);
        $this->info('User '.$user['email'].' created successfully');
    }
}
