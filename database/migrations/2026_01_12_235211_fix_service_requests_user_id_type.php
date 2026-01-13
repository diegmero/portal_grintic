<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Foreign key does not exist, so we skip dropping it.
        // Schema::table('service_requests', function (Blueprint $table) {
        //    $table->dropForeign(['user_id']);
        // });

        // Modifying column to CHAR(36) for UUID
        // Using raw statement to avoid doctrine dependency issues if not installed
        DB::statement('ALTER TABLE service_requests MODIFY user_id CHAR(36) NOT NULL');

        Schema::table('service_requests', function (Blueprint $table) {
            // Re-add FK
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('service_requests', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        // Revert to BIGINT (unsigned)
        DB::statement('ALTER TABLE service_requests MODIFY user_id BIGINT UNSIGNED NOT NULL');
    }
};
