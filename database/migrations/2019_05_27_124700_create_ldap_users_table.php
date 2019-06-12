<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLdapUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ldap_users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
            $table->string('name', 100);
            $table->string('employee_id', 20);
            $table->string('location', 100)->nullable();
            $table->tinyInteger('accepted_terms_conditions')->default(0);
            $table->string('status', 15)->nullable();
            $table->string('profile_image', 200)->nullable();
            $table->string('email', 200);
            $table->string('about_me', 255)->nullable();
            $table->string('phone', 15)->nullable();
            $table->string('blood_group', 15)->nullable();
            $table->string('background_image', 255)->nullable();
            $table->string('api_token', 255)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ldap_users');
    }
}
