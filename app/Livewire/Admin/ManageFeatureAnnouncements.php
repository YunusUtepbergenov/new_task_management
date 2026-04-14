<?php

namespace App\Livewire\Admin;

use App\Models\FeatureAnnouncement;
use App\Models\Role;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

class ManageFeatureAnnouncements extends Component
{
    use WithFileUploads;

    public const ADMIN_USER_ID = 30;

    public ?int $editingId = null;
    public string $title_ru = '';
    public ?string $title_uz = null;
    public string $body_ru = '';
    public ?string $body_uz = null;
    public ?string $link_url = null;
    public bool $target_all = true;
    public array $selectedRoles = [];
    public bool $publish = true;

    public $image;
    public ?string $existingImage = null;

    public bool $showForm = false;

    public function mount(): void
    {
        abort_unless(Auth::id() === self::ADMIN_USER_ID, 403);
    }

    public function rules(): array
    {
        return [
            'title_ru' => 'required|string|max:255',
            'title_uz' => 'nullable|string|max:255',
            'body_ru' => 'required|string',
            'body_uz' => 'nullable|string',
            'link_url' => 'nullable|url|max:500',
            'target_all' => 'boolean',
            'selectedRoles' => 'array',
            'selectedRoles.*' => [Rule::exists('roles', 'id')],
            'publish' => 'boolean',
            'image' => 'nullable|image|max:5120|mimes:jpg,jpeg,png,webp',
        ];
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $announcement = FeatureAnnouncement::with('roles')->findOrFail($id);

        $this->editingId = $announcement->id;
        $this->title_ru = $announcement->title_ru;
        $this->title_uz = $announcement->title_uz;
        $this->body_ru = $announcement->body_ru;
        $this->body_uz = $announcement->body_uz;
        $this->link_url = $announcement->link_url;
        $this->target_all = $announcement->target_all;
        $this->selectedRoles = $announcement->roles->pluck('id')->all();
        $this->publish = $announcement->published_at !== null;
        $this->existingImage = $announcement->image_path;
        $this->image = null;
        $this->showForm = true;
    }

    public function save(): void
    {
        $data = $this->validate();

        $imagePath = $this->existingImage;
        if ($this->image) {
            $imagePath = $this->image->store('announcements', 'public');
        }

        $payload = [
            'title_ru' => $data['title_ru'],
            'title_uz' => $data['title_uz'] ?? null,
            'body_ru' => $data['body_ru'],
            'body_uz' => $data['body_uz'] ?? null,
            'link_url' => $data['link_url'] ?? null,
            'target_all' => (bool) $data['target_all'],
            'image_path' => $imagePath,
            'published_at' => $this->publish ? now() : null,
        ];

        if ($this->editingId) {
            $announcement = FeatureAnnouncement::findOrFail($this->editingId);
            $announcement->update($payload);
        } else {
            $announcement = FeatureAnnouncement::create($payload);
        }

        $announcement->roles()->sync($this->target_all ? [] : $this->selectedRoles);

        if (! $this->target_all && $this->editingId) {
            $announcement->seenByUsers()->detach();
        }

        $this->resetForm();
        $this->toast(__('announcements.saved'));
    }

    public function delete(int $id): void
    {
        FeatureAnnouncement::findOrFail($id)->delete();
        $this->toast(__('announcements.deleted'));
    }

    private function resetForm(): void
    {
        $this->reset([
            'editingId', 'title_ru', 'title_uz', 'body_ru', 'body_uz',
            'link_url', 'target_all', 'selectedRoles', 'publish', 'image',
            'existingImage', 'showForm',
        ]);
        $this->target_all = true;
        $this->publish = true;
    }

    private function toast(string $message): void
    {
        $encoded = json_encode($message, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE);

        $this->js(<<<JS
            toastr.options = {
                closeButton: true,
                progressBar: true,
                timeOut: 5000,
                extendedTimeOut: 1000,
                positionClass: 'toast-top-right'
            };
            toastr.success({$encoded});
        JS);
    }

    public function render(): View
    {
        return view('livewire.admin.manage-feature-announcements', [
            'announcements' => FeatureAnnouncement::with('roles')->orderByDesc('id')->get(),
            'roles' => Role::orderBy('name')->get(),
        ]);
    }
}
