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
        Schema::create('graph_edges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scan_id')->constrained()->cascadeOnDelete();
            $table->string('source_node_id');
            $table->string('target_node_id');
            $table->string('relation');                    // resolves_to, has_endpoint, contains, runs
            $table->timestamps();
        
            $table->unique(['scan_id', 'source_node_id', 'target_node_id', 'relation'], 'unique_edge');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('graph_edges');
    }
};
