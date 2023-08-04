<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVtSearchGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vt_search_groups', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('search_group', 200)->nullable();
            $table->boolean('is_report')->nullable();
            $table->smallInteger('status')->nullable()->default(1);
            $table->integer('parent_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vt_search_groups');
    }
}
