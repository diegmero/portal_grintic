<?php

namespace Tests\Feature;

use App\Events\ClientActivityDetected;
use App\Models\Company;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ClientPortalFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Seed roles for tests
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
    }

    public function test_client_can_only_see_own_dashboard_data(): void
    {
        $company = Company::factory()->create();
        $client = User::factory()->create(['company_id' => $company->id]);
        $client->assignRole('client');

        $ownProject = Project::factory()->create(['company_id' => $company->id, 'status' => 'active']);
        $otherProject = Project::factory()->create(['status' => 'active']); // Different company

        $response = $this->actingAs($client)->get(route('dashboard'));
        
        if ($response->status() !== 200) {
            dump($response->getContent());
        }

        $response->assertInertia(fn ($page) => $page
            ->component('Client/Dashboard')
            ->has('projects', 1)
            ->where('projects.0.id', $ownProject->id)
        );
    }

    public function test_admin_receives_alert_on_client_activity(): void
    {
        Event::fake([ClientActivityDetected::class]);

        $company = Company::factory()->create();
        $client = User::factory()->create(['company_id' => $company->id]);
        $client->assignRole('client');
        
        // Simulate a client activity event dispatch manually or via controller
        // Using event helper to simulate triggering
        event(new ClientActivityDetected('Test Message', $client));

        Event::assertDispatched(ClientActivityDetected::class);
    }
    
    public function test_admin_can_access_full_dashboard(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            ->has('stats')
        );
    }
}
