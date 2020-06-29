<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMilestonesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('milestones', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('project_id');
            $table->string('name');
            $table->string('background_color')->nullable();
            $table->string('background_text_color')->nullable();            
            $table->date('due_date')->nullable();
            $table->text('description')->nullable();
            $table->boolean('show_description_to_customer')->nullable();
            $table->integer('order')->nullable();
            $table->timestamps();

            $table->index('project_id');      
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('milestones');
    }
}
