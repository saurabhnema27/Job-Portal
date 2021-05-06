<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateJobsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('jobs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->bigInteger('phone');
            $table->string('role');
            $table->string('employeer_location');
            $table->string("company_name");
            $table->string("company_location");
            $table->string("address1");
            $table->string("address2");
            $table->string("city");
            $table->string("organization_type");
            $table->string("company_size");
            $table->string("state");
            $table->integer("zipcode");
            $table->string("company_link")->nullable();
            $table->string("job_title");
            $table->string("type_of_role");
            $table->string("job_location");
            $table->string("contract_type");
            $table->string("application_receive_type");
            $table->tinyInteger("submit_resume");
            $table->float("min_salary");
            $table->float("max_salary");
            $table->longText("job_description");
            $table->enum("job_status",array("OPEN","PAUSED","CLOSED"));
            $table->string("job_type")->comment("Free or Pay per click");
            $table->tinyInteger("can_job_publish")->comment("this change can only made by admin")->default(0);
            $table->longText("find_candidate")->comment("It contains the json data");
            $table->unsignedBigInteger('employeer_id');
            $table->foreign('employeer_id')->references('id')->on('users')->onDelete('cascade');
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
        Schema::dropIfExists('jobs');
    }
}
