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
        Schema::create('graph_nodes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scan_id')->constrained()->cascadeOnDelete();
            $table->string('node_id');                     // unique string id للـ frontend
            $table->enum('type', ['domain', 'subdomain', 'ip', 'endpoint', 'js_file', 'technology']);
            $table->string('label');
            $table->json('metadata')->nullable();          // أي بيانات إضافية
            $table->enum('risk_level', ['info', 'low', 'medium', 'high', 'critical'])->default('info');
            $table->timestamps();
        
            $table->unique(['scan_id', 'node_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('graph_nodes');
    }
};
