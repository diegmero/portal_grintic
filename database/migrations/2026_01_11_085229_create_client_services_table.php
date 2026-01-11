<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_services', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignUuid('product_id')->constrained('products')->cascadeOnDelete();
            $table->decimal('custom_price', 15, 2)->nullable(); // Precio pactado, si es null usa base_price
            $table->date('start_date');
            $table->date('end_date')->nullable(); // Fecha vencimiento para suscripciones
            $table->string('status')->default('active'); // active, expired, cancelled, suspended
            $table->text('credentials')->nullable(); // URLs, usuarios, contraseñas (encriptado)
            $table->text('notes')->nullable(); // Notas internas
            $table->timestamps();

            $table->index(['company_id', 'status']);
            $table->index('end_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_services');
    }
};
