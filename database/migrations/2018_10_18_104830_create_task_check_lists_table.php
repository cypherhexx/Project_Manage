<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTaskCheckListsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('task_check_lists', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->boolean('is_complete')->nullable();
            $table->date('completed_date')->nullable();
            $table->unsignedInteger('task_id');
                
            $table->index('task_id'); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('task_check_lists');
    }
}
