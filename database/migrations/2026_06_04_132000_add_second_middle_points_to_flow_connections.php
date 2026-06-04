<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('master_flow_connections', function (Blueprint $table) {
            $table->decimal('mid2_x', 5, 1)->nullable()->after('bend_y');
            $table->decimal('mid2_y', 5, 1)->nullable()->after('mid2_x');
        });

        Schema::table('project_process_connections', function (Blueprint $table) {
            $table->decimal('mid2_x', 5, 1)->nullable()->after('bend_y');
            $table->decimal('mid2_y', 5, 1)->nullable()->after('mid2_x');
        });
    }

    public function down(): void
    {
        Schema::table('project_process_connections', function (Blueprint $table) {
            $table->dropColumn(['mid2_x', 'mid2_y']);
        });

        Schema::table('master_flow_connections', function (Blueprint $table) {
            $table->dropColumn(['mid2_x', 'mid2_y']);
        });
    }
};
