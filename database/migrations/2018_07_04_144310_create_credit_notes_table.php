<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCreditNotesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('credit_notes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('url_slug');
            $table->string('number');
            $table->string('reference')->nullable();
            $table->unsignedInteger('customer_id');
            $table->foreign('customer_id')->references('id')->on('customers'); 
       
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->unsignedInteger('country_id')->nullable();
            $table->foreign('country_id')->references('id')->on('countries'); 
            $table->string('zip_code')->nullable();
            $table->string('shipping_address')->nullable();
            $table->string('shipping_city')->nullable();
            $table->string('shipping_state')->nullable();
            $table->string('shipping_zip_code')->nullable();
            $table->unsignedInteger('shipping_country_id')->nullable();
            $table->foreign('shipping_country_id')->references('id')->on('countries'); 
            $table->unsignedInteger('currency_id')->nullable();
            $table->foreign('currency_id')->references('id')->on('currencies'); 
            
            $table->unsignedInteger('discount_type_id')->nullable();
            $table->unsignedInteger('status_id');
            
            $table->text('admin_note')->nullable();
            $table->date('date');
   

            $table->string('show_quantity_as')->nullable();
            $table->decimal('sub_total', 10,2)->nullable();
            $table->unsignedInteger('discount_method_id')->nullable();
            $table->decimal('discount_rate', 10,2)->nullable();
            $table->decimal('discount_total', 10,2)->nullable();
            $table->longText('taxes')->nullable();
            $table->decimal('tax_total', 10,2)->nullable();
            $table->decimal('adjustment', 10,2)->nullable();
            $table->decimal('total', 10,2)->nullable();
            $table->decimal('amount_credited', 10,2)->nullable();


            $table->text('client_note')->nullable();
            $table->text('terms_and_condition')->nullable();
       

        
            $table->unsignedInteger('created_by');          
            
   
            $table->timestamps();
            $table->softDeletes();


            $table->index('number');    
            $table->index('status_id'); 
            $table->index('customer_id');               
            $table->index('created_by');  
   
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('credit_notes');
    }
}
