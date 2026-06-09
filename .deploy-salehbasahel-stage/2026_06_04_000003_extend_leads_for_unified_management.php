<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            if (! Schema::hasColumn('leads', 'status')) {
                $table->string('status', 40)->default('new')->after('source');
            }

            if (! Schema::hasColumn('leads', 'external_id')) {
                $table->string('external_id', 160)->nullable()->after('status');
            }

            if (! Schema::hasColumn('leads', 'source_detail')) {
                $table->string('source_detail', 120)->nullable()->after('external_id');
            }

            if (! Schema::hasColumn('leads', 'conversation_history')) {
                $table->longText('conversation_history')->nullable()->after('message');
            }

            if (! Schema::hasColumn('leads', 'last_message_at')) {
                $table->timestamp('last_message_at')->nullable()->after('conversation_history');
            }

            if (! Schema::hasColumn('leads', 'metadata')) {
                $table->json('metadata')->nullable()->after('last_message_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            foreach (['metadata', 'last_message_at', 'conversation_history', 'source_detail', 'external_id', 'status'] as $column) {
                if (Schema::hasColumn('leads', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
