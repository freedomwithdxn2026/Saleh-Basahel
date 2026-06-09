<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->index('source', 'leads_source_index');
            $table->index('status', 'leads_status_index');
            $table->index('created_at', 'leads_created_at_index');
            $table->index('external_id', 'leads_external_id_index');
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropIndex('leads_source_index');
            $table->dropIndex('leads_status_index');
            $table->dropIndex('leads_created_at_index');
            $table->dropIndex('leads_external_id_index');
        });
    }
};
