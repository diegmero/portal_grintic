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
        // 1. Rename table
        Schema::rename('product_variants', 'product_addons');

        // 2. Update foreign keys in client_services if referencing variants
        // Check if column exists first to be safe (it should based on previous steps)
        if (Schema::hasColumn('client_services', 'product_variant_id')) {
            Schema::table('client_services', function (Blueprint $table) {
                $table->renameColumn('product_variant_id', 'product_addon_id');
            });
        }

        // 3. Add features to products
        Schema::table('products', function (Blueprint $table) {
            $table->json('features')->nullable()->after('description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Revert features
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('features');
        });

        // 2. Revert client_services column
        if (Schema::hasColumn('client_services', 'product_addon_id')) {
            Schema::table('client_services', function (Blueprint $table) {
                $table->renameColumn('product_addon_id', 'product_variant_id');
            });
        }

        // 3. Revert table name
        Schema::rename('product_addons', 'product_variants');
    }
};
