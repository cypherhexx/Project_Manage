<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateArticlesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('article_group_id')->unsigned();
            $table->foreign('article_group_id')->references('id')->on('article_groups');
            $table->string('subject');
            $table->text('details')->nullable();
            $table->boolean('is_internal')->nullable();
            $table->boolean('is_disabled')->nullable();
            $table->string('slug');            
            $table->timestamps();

            // Indexing
            $table->index('slug');
            $table->index('subject');
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('articles');
    }
}
