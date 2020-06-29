<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLeadStatusesTable extends Migration
{


    /**
     * Run the migrations.
     *
     * @return  void
     */
    public function up()
    {
        Schema::create('lead_statuses', function (Blueprint $table) {

            $table->increments('id');
            $table->string('name');
            $table->boolean('is_system')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return  void
     */
    public function down()
    {
        Schema::dropIfExists('lead_statuses');

    }

}


