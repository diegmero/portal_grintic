<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Invoice;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        return [
            'number' => 'INV-' . now()->year . '-' . str_pad($this->faker->unique()->randomNumber(4), 4, '0', STR_PAD_LEFT),
            'company_id' => Company::factory(),
            'date' => now(),
            'due_date' => now()->addDays(30),
            'status' => 'sent',
            'total' => $this->faker->randomFloat(2, 100, 5000),
            'balance_due' => $this->faker->randomFloat(2, 0, 5000),
            'currency' => 'USD',
        ];
    }
}
