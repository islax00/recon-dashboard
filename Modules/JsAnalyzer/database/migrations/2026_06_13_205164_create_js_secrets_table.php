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
        Schema::create('js_secrets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('js_file_id')->constrained()->cascadeOnDelete();
            $table->string('type');                       // api_key, token, password, aws_key
            $table->text('value');
            $table->enum('severity', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->integer('line_number')->nullable();
            $table->float('confidence')->default(1.0);    // 0.0 - 1.0
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('js_secrets');
    }
};
