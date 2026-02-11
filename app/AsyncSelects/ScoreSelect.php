<?php

namespace App\AsyncSelects;

use App\Services\TaskService;
use LennyLabs\LivewireAsyncSelect\AsyncSelect;

class ScoreSelect extends AsyncSelect
{
    public function options($searchTerm = null): array
    {
        $scores = (new TaskService())->scoresList();

        return $scores
            ->when($searchTerm, fn($collection) => $collection->filter(
                fn($score) => str_contains(strtolower($score->name), strtolower($searchTerm))
            ))
            ->map(fn($score) => [
                'value' => $score->id,
                'label' => $score->name . ' (Макс: ' . $score->max_score . ')',
                'group' => 'Категории',
            ])
            ->toArray();
    }
}