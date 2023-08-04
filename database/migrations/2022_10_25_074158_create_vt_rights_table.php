<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVtRightsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vt_rights', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('userid')->nullable();
            $table->integer('searchtemplateid')->nullable();
            $table->integer('reporttemplateid')->nullable();
            $table->timeTz('created_at')->nullable();
            $table->timeTz('updated_at')->nullable();
            $table->smallInteger('status')->nullable()->default(1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vt_rights');
    }
}
