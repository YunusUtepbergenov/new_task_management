<div>
    <div class="row">
        <div class="col-md-12">
            <div id="employeeTable">
                <table class="table custom-table" id="filteredTasks" style="overflow-y: auto;">
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
                        @forelse ($results as $key => $result)
                            <tr>
                                <td>{{ $key+1 }}</td>
                                <td>{{ $result->name }}</td>
                                <td>{{ $result->date }}</td>
                                <td>{{ $result->file }}</td>
                                <td><a href="{{ route('scrape.download', $result->id) }}">{{ __('ui.research.download') }}</a></td>
                            </tr>
                        @empty

                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
