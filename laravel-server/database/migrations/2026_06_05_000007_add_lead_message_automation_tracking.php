<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->timestamp('automation_enrolled_at')->nullable()->after('last_message_at');
            $table->timestamp('welcome_whatsapp_sent_at')->nullable()->after('automation_enrolled_at');
            $table->timestamp('welcome_email_sent_at')->nullable()->after('welcome_whatsapp_sent_at');
            $table->timestamp('next_guidance_at')->nullable()->after('welcome_email_sent_at');
            $table->timestamp('last_guidance_at')->nullable()->after('next_guidance_at');
            $table->unsignedTinyInteger('guidance_step')->default(0)->after('last_guidance_at');

            $table->index('next_guidance_at', 'leads_next_guidance_index');
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropIndex('leads_next_guidance_index');
            $table->dropColumn([
                'automation_enrolled_at',
                'welcome_whatsapp_sent_at',
                'welcome_email_sent_at',
                'next_guidance_at',
                'last_guidance_at',
                'guidance_step',
            ]);
        });
    }
};
