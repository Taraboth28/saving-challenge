<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HistoryFeatureTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    public function test_can_list_history_for_goal_sorted_descending(): void
    {
        $goal = $this->postJson('/api/goals', [
            'name' => 'College Fund',
            'target_amount' => 5000,
        ])->json('data');

        $this->postJson("/api/goals/{$goal['id']}/history", [
            'amount' => 100,
            'type' => 'deposit',
            'date' => '2026-09-01T10:00',
        ])->assertStatus(201);

        $this->postJson("/api/goals/{$goal['id']}/history", [
            'amount' => 200,
            'type' => 'deposit',
            'date' => '2026-09-15T12:00',
        ])->assertStatus(201);

        $response = $this->getJson("/api/goals/{$goal['id']}/history");

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.amount', 200)
            ->assertJsonPath('data.1.amount', 100);
    }

    public function test_cannot_list_history_for_non_existent_goal(): void
    {
        $this->getJson('/api/goals/invalid-goal/history')
            ->assertStatus(404);
    }

    public function test_adding_deposit_updates_goal_saved_amount(): void
    {
        $goal = $this->postJson('/api/goals', [
            'name' => 'Bike Fund',
            'target_amount' => 500,
            'saved_amount' => 50,
        ])->json('data');

        $response = $this->postJson("/api/goals/{$goal['id']}/history", [
            'amount' => 75.50,
            'type' => 'deposit',
            'note' => 'Birthday cash',
            'date' => '2026-09-20T10:00',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.amount', 75.50)
            ->assertJsonPath('data.type', 'deposit');

        $updatedGoal = $this->getJson("/api/goals/{$goal['id']}")->json('data');
        $this->assertEquals(125.50, $updatedGoal['saved_amount']);
    }

    public function test_adding_withdrawal_decreases_goal_saved_amount(): void
    {
        $goal = $this->postJson('/api/goals', [
            'name' => 'Emergency Fund',
            'target_amount' => 2000,
            'saved_amount' => 500,
        ])->json('data');

        $response = $this->postJson("/api/goals/{$goal['id']}/history", [
            'amount' => 150,
            'type' => 'withdrawal',
            'note' => 'Car tire repair',
            'date' => '2026-09-20T15:00',
        ]);

        $response->assertStatus(201);

        $updatedGoal = $this->getJson("/api/goals/{$goal['id']}")->json('data');
        $this->assertEquals(350, $updatedGoal['saved_amount']);
    }

    public function test_deleting_history_entry_reverts_goal_saved_amount(): void
    {
        $goal = $this->postJson('/api/goals', [
            'name' => 'Vacation',
            'target_amount' => 1000,
        ])->json('data');

        $entry = $this->postJson("/api/goals/{$goal['id']}/history", [
            'amount' => 300,
            'type' => 'deposit',
            'date' => '2026-09-10T10:00',
        ])->json('data');

        $this->assertEquals(300, $this->getJson("/api/goals/{$goal['id']}")->json('data.saved_amount'));

        $this->deleteJson("/api/history/{$entry['id']}")->assertStatus(200);

        $this->assertEquals(0, $this->getJson("/api/goals/{$goal['id']}")->json('data.saved_amount'));
    }

    public function test_transaction_report_returns_all_transactions_with_goal_details(): void
    {
        $goal1 = $this->postJson('/api/goals', ['name' => 'Goal 1', 'currency' => 'USD'])->json('data');
        $goal2 = $this->postJson('/api/goals', ['name' => 'Goal 2', 'currency' => 'KHR'])->json('data');

        $this->postJson("/api/goals/{$goal1['id']}/history", [
            'amount' => 50,
            'type' => 'deposit',
            'date' => '2026-09-01T10:00',
        ]);

        $this->postJson("/api/goals/{$goal2['id']}/history", [
            'amount' => 200000,
            'type' => 'deposit',
            'date' => '2026-09-02T10:00',
        ]);

        $response = $this->getJson('/api/transactions');
        $response->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.goal_name', 'Goal 2')
            ->assertJsonPath('data.0.currency', 'KHR')
            ->assertJsonPath('data.1.goal_name', 'Goal 1')
            ->assertJsonPath('data.1.currency', 'USD');
    }

    public function test_cannot_add_history_to_non_existent_goal(): void
    {
        $this->postJson('/api/goals/fake-id/history', [
            'amount' => 50,
            'type' => 'deposit',
            'date' => '2026-09-20',
        ])->assertStatus(404);
    }

    public function test_cannot_delete_non_existent_history_entry(): void
    {
        $this->deleteJson('/api/history/fake-entry-id')->assertStatus(404);
    }
}
