<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('student')->index();
            $table->foreignId('student_id')
                ->nullable()
                ->unique()
                ->constrained('students')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['student_id']);
            $table->dropUnique(['student_id']);
            $table->dropIndex(['role']);
            $table->dropColumn(['student_id', 'role']);
        });
    }
};
