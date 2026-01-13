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
        Schema::table('service_requests', function (Blueprint $table) {
            $table->foreignUuid('user_id')->after('id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('product_id')->after('user_id')->constrained()->cascadeOnDelete();
            $table->json('configuration')->nullable()->after('product_id'); // Store selected addons
            $table->decimal('total_price', 10, 2)->after('configuration');
            $table->string('status')->default('pending')->after('total_price'); // pending, approved, rejected, completed
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_requests', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['product_id']);
            $table->dropColumn(['user_id', 'product_id', 'configuration', 'total_price', 'status']);
        });
    }
};
