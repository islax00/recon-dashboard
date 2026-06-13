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
        Schema::create('js_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('endpoint_id')->nullable()->constrained()->nullOnDelete();
            $table->string('url');
            $table->bigInteger('size')->nullable();
            $table->boolean('is_analyzed')->default(false);
            $table->timestamps();
        
            $table->unique(['scan_id', 'url']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('js_files');
    }
};
