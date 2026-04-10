<div>
    <div class="row filter-row">
        <div class="col-sm-4 col-md-2">
            <label for="input">{{ __('ui.research.source') }}</label>
            <div class="form-group">
                <select class="form-control" wire:model='type' name="type">
                    <option value="houses">{{ __('ui.research.real_estate') }}</option>
                    <option value="cars">{{ __('ui.research.cars') }}</option>
                </select>
            </div>
        </div>
        @if (Auth::user()->id == 30)
            <div class="col-sm-4 col-md-5">
                <label style="color: #f7f7f7">S</label>
                <div class="">
                    <a id="btnExport" class="btn btn-primary search_button" data-toggle="modal" data-target="#create_scrape">{{ __('ui.research.add') }}</a>
                </div>
            </div>
        @endif
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="table-responsive" id="employeeTable">
                <table class="table custom-table" id="filteredTasks" style="overflow-y: auto; height: 110px;">
                    <thead id="employee_header">
                        <tr>
                            <th><span>&#8470;</span></th>
                            <th>{{ __('ui.research.name') }}</th>
                            <th>{{ __('ui.research.date') }}</th>
                            <th>{{ __('ui.research.file') }}</th>
                            <th>{{ __('ui.research.action') }}</th>
                        </tr>
                    </thead>
                    <tbody style="overflow: auto;">
                        @foreach ($results as $key=>$result)
                            <tr>
                                <td>{{ $key+1 }}</td>
                                <td>{{ $result->name }}</td>
                                <td>{{ $result->date }}</td>
                                <td>{{ $result->file }}</td>
                                <td><a href="{{ route('scrape.download', $result->id) }}">{{ __('ui.research.download') }}</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
