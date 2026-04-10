<div>
    <div class="wto-page-header mb-4">
        <div class="wto-page-header-icon">
            <i class="fa fa-paper-plane-o"></i>
        </div>
        <div>
            <h4 class="wto-page-title">{{ __('ui.direct_messages.title') }}</h4>
            <p class="wto-page-subtitle">{{ __('ui.direct_messages.subtitle') }}</p>
        </div>
    </div>

    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fa fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    <div class="row">
        {{-- Send Form --}}
        <div class="col-lg-5 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="fa fa-envelope"></i> {{ __('ui.direct_messages.new_message') }}</h5>
                </div>
                <div class="card-body">
                    {{-- Recipients --}}
                    <div class="form-group mb-3"
                         x-data="{
                             open: false,
                             search: '',
                             get filteredUsers() {
                                 if (!this.search) return {{ $availableUsers->toJson() }};
                                 const s = this.search.toLowerCase();
                                 return {{ $availableUsers->toJson() }}.filter(u => u.short_name.toLowerCase().includes(s) || u.name.toLowerCase().includes(s));
                             }
                         }"
                         @click.away="open = false">
                        <label class="font-weight-bold mb-2">{{ __('ui.direct_messages.recipients') }}</label>

                        {{-- Selected tags --}}
                        <div class="form-control d-flex flex-wrap align-items-center gap-1" style="min-height: 38px; cursor: pointer; height: auto;" @click="open = !open">
                            @if (count($selectedUserIds) > 0)
                                @foreach ($availableUsers->whereIn('id', $selectedUserIds) as $user)
                                    <span class="badge badge-primary d-inline-flex align-items-center mr-1 mb-1" style="font-size: 12px;">
                                        {{ $user->short_name }}
                                        <span class="ml-1" style="cursor: pointer;" wire:click="removeRecipient({{ $user->id }})">&times;</span>
                                    </span>
                                @endforeach
                            @else
                                <span class="text-muted">{{ __('ui.direct_messages.select_recipients') }}</span>
                            @endif
                        </div>

                        {{-- Dropdown --}}
                        <div x-show="open" x-cloak class="border rounded shadow-sm bg-white position-absolute" style="z-index: 1050; width: calc(100% - 30px); max-height: 350px; overflow: hidden; display: flex; flex-direction: column;">
                            {{-- Search --}}
                            <div class="p-2 border-bottom">
                                <input type="text" x-model="search" class="form-control form-control-sm" placeholder="{{ __('ui.direct_messages.search') }}" @click.stop>
                            </div>
                            {{-- Select all / Deselect --}}
                            <div class="px-2 py-1 border-bottom d-flex justify-content-between">
                                <button type="button" class="btn settings-btn settings-btn--primary" style="padding: 1px 6px; font-size: 10px; line-height: 1.2;" wire:click="selectAll" @click.stop>{{ __('ui.direct_messages.select_all') }}</button>
                                <button type="button" class="btn settings-btn settings-btn--ghost" style="padding: 1px 6px; font-size: 10px; line-height: 1.2;" wire:click="deselectAll" @click.stop>{{ __('ui.direct_messages.deselect_all') }}</button>
                            </div>
                            {{-- User list --}}
                            <div style="overflow-y: auto; max-height: 260px;">
                                <template x-for="user in filteredUsers" :key="user.id">
                                    <label class="d-flex align-items-center px-2 py-1 mb-0" style="cursor: pointer;" :class="{ 'bg-light': $wire.selectedUserIds.includes(String(user.id)) }" @click.stop>
                                        <input type="checkbox"
                                               class="mr-2"
                                               :value="user.id"
                                               :checked="$wire.selectedUserIds.includes(String(user.id))"
                                               @change="
                                                   let id = String(user.id);
                                                   if ($event.target.checked) {
                                                       $wire.selectedUserIds.push(id);
                                                   } else {
                                                       $wire.selectedUserIds = $wire.selectedUserIds.filter(i => i !== id);
                                                   }
                                                   $wire.$set('selectedUserIds', [...$wire.selectedUserIds]);
                                               ">
                                        <span x-text="user.short_name" style="font-size: 13px;"></span>
                                        <small class="text-muted ml-1" x-show="user.sector" x-text="user.sector ? '— ' + user.sector.name : ''"></small>
                                    </label>
                                </template>
                                <div x-show="filteredUsers.length === 0" class="text-muted text-center py-2" style="font-size: 13px;">
                                    {{ __('ui.direct_messages.not_found') }}
                                </div>
                            </div>
                        </div>

                        @error('selectedUserIds')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    {{-- Message --}}
                    <div class="form-group mb-3">
                        <label class="font-weight-bold mb-2">{{ __('ui.direct_messages.message_text') }}</label>
                        <textarea wire:model="messageText" class="form-control" rows="5" placeholder="{{ __('ui.direct_messages.message_placeholder') }}"></textarea>
                        @error('messageText')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    {{-- Send Button --}}
                    <button wire:click="sendMessage"
                            wire:confirm="{{ __('ui.direct_messages.confirm_send') }}"
                            wire:loading.attr="disabled"
                            class="btn export-btn" style="width: 100%; justify-content: center;">
                        <span wire:loading.remove wire:target="sendMessage"><i class="fa fa-paper-plane"></i> {{ __('ui.direct_messages.send') }}</span>
                        <span wire:loading wire:target="sendMessage"><i class="fa fa-spinner fa-spin"></i> {{ __('ui.direct_messages.sending') }}</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- History --}}
        <div class="col-lg-7 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="fa fa-history"></i> {{ __('ui.direct_messages.history') }}</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-nowrap mb-0">
                        <thead>
                            <tr>
                                <th>{{ __('ui.direct_messages.date') }}</th>
                                <th>{{ __('ui.direct_messages.message') }}</th>
                                <th>{{ __('ui.direct_messages.recipients') }}</th>
                                <th>{{ __('ui.direct_messages.channel') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($sentMessages as $msg)
                                <tr>
                                    <td class="text-nowrap">{{ $msg->created_at->format('d.m.Y H:i') }}</td>
                                    <td>{{ Str::limit($msg->message_text, 80) }}</td>
                                    <td>
                                        <span class="badge badge-info" title="{{ $msg->recipients->pluck('short_name')->join(', ') }}">
                                            {{ $msg->recipients->count() }}
                                        </span>
                                    </td>
                                    <td>
                                        @if ($msg->channel === 'web')
                                            <span class="badge badge-light"><i class="fa fa-globe"></i> {{ __('ui.direct_messages.web') }}</span>
                                        @else
                                            <span class="badge badge-light"><i class="fa fa-send"></i> {{ __('ui.direct_messages.telegram') }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">{{ __('ui.direct_messages.no_messages') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($sentMessages->hasPages())
                    <div class="card-body">
                        {{ $sentMessages->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

