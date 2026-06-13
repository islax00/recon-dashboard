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
        Schema::create('findings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->constrained()->cascadeOnDelete();
            $table->foreignId('scan_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description');
            $table->enum('severity', ['info', 'low', 'medium', 'high', 'critical']);
            $table->string('type');                        // secret, open_port, missing_header
            $table->morphs('findable');                    // polymorphic: JsSecret, Port, Endpoint
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('findings');
    }
};
