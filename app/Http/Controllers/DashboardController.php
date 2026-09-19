<?php

namespace App\Http\Controllers;

use App\Services\JsonStorageService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function __construct(private JsonStorageService $storage) {}

    /**
     * GET /api/dashboard/stats
     * Return aggregated stats for the dashboard.
     */
    public function stats(): JsonResponse
    {
        $goals = $this->storage->all('goals');
        $history = $this->storage->all('history');

        $totalGoals = count($goals);
        $completedGoals = 0;
        $totalTarget = 0;
        $totalSaved = 0;

        $goalBreakdown = [];

        foreach ($goals as $goal) {
            $target = (float) ($goal['target_amount'] ?? 0);
            $saved = (float) ($goal['saved_amount'] ?? 0);

            $totalTarget += $target;
            $totalSaved += $saved;

            if ($target > 0 && $saved >= $target) {
                $completedGoals++;
            }

            $goalBreakdown[] = [
                'id' => $goal['id'],
                'name' => $goal['name'],
                'color' => $goal['color'] ?? '#6366f1',
                'target_amount' => $target,
                'saved_amount' => $saved,
                'percentage' => $target > 0 ? round(($saved / $target) * 100, 2) : 0,
            ];
        }

        $totalRemaining = max(0, $totalTarget - $totalSaved);
        $overallPercent = $totalTarget > 0 ? round(($totalSaved / $totalTarget) * 100, 2) : 0;
        $activeGoals = $totalGoals - $completedGoals;

        // Monthly saving trend (last 6 months)
        $trend = $this->buildMonthlyTrend($history);

        return response()->json([
            'data' => [
                'total_goals' => $totalGoals,
                'active_goals' => $activeGoals,
                'completed_goals' => $completedGoals,
                'total_target' => round($totalTarget, 2),
                'total_saved' => round($totalSaved, 2),
                'total_remaining' => round($totalRemaining, 2),
                'overall_percent' => $overallPercent,
                'goal_breakdown' => $goalBreakdown,
                'monthly_trend' => $trend,
            ],
        ]);
    }

    private function buildMonthlyTrend(array $history): array
    {
        $months = [];
        for ($i = 5; $i >= 0; $i--) {
            $months[] = now()->startOfMonth()->subMonthsNoOverflow($i)->format('Y-m');
        }

        $trend = [];
        foreach ($months as $month) {
            $deposited = 0;
            $withdrawn = 0;
            foreach ($history as $entry) {
                $entryMonth = substr($entry['date'] ?? '', 0, 7);
                if ($entryMonth === $month) {
                    if (($entry['type'] ?? '') === 'deposit') {
                        $deposited += (float) ($entry['amount'] ?? 0);
                    } else {
                        $withdrawn += (float) ($entry['amount'] ?? 0);
                    }
                }
            }
            $trend[] = [
                'month' => $month,
                'label' => Carbon::parse("{$month}-01")->format('M Y'),
                'deposit' => round($deposited, 2),
                'withdrawal' => round($withdrawn, 2),
                'net' => round($deposited - $withdrawn, 2),
            ];
        }

        return $trend;
    }
}
