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
        // 1. Add product_category_id to products
        Schema::table('products', function (Blueprint $table) {
            $table->foreignUuid('product_category_id')
                ->nullable() // Nullable at first to allow seeding
                ->after('id')
                ->constrained('product_categories')
                ->nullOnDelete();
        });

        // 2. Add product_variant_id to client_services
        Schema::table('client_services', function (Blueprint $table) {
            $table->foreignUuid('product_variant_id')
                ->nullable()
                ->after('product_id')
                ->constrained('product_variants')
                ->nullOnDelete();
        });

        // 3. Seed Categories from Enum & Migrate Existing Data
        $categories = \App\Enums\ProductCategory::cases();
        
        foreach ($categories as $categoryEnum) {
            // Create Category in DB
            $catId = \Illuminate\Support\Str::uuid();
            \Illuminate\Support\Facades\DB::table('product_categories')->insert([
                'id' => $catId,
                'name' => $categoryEnum->label(),
                'slug' => $categoryEnum->value, // hosting, servers, etc.
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Update existing products matching this enum category
            \Illuminate\Support\Facades\DB::table('products')
                ->where('category', $categoryEnum->value)
                ->update(['product_category_id' => $catId]);
        }
    }

    public function down(): void
    {
        Schema::table('client_services', function (Blueprint $table) {
            $table->dropForeign(['product_variant_id']);
            $table->dropColumn('product_variant_id');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['product_category_id']);
            $table->dropColumn('product_category_id');
        });
    }
};
