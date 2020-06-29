<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAppliedCreditsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('applied_credits', function (Blueprint $table) {
            $table->increments('id');          
            $table->unsignedInteger('credit_note_id');
            $table->foreign('credit_note_id')->references('id')->on('credit_notes'); 
            $table->unsignedInteger('invoice_id');
            $table->foreign('invoice_id')->references('id')->on('invoices'); 
            $table->date('date');
            $table->decimal('amount', 10,2);        
            $table->unsignedInteger('created_by');          
            $table->foreign('created_by')->references('id')->on('users');          
   
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
        Schema::dropIfExists('applied_credits');
    }
}
