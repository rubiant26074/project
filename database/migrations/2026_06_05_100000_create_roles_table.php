<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_system')->default(false);
            $table->timestamps();
        });

        DB::table('roles')->insert([
            [
                'code' => 'admin',
                'name' => 'Administrator',
                'description' => 'Akses penuh ke seluruh fitur aplikasi termasuk master flow, role, dan user management.',
                'is_active' => true,
                'is_system' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'user',
                'name' => 'User',
                'description' => 'Akses operasional untuk melihat dashboard project dan memperbarui proses sesuai izin.',
                'is_active' => true,
                'is_system' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'manager',
                'name' => 'Manager',
                'description' => 'Akses mengelola project dan memantau progress, tanpa hak kelola role dan user.',
                'is_active' => true,
                'is_system' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'viewer',
                'name' => 'Viewer',
                'description' => 'Akses lihat dashboard dan detail progress tanpa hak edit proses.',
                'is_active' => true,
                'is_system' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
