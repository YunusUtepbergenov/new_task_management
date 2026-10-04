{{-- Prototype A: one row per employee, grouped by sector; a row expands into both work lists. --}}
<div class="wl-card wl-table-wrap">
    <table class="wl-table">
        <colgroup>
            <col>
            <col class="wl-col-level">
            <col class="wl-col-num">
            <col class="wl-col-num">
            <col class="wl-col-num">
            <col class="wl-col-num">
            <col class="wl-col-caret">
        </colgroup>
        <thead>
            <tr>
                <th class="wl-th-left">{{ __('reports.workload.columns.employee') }}</th>
                <th class="wl-th-left">{{ __('reports.workload.columns.load') }}</th>
                <th>{{ __('reports.workload.columns.tasks') }}</th>
                <th>{{ __('reports.workload.columns.mails') }}</th>
                <th>{{ __('reports.workload.columns.overdue') }}</th>
                <th>{{ __('reports.workload.columns.review') }}</th>
                <th></th>
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
                        <td></td>
                        <td class="wl-td-num" x-text="sectorSum(block.sector, 'tasks')"></td>
                        <td class="wl-td-num" x-text="sectorSum(block.sector, 'mails')"></td>
                        <td class="wl-td-num wl-text-late" x-text="sectorSum(block.sector, 'overdue') || '–'"></td>
                        <td class="wl-td-num wl-text-muted" x-text="sectorSum(block.sector, 'tasks_review') + sectorSum(block.sector, 'mails_review')"></td>
                        <td></td>
                    </tr>
                </template>

                <template x-if="block.employee">
                    <tr class="wl-row" :class="expanded === block.employee.id && 'is-open'" @click="toggleRow(block.employee.id)">
                        <td>
                            <div class="wl-person">
                                <span class="wl-person-name" x-text="block.employee.name"></span>
                                <span class="wl-person-role" x-text="block.employee.role"></span>
                            </div>
                        </td>
                        <td>@include('partials.workload.level', ['expr' => 'block.employee'])</td>
                        <td class="wl-td-num" :class="!block.employee.counts.tasks && 'wl-text-muted'" x-text="block.employee.counts.tasks || '–'"></td>
                        <td class="wl-td-num" :class="!block.employee.counts.mails && 'wl-text-muted'" x-text="block.employee.counts.mails || '–'"></td>
                        <td class="wl-td-num" :class="block.employee.counts.overdue ? 'wl-text-late' : 'wl-text-muted'" x-text="block.employee.counts.overdue || '–'"></td>
                        <td class="wl-td-num wl-text-muted" x-text="(block.employee.counts.tasks_review + block.employee.counts.mails_review) || '–'"></td>
                        <td class="wl-td-caret"><i class="la la-angle-down"></i></td>
                    </tr>
                </template>

                <template x-if="block.employee && expanded === block.employee.id">
                    <tr class="wl-detail-row">
                        <td colspan="7">
                            <div class="wl-detail">
                                @foreach (['tasks', 'mails'] as $source)
                                    <section class="wl-detail-col">
                                        <header class="wl-detail-head">
                                            {{ __('reports.workload.sources.'.$source) }}
                                            <span x-text="block.employee.counts.{{ $source }}"></span>
                                        </header>
                                        <template x-for="entry in activeEntries(block.employee, '{{ $source }}')" :key="entry.id">
                                            @include('partials.workload.entry', ['showSource' => false])
                                        </template>
                                        <div class="wl-empty wl-empty--sm" x-show="!activeEntries(block.employee, '{{ $source }}').length">{{ __('reports.workload.empty') }}</div>
                                    </section>
                                @endforeach
                            </div>
                            <button type="button" class="wl-text-btn wl-detail-more" @click.stop="openEmployee(block.employee)" x-show="block.employee.counts.tasks_review + block.employee.counts.mails_review">
                                {{ __('reports.workload.columns.review') }}: <span x-text="block.employee.counts.tasks_review + block.employee.counts.mails_review"></span> →
                            </button>
                        </td>
                    </tr>
                </template>
            </tbody>
        </template>
    </table>

    <div class="wl-empty" x-show="visibleCount === 0">{{ __('reports.workload.nothing_found') }}</div>
</div>
