<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePaymentApiResponsesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_api_responses', function (Blueprint $table) {
            $table->increments('id');          
            $table->integer('payment_id')->unsigned();
            $table->foreign('payment_id')->references('id')->on('payments');            
            $table->text('data');
            $table->timestamps();

            $table->index('payment_id');
    
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payment_api_responses');
    }
}
