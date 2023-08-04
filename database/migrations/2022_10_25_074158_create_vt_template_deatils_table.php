<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVtTemplateDeatilsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vt_template_deatils', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('report_template_id');
            $table->decimal('x', 18)->nullable();
            $table->decimal('y', 18)->nullable();
            $table->string('field_type', 100)->nullable();
            $table->string('field_name', 200)->nullable();
            $table->string('font_name', 100)->nullable();
            $table->integer('font_size')->nullable();
            $table->integer('width')->nullable();
            $table->boolean('is_bold')->nullable();
            $table->boolean('is_italic')->nullable();
            $table->boolean('is_underline')->nullable();
            $table->boolean('is_visible')->nullable();
            $table->boolean('is_boxed')->nullable();
            $table->string('alignment', 100)->nullable();
            $table->string('color', 100)->nullable();
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
        Schema::dropIfExists('vt_template_deatils');
    }
}
