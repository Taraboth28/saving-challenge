<?php

namespace App\Http\Controllers;

use App\Services\JsonStorageService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GoalController extends Controller
{
    public function __construct(private JsonStorageService $storage) {}

    /**
     * GET /api/goals
     * List all saving goals.
     */
    public function index(): JsonResponse
    {
        $goals = $this->storage->all('goals');

        // Attach computed fields
        $goals = array_map(fn ($g) => $this->withComputed($g), $goals);

        return response()->json([
            'data' => array_values($goals),
        ]);
    }

    /**
     * POST /api/goals
     * Create a new saving goal.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'target_amount' => 'nullable|numeric|min:1',
            'saved_amount' => 'nullable|numeric|min:0',
            'deadline' => 'nullable|date|after:today',
            'currency' => 'nullable|in:USD,KHR',
            'color' => 'nullable|string|max:20',
            'icon' => 'nullable|string|max:50',
        ]);

        $data = array_merge([
            'saved_amount' => 0,
            'currency' => 'USD',
            'color' => '#6366f1',
            'icon' => 'piggy-bank',
        ], $validated);

        $goal = $this->storage->create('goals', $data);

        return response()->json([
            'message' => 'Goal created successfully.',
            'data' => $this->withComputed($goal),
        ], 201);
    }

    /**
     * GET /api/goals/{id}
     * Show a single saving goal.
     */
    public function show(string $id): JsonResponse
    {
        $goal = $this->storage->find('goals', $id);
        if (! $goal) {
            return response()->json(['message' => 'Goal not found.'], 404);
        }

        return response()->json([
            'data' => $this->withComputed($goal),
        ]);
    }

    /**
     * PUT /api/goals/{id}
     * Update a saving goal.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $goal = $this->storage->find('goals', $id);
        if (! $goal) {
            return response()->json(['message' => 'Goal not found.'], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:100',
            'description' => 'nullable|string|max:500',
            'target_amount' => 'sometimes|numeric|min:1',
            'saved_amount' => 'sometimes|numeric|min:0',
            'deadline' => 'sometimes|date',
            'currency' => 'nullable|in:USD,KHR',
            'color' => 'nullable|string|max:20',
            'icon' => 'nullable|string|max:50',
        ]);

        $updated = $this->storage->update('goals', $id, $validated);

        return response()->json([
            'message' => 'Goal updated successfully.',
            'data' => $this->withComputed($updated),
        ]);
    }

    /**
     * DELETE /api/goals/{id}
     * Delete a saving goal and its history.
     */
    public function destroy(string $id): JsonResponse
    {
        $goal = $this->storage->find('goals', $id);
        if (! $goal) {
            return response()->json(['message' => 'Goal not found.'], 404);
        }

        $this->storage->delete('goals', $id);
        // Also remove all related history entries
        $this->storage->deleteWhere('history', 'goal_id', $id);

        return response()->json(['message' => 'Goal deleted successfully.']);
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    /**
     * Attach computed/derived fields to a goal array.
     */
    private function withComputed(array $goal): array
    {
        $target = (float) ($goal['target_amount'] ?? 0);
        $saved = (float) ($goal['saved_amount'] ?? 0);
        $remaining = max(0, $target - $saved);
        $percent = $target > 0 ? round(($saved / $target) * 100, 2) : 0;

        $deadline = isset($goal['deadline']) ? Carbon::parse($goal['deadline']) : null;
        $remainingDays = $deadline ? max(0, (int) now()->startOfDay()->diffInDays($deadline->startOfDay(), false)) : null;

        // Required daily / weekly / monthly savings
        $requiredDaily = ($remainingDays && $remainingDays > 0) ? round($remaining / $remainingDays, 2) : null;
        $requiredWeekly = $requiredDaily !== null ? round($requiredDaily * 7, 2) : null;
        $requiredMonthly = $requiredDaily !== null ? round($requiredDaily * 30, 2) : null;

        return array_merge($goal, [
            'remaining_amount' => $remaining,
            'percentage' => $percent,
            'remaining_days' => $remainingDays,
            'required_daily' => $requiredDaily,
            'required_weekly' => $requiredWeekly,
            'required_monthly' => $requiredMonthly,
            'is_completed' => $saved >= $target,
        ]);
    }
}
