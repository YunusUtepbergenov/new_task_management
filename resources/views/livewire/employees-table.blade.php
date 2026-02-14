<div>
    <div wire:loading wire:target="markAsLeft">
        <div class="loading">Loading&#8230;</div>
    </div>

    <!-- Page Header -->
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title">Сотрудники</h3>
            </div>
            @if (Auth::user()->isHR())
                <div class="col-auto float-right ml-auto" style="margin-top: 10px;">
                    <a href="#" class="btn add-btn" data-toggle="modal" data-target="#create_employee">Добавить сотрудника</a>
                </div>
            @endif
        </div>
    </div>

    <!-- Employees Table -->
    <div class="row">
        <div class="col-md-12">
            <div class="table-responsive" id="employeeTable">
                <table class="table custom-table" style="overflow-y: auto; height: 110px;">
                    <thead id="employee_header">
                        <tr>
                            <th>#</th>
                            <th>Ф.И.О</th>
                            <th>Почта</th>
                            <th>Сектор</th>
                            <th>Должность</th>
                            <th>Дата рождения</th>
                            <th>Тел.Номер</th>
                            <th>Внутренный номер</th>
                            @if (Auth::user()->isHR())
                                <th>Действие</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody style="overflow: auto;">
                        @php $counter = 0; @endphp
                        @foreach ($sectors as $sector)
                            <tr>
                                <th colspan="{{ Auth::user()->isHR() ? 9 : 8 }}" id="employee_normal">{{ $sector->name }}</th>
                            </tr>
                            @foreach ($sector->users as $employee)
                                <tr wire:key="employee-{{ $employee->id }}">
                                    <td>{{ ++$counter }}</td>
                                    <td>
                                        <h2 class="table-avatar">
                                            <a href="#" wire:click.prevent="viewProfile({{ $employee->id }})">{{ $employee->name }}</a>
                                        </h2>
                                    </td>
                                    <td>{{ $employee->email }}</td>
                                    <td class="text-wrap"></td>
                                    <td>{{ $employee->role->name }}</td>
                                    <td>{{ $employee->birth_date ? $employee->birth_date->format('Y-m-d') : '' }}</td>
                                    <td>{{ $employee->phone }}</td>
                                    <td>{{ $employee->internal }}</td>
                                    @if (Auth::user()->isHR())
                                        <td>
                                            @if ($sector->id != 1)
                                                <button class="btn" wire:click="markAsLeft({{ $employee->id }})"
                                                    wire:confirm="Вы уверены, что хотите удалить этого сотрудника?">
                                                    <i class="fa fa-sign-out" aria-hidden="true"></i>
                                                </button>
                                            @endif
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Create Employee Modal -->
    @if (Auth::user()->isHR())
        <div id="create_employee" class="modal custom-modal fade" role="dialog" wire:ignore.self>
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Новый сотрудник</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit="createEmployee">
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <label>Введите имя</label>
                                        <input class="form-control" wire:model="userName" type="text" placeholder="Введите имя">
                                    </div>
                                    @error('userName')
                                        <div class="alert alert-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <label>Введите почта</label>
                                        <input class="form-control" wire:model="email" type="email" placeholder="Введите почта">
                                    </div>
                                    @error('email')
                                        <div class="alert alert-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Сектор</label>
                                <div class="col-sm-4">
                                    <select class="form-control" wire:model="sectorId">
                                        @foreach ($sectors as $sector)
                                            <option value="{{ $sector->id }}">{{ $sector->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Должность</label>
                                <div class="col-sm-4">
                                    <select class="form-control" wire:model="roleId">
                                        @foreach ($roles as $role)
                                            <option value="{{ $role->id }}">{{ $role->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Введите дату рождения</label>
                                <div class="col-sm-4">
                                    <div class="form-group" wire:ignore>
                                        <input class="form-control datetimepicker" id="birth_date_picker" type="text">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Введите номер телефона</label>
                                <div class="col-sm-4">
                                    <input class="form-control" wire:model="phone" type="text" placeholder="(93) 123-45-67">
                                </div>
                            </div>

                            <div class="submit-section">
                                <button class="btn btn-primary submit-btn" wire:loading.attr="disabled">
                                    <span wire:loading.remove wire:target="createEmployee">Добавить нового сотрудника</span>
                                    <span wire:loading wire:target="createEmployee">Добавление...</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

@script
<script>
    const $birthDate = $('#birth_date_picker');
    if ($birthDate.length) {
        $birthDate.datetimepicker({
            format: 'YYYY-MM-DD',
            useCurrent: false,
            icons: {
                up: "fa fa-angle-up",
                down: "fa fa-angle-down",
                next: 'fa fa-angle-right',
                previous: 'fa fa-angle-left'
            }
        });

        $birthDate.on('dp.change', function (e) {
            $wire.$set('birthDate', e.date ? e.date.format('YYYY-MM-DD') : null);
        });
    }

    $wire.on('close-create-modal', () => {
        $('#create_employee').modal('hide');
        if ($birthDate.length) {
            $birthDate.val('');
        }
    });
</script>
@endscript
