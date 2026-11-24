<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('requirements', fn (Blueprint $table) => $table->index(['status', 'id'], 'requirements_status_id_index'));
    }

    public function down(): void
    {
        Schema::table('requirements', fn (Blueprint $table) => $table->dropIndex('requirements_status_id_index'));
    }
};
