<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_analyses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')->unique()->constrained('submissions')->cascadeOnDelete();
            $table->string('status')->default('pending');
            $table->text('summary')->nullable();
            $table->integer('plagiarism_score')->nullable();
            $table->json('plagiarism_detail')->nullable();
            $table->integer('structure_score')->nullable();
            $table->json('structure_detail')->nullable();
            $table->integer('quality_score')->nullable();
            $table->json('quality_detail')->nullable();
            $table->integer('overall_score')->nullable();
            $table->json('raw_response')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_analyses');
    }
};
