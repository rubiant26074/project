<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_process_connections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('from_process_id')->constrained('project_processes')->cascadeOnDelete();
            $table->foreignId('to_process_id')->constrained('project_processes')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['from_process_id', 'to_process_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_process_connections');
    }
};
