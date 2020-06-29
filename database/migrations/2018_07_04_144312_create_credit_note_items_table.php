<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCreditNoteItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('credit_note_items', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('credit_note_id');
            $table->string('description');
            $table->text('long_description')->nullable();
            $table->integer('quantity');
            $table->string('unit')->nullable();
            $table->decimal('rate', 10,2);
            $table->longText('tax_id')->nullable();
            $table->decimal('sub_total', 10,2);
            $table->integer('sorting_order')->nullable();


            $table->index('credit_note_id');    
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('credit_note_items');
    }
}
