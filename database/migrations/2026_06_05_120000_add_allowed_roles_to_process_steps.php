<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('master_flow_steps', function (Blueprint $table) {
            $table->json('allowed_role_codes')->nullable()->after('sort_order');
        });

        Schema::table('project_processes', function (Blueprint $table) {
            $table->json('allowed_role_codes')->nullable()->after('sort_order');
        });
    }

    public function down(): void
    {
        Schema::table('master_flow_steps', function (Blueprint $table) {
            $table->dropColumn('allowed_role_codes');
        });

        Schema::table('project_processes', function (Blueprint $table) {
            $table->dropColumn('allowed_role_codes');
        });
    }
};
