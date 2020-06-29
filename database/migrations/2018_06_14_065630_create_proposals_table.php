<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProposalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('proposals', function (Blueprint $table) {
            $table->increments('id');
            $table->string('url_slug');
            $table->string('number');
            $table->string('title');
            $table->unsignedInteger('component_id');
            $table->unsignedInteger('component_number');
            $table->date('date');
            $table->date('open_till')->nullable();
            $table->unsignedInteger('currency_id')->nullable();
            $table->unsignedInteger('discount_type_id')->nullable();
            $table->unsignedInteger('status_id');
            $table->unsignedInteger('assigned_to')->nullable();
            $table->boolean('is_comments_allowed')->nullable();
            $table->string('send_to');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->unsignedInteger('country_id')->nullable();
            $table->string('zip_code')->nullable();
            $table->string('show_quantity_as')->nullable();
            $table->decimal('sub_total', 10,2)->nullable();
            $table->unsignedInteger('discount_method_id')->nullable();
            $table->decimal('discount_rate', 10,2)->nullable();
            $table->decimal('discount_total', 10,2)->nullable();
            $table->longText('taxes')->nullable();
            $table->decimal('tax_total', 10,2)->nullable();
            $table->decimal('adjustment', 10,2)->nullable();
            $table->decimal('total', 10,2)->nullable();
            $table->text('content')->nullable();
            $table->integer('converted_to')->nullable();
            $table->unsignedInteger('converted_to_id')->nullable();
            $table->string('accepted_by_first_name')->nullable();
            $table->string('accepted_by_last_name')->nullable();
            $table->string('accepted_by_email')->nullable();
            $table->string('accepted_by_signature')->nullable();
            $table->timestamp('accepted_date')->nullable();  
            $table->unsignedInteger('created_by');          
            
            $table->timestamps();
            $table->softDeletes();

            $table->index('status_id');            
            $table->index('assigned_to');
            $table->index('currency_id');
            $table->index('number');
            $table->index('title');            
            $table->index('component_id');

            
            
            

            

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('proposals');
    }
}
