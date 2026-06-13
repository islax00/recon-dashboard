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
        Schema::create('endpoints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subdomain_id')->nullable()->constrained()->nullOnDelete();
            $table->string('url');
            $table->string('method')->default('GET');
            $table->integer('status_code')->nullable();
            $table->string('content_type')->nullable();
            $table->bigInteger('content_length')->nullable();
            $table->json('parameters')->nullable();       // query params
            $table->timestamps();
        
            $table->unique(['scan_id', 'url', 'method']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('endpoints');
    }
};
