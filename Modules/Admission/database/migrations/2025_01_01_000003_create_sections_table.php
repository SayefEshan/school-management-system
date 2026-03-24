<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->string('name')->comment('e.g. A, B, C');
            $table->string('name_bn')->nullable()->comment('e.g. ক, খ, গ');
            $table->integer('capacity')->default(60);
            $table->integer('current_count')->default(0);
            $table->boolean('is_active')->default(true);
            tableDataInfo($table);
            $table->timestamps();

            $table->unique(['class_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sections');
    }
};
