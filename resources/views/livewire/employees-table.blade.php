<div>
    <div wire:loading wire:target="markAsLeft">
        <div class="loading">Loading&#8230;</div>
    </div>

    <!-- Page Header -->
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title">{{ __('employees.title') }}</h3>
            </div>
            @if (Auth::user()->isHR())
                <div class="col-auto float-right ml-auto" style="margin-top: 10px;">
                    <a href="#" class="btn add-btn" data-toggle="modal" data-target="#create_employee">{{ __('employees.add_employee') }}</a>
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
                            <th>{{ __('employees.fio') }}</th>
                            <th>{{ __('employees.email') }}</th>
                            <th>{{ __('employees.sector') }}</th>
                            <th>{{ __('employees.position') }}</th>
                            <th>{{ __('employees.birth_date') }}</th>
                            <th>{{ __('employees.phone') }}</th>
                            <th>{{ __('employees.internal_number') }}</th>
                            @if (Auth::user()->isHR())
                                <th>{{ __('employees.action') }}</th>
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
                                                    wire:confirm="{{ __('employees.delete_confirm') }}">
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
            <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 620px;">
                <div class="modal-content">
                    <div class="vm-header">
                        <div class="vm-header-left">
                            <i class="fa fa-user-plus vm-header-icon"></i>
                            <h5 class="vm-header-title">{{ __('employees.new_employee') }}</h5>
                        </div>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body" style="padding: 0;">
                        <form wire:submit="createEmployee">
                            <div class="vm-section">
                                <div class="vm-section-header">
                                    <i class="fa fa-id-card-o"></i>
                                    <span class="vm-section-title">{{ __('employees.fio') }}</span>
                                </div>
                                <div class="form-group mb-3">
                                    <input class="form-control" wire:model="userName" type="text" placeholder="{{ __('employees.enter_name') }}">
                                </div>
                                @error('userName')
                                    <div class="text-danger mb-2">{{ $message }}</div>
                                @enderror

                                <div class="vm-section-header">
                                    <i class="fa fa-envelope-o"></i>
                                    <span class="vm-section-title">{{ __('employees.email') }}</span>
                                </div>
                                <div class="form-group mb-3">
                                    <input class="form-control" wire:model="email" type="email" placeholder="{{ __('employees.enter_email') }}">
                                </div>
                                @error('email')
                                    <div class="text-danger mb-2">{{ $message }}</div>
                                @enderror

                                <div class="vm-section-header">
                                    <i class="fa fa-sitemap"></i>
                                    <span class="vm-section-title">{{ __('employees.sector') }}</span>
                                </div>
                                <div class="form-group mb-3">
                                    <select class="form-control" wire:model="sectorId">
                                        @foreach ($sectors as $sector)
                                            <option value="{{ $sector->id }}">{{ $sector->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="vm-section-header">
                                    <i class="fa fa-briefcase"></i>
                                    <span class="vm-section-title">{{ __('employees.position') }}</span>
                                </div>
                                <div class="form-group mb-3">
                                    <select class="form-control" wire:model="roleId">
                                        @foreach ($roles as $role)
                                            <option value="{{ $role->id }}">{{ $role->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="vm-section-header">
                                    <i class="fa fa-calendar"></i>
                                    <span class="vm-section-title">{{ __('employees.birth_date') }}</span>
                                </div>
                                <div class="form-group mb-3" wire:ignore>
                                    <input class="form-control datetimepicker" id="birth_date_picker" type="text" placeholder="{{ __('employees.select_date') }}">
                                </div>

                                <div class="vm-section-header">
                                    <i class="fa fa-phone"></i>
                                    <span class="vm-section-title">{{ __('employees.phone') }}</span>
                                </div>
                                <div class="form-group mb-0">
                                    <input class="form-control" wire:model="phone" type="text" placeholder="{{ __('employees.phone_placeholder') }}">
                                </div>
                            </div>

                            <div class="vm-footer">
                                <button class="vm-btn-submit" wire:loading.attr="disabled">
                                    <span wire:loading.remove wire:target="createEmployee">{{ __('employees.add_employee_btn') }}</span>
                                    <span wire:loading wire:target="createEmployee">{{ __('employees.adding') }}</span>
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
