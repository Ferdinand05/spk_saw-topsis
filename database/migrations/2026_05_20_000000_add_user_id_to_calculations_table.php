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
        if (! Schema::hasColumn('calculations', 'user_id')) {
            Schema::table('calculations', function (Blueprint $table): void {
                $table->foreignId('user_id')
                    ->nullable()
                    ->constrained()
                    ->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Intentionally left blank.
        // The existing database already contains a manually added user_id column,
        // so rollback cannot safely distinguish between pre-existing and migrated state.
    }
};
