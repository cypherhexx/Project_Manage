<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomerTable extends Migration
{


    /**
     * Run the migrations.
     *
     * @return  void
     */
    public function up()
    {
        Schema::create('customers', function (Blueprint $table) {

            $table->increments('id');
            $table->string('number')->nullable();
            $table->string('name');
            $table->string('vat_number')->nullable();
            $table->string('phone')->nullable();
            $table->string('website')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('zip_code')->nullable();
            $table->unsignedInteger('country_id')->nullable();
            $table->boolean('shipping_is_same_as_billing')->nullable();
            $table->string('shipping_address')->nullable();
            $table->string('shipping_city')->nullable();
            $table->string('shipping_state')->nullable();
            $table->string('shipping_zip_code')->nullable();
            $table->unsignedInteger('shipping_country_id')->nullable();
            $table->string('default_language', 10)->nullable();
            $table->unsignedInteger('currency_id')->nullable();
            $table->text('notes')->nullable();
          
            $table->text('latitude')->nullable();
            $table->text('longitude')->nullable();
            $table->string('stripe_id')->nullable();            
            $table->boolean('inactive')->nullable();
            $table->unsignedInteger('created_by')->nullable();            
            $table->timestamps();
            $table->softDeletes();


            $table->index('number');
            $table->index('name');
            $table->index('phone'); 
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return  void
     */
    public function down()
    {
        Schema::dropIfExists('customers');

    }

}


