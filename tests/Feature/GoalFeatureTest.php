<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GoalFeatureTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    public function test_can_list_empty_goals(): void
    {
        $response = $this->getJson('/api/goals');

        $response->assertStatus(200)
            ->assertJson([
                'data' => [],
            ]);
    }

    public function test_can_create_goal_with_defaults(): void
    {
        $response = $this->postJson('/api/goals', [
            'name' => 'New Laptop',
            'target_amount' => 1500,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'New Laptop')
            ->assertJsonPath('data.saved_amount', 0)
            ->assertJsonPath('data.currency', 'USD')
            ->assertJsonPath('data.color', '#6366f1')
            ->assertJsonPath('data.icon', 'piggy-bank')
            ->assertJsonPath('data.percentage', 0)
            ->assertJsonPath('data.remaining_amount', 1500)
            ->assertJsonPath('data.is_completed', false);
    }

    public function test_creating_goal_with_initial_saved_amount_creates_history_record(): void
    {
        $response = $this->postJson('/api/goals', [
            'name' => 'Emergency Fund',
            'target_amount' => 5000,
            'saved_amount' => 500,
        ]);

        $response->assertStatus(201);
        $goalId = $response->json('data.id');

        $historyResponse = $this->getJson("/api/goals/{$goalId}/history");
        $historyResponse->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.amount', 500)
            ->assertJsonPath('data.0.type', 'deposit')
            ->assertJsonPath('data.0.note', 'Initial deposit');
    }

    public function test_goal_with_empty_or_null_deadline_does_not_crash(): void
    {
        $response = $this->postJson('/api/goals', [
            'name' => 'General Savings',
            'target_amount' => 2000,
            'deadline' => null,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.remaining_days', null)
            ->assertJsonPath('data.required_daily', null);

        $listResponse = $this->getJson('/api/goals');
        $listResponse->assertStatus(200);
    }

    public function test_zero_or_missing_target_amount_goal_is_not_marked_completed(): void
    {
        $response = $this->postJson('/api/goals', [
            'name' => 'Open Ended Savings',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.is_completed', false)
            ->assertJsonPath('data.percentage', 0);
    }

    public function test_goal_computed_fields_are_accurate(): void
    {
        Carbon::setTestNow('2026-09-20 00:00:00');

        $response = $this->postJson('/api/goals', [
            'name' => 'Vacation',
            'target_amount' => 1000,
            'saved_amount' => 400,
            'deadline' => '2026-09-30',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.remaining_amount', 600)
            ->assertJsonPath('data.percentage', 40)
            ->assertJsonPath('data.remaining_days', 10)
            ->assertJsonPath('data.required_daily', 60)
            ->assertJsonPath('data.required_weekly', 420)
            ->assertJsonPath('data.required_monthly', 1800)
            ->assertJsonPath('data.is_completed', false);
    }

    public function test_can_show_single_goal(): void
    {
        $created = $this->postJson('/api/goals', [
            'name' => 'Car Fund',
            'target_amount' => 10000,
        ])->json('data');

        $response = $this->getJson("/api/goals/{$created['id']}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $created['id'])
            ->assertJsonPath('data.name', 'Car Fund');
    }

    public function test_show_non_existent_goal_returns_404(): void
    {
        $response = $this->getJson('/api/goals/non-existent-id');

        $response->assertStatus(404)
            ->assertJson(['message' => 'Goal not found.']);
    }

    public function test_can_update_goal_and_clear_deadline(): void
    {
        Carbon::setTestNow('2026-09-20 00:00:00');

        $created = $this->postJson('/api/goals', [
            'name' => 'Home Renovation',
            'target_amount' => 5000,
            'deadline' => '2026-12-31',
        ])->json('data');

        $updateResponse = $this->putJson("/api/goals/{$created['id']}", [
            'name' => 'Home Renovation 2026',
            'deadline' => null,
        ]);

        $updateResponse->assertStatus(200)
            ->assertJsonPath('data.name', 'Home Renovation 2026')
            ->assertJsonPath('data.deadline', null)
            ->assertJsonPath('data.remaining_days', null);
    }

    public function test_can_delete_goal_and_cascades_history(): void
    {
        $created = $this->postJson('/api/goals', [
            'name' => 'To Delete',
            'target_amount' => 1000,
            'saved_amount' => 200,
        ])->json('data');

        $deleteResponse = $this->deleteJson("/api/goals/{$created['id']}");
        $deleteResponse->assertStatus(200);

        $this->getJson("/api/goals/{$created['id']}")->assertStatus(404);

        $transactions = $this->getJson('/api/transactions')->json('data');
        $this->assertEmpty($transactions);
    }

    public function test_create_goal_validation_errors(): void
    {
        $response = $this->postJson('/api/goals', [
            'name' => '',
            'currency' => 'EUR',
            'target_amount' => 0,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'currency', 'target_amount']);
    }
}
