<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_process_checklists', function (Blueprint $table): void {
            $table->foreignId('parent_id')
                ->nullable()
                ->after('project_process_id')
                ->constrained('project_process_checklists')
                ->cascadeOnDelete();
            $table->unsignedTinyInteger('depth')->default(0)->after('parent_id');
        });
    }

    public function down(): void
    {
        Schema::table('project_process_checklists', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('parent_id');
            $table->dropColumn('depth');
        });
    }
};
