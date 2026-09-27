<?php

namespace App\Services;

use App\Models\MailDeadline;
use App\Models\MailItem;
use App\Models\Sector;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Builds the execution summaries by sector and by employee. "Past due" counts every
 * deadline whose date has passed, broken down by its current status.
 */
class MailReportService
{
    /**
     * @return array{rows: list<array<string, mixed>>, total: array<string, mixed>}
     */
    public function bySector(): array
    {
        $items = $this->items();
        $sectorNames = Sector::pluck('name', 'id');

        $grouped = [];

        foreach ($items as $item) {
            $sectorIds = $item->executors->pluck('pivot.sector_id')
                ->map(fn ($id) => $id ?? 0)
                ->unique();

            foreach ($sectorIds as $sectorId) {
                $grouped[$sectorId][] = $item;
            }
        }

        $rows = collect($grouped)
            ->map(function (array $sectorItems, int $sectorId) use ($sectorNames): array {
                $row = $this->summarize(collect($sectorItems));
                $row['name'] = $sectorNames[$sectorId] ?? __('mails.report.no_sector');
                $row['employees'] = collect($sectorItems)->flatMap->executors
                    ->filter(fn (User $user): bool => (int) $user->pivot->sector_id === $sectorId)
                    ->pluck('id')->unique()->count();

                return $row;
            })
            ->sortByDesc('past_due')
            ->values()
            ->all();

        $total = $this->summarize($items);
        $total['employees'] = $items->flatMap->executors->pluck('id')->unique()->count();

        return ['rows' => $rows, 'total' => $total];
    }

    /**
     * @return array{rows: list<array<string, mixed>>, total: array<string, mixed>}
     */
    public function byEmployee(): array
    {
        $items = $this->items();

        $rows = $items->flatMap(fn (MailItem $item) => $item->executors->map(fn (User $user): array => ['user' => $user, 'item' => $item]))
            ->groupBy(fn (array $entry): int => $entry['user']->id)
            ->map(function (Collection $entries): array {
                /** @var User $user */
                $user = $entries->first()['user'];
                $row = $this->summarize($entries->pluck('item'));
                $row['name'] = $user->short_name;
                $row['initials'] = $user->initials();
                $row['left'] = (bool) $user->leave;
                $row['main_items'] = $entries->filter(fn (array $entry): bool => (bool) $entry['user']->pivot->is_main)->count();

                return $row;
            })
            ->sortBy([['past_due', 'desc'], ['items', 'desc'], ['name', 'asc']])
            ->values()
            ->all();

        return ['rows' => $rows, 'total' => $this->summarize($items)];
    }

    /**
     * @param  Collection<int, MailItem>  $items
     * @return array{items: int, deadlines: int, past_due: int, past_due_by_status: array<string, int>, multi: int}
     */
    private function summarize(Collection $items): array
    {
        $deadlines = $items->flatMap->deadlines;
        $pastDue = $deadlines->filter(fn (MailDeadline $deadline): bool => $deadline->deadline->lt(today()));

        return [
            'items' => $items->count(),
            'deadlines' => $deadlines->count(),
            'past_due' => $pastDue->count(),
            'past_due_by_status' => collect(MailDeadline::STATUSES)
                ->mapWithKeys(fn (string $status): array => [$status => $pastDue->where('status', $status)->count()])
                ->all(),
            'multi' => $items->filter(fn (MailItem $item): bool => $item->executors->count() > 1)->count(),
        ];
    }

    /**
     * @return Collection<int, MailItem>
     */
    private function items(): Collection
    {
        return MailItem::query()
            ->whereHas('deadlines')
            ->with(['executors', 'deadlines'])
            ->get();
    }
}
