<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scan_id')->constrained()->cascadeOnDelete();
            $table->integer('risk_score')->default(0);    // 0 - 100
            $table->enum('risk_level', ['info', 'low', 'medium', 'high', 'critical'])->default('info');
            $table->integer('subdomains_count')->default(0);
            $table->integer('endpoints_count')->default(0);
            $table->integer('secrets_count')->default(0);
            $table->integer('vulnerabilities_count')->default(0);
            $table->text('ai_summary')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
