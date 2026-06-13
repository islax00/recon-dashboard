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
        Schema::create('ip_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subdomain_id')->nullable()->constrained()->nullOnDelete();
            $table->string('ip');
            $table->string('asn')->nullable();
            $table->string('org')->nullable();
            $table->string('country')->nullable();
            $table->string('city')->nullable();
            $table->timestamps();
        
            $table->unique(['scan_id', 'ip']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ip_addresses');
    }
};
