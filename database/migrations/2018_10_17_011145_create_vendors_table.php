<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVendorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vendors', function (Blueprint $table) {
            $table->increments('id');
            $table->string('number')->nullable();
            $table->string('name');  
            $table->string('contact_first_name');
            $table->string('contact_last_name');
            $table->string('contact_email')->unique();       
            $table->string('contact_phone')->nullable();
            $table->string('contact_position')->nullable();
            $table->string('phone')->nullable();
            $table->string('website')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('zip_code')->nullable();
            $table->unsignedInteger('country_id')->nullable();
            $table->text('notes')->nullable();      
            $table->text('latitude')->nullable();
            $table->text('longitude')->nullable();                     
            $table->boolean('inactive')->nullable();
            $table->unsignedInteger('created_by')->nullable(); 
            $table->timestamps();
            $table->softDeletes();

            $table->index('number'); 
            $table->index('name');            
            $table->index('contact_first_name');
            $table->index('contact_last_name');
            $table->index('contact_email');
            $table->index('contact_phone');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vendors');
    }
}
