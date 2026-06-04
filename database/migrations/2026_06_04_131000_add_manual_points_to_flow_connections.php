<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('master_flow_connections', function (Blueprint $table) {
            $table->decimal('start_x', 5, 1)->nullable()->after('to_step_id');
            $table->decimal('start_y', 5, 1)->nullable()->after('start_x');
            $table->decimal('end_x', 5, 1)->nullable()->after('bend_y');
            $table->decimal('end_y', 5, 1)->nullable()->after('end_x');
        });

        Schema::table('project_process_connections', function (Blueprint $table) {
            $table->decimal('start_x', 5, 1)->nullable()->after('to_process_id');
            $table->decimal('start_y', 5, 1)->nullable()->after('start_x');
            $table->decimal('end_x', 5, 1)->nullable()->after('bend_y');
            $table->decimal('end_y', 5, 1)->nullable()->after('end_x');
        });
    }

    public function down(): void
    {
        Schema::table('project_process_connections', function (Blueprint $table) {
            $table->dropColumn(['start_x', 'start_y', 'end_x', 'end_y']);
        });

        Schema::table('master_flow_connections', function (Blueprint $table) {
            $table->dropColumn(['start_x', 'start_y', 'end_x', 'end_y']);
        });
    }
};
