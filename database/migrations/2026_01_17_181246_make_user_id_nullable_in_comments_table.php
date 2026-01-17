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
        Schema::table('comments', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->string('user_id', 36)->nullable()->change();
            
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('comments', function (Blueprint $table) {
             // We can't easily revert nullable data to non-nullable without cleanup
             // Assuming we want to revert structure:
            $table->dropForeign(['user_id']);
            
            // Note: This might fail if there are null values
            // $table->string('user_id', 36)->nullable(false)->change();
            
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
        });
    }
};
