<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVtTemplateFootersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vt_template_footers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('report_template_id');
            $table->string('serial_no', 100)->nullable();
            $table->string('field_type', 100)->nullable();
            $table->text('caption')->nullable();
            $table->string('field_name', 200)->nullable();
            $table->string('resource', 200)->nullable();
            $table->decimal('x', 18)->nullable();
            $table->decimal('y', 18)->nullable();
            $table->integer('width')->nullable();
            $table->integer('height')->nullable();
            $table->string('fontname', 100)->nullable();
            $table->decimal('size', 18)->nullable();
            $table->boolean('is_underline')->nullable();
            $table->boolean('is_bold')->nullable();
            $table->boolean('is_italic')->nullable();
            $table->string('color', 100)->nullable();
            $table->string('alignment', 100)->nullable();
            $table->boolean('is_visible')->nullable();
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
        Schema::dropIfExists('vt_template_footers');
    }
}
