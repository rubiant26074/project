<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('role');
            $table->timestamp('approved_at')->nullable()->after('is_active');
            $table->foreignId('approved_by')->nullable()->after('approved_at')->constrained('users')->nullOnDelete();
        });

        DB::table('users')->update([
            'is_active' => true,
            'approved_at' => now(),
        ]);

        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->nullable()->default(null)->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('approved_by');
            $table->dropColumn(['is_active', 'approved_at']);
        });

        DB::table('users')->whereNull('role')->update(['role' => 'user']);

        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('user')->nullable(false)->change();
        });
    }
};
