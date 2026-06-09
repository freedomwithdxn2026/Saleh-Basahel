<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_content_overrides', function (Blueprint $table) {
            $table->id();
            $table->string('locale', 8)->default('en');
            $table->string('key', 160);
            $table->text('value')->nullable();
            $table->timestamps();

            $table->unique(['locale', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_content_overrides');
    }
};
