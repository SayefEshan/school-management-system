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
        Schema::create('download_import_managers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->comment('Requested User ID')->index();
            $table->string('title');
            $table->string('url')->nullable();
            $table->longText('remarks')->nullable();
            $table->enum('status', ['Pending', 'Processing', 'Failed', 'Completed'])->default('Pending');
            $table->enum('type', ['Import', 'Download']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('download_import_managers');
    }
};
