<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomerRegistrationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {


        Schema::create('customer_registrations', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('vat_number')->nullable();
            $table->string('phone')->nullable();
            $table->string('website')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('zip_code')->nullable();
            $table->unsignedInteger('country_id')->nullable();            
            $table->string('contact_first_name');
            $table->string('contact_last_name');
            $table->string('contact_email')->unique();
            $table->string('contact_password')->unique();
            $table->string('contact_phone')->nullable();
            $table->string('contact_position')->nullable();
            $table->text('verification_token')->nullable();
            $table->boolean('verified')->nullable();            
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
        Schema::dropIfExists('customer_registrations');
    }
}
