<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSubscriptionPlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('plan_title')->commrnt("Name of the Plan");
            $table->float('plan_price')->comment("Price of a overall plan");
            $table->integer('daily_budget')->comment("how much daily you'll spent on this");
            $table->integer('montly_budget')->comment("how much you'll get montly in this pack o spent");
            $table->tinyInteger('is_active')->comment("Plan is active or not");
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
        Schema::dropIfExists('subscription_plans');
    }
}
