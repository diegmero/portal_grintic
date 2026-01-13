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
            if (!Schema::hasColumn('service_requests', 'user_id')) {
                $table->foreignUuid('user_id')->after('id')->constrained()->cascadeOnDelete();
            }
            if (!Schema::hasColumn('service_requests', 'product_id')) {
                $table->foreignUuid('product_id')->after('user_id')->constrained()->cascadeOnDelete();
            }
            if (!Schema::hasColumn('service_requests', 'configuration')) {
                $table->json('configuration')->nullable()->after('product_id'); // Store selected addons
            }
            if (!Schema::hasColumn('service_requests', 'total_price')) {
                 $table->decimal('total_price', 10, 2)->after('configuration');
            }
            if (!Schema::hasColumn('service_requests', 'status')) {
                 $table->string('status')->default('pending')->after('total_price'); // pending, approved, rejected, completed
            }
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
