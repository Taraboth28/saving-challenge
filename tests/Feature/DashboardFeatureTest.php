<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DashboardFeatureTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    public function test_dashboard_stats_empty_state(): void
    {
        $response = $this->getJson('/api/dashboard/stats');

        $response->assertStatus(200)
            ->assertJsonPath('data.total_goals', 0)
            ->assertJsonPath('data.active_goals', 0)
            ->assertJsonPath('data.completed_goals', 0)
            ->assertJsonPath('data.total_target', 0)
            ->assertJsonPath('data.total_saved', 0)
            ->assertJsonPath('data.total_remaining', 0)
            ->assertJsonPath('data.overall_percent', 0)
            ->assertJsonCount(6, 'data.monthly_trend');
    }

    public function test_dashboard_stats_aggregates_properly(): void
    {
        // Completed goal
        $this->postJson('/api/goals', [
            'name' => 'Goal 1',
            'target_amount' => 500,
            'saved_amount' => 500,
        ]);

        // Active goal
        $this->postJson('/api/goals', [
            'name' => 'Goal 2',
            'target_amount' => 1000,
            'saved_amount' => 200,
        ]);

        $response = $this->getJson('/api/dashboard/stats');

        $response->assertStatus(200)
            ->assertJsonPath('data.total_goals', 2)
            ->assertJsonPath('data.active_goals', 1)
            ->assertJsonPath('data.completed_goals', 1)
            ->assertJsonPath('data.total_target', 1500)
            ->assertJsonPath('data.total_saved', 700)
            ->assertJsonPath('data.total_remaining', 800)
            ->assertJsonPath('data.overall_percent', 46.67);
    }

    public function test_zero_target_goal_is_not_counted_as_completed(): void
    {
        $this->postJson('/api/goals', [
            'name' => 'Targetless Goal',
            'saved_amount' => 0,
        ]);

        $response = $this->getJson('/api/dashboard/stats');

        $response->assertStatus(200)
            ->assertJsonPath('data.total_goals', 1)
            ->assertJsonPath('data.active_goals', 1)
            ->assertJsonPath('data.completed_goals', 0);
    }

    public function test_monthly_trend_does_not_overflow_or_skip_february_on_31st(): void
    {
        Carbon::setTestNow('2026-03-31 12:00:00');

        $response = $this->getJson('/api/dashboard/stats');

        $response->assertStatus(200);
        $trend = $response->json('data.monthly_trend');

        $months = array_column($trend, 'month');
        $labels = array_column($trend, 'label');

        $this->assertCount(6, $months);
        $this->assertEquals([
            '2025-10',
            '2025-11',
            '2025-12',
            '2026-01',
            '2026-02',
            '2026-03',
        ], $months);

        $this->assertEquals([
            'Oct 2025',
            'Nov 2025',
            'Dec 2025',
            'Jan 2026',
            'Feb 2026',
            'Mar 2026',
        ], $labels);
    }

    public function test_monthly_trend_calculates_deposits_withdrawals_and_net(): void
    {
        Carbon::setTestNow('2026-09-20 00:00:00');

        $goal = $this->postJson('/api/goals', [
            'name' => 'Vacation',
            'target_amount' => 2000,
        ])->json('data');

        $this->postJson("/api/goals/{$goal['id']}/history", [
            'amount' => 300,
            'type' => 'deposit',
            'date' => '2026-09-05T10:00',
        ]);

        $this->postJson("/api/goals/{$goal['id']}/history", [
            'amount' => 50,
            'type' => 'withdrawal',
            'date' => '2026-09-10T12:00',
        ]);

        $response = $this->getJson('/api/dashboard/stats');
        $response->assertStatus(200);

        $trend = $response->json('data.monthly_trend');
        $currentMonth = end($trend);

        $this->assertEquals('2026-09', $currentMonth['month']);
        $this->assertEquals(300, $currentMonth['deposit']);
        $this->assertEquals(50, $currentMonth['withdrawal']);
        $this->assertEquals(250, $currentMonth['net']);
    }
}
