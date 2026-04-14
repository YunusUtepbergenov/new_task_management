<div>
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title">{{ __('announcements.admin_title') }}</h3>
            </div>
            @unless ($showForm)
                <div class="col-auto float-right ml-auto" style="margin-top: 10px;">
                    <a href="#" class="btn add-btn" wire:click.prevent="openCreate">
                        <i class="fa fa-plus"></i> {{ __('announcements.create') }}
                    </a>
                </div>
            @endunless
        </div>
    </div>

    @if ($showForm)
        <div class="row" wire:key="fa-form-section">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <form wire:submit.prevent="save">
                            <div class="form-group row">
                                <label class="col-md-3 col-form-label">{{ __('announcements.title_ru') }} <span class="text-danger">*</span></label>
                                <div class="col-md-9">
                                    <input type="text" class="form-control @error('title_ru') is-invalid @enderror" wire:model.defer="title_ru">
                                    @error('title_ru') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-md-3 col-form-label">{{ __('announcements.title_uz') }}</label>
                                <div class="col-md-9">
                                    <input type="text" class="form-control" wire:model.defer="title_uz">
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-md-3 col-form-label">{{ __('announcements.body_ru') }} <span class="text-danger">*</span> <small class="text-muted d-block">markdown</small></label>
                                <div class="col-md-9">
                                    <textarea class="form-control @error('body_ru') is-invalid @enderror" rows="5" wire:model.defer="body_ru"></textarea>
                                    @error('body_ru') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-md-3 col-form-label">{{ __('announcements.body_uz') }} <small class="text-muted d-block">markdown</small></label>
                                <div class="col-md-9">
                                    <textarea class="form-control" rows="5" wire:model.defer="body_uz"></textarea>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-md-3 col-form-label">{{ __('announcements.image') }}</label>
                                <div class="col-md-9">
                                    <input type="file" class="form-control-file" wire:model="image" accept="image/*">
                                    @error('image') <small class="text-danger">{{ $message }}</small> @enderror
                                    @if ($existingImage && ! $image)
                                        <div class="mt-2">
                                            <img src="{{ asset('storage/' . $existingImage) }}" style="max-width: 200px; border-radius: 4px;">
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-md-3 col-form-label">{{ __('announcements.link_url') }}</label>
                                <div class="col-md-9">
                                    <input type="url" class="form-control" wire:model.defer="link_url" placeholder="https://...">
                                    @error('link_url') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-md-3 col-form-label">{{ __('announcements.audience') }}</label>
                                <div class="col-md-9">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" id="target_all_yes"
                                               wire:model.live="target_all" value="1" @checked($target_all)>
                                        <label class="form-check-label" for="target_all_yes">
                                            {{ __('announcements.target_all') }}
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" id="target_all_no"
                                               wire:model.live="target_all" value="0" @checked(! $target_all)>
                                        <label class="form-check-label" for="target_all_no">
                                            {{ __('announcements.target_roles') }}
                                        </label>
                                    </div>
                                </div>
                            </div>

                            @unless ($target_all)
                                <div class="form-group row">
                                    <label class="col-md-3 col-form-label">{{ __('announcements.select_roles') }}</label>
                                    <div class="col-md-9">
                                        <div style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 6px 16px; max-height: 260px; overflow-y: auto; padding: 10px 12px; border: 1px solid #e3e3e3; border-radius: 4px; background: #fafafa;">
                                            @foreach ($roles as $role)
                                                <label class="form-check" style="margin: 0; display: flex; align-items: center; gap: 6px; cursor: pointer;">
                                                    <input type="checkbox"
                                                           class="form-check-input"
                                                           style="position: static; margin: 0;"
                                                           value="{{ $role->id }}"
                                                           wire:model.defer="selectedRoles">
                                                    <span style="font-size: 13px;">{{ $role->name }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                        @error('selectedRoles.*') <small class="text-danger">{{ $message }}</small> @enderror
                                    </div>
                                </div>
                            @endunless

                            <div class="form-group row">
                                <div class="col-md-9 offset-md-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="publish_toggle" wire:model.defer="publish">
                                        <label class="form-check-label" for="publish_toggle">
                                            {{ __('announcements.publish_now') }}
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row">
                                <div class="col-md-9 offset-md-3">
                                    <button type="submit" class="btn add-btn">{{ __('announcements.save') }}</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="row" wire:key="fa-table-section">
        <div class="col-md-12">
            <div class="table-responsive">
                <table class="table custom-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ __('announcements.col_title') }}</th>
                            <th>{{ __('announcements.col_audience') }}</th>
                            <th>{{ __('announcements.col_status') }}</th>
                            <th>{{ __('announcements.col_published_at') }}</th>
                            <th class="text-right">{{ __('employees.action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($announcements as $a)
                            <tr wire:key="fa-row-{{ $a->id }}">
                                <td>{{ $a->id }}</td>
                                <td>{{ $a->title_ru }}</td>
                                <td>
                                    @if ($a->target_all)
                                        {{ __('announcements.target_all') }}
                                    @else
                                        {{ $a->roles->pluck('name')->join(', ') ?: '—' }}
                                    @endif
                                </td>
                                <td>
                                    @if ($a->published_at)
                                        <span class="badge badge-success">{{ __('announcements.published') }}</span>
                                    @else
                                        <span class="badge badge-secondary">{{ __('announcements.draft') }}</span>
                                    @endif
                                </td>
                                <td>{{ $a->published_at?->format('d.m.Y H:i') ?? '—' }}</td>
                                <td class="text-right">
                                    <button type="button" class="btn" wire:click="edit({{ $a->id }})" title="{{ __('announcements.edit') }}">
                                        <i class="fa fa-pencil"></i>
                                    </button>
                                    <button type="button" class="btn"
                                            wire:click="delete({{ $a->id }})"
                                            wire:confirm="{{ __('announcements.delete_confirm') }}"
                                            title="{{ __('announcements.delete') }}">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">{{ __('announcements.none_yet') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
