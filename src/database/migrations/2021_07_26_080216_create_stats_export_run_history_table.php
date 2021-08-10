<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStatsExportRunHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stats_export_run_history', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('job_name')->unique();
            $table->string('job_code');
            $table->index('job_code');
            $table->dateTime('last_run_date');
            $table->index('last_run_date');
            $table->dateTime('max_to_date');
            $table->index('max_to_date');
            $table->string('status');
            $table->index('status');
            $table->string('exporter_class');
            $table->index('exporter_class');
            $table->text('exception')->nullable();
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
        Schema::dropIfExists('stats_export_run_history');
    }
}
