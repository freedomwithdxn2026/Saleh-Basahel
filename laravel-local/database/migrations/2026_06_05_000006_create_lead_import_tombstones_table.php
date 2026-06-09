<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lead_import_tombstones', function (Blueprint $table): void {
            $table->id();
            $table->string('external_id')->unique();
            $table->string('source')->nullable();
            $table->timestamp('deleted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_import_tombstones');
    }
};
