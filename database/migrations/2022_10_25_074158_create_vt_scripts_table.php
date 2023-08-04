<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVtScriptsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vt_scripts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('report_template_id');
            $table->string('script_name', 200)->nullable();
            $table->text('script_text')->nullable();
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
        Schema::dropIfExists('vt_scripts');
    }
}
