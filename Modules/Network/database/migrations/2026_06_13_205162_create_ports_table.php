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
        Schema::create('ports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ip_address_id')->constrained()->cascadeOnDelete();
            $table->integer('port');
            $table->string('protocol')->default('tcp');  // tcp, udp
            $table->string('service')->nullable();        // http, ssh, ftp
            $table->string('banner')->nullable();
            $table->boolean('is_open')->default(true);
            $table->timestamps();
        
            $table->unique(['ip_address_id', 'port', 'protocol']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ports');
    }
};
