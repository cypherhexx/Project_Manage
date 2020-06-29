<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChildInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('child_invoices', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('parent_invoice_id')->nullable();
            $table->foreign('parent_invoice_id')->references('id')->on('invoices')->onDelete('cascade');  
            $table->unsignedInteger('child_invoice_id')->nullable();
            $table->foreign('child_invoice_id')->references('id')->on('invoices')->onDelete('cascade'); 
           
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('child_invoices');
    }
}
