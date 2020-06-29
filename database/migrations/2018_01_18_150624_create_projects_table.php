<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProjectsTable extends Migration
{


    /**
     * Run the migrations.
     *
     * @return  void
     */
    public function up()
    {
        Schema::create('projects', function (Blueprint $table) {

            $table->increments('id');
            $table->string('number')->nullable();            
            $table->string('name');
            $table->string('prefix')->nullable();
            $table->unsignedInteger('customer_id');
            $table->unsignedInteger('billing_type_id');
            $table->boolean('calculate_progress_through_tasks')->nullable();
            $table->decimal('billing_rate', 10,2)->nullable();
            $table->date('start_date')->nullable();
            $table->date('dead_line')->nullable();
            $table->text('description')->nullable();
            $table->string('progress')->nullable();
            $table->unsignedInteger('status_id')->unsigned();
            $table->longText('settings')->nullable();            
            $table->unsignedInteger('created_by')->unsigned();;
            $table->timestamps();
            $table->softDeletes();


            $table->index('status_id');
            $table->index('customer_id');
            $table->index('name');
            $table->index('number');  

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return  void
     */
    public function down()
    {
        Schema::dropIfExists('projects');

    }

}


