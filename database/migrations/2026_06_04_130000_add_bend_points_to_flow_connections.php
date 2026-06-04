<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('master_flow_connections', function (Blueprint $table) {
            $table->decimal('bend_x', 5, 1)->nullable()->after('to_step_id');
            $table->decimal('bend_y', 5, 1)->nullable()->after('bend_x');
        });

        Schema::table('project_process_connections', function (Blueprint $table) {
            $table->decimal('bend_x', 5, 1)->nullable()->after('to_process_id');
            $table->decimal('bend_y', 5, 1)->nullable()->after('bend_x');
        });
    }

    public function down(): void
    {
        Schema::table('project_process_connections', function (Blueprint $table) {
            $table->dropColumn(['bend_x', 'bend_y']);
        });

        Schema::table('master_flow_connections', function (Blueprint $table) {
            $table->dropColumn(['bend_x', 'bend_y']);
        });
    }
};
