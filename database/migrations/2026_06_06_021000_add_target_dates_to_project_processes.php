<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_processes', function (Blueprint $table) {
            $table->date('target_start')->nullable()->after('allowed_role_codes');
            $table->date('target_finish')->nullable()->after('target_start');
        });
    }

    public function down(): void
    {
        Schema::table('project_processes', function (Blueprint $table) {
            $table->dropColumn(['target_start', 'target_finish']);
        });
    }
};
