<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * JsonStorageService
 *
 * Abstracts all file-based JSON storage operations.
 * This is the migration seam: replace with EloquentStorageService
 * when ready to move to MySQL, binding it in AppServiceProvider.
 */
class JsonStorageService
{
    /**
     * Read all records from a JSON store.
     */
    public function all(string $store): array
    {
        return $this->read($store);
    }

    /**
     * Find a single record by ID.
     */
    public function find(string $store, string $id): ?array
    {
        $records = $this->read($store);
        $index = $this->indexOf($records, $id);

        return $index !== -1 ? $records[$index] : null;
    }

    /**
     * Find all records matching a key-value pair.
     */
    public function where(string $store, string $key, mixed $value): array
    {
        return array_values(
            array_filter($this->read($store), fn ($r) => ($r[$key] ?? null) === $value)
        );
    }

    /**
     * Create a new record (auto-generates UUID + timestamps).
     */
    public function create(string $store, array $data): array
    {
        $records = $this->read($store);
        $now = now()->toIso8601String();
        $record = array_merge([
            'id' => (string) Str::uuid(),
            'created_at' => $now,
            'updated_at' => $now,
        ], $data);
        $records[] = $record;
        $this->write($store, $records);

        return $record;
    }

    /**
     * Update an existing record by ID.
     */
    public function update(string $store, string $id, array $data): ?array
    {
        $records = $this->read($store);
        $index = $this->indexOf($records, $id);
        if ($index === -1) {
            return null;
        }

        $records[$index] = array_merge($records[$index], $data, [
            'id' => $id,
            'updated_at' => now()->toIso8601String(),
        ]);
        $this->write($store, $records);

        return $records[$index];
    }

    /**
     * Delete a record by ID.
     */
    public function delete(string $store, string $id): bool
    {
        $records = $this->read($store);
        $index = $this->indexOf($records, $id);
        if ($index === -1) {
            return false;
        }

        array_splice($records, $index, 1);
        $this->write($store, $records);

        return true;
    }

    /**
     * Delete all records matching a key-value pair.
     */
    public function deleteWhere(string $store, string $key, mixed $value): int
    {
        $records = $this->read($store);
        $before = count($records);
        $records = array_values(
            array_filter($records, fn ($r) => ($r[$key] ?? null) !== $value)
        );
        $this->write($store, $records);

        return $before - count($records);
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    private function path(string $store): string
    {
        return "data/{$store}.json";
    }

    private function read(string $store): array
    {
        $path = $this->path($store);
        if (! Storage::exists($path)) {
            Storage::put($path, json_encode([]));

            return [];
        }
        $contents = Storage::get($path);

        return json_decode($contents, true) ?? [];
    }

    private function write(string $store, array $records): void
    {
        Storage::put($this->path($store), json_encode(array_values($records), JSON_PRETTY_PRINT));
    }

    private function indexOf(array $records, string $id): int
    {
        foreach ($records as $i => $record) {
            if (($record['id'] ?? null) === $id) {
                return $i;
            }
        }

        return -1;
    }
}
