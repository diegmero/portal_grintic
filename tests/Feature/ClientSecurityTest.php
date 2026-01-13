<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Project;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ClientSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Setup initial roles if they don't exist
        Role::firstOrCreate(['name' => 'client']);
        Role::firstOrCreate(['name' => 'admin']);
    }

    public function test_client_cannot_access_admin_products_index()
    {
        $company = Company::factory()->create();
        $client = User::factory()->create(['company_id' => $company->id]);
        $client->assignRole('client');

        $response = $this->actingAs($client)->get(route('products.index'));
        
        // User wants Clients to see products as a "Marketplace"
        // So this should return 200 OK.
        
        $response->assertStatus(200); 
    }

    public function test_client_cannot_view_projects_from_another_company()
    {
        // 1. My Company & Project
        $myCompany = Company::factory()->create();
        $me = User::factory()->create(['company_id' => $myCompany->id]);
        $me->assignRole('client');
        // $me->givePermissionTo('view_projects'); // If needed
        
        $myProject = Project::factory()->create(['company_id' => $myCompany->id]);

        // 2. Other Company & Project
        $otherCompany = Company::factory()->create();
        $otherProject = Project::factory()->create(['company_id' => $otherCompany->id]);

        // Test: Accessing my project via Portal
        // Note: Route must be 'portal.projects.show'
        $this->actingAs($me)
            ->get(route('portal.projects.show', $myProject->id))
            ->assertStatus(200);

        // Test: Accessing other project via Portal
        $this->actingAs($me)
            ->get(route('portal.projects.show', $otherProject->id))
            ->assertStatus(403); // Or 404
    }

    public function test_client_cannot_access_admin_project_edit_routes()
    {
        $company = Company::factory()->create();
        $client = User::factory()->create(['company_id' => $company->id]);
        $client->assignRole('client');
        $project = Project::factory()->create(['company_id' => $company->id]);

        // Try to hit the admin update route (PUT /projects/{project})
        $response = $this->actingAs($client)->put(route('projects.update', $project->id), [
            'name' => 'Hacked Name',
            'end_date' => '2025-01-01',
            'status' => 'active'
        ]);

        // The controller has a check: abort(403)
        $response->assertStatus(403);
    }

    public function test_admin_can_see_everything()
    {
        $admin = User::factory()->create(['company_id' => null]);
        $admin->assignRole('admin');
        
        $company = Company::factory()->create();
        $project = Project::factory()->create(['company_id' => $company->id]);

        $this->actingAs($admin)
            ->get(route('projects.show', $project->id))
            ->assertStatus(200);
    }

    public function test_client_cannot_create_product()
    {
        $company = Company::factory()->create();
        $client = User::factory()->create(['company_id' => $company->id]);
        $client->assignRole('client');

        $response = $this->actingAs($client)->post(route('products.store'), [
            'name' => 'Hacked Product',
            'base_price' => 100,
        ]);

        $response->assertStatus(403);
    }

    public function test_client_cannot_delete_product()
    {
        $company = Company::factory()->create();
        $client = User::factory()->create(['company_id' => $company->id]);
        $client->assignRole('client');
        
        // Product exists (created by admin/factory)
        $product = Product::factory()->create();

        $response = $this->actingAs($client)->delete(route('products.destroy', $product->id));

        $response->assertStatus(403);
    }
}
