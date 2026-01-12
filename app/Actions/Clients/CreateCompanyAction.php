<?php

namespace App\Actions\Clients;

use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateCompanyAction
{
    public function execute(array $data): Company
    {
        return DB::transaction(function () use ($data) {
            // 1. Create Company
            $company = Company::create([
                'name' => $data['company_name'],
                'tax_id' => $data['tax_id'],
                'country' => $data['country'],
                'currency' => $data['currency'] ?? 'USD',
                'address' => $data['address'] ?? null,
            ]);

            // 2. Generate Secure Password
            $password = Str::password(12);

            // 3. Create Admin User (Contact)
            $user = User::create([
                'company_id' => $company->id,
                'name' => $data['contact_name'],
                'email' => $data['contact_email'],
                'password' => Hash::make($password),
            ]);

            $user->assignRole('client');

            // 4. TODO: Send Welcome Email with Password
            // Mail::to($user)->send(new WelcomeClientMail($user, $password));

            return $company;
        });
    }
}
