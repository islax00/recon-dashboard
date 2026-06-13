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
        Schema::create('subdomains', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scan_id')->constrained()->cascadeOnDelete();
            $table->string('hostname');
            $table->string('ip_address')->nullable();
            $table->integer('status_code')->nullable();
            $table->string('title')->nullable();
            $table->boolean('is_alive')->default(false);
            $table->timestamps();
        
            $table->unique(['scan_id', 'hostname']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subdomains');
    }
};
