<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lead_communications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained()->cascadeOnDelete();
            $table->string('direction', 20);
            $table->string('channel', 30);
            $table->string('category', 50)->default('message');
            $table->string('subject')->nullable();
            $table->longText('body');
            $table->string('status', 30);
            $table->string('recipient')->nullable();
            $table->string('external_key', 190)->nullable();
            $table->text('failure_reason')->nullable();
            $table->unsignedInteger('attempt_count')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamp('last_attempt_at')->nullable();
            $table->timestamps();

            $table->unique(['lead_id', 'channel', 'external_key'], 'lead_communications_external_unique');
            $table->index(['lead_id', 'created_at'], 'lead_communications_timeline_index');
            $table->index(['status', 'channel'], 'lead_communications_delivery_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_communications');
    }
};
