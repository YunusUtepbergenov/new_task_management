{{-- Prototype C: deadline table — rows are employees, columns are deadline horizons; a cell opens that slice. --}}
@php($buckets = \App\Services\WorkloadService::BUCKETS)

<div class="wl-card wl-table-wrap">
    <table class="wl-table">
        <colgroup>
            <col>
            @foreach ($buckets as $bucket)
                <col class="wl-col-num">
            @endforeach
            <col class="wl-col-level">
            <col class="wl-col-num">
        </colgroup>
        <thead>
            <tr>
                <th class="wl-th-left">{{ __('reports.workload.columns.employee') }}</th>
                @foreach ($buckets as $bucket)
                    <th @class(['wl-th-late' => $bucket === 'overdue'])>{{ __('reports.workload.buckets.'.$bucket) }}</th>
                @endforeach
                <th class="wl-th-left">{{ __('reports.workload.columns.load') }}</th>
                <th>{{ __('reports.workload.columns.review') }}</th>
            </tr>
        </thead>

        <template x-for="block in blocks" :key="block.key">
            <tbody x-show="!block.employee || !collapsed[block.sector.id]">
                <template x-if="!block.employee">
                    <tr class="wl-group-row" @click="toggleSector(block.sector.id)">
                        <td>
                            <div class="wl-group-name">
                                <i class="la la-angle-down wl-caret" :class="collapsed[block.sector.id] && 'is-closed'"></i>
                                <span class="wl-group-title" x-text="block.sector.name"></span>
                                <span class="wl-group-count" x-text="visible(block.sector).length"></span>
                            </div>
                        </td>
                        @foreach ($buckets as $bucket)
                            <td @class(['wl-td-num', 'wl-text-late' => $bucket === 'overdue']) x-text="bucketSum(block.sector, '{{ $bucket }}') || '–'"></td>
                        @endforeach
                        <td></td>
                        <td class="wl-td-num wl-text-muted" x-text="(sectorSum(block.sector, 'tasks_review') + sectorSum(block.sector, 'mails_review')) || '–'"></td>
                    </tr>
                </template>

                <template x-if="block.employee">
                    <tr class="wl-row">
                        <td>
                            <button type="button" class="wl-person wl-person--link" @click="openEmployee(block.employee)">
                                <span class="wl-person-name" x-text="block.employee.name"></span>
                                <span class="wl-person-role" x-text="block.employee.role"></span>
                            </button>
                        </td>
                        @foreach ($buckets as $bucket)
                            <td class="wl-td-num">
                                <button type="button" class="wl-cell wl-cell--{{ $bucket }}"
                                        :class="!bucketCount(block.employee, '{{ $bucket }}') && 'is-empty'"
                                        @click="bucketCount(block.employee, '{{ $bucket }}') && openEmployee(block.employee, '{{ $bucket }}')"
                                        x-text="bucketCount(block.employee, '{{ $bucket }}') || '–'"></button>
                            </td>
                        @endforeach
                        <td>@include('partials.workload.level', ['expr' => 'block.employee'])</td>
                        <td class="wl-td-num wl-text-muted" x-text="(block.employee.counts.tasks_review + block.employee.counts.mails_review) || '–'"></td>
                    </tr>
                </template>
            </tbody>
        </template>
    </table>

    <div class="wl-empty" x-show="visibleCount === 0">{{ __('reports.workload.nothing_found') }}</div>
</div>
