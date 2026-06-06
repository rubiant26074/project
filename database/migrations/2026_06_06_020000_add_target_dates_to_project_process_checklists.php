<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_process_checklists', function (Blueprint $table) {
            $table->date('target_start')->nullable()->after('document_link');
            $table->date('target_finish')->nullable()->after('target_start');
        });
    }

    public function down(): void
    {
        Schema::table('project_process_checklists', function (Blueprint $table) {
            $table->dropColumn(['target_start', 'target_finish']);
        });
    }
};
