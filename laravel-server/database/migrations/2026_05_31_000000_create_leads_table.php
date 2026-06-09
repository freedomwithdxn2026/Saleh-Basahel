<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('phone', 50)->nullable();
            $table->string('email', 160)->nullable();
            $table->string('country', 120)->nullable();
            $table->string('interest', 120)->nullable();
            $table->text('message')->nullable();
            $table->boolean('consent')->default(false);
            $table->string('source', 40)->default('website');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
