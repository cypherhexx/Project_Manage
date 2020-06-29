<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->increments('id');
            $table->string('number');            
            $table->date('date');
            $table->unsignedInteger('invoice_id');
            $table->decimal('amount', 10,2);            
            $table->unsignedInteger('payment_mode_id');
            $table->string('payment_method')->nullable();
            $table->string('transaction_id')->nullable();
            $table->text('note')->nullable();
            $table->unsignedInteger('entry_by')->nullable();
            $table->timestamps();

            $table->index('invoice_id');
            $table->index('number');   
            $table->index('date');
            $table->index('payment_mode_id');   

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payments');
    }
}
