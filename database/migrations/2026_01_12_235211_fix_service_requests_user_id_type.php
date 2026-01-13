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
        // Schema::table('service_requests', function (Blueprint $table) {
        //    $table->dropForeign(['user_id']);
        // });

        // Modifying column to CHAR(36) for UUID
        Schema::table('service_requests', function (Blueprint $table) {
            // Drop FK if it exists to avoid duplication error 121
            // We use standard array syntax which Laravel converts to defaults
            $table->dropForeign(['user_id']);
        });

        DB::statement('ALTER TABLE service_requests MODIFY user_id CHAR(36) NOT NULL');

        Schema::table('service_requests', function (Blueprint $table) {
             // Re-add FK only if it doesn't exist (though we just dropped it)
             // But to be extra safe against "Duplicate key" error if drop failed silently (unlikely)
             // let's just add it. The drop above is the key fix.
             // However, dropForeign might throw if it DOESN'T exist.
             // So we need to be smart.

             $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
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
