<div>
    <div class="wto-page-header mb-4">
        <div class="wto-page-header-icon">
            <i class="fa fa-paper-plane-o"></i>
        </div>
        <div>
            <h4 class="wto-page-title">Рассылка сообщений</h4>
            <p class="wto-page-subtitle">Отправка уведомлений сотрудникам через Telegram</p>
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
                    <h5 class="card-title mb-0"><i class="fa fa-envelope"></i> Новое сообщение</h5>
                </div>
                <div class="card-body">
                    {{-- Recipients --}}
                    <div class="form-group mb-3">
                        <label class="font-weight-bold mb-2">Получатели</label>
                        <div wire:ignore>
                            <select class="form-control select2" id="dm_recipients" multiple>
                                @foreach ($availableUsers->groupBy('sector.name') as $sectorName => $users)
                                    <optgroup label="{{ $sectorName ?: 'Без сектора' }}">
                                        @foreach ($users as $user)
                                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                        </div>
                        @error('selectedUserIds')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    {{-- Message --}}
                    <div class="form-group mb-3">
                        <label class="font-weight-bold mb-2">Текст сообщения</label>
                        <textarea wire:model="messageText" class="form-control" rows="5" placeholder="Введите текст сообщения..."></textarea>
                        @error('messageText')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    {{-- Send Button --}}
                    <button wire:click="sendMessage"
                            wire:confirm="Отправить сообщение выбранным пользователям?"
                            wire:loading.attr="disabled"
                            class="btn btn-primary btn-block">
                        <span wire:loading.remove wire:target="sendMessage"><i class="fa fa-paper-plane"></i> Отправить</span>
                        <span wire:loading wire:target="sendMessage"><i class="fa fa-spinner fa-spin"></i> Отправка...</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- History --}}
        <div class="col-lg-7 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="fa fa-history"></i> История отправок</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-nowrap mb-0">
                        <thead>
                            <tr>
                                <th>Дата</th>
                                <th>Сообщение</th>
                                <th>Получатели</th>
                                <th>Канал</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($sentMessages as $msg)
                                <tr>
                                    <td class="text-nowrap">{{ $msg->created_at->format('d.m.Y H:i') }}</td>
                                    <td>{{ Str::limit($msg->message_text, 80) }}</td>
                                    <td>
                                        <span class="badge badge-info" title="{{ $msg->recipients->pluck('name')->join(', ') }}">
                                            {{ $msg->recipients->count() }}
                                        </span>
                                    </td>
                                    <td>
                                        @if ($msg->channel === 'web')
                                            <span class="badge badge-light"><i class="fa fa-globe"></i> Веб</span>
                                        @else
                                            <span class="badge badge-light"><i class="fa fa-send"></i> Telegram</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">Нет отправленных сообщений</td>
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

@script
    <script>
        function initDmSelect2() {
            const $recipients = $('#dm_recipients');

            if ($recipients.hasClass('select2-hidden-accessible')) {
                $recipients.select2('destroy');
            }

            $recipients.select2({ width: '100%', closeOnSelect: false, placeholder: 'Выберите получателей...' });

            $recipients.off('change.dm').on('change.dm', function () {
                $wire.$set('selectedUserIds', $(this).val() || []);
            });
        }

        initDmSelect2();

        $wire.on('message-sent', () => {
            $('#dm_recipients').val(null).trigger('change.select2');
        });
    </script>
@endscript
