<div>
    <div class="row filter-row">
        <div class="col-sm-4 col-md-2">
            <label for="input">Источник</label>
            <div class="form-group">
                <select class="form-control" wire:model='type' name="type">
                    <option value="houses">Недвижимости</option>
                    <option value="jobs">Работы</option>
                    <option value="cars">Автомобили</option>
                    <option value="products">Продукты</option>
                </select>
            </div>
        </div>
        @if (Auth::user()->id == 30)
            <div class="col-sm-4 col-md-5">
                <label style="color: #f7f7f7">S</label>
                <div class="">
                    {{-- <a id="btnExport" class="btn btn-primary search_button" data-toggle="modal" data-target="#create_scrape">Добавить</a> --}}
                    <a id="btnExport" class="btn btn-primary search_button" data-toggle="modal" data-target="#profile_modal">Profile</a>
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
                            <th>Наименование</th>
                            <th>Дата</th>
                            <th>Файл</th>
                            <th>Действие</th>
                        </tr>
                    </thead>
                    <tbody style="overflow: auto;">
                        @foreach ($results as $key=>$result)
                            <tr>
                                <td>{{ $key+1 }}</td>
                                <td>{{ $result->name }}</td>
                                <td>{{ $result->date }}</td>
                                <td>{{ $result->file }}</td>
                                <td><a href="{{ route('scrape.download', $result->id) }}">Скачать</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
