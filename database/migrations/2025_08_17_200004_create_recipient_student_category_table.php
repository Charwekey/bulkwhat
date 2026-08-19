<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recipient_student_category', function (Blueprint $table) {
            $table->foreignId('recipient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_category_id')->constrained()->cascadeOnDelete();
            $table->primary(['recipient_id', 'student_category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recipient_student_category');
    }
};
