<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table): void {
            if (! Schema::hasColumn('leads', 'lead_category')) {
                $table->string('lead_category')->nullable()->after('interest');
            }

            if (! Schema::hasColumn('leads', 'lead_subcategory')) {
                $table->string('lead_subcategory')->nullable()->after('lead_category');
            }

            if (! Schema::hasColumn('leads', 'lead_detail_option')) {
                $table->string('lead_detail_option')->nullable()->after('lead_subcategory');
            }

            if (! Schema::hasColumn('leads', 'preferred_time_interest')) {
                $table->string('preferred_time_interest')->nullable()->after('lead_detail_option');
            }
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table): void {
            foreach (['preferred_time_interest', 'lead_detail_option', 'lead_subcategory', 'lead_category'] as $column) {
                if (Schema::hasColumn('leads', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
