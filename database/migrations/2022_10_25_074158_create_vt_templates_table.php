<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVtTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vt_templates', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('search_group_id');
            $table->string('template_code', 100)->nullable();
            $table->string('template_name', 200)->nullable();
            $table->string('paper_size_enum', 100)->nullable();
            $table->text('detail_layout')->nullable();
            $table->integer('header_height')->nullable();
            $table->integer('header_height_page2')->nullable();
            $table->integer('footer_height')->nullable();
            $table->integer('detail_line_spacing')->nullable();
            $table->text('layout_sql')->nullable();
            $table->text('detail_sql')->nullable();
            $table->text('footer_sql')->nullable();
            $table->boolean('is_default')->nullable();
            $table->boolean('is_landscape')->nullable();
            $table->boolean('is_global_header')->nullable();
            $table->boolean('is_render_global_header')->nullable();
            $table->boolean('is_page_layout_in_pager2')->nullable();
            $table->string('groupby_expression', 200)->nullable();
            $table->boolean('is_show_grid_line')->nullable();
            $table->integer('header_distance')->nullable();
            $table->string('screen_display_string', 200)->nullable();
            $table->integer('parent_id')->nullable();
            $table->integer('label_row_count')->nullable();
            $table->integer('label_column_count')->nullable();
            $table->boolean('is_detail_wordwrap')->nullable();
            $table->boolean('is_compact_footer')->nullable();
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
        Schema::dropIfExists('vt_templates');
    }
}
