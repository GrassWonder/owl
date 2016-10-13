<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReminderTokensTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        if (env('DB_DRIVER') === 'mysql') {
            Schema::create('reminder_tokens', function($table) {
                $table->increments('id');
                $table->integer('user_id')
                    ->references('id')->on('users')
                    ->onDelete('cascade')->onUpdate('cascade');
                $table->string('token', 512)->unique();
                $table->timestamps();
            });
        } else {
            Schema::create('reminder_tokens', function($table) {
                $table->increments('id');
                $table->integer('user_id')
                    ->references('id')->on('users')
                    ->onDelete('cascade')->onUpdate('cascade');
                $table->text('token')->unique();
                $table->timestamps();
            });
        }
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        Schema::drop('reminder_tokens');
	}

}
