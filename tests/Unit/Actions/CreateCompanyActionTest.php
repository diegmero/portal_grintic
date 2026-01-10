<?php

namespace Tests\Unit\Actions;

use App\Actions\Clients\CreateCompanyAction;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateCompanyActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_company_and_admin_user(): void
    {
        $action = new CreateCompanyAction();

        $data = [
            'company_name' => 'Acme Corp',
            'tax_id' => '123456789',
            'country' => 'Colombia',
            'contact_name' => 'John Doe',
            'contact_email' => 'john@acme.com',
        ];

        $company = $action->execute($data);

        $this->assertDatabaseHas('companies', [
            'name' => 'Acme Corp',
            'tax_id' => '123456789',
            'country' => 'Colombia',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'john@acme.com',
            'company_id' => $company->id,
        ]);
    }
}
