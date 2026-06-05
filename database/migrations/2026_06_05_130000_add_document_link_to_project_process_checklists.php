<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_process_checklists', function (Blueprint $table) {
            $table->string('document_link', 2048)->nullable()->after('label');
        });
    }

    public function down(): void
    {
        Schema::table('project_process_checklists', function (Blueprint $table) {
            $table->dropColumn('document_link');
        });
    }
};
