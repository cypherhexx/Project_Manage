<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTicketsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->increments('id');            
            $table->string('number')->nullable();
            $table->string('subject');
            
            $table->text('details')->nullable();

            $table->integer('project_id')->unsigned()->nullable();
            $table->foreign('project_id')->references('id')->on('projects');

            $table->integer('customer_contact_id')->unsigned()->nullable();
            $table->foreign('customer_contact_id')->references('id')->on('customer_contacts');
            $table->string('name');       
            $table->string('email'); 
            
            
            $table->integer('department_id')->unsigned();
            $table->foreign('department_id')->references('id')->on('departments'); 

            $table->integer('ticket_priority_id')->unsigned();
            $table->foreign('ticket_priority_id')->references('id')->on('ticket_priorities'); 

            $table->integer('ticket_service_id')->unsigned()->nullable();
            $table->foreign('ticket_service_id')->references('id')->on('ticket_services'); 

            $table->integer('user_type');            
            $table->integer('created_by')->unsigned();
        

            $table->integer('assigned_to')->unsigned()->nullable();
            $table->foreign('assigned_to')->references('id')->on('users'); 
            
            $table->integer('ticket_status_id')->unsigned();
            $table->foreign('ticket_status_id')->references('id')->on('ticket_statuses'); 

            $table->timestamp('last_reply')->nullable();
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
        Schema::dropIfExists('tickets');
    }
}
