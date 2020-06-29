<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTimeSheetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('time_sheets', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('task_id');
            $table->unsignedInteger('user_id');
            $table->dateTime('start_time');
            $table->dateTime('end_time')->nullable();
            $table->time('duration')->nullable();
            $table->text('note')->nullable();
            // $table->boolean('is_billed')->nullable();
            $table->unsignedInteger('invoice_id')->nullable();
            $table->unsignedInteger('invoice_item_id')->nullable();
            $table->timestamps();

            $table->index('task_id');                
            $table->index('invoice_id'); 
            $table->index('invoice_item_id'); 
            $table->index('user_id');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('time_sheets');
    }
}
