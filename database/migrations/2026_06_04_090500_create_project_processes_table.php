<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_processes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('master_flow_step_id')->nullable()->constrained()->nullOnDelete();
            $table->string('code');
            $table->string('name');
            $table->string('status')->default('open');
            $table->unsignedTinyInteger('progress')->default(0);
            $table->unsignedInteger('completed_checklists')->default(0);
            $table->unsignedInteger('total_checklists')->default(0);
            $table->decimal('position_x', 5, 2)->default(12);
            $table->decimal('position_y', 5, 2)->default(12);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['project_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_processes');
    }
};
