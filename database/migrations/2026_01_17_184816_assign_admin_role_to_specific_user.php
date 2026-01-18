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
        $userId = '019bb36d-6f41-739a-8bee-9872e66811d9';
        
        $user = \App\Models\User::find($userId);
        $role = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin']);

        if ($user) {
            $user->assignRole($role);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Optional: Remove role if needed, but risky to revoke admin rights automatically
    }
};
