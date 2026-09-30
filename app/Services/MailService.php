<?php

namespace App\Services;

use App\Models\MailDeadline;
use App\Models\MailDocument;
use App\Models\MailFile;
use App\Models\MailItem;
use App\Models\User;
use App\Notifications\MailAssignedNotification;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MailService
{
    /**
     * Executors attached during the current save: user id => item ids.
     *
     * @var array<int, list<int>>
     */
    private array $assigned = [];

    /**
     * Create or update a document together with its items, executors and deadlines.
     * Items and deadlines missing from the payload are removed; existing deadlines keep their status.
     *
     * @param  array<string, mixed>  $attributes
     * @param  list<array{id?: int|null, clause?: string|null, content?: string|null, main_executor_id?: int|string|null, co_executor_ids?: list<int|string>, deadlines?: list<array{id?: int|null, deadline: string, status?: string|null, note?: string|null}>}>  $items
     */
    public function save(array $attributes, array $items, User $author, ?MailDocument $document = null, bool $notify = true): MailDocument
    {
        $this->assigned = [];

        $document = DB::transaction(function () use ($attributes, $items, $author, $document): MailDocument {
            if ($document) {
                $document->update($attributes);
            } else {
                $document = MailDocument::create($attributes + ['creator_id' => $author->id]);
            }

            $keptItemIds = [];

            foreach (array_values($items) as $position => $itemData) {
                $item = $this->saveItem($document, $itemData, $position);
                $keptItemIds[] = $item->id;
            }

            $document->items()->whereNotIn('id', $keptItemIds)->get()->each(function (MailItem $item): void {
                $this->deleteDeadlineFiles($item);
                $item->delete();
            });

            return $document->refresh();
        });

        if ($notify) {
            $this->notifyAssigned($document, $author);
        }

        return $document;
    }

    public function attachFile(MailDocument $document, UploadedFile $upload, User $uploader, ?MailDeadline $deadline = null): MailFile
    {
        $storedName = Str::uuid().'.'.strtolower($upload->getClientOriginalExtension() ?: 'bin');

        Storage::disk('local')->putFileAs(MailFile::DIRECTORY, $upload, $storedName);

        return $document->files()->create([
            'mail_deadline_id' => $deadline?->id,
            'original_name' => $upload->getClientOriginalName(),
            'stored_name' => $storedName,
            'size' => $upload->getSize(),
            'uploaded_by' => $uploader->id,
        ]);
    }

    public function delete(MailDocument $document): void
    {
        DB::transaction(function () use ($document): void {
            $document->files->each->delete();
            $document->delete();
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function saveItem(MailDocument $document, array $data, int $position): MailItem
    {
        $coExecutorIds = collect($data['co_executor_ids'] ?? [])->filter();
        $attributes = [
            'clause' => $data['clause'] ?? null,
            'content' => $data['content'] ?? null,
            'position' => $position,
            // A head who is the main executor still belongs to an "all heads" assignment.
            'heads_group' => $coExecutorIds->isEmpty()
                ? null
                : MailItem::headsGroupFor($coExecutorIds->push($data['main_executor_id'] ?? null)->filter()),
        ];

        $item = filled($data['id'] ?? null)
            ? $document->items()->findOrFail($data['id'])
            : new MailItem(['mail_document_id' => $document->id]);

        $item->fill($attributes)->save();

        $this->syncExecutors($item, $data['main_executor_id'] ?? null, $data['co_executor_ids'] ?? []);
        $this->syncDeadlines($item, $data['deadlines'] ?? []);

        return $item;
    }

    /**
     * @param  list<int|string>  $coExecutorIds
     */
    private function syncExecutors(MailItem $item, int|string|null $mainExecutorId, array $coExecutorIds): void
    {
        $mainExecutorId = filled($mainExecutorId) ? (int) $mainExecutorId : null;
        $coExecutorIds = collect($coExecutorIds)->filter()->map(fn ($id): int => (int) $id)
            ->reject(fn (int $id): bool => $id === $mainExecutorId)
            ->unique();

        $userIds = $coExecutorIds->when($mainExecutorId, fn ($ids) => $ids->prepend($mainExecutorId))->values();

        $existingSectors = $item->executors()->pluck('mail_item_user.sector_id', 'users.id');

        foreach ($userIds->reject(fn (int $id): bool => $existingSectors->has($id)) as $id) {
            $this->assigned[$id][] = $item->id;
        }
        $currentSectors = User::whereIn('id', $userIds)->pluck('sector_id', 'id');

        $item->executors()->sync($userIds->mapWithKeys(fn (int $id): array => [$id => [
            'is_main' => $id === $mainExecutorId,
            'sector_id' => $existingSectors->has($id) ? $existingSectors[$id] : $currentSectors[$id] ?? null,
        ]])->all());
    }

    /**
     * @param  list<array{id?: int|null, deadline: string, status?: string|null, note?: string|null}>  $deadlines
     */
    private function syncDeadlines(MailItem $item, array $deadlines): void
    {
        $keptIds = [];

        foreach ($deadlines as $data) {
            $deadline = filled($data['id'] ?? null)
                ? $item->deadlines()->findOrFail($data['id'])
                : new MailDeadline(['mail_item_id' => $item->id]);

            $deadline->deadline = $data['deadline'];

            if (! $deadline->exists) {
                $deadline->status = $data['status'] ?? MailDeadline::STATUS_PENDING;
                $deadline->note = $data['note'] ?? null;
            }

            $deadline->save();
            $keptIds[] = $deadline->id;
        }

        $item->deadlines()->whereNotIn('id', $keptIds)->get()->each(function (MailDeadline $deadline): void {
            $deadline->files->each->delete();
            $deadline->delete();
        });
    }

    /**
     * Tells newly attached executors (other than the author) which items they were given.
     */
    private function notifyAssigned(MailDocument $document, User $author): void
    {
        if (! $this->assigned) {
            return;
        }

        $users = User::whereIn('id', array_keys($this->assigned))
            ->where('id', '!=', $author->id)
            ->where('leave', 0)
            ->get();

        foreach ($users as $user) {
            $user->notify(new MailAssignedNotification($document, $this->assigned[$user->id], $author));
        }
    }

    private function deleteDeadlineFiles(MailItem $item): void
    {
        MailFile::whereIn('mail_deadline_id', $item->deadlines()->select('id'))->get()->each->delete();
    }
}
