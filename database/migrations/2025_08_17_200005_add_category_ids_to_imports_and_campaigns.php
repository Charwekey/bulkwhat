<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('imports', function (Blueprint $table) {
            $table->foreignId('student_category_id')->nullable()->after('user_id')->constrained('student_categories')->nullOnDelete();
        });

        Schema::table('campaigns', function (Blueprint $table) {
            $table->foreignId('student_category_id')->nullable()->after('import_id')->constrained('student_categories')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('imports', function (Blueprint $table) {
            $table->dropForeign(['student_category_id']);
            $table->dropColumn('student_category_id');
        });

        Schema::table('campaigns', function (Blueprint $table) {
            $table->dropForeign(['student_category_id']);
            $table->dropColumn('student_category_id');
        });
    }
};
