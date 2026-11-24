<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('requirements', function (Blueprint $table) {
            $table->unique(['student_id', 'requirement_name'], 'student_requirement_unique');
        });
    }

    public function down(): void
    {
        Schema::table('requirements', fn (Blueprint $table) => $table->dropUnique('student_requirement_unique'));
    }
};
