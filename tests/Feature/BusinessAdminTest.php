<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BusinessAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_login_and_view_dashboard(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->post('/login', [
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect('/admin/dashboard');
        $this->assertAuthenticatedAs($user);

        $dashboardRes = $this->get('/admin/dashboard');
        $dashboardRes->assertStatus(200)
            ->assertSee('Dashboard Overview')
            ->assertSee('Total Businesses');
    }

    public function test_admin_can_filter_and_search_businesses(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Business::create([
            'user_id' => $user->id,
            'name' => 'Royal Hotel',
            'city' => 'Ahmedabad',
            'category' => 'Hotel',
        ]);

        Business::create([
            'user_id' => $user->id,
            'name' => 'Apex Dental',
            'city' => 'Mumbai',
            'category' => 'Dentist',
        ]);

        $searchRes = $this->get('/admin/businesses?search=Royal');
        $searchRes->assertStatus(200)
            ->assertSee('Royal Hotel')
            ->assertDontSee('Apex Dental');

        $filterRes = $this->get('/admin/businesses?city=Mumbai');
        $filterRes->assertStatus(200)
            ->assertSee('Apex Dental')
            ->assertDontSee('Royal Hotel');
    }

    public function test_admin_can_delete_single_and_bulk_businesses(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $b1 = Business::create([
            'user_id' => $user->id,
            'name' => 'Store 1',
        ]);

        $b2 = Business::create([
            'user_id' => $user->id,
            'name' => 'Store 2',
        ]);

        $b3 = Business::create([
            'user_id' => $user->id,
            'name' => 'Store 3',
        ]);

        // Delete single
        $deleteSingleRes = $this->delete("/admin/businesses/{$b1->id}");
        $deleteSingleRes->assertRedirect('/admin/businesses');
        $this->assertDatabaseMissing('businesses', ['id' => $b1->id]);

        // Bulk delete
        $bulkDeleteRes = $this->post('/admin/businesses/bulk-delete', [
            'ids' => [$b2->id, $b3->id],
        ]);

        $bulkDeleteRes->assertRedirect('/admin/businesses');
        $this->assertDatabaseCount('businesses', 0);
    }

    public function test_csv_export_streams_file_with_headers(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Business::create([
            'user_id' => $user->id,
            'name' => 'Export Business',
            'phone' => '+1234567890',
            'city' => 'Ahmedabad',
        ]);

        $response = $this->get('/admin/businesses/export/all');

        $response->assertStatus(200)
            ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

        $content = $response->streamedContent();
        $this->assertStringContainsString('Business Name', $content);
        $this->assertStringContainsString('Phone', $content);
        $this->assertStringContainsString('Export Business', $content);
    }
}
