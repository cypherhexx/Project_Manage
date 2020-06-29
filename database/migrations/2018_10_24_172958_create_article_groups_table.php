<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateArticleGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('article_groups', function (Blueprint $table) {
            $table->increments('id');            
            $table->integer('parent_id')->unsigned()->nullable();
            $table->foreign('parent_id')->references('id')->on('article_groups');
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->string('color_code')->nullable();
            $table->integer('sequence_number')->nullable();
            $table->integer('is_disabled')->nullable();
            $table->timestamps();

            // Indexing
            $table->index('slug');
            $table->index('parent_id');
            $table->index('sequence_number');
            $table->index('name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('article_groups');
    }
}
