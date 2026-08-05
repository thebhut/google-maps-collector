<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BusinessApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_bulk_business_upload_and_duplicate_detection(): void
    {
        $user = User::factory()->create();

        $payload = [
            'businesses' => [
                [
                    'name' => 'First Doctor Clinic',
                    'phone' => '+91 98765 43210',
                    'address' => '101 Medical Center',
                    'city' => 'Ahmedabad',
                    'category' => 'Clinic',
                    'rating' => 4.8,
                    'review_count' => 50,
                    'place_id' => 'place_abc_123',
                ],
                [
                    'name' => 'Second Doctor Clinic',
                    'phone' => '+91 98765 43211',
                    'address' => '102 Medical Center',
                    'city' => 'Ahmedabad',
                    'category' => 'Clinic',
                    'rating' => 4.5,
                    'review_count' => 30,
                    'place_id' => 'place_def_456',
                ],
            ],
        ];

        $response = $this->postJson('/api/v1/businesses/bulk', $payload);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('summary.inserted', 2)
            ->assertJsonPath('summary.collected', 2);

        $this->assertDatabaseCount('businesses', 2);

        // Upload duplicate record with updated rating
        $duplicatePayload = [
            'businesses' => [
                [
                    'name' => 'First Doctor Clinic',
                    'phone' => '+91 98765 43210',
                    'address' => '101 Medical Center',
                    'city' => 'Ahmedabad',
                    'category' => 'Clinic',
                    'rating' => 4.9, // Updated rating
                    'review_count' => 55,
                    'place_id' => 'place_abc_123',
                ],
            ],
        ];

        $dupResponse = $this->postJson('/api/v1/businesses/bulk', $duplicatePayload);

        $dupResponse->assertStatus(200)
            ->assertJsonPath('summary.inserted', 0)
            ->assertJsonPath('summary.updated', 1)
            ->assertJsonPath('summary.collected', 1);

        $this->assertDatabaseCount('businesses', 2);

        $updated = Business::where('place_id', 'place_abc_123')->first();
        $this->assertEquals(4.9, (float) $updated->rating);
        $this->assertEquals(55, $updated->review_count);
    }

    public function test_bulk_upload_skips_invalid_records_without_name(): void
    {
        $user = User::factory()->create();

        $payload = [
            'businesses' => [
                [
                    'name' => '', // Missing name
                    'phone' => '+91 99999 99999',
                ],
                [
                    'name' => 'Valid Spa Center',
                    'city' => 'Mumbai',
                ],
            ],
        ];

        $response = $this->postJson('/api/v1/businesses/bulk', $payload);

        $response->assertStatus(200)
            ->assertJsonPath('summary.inserted', 1)
            ->assertJsonPath('summary.failed', 1);

        $this->assertDatabaseCount('businesses', 1);
        $this->assertDatabaseCount('collection_errors', 1);
    }

    public function test_remote_error_logging_api(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/v1/errors', [
            'error_type' => 'PARSER_DOM_ERROR',
            'message' => 'DOM structure selector missing',
            'payload' => ['url' => 'https://www.google.com/maps/search/dentist'],
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true);

        $this->assertDatabaseCount('collection_errors', 1);
    }
}
