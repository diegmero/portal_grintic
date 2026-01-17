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
        // 1. Add company_id (nullable initially)
        Schema::table('service_requests', function (Blueprint $table) {
            $table->foreignUuid('company_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
        });

        // 2. Populate company_id from users
        // Usage of raw SQL for performance and simplicity
        DB::statement('
            UPDATE service_requests
            JOIN users ON service_requests.user_id = users.id
            SET service_requests.company_id = users.company_id
        ');

        // 3. Make user_id nullable and change FK to SET NULL
        Schema::table('service_requests', function (Blueprint $table) {
             // Drop old FK
            $table->dropForeign(['user_id']);
            
            // Modify column to be nullable
            $table->string('user_id', 36)->nullable()->change();

            // Add new FK with SET NULL
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('service_requests', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropColumn('company_id');
            
            $table->dropForeign(['user_id']);
            $table->string('user_id', 36)->nullable(false)->change();
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
        });
    }
};
