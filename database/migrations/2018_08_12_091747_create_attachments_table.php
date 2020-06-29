<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAttachmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attachments', function (Blueprint $table) {
            $table->increments('id');
            $table->string('short_code');
            $table->unsignedInteger('attachable_id');
            $table->string('attachable_type');
            $table->string('name');
            $table->string('display_name');
            $table->integer('user_type')->unsigned()->nullable();            
            $table->integer('created_by')->unsigned()->nullable();
            $table->timestamps();

            $table->index('attachable_id');
            $table->index('attachable_type'); 
            $table->index('display_name');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('attachments');
    }
}
