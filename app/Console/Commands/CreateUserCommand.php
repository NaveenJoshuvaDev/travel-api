<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

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
       $user['password']= $this->secret('Password of the new user');
       //Role is connected via many to many relationships.
       $roleName = $this->choice('Role of the new user', ['admin', 'editor'], 1);
       $role = Role::where('name', $roleName)->first();

       if(! $role)
       {
        $this->error('Role not found');
        return -1;
       }
       //where we get roles we need to seed them.
        //watch part-5 for better understanging of the attach concept
      //we are attaching so use Database Transaction class why?
      //If we have more than one operation like updating or inserting something
      // one of the above or below will fail
      /*
       $newUser= User::create([$user]);

      $newUser->roles()->attach($role->id);
      */
      /*so suppose if the role attachment fail for whatever the reason ,then the user email would be already taken in the database and
      later create the user with same email would be taken by unique validation though you haven't actually fully created that user so to avoid that
      Lets call DB::transaction
      */


        //making validation

        $validator = Validator::make($user, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'max:255', 'unique:'.User::class],
            'password' => ['required', Password::defaults()],
        ]);
          if($validator->fails())
          {
            foreach($validator->errors()->all() as $error)
            {
                $this->error($error);
            }

            return -1;
          }
          DB::transaction(function() use ($user, $role)
          {
            //hashing
            $user['password'] = Hash::make($user['password']);
            $newUser= User::create($user);
            $newUser->roles()->attach($role->id);
          });

            $this->info('User '.$user['email'].' created successfully');
    }
}
