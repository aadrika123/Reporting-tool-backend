<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToVtTemplateParametersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vt_template_parameters', function (Blueprint $table) {
            $table->foreign(['report_template_id'], 'vt_template_parameters_report_template_id_fkey')->references(['id'])->on('vt_templates');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vt_template_parameters', function (Blueprint $table) {
            $table->dropForeign('vt_template_parameters_report_template_id_fkey');
        });
    }
}
