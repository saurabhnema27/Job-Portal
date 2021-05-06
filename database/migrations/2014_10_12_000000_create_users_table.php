<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('image')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->unique();
            $table->bigInteger('mobile')->nullable();
            $table->tinyInteger('user_type')->comment("1: user, 2: employeer 3:Admin");
            $table->string('disibility')->nullable()->comment("This is for only for Jobseeker");
            $table->string("disability_comment")->nullable()->comment("This is for the disability comment of user");
            $table->tinyInteger('user_status')->comment("This is the user status 1: active and 0: inactive/deactive");
            $table->tinyInteger('block_unblock')->comment("This is the block_unblock status 1: unblock and 0: block ")->default(1);
            $table->tinyInteger('email_notification')->comment('0: NO 1: Yes')->default(1);
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
