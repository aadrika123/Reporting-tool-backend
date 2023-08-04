<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToVtTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vt_templates', function (Blueprint $table) {
            $table->foreign(['search_group_id'], 'vt_templates_search_group_id_fkey')->references(['id'])->on('vt_search_groups');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vt_templates', function (Blueprint $table) {
            $table->dropForeign('vt_templates_search_group_id_fkey');
        });
    }
}
