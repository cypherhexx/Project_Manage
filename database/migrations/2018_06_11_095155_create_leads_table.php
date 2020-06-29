<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLeadsTable extends Migration
{


    /**
     * Run the migrations.
     *
     * @return  void
     */
    public function up()
    {
        Schema::create('leads', function (Blueprint $table) {

            $table->increments('id');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('position')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->string('phone')->nullable();
            $table->string('company')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->unsignedInteger('country_id')->nullable();
            $table->string('zip_code')->nullable();
            $table->text('description')->nullable();
            $table->unsignedInteger('lead_status_id');
            $table->unsignedInteger('lead_source_id');
            $table->unsignedInteger('assigned_to')->nullable();
            $table->boolean('is_lost')->nullable();
            $table->unsignedInteger('customer_id')->nullable();
            $table->dateTime('last_contacted')->nullable();            
            $table->unsignedInteger('last_contacted_by')->nullable();
            $table->boolean('is_important')->nullable();            
            $table->unsignedInteger('created_by')->nullable();
            $table->text('social_links')->nullable();        
            $table->text('smart_summary')->nullable();        
            $table->string('photo')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('first_name');
            $table->index('last_name');
            $table->index('email');
            $table->index('phone');
            $table->index('lead_status_id');
            $table->index('lead_source_id');
            $table->index('assigned_to');
            $table->index('created_by');

        });

       
    }

    /**
     * Reverse the migrations.
     *
     * @return  void
     */
    public function down()
    {
        Schema::dropIfExists('leads');

    }

}


