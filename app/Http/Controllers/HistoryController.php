<?php

namespace App\Http\Controllers;

use App\Services\JsonStorageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HistoryController extends Controller
{
    public function __construct(private JsonStorageService $storage) {}

    /**
     * GET /api/goals/{goalId}/history
     * List saving history for a goal.
     */
    public function index(string $goalId): JsonResponse
    {
        $goal = $this->storage->find('goals', $goalId);
        if (! $goal) {
            return response()->json(['message' => 'Goal not found.'], 404);
        }

        $history = $this->storage->where('history', 'goal_id', $goalId);

        // Sort by date descending
        usort($history, fn ($a, $b) => strcmp($b['date'] ?? '', $a['date'] ?? ''));

        return response()->json(['data' => $history]);
    }

    /**
     * GET /api/transactions
     * List all saving transactions with their goal names.
     */
    public function report(): JsonResponse
    {
        $goals = [];
        foreach ($this->storage->all('goals') as $goal) {
            $goals[$goal['id']] = $goal;
        }

        $transactions = array_map(function (array $entry) use ($goals): array {
            $goal = $goals[$entry['goal_id']] ?? null;

            return array_merge($entry, [
                'goal_name' => $goal['name'] ?? 'Deleted goal',
                'currency' => $goal['currency'] ?? 'USD',
            ]);
        }, $this->storage->all('history'));

        usort($transactions, function (array $a, array $b): int {
            $dateOrder = strcmp($b['date'] ?? '', $a['date'] ?? '');

            return $dateOrder !== 0
                ? $dateOrder
                : strcmp($b['created_at'] ?? '', $a['created_at'] ?? '');
        });

        return response()->json(['data' => $transactions]);
    }

    /**
     * POST /api/goals/{goalId}/history
     * Add a saving deposit or withdrawal.
     */
    public function store(Request $request, string $goalId): JsonResponse
    {
        $goal = $this->storage->find('goals', $goalId);
        if (! $goal) {
            return response()->json(['message' => 'Goal not found.'], 404);
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'type' => 'required|in:deposit,withdrawal',
            'note' => 'nullable|string|max:255',
            'date' => 'required|date',
        ]);

        $entry = $this->storage->create('history', array_merge($validated, [
            'goal_id' => $goalId,
        ]));

        // Update goal's saved_amount
        $currentSaved = (float) ($goal['saved_amount'] ?? 0);
        $amount = (float) $validated['amount'];
        $newSaved = $validated['type'] === 'deposit'
            ? $currentSaved + $amount
            : max(0, $currentSaved - $amount);
        $newSaved = round($newSaved, 2);

        $this->storage->update('goals', $goalId, ['saved_amount' => $newSaved]);

        return response()->json([
            'message' => 'History entry added.',
            'data' => $entry,
        ], 201);
    }

    /**
     * DELETE /api/history/{id}
     * Delete a history entry and revert the saved_amount.
     */
    public function destroy(string $id): JsonResponse
    {
        $entry = $this->storage->find('history', $id);
        if (! $entry) {
            return response()->json(['message' => 'Entry not found.'], 404);
        }

        $goal = $this->storage->find('goals', $entry['goal_id']);
        if ($goal) {
            $currentSaved = (float) ($goal['saved_amount'] ?? 0);
            $amount = (float) $entry['amount'];
            $newSaved = $entry['type'] === 'deposit'
                ? max(0, $currentSaved - $amount)
                : $currentSaved + $amount;
            $newSaved = round($newSaved, 2);

            $this->storage->update('goals', $goal['id'], ['saved_amount' => $newSaved]);
        }

        $this->storage->delete('history', $id);

        return response()->json(['message' => 'Entry deleted and saved amount reverted.']);
    }
}
