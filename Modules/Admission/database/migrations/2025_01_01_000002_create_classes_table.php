<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('classes', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('e.g. Class 6, Class 7');
            $table->string('name_bn')->nullable()->comment('e.g. ষষ্ঠ শ্রেণী');
            $table->string('numeric_code', 2)->unique()->comment('e.g. 06, 07, 08');
            $table->integer('order')->default(0)->comment('Display order');
            $table->boolean('is_active')->default(true);
            tableDataInfo($table);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('classes');
    }
};
