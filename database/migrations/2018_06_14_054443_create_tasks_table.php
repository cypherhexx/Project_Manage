<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->increments('id');
            $table->string('number')->nullable();            
            $table->unsignedInteger('component_id')->nullable();
            $table->unsignedInteger('component_number')->nullable();
            $table->unsignedInteger('parent_task_id')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->boolean('is_public')->nullable();
            $table->boolean('is_billable')->nullable();

            $table->unsignedInteger('milestone_id')->nullable();
            $table->decimal('hourly_rate')->nullable();
            $table->date('start_date')->nullable();
            $table->date('due_date')->nullable();
            $table->unsignedInteger('priority_id');
            $table->unsignedInteger('repeat_type_id')->nullable();
            $table->integer('custom_repeat_number')->nullable();
            $table->integer('custom_repeat_type')->nullable();            
            $table->unsignedInteger('user_type');
            $table->unsignedInteger('created_by');
            $table->unsignedInteger('assigned_to')->nullable();
            $table->unsignedInteger('status_id');
            
            $table->timestamps();
            $table->softDeletes();

            
            $table->index('status_id');
            $table->index('priority_id');
            $table->index('assigned_to');
            $table->index('created_by');
            $table->index('component_id');
            $table->index('component_number');
            $table->index('milestone_id');
            $table->index('number');
            $table->index('title');            
            $table->index('parent_task_id');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tasks');
    }
}
