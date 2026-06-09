<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->string('occupation', 160)->nullable()->after('country');
            $table->unsignedSmallInteger('lead_score')->default(0)->after('status');
            $table->string('lead_temperature', 20)->default('cold')->after('lead_score');
            $table->string('meeting_status', 40)->default('not_scheduled')->after('lead_temperature');
            $table->string('calendly_status', 40)->default('not_sent')->after('meeting_status');
            $table->string('calendly_event_uri', 500)->nullable()->after('calendly_status');
            $table->timestamp('meeting_scheduled_at')->nullable()->after('calendly_event_uri');
            $table->text('notes')->nullable()->after('message');
            $table->timestamp('next_follow_up_at')->nullable()->after('last_message_at');
            $table->timestamp('last_follow_up_at')->nullable()->after('next_follow_up_at');
            $table->unsignedTinyInteger('follow_up_step')->default(0)->after('last_follow_up_at');
            $table->json('reminders_sent')->nullable()->after('follow_up_step');
            $table->json('admin_notifications_sent')->nullable()->after('reminders_sent');
            $table->timestamp('converted_at')->nullable()->after('admin_notifications_sent');

            $table->index('lead_temperature', 'leads_temperature_index');
            $table->index('meeting_status', 'leads_meeting_status_index');
            $table->index('next_follow_up_at', 'leads_next_follow_up_index');
            $table->index('meeting_scheduled_at', 'leads_meeting_scheduled_index');
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropIndex('leads_temperature_index');
            $table->dropIndex('leads_meeting_status_index');
            $table->dropIndex('leads_next_follow_up_index');
            $table->dropIndex('leads_meeting_scheduled_index');

            $table->dropColumn([
                'occupation',
                'lead_score',
                'lead_temperature',
                'meeting_status',
                'calendly_status',
                'calendly_event_uri',
                'meeting_scheduled_at',
                'notes',
                'next_follow_up_at',
                'last_follow_up_at',
                'follow_up_step',
                'reminders_sent',
                'admin_notifications_sent',
                'converted_at',
            ]);
        });
    }
};
