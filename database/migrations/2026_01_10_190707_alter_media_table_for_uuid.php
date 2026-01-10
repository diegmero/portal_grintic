<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Clear existing media to avoid data conflicts
        DB::table('media')->truncate();

        // Drop the old model_id column and recreate as string for UUID
        Schema::table('media', function (Blueprint $table) {
            $table->dropIndex(['model_type', 'model_id']);
            $table->dropColumn('model_id');
        });

        Schema::table('media', function (Blueprint $table) {
            $table->string('model_id', 36)->after('id');
            $table->index(['model_type', 'model_id']);
        });
    }

    public function down(): void
    {
        Schema::table('media', function (Blueprint $table) {
            $table->dropIndex(['model_type', 'model_id']);
            $table->dropColumn('model_id');
        });

        Schema::table('media', function (Blueprint $table) {
            $table->unsignedBigInteger('model_id')->after('id');
            $table->index(['model_type', 'model_id']);
        });
    }
};
