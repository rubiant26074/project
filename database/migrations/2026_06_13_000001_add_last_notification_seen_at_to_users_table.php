<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'last_notification_seen_at')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->timestamp('last_notification_seen_at')->nullable()->after('approved_by');
            });
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('last_notification_seen_at');
        });
    }
};
