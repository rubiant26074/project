<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('master_flow_id')->nullable()->constrained()->nullOnDelete();
            $table->string('wo_number')->unique();
            $table->string('client_name');
            $table->string('project_name');
            $table->text('description')->nullable();
            $table->string('status')->default('open');
            $table->unsignedTinyInteger('progress')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
