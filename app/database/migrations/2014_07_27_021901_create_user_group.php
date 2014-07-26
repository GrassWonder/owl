<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserGroup extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
    {
        // Admin$B%0%k!<%W$N:n@.(B
        try{
            $group = Sentry::createGroup(array(
                'name' => 'Administrators',
                'permissions' => array(
                    'admin' => 1,
                    'user' => 1,
                ),
            ));
        } catch (Cartalyst\Sentry\Groups\GroupExistsException $e) {
            echo 'Administrator$B%0%k!<%W$O4{$KB8:_$7$F$$$^$9!#(B';
        }

        // User$B%0%k!<%W$N:n@.(B
        try {
            $group = Sentry::createGroup(array(
                'name' => 'Users',
                'permissions' => array(
                    'admin' => 0,
                    'user' => 1,
                ),
            ));
        } catch (Cartalyst\Sentry\Groups\GroupExistsException $e) {
            echo 'Users$B%0%k!<%W$O4{$KB8:_$7$F$$$^$9!#(B';
        }

        try {
            // $B%f!<%6!<$N:n@.(B
            $user = Sentry::createUser(array(
                'username' => 'admin',
                'email' => 'admin@athena.example',
                'password' => 'password',
                'activated' => 1,
                'permissions' => array(
                    'superuser' => 1,
                ),
            ));
            //$B%0%k!<%W(BID$B$r;HMQ$7$F%0%k!<%W$r8!:w(B
            $adminGroup = Sentry::findGroupById(1);
            // $B%f!<%6!<$K(Badmin$B%0%k!<%W$r3d$jEv$F$k(B
            $user->addGroup($adminGroup);
        } catch (Cartalyst\Sentry\Users\UserExistsException $e) {
            echo '$B$3$N%m%0%$%s%f!<%6!<$OB8:_$7$^$9!#(B';
        }
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        User::where('username','=','admin')->firstOrFail()->delete();

        $userGroup = Sentry::findGroupById(2);
        $userGroup->delete();

        $adminGroup = Sentry::findGroupById(1);
        $adminGroup->delete();

	}

}
