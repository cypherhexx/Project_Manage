<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('item_categories', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->unsignedInteger('parent_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });


        Schema::create('items', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('item_category_id')->nullable();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('rate', 10, 2);
            $table->string('unit')->nullable();
            $table->unsignedInteger('tax_id_1')->nullable();
            $table->unsignedInteger('tax_id_2')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('name');
            $table->index('item_category_id');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('items');
        Schema::dropIfExists('item_categories');

    }
}
