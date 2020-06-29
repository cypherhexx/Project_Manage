<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProposalItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('proposal_items', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('proposal_id')->nullable();
            $table->string('description');
            $table->text('long_description')->nullable();
            $table->integer('quantity');
            $table->string('unit')->nullable();
            $table->decimal('rate', 10,2);
            $table->longText('tax_id')->nullable();
            $table->decimal('sub_total', 10,2);
            $table->integer('sorting_order')->nullable();
            $table->index('proposal_id');            
        

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('proposal_items');
    }
}
