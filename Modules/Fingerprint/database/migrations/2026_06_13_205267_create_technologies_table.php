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
        Schema::create('technologies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subdomain_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');                       // Laravel, Nginx, React
            $table->string('version')->nullable();
            $table->string('category');                   // framework, server, cms, cdn
            $table->timestamps();
        
            $table->unique(['scan_id', 'subdomain_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('technologies');
    }
};
