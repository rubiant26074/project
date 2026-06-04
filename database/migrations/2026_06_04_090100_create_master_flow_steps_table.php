<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('master_flow_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('master_flow_id')->constrained()->cascadeOnDelete();
            $table->string('code');
            $table->string('name');
            $table->decimal('position_x', 5, 2)->default(12);
            $table->decimal('position_y', 5, 2)->default(12);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['master_flow_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_flow_steps');
    }
};
