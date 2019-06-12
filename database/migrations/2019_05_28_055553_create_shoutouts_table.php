<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShoutoutsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shoutouts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
            $table->mediumText('shoutout_text');
            $table->integer('created_by');
            $table->integer('department');
            $table->integer('location')->nullable();
            $table->tinyInteger('is_sensored')->default(0);
            $table->string('status')->nullable();
            $table->integer('hats_off')->nullable();
            $table->tinyInteger('is_approved')->default(0);
            $table->integer('shoutout_for_user')->nullable();
            $table->tinyInteger('is_reported')->default(0);
            $table->string('core_values')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shoutouts');
    }
}
