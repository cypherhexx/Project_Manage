<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->increments('id');
            $table->string('url_slug');
            $table->string('number');
            $table->string('reference')->nullable();
            $table->unsignedInteger('customer_id');
            $table->unsignedInteger('project_id')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->unsignedInteger('country_id')->nullable();
            $table->string('zip_code')->nullable();
            $table->string('shipping_address')->nullable();
            $table->string('shipping_city')->nullable();
            $table->string('shipping_state')->nullable();
            $table->string('shipping_zip_code')->nullable();
            $table->unsignedInteger('shipping_country_id')->nullable();

            $table->unsignedInteger('currency_id')->nullable();
            $table->unsignedInteger('discount_type_id')->nullable();
            $table->unsignedInteger('status_id');
            $table->unsignedInteger('sales_agent_id')->nullable();
            $table->text('admin_note')->nullable();
            $table->date('date');
            $table->date('due_date')->nullable();

            $table->string('show_quantity_as')->nullable();
            $table->decimal('sub_total', 10,2)->nullable();
            $table->unsignedInteger('discount_method_id')->nullable();
            $table->decimal('discount_rate', 10,2)->nullable();
            $table->decimal('discount_total', 10,2)->nullable();
            $table->longText('taxes')->nullable();
            $table->decimal('tax_total', 10,2)->nullable();
            $table->decimal('adjustment', 10,2)->nullable();
            $table->decimal('total', 10,2)->nullable();
            $table->decimal('amount_paid', 10,2)->nullable();
            $table->decimal('applied_credits', 10,2)->nullable();

            $table->text('client_note')->nullable();
            $table->text('terms_and_condition')->nullable();

            $table->boolean('prevent_sending_overdue_reminder')->nullable();

            $table->string('recurring_invoice_type')->nullable();
            $table->integer('recurring_invoice_total_cycle')->nullable();
            $table->integer('recurring_invoice_custom_parameter')->nullable();
            $table->string('recurring_invoice_custom_type')->nullable();
            $table->integer('recurring_invoice_custom_num_of_times_ran')->nullable();
            $table->boolean('is_recurring_invoice_period_infinity')->nullable();
            $table->dateTime('date_of_last_recurring_invoice_generated')->nullable();

            $table->boolean('allow_partial_payment')->nullable();            
            $table->unsignedInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();


            $table->index('number');    
            $table->index('status_id');
            $table->index('currency_id'); 
            $table->index('customer_id');    
            $table->index('project_id');              
            $table->index('created_by');  
            $table->index('sales_agent_id'); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('invoices');
    }
}
