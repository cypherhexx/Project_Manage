<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExpensesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('expense_category_id');
            $table->date('date');
            $table->decimal('amount', 10,2);
            $table->decimal('amount_after_tax', 10,2);
            $table->string('name')->nullable();
            $table->string('note')->nullable();
            $table->string('attachment')->nullable();
            $table->unsignedInteger('currency_id')->nullable();
            $table->unsignedInteger('payment_mode_id')->nullable();
            $table->string('reference')->nullable();
            $table->unsignedInteger('customer_id')->nullable();
            $table->unsignedInteger('vendor_id')->nullable();
            $table->unsignedInteger('project_id')->nullable();
            $table->unsignedInteger('invoice_id')->nullable();
            $table->boolean('is_billable')->nullable();            
            $table->text('tax_id')->nullable();
            $table->unsignedInteger('user_id');
            $table->timestamps();
            $table->softDeletes();


            $table->index('expense_category_id');
            $table->index('date');
            $table->index('currency_id');            
            $table->index('customer_id');
            $table->index('project_id');
            $table->index('invoice_id');
            $table->index('vendor_id');
            $table->index('payment_mode_id');            
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
        Schema::dropIfExists('expenses');
    }
}
