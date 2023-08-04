<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVtTemplateParametersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vt_template_parameters', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('report_template_id');
            $table->string('serial', 100)->nullable();
            $table->string('control_name', 200)->nullable();
            $table->text('display_string')->nullable();
            $table->string('control_type', 200)->nullable();
            $table->string('link_name', 200)->nullable();
            $table->text('source_sql')->nullable();
            $table->string('bound_column', 200)->nullable();
            $table->string('display_column', 200)->nullable();
            $table->string('dependency_control_code', 100)->nullable();
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
        Schema::dropIfExists('vt_template_parameters');
    }
}
