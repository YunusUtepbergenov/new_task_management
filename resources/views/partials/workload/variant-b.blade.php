{{-- Prototype B: the people with the most overdue work, then one card per sector; a person opens the drawer. --}}
<section class="wl-card wl-attention" x-show="attention.length && filter === 'all' && !search">
    <header class="wl-card-head">
        <h4>{{ __('reports.workload.attention') }}</h4>
    </header>
    <div class="wl-attention-grid">
        <template x-for="employee in attention" :key="employee.id">
            <button type="button" class="wl-attention-row" @click="openEmployee(employee, 'overdue')">
                <span class="wl-person">
                    <span class="wl-person-name" x-text="employee.short_name"></span>
                    <span class="wl-person-role" x-text="employee.sectorName"></span>
                </span>
                <span class="wl-attention-num">
                    <b class="wl-text-late" x-text="employee.counts.overdue"></b>
                    <span x-text="t('worst', employee.worst_overdue)"></span>
                </span>
            </button>
        </template>
    </div>
</section>

<div class="wl-board">
    <template x-for="sector in sectors" :key="sector.id">
        <article class="wl-card wl-sector" x-show="visible(sector).length">
            <header class="wl-sector-head">
                <h4 x-text="sector.name"></h4>
                <template x-if="sector.head"><p class="wl-text-muted"><span>{{ __('reports.workload.head') }}:</span> <span x-text="sector.head"></span></p></template>
            </header>

            <dl class="wl-sector-facts">
                <div><dt>{{ __('reports.workload.in_work') }}</dt><dd x-text="sectorSum(sector, 'load')"></dd></div>
                <div><dt>{{ __('reports.workload.columns.overdue') }}</dt><dd :class="sectorSum(sector, 'overdue') ? 'wl-text-late' : 'wl-text-muted'" x-text="sectorSum(sector, 'overdue') || '–'"></dd></div>
                <div><dt>{{ __('reports.workload.columns.review') }}</dt><dd class="wl-text-muted" x-text="sectorSum(sector, 'tasks_review') + sectorSum(sector, 'mails_review')"></dd></div>
            </dl>

            <ul class="wl-roster">
                <template x-for="employee in visible(sector)" :key="employee.id">
                    <li>
                        <button type="button" class="wl-roster-row" @click="openEmployee(employee)">
                            <span class="wl-person">
                                <span class="wl-person-name" x-text="employee.short_name"></span>
                                <span class="wl-person-role" x-text="employee.role"></span>
                            </span>
                            <span class="wl-roster-late">
                                <b :class="employee.counts.overdue ? 'wl-text-late' : 'wl-text-muted'" x-text="employee.counts.overdue || '–'"></b>
                                <span>{{ __('reports.workload.columns.overdue') }}</span>
                            </span>
                            @include('partials.workload.level', ['expr' => 'employee'])
                        </button>
                    </li>
                </template>
            </ul>
        </article>
    </template>
</div>

<div class="wl-card wl-empty" x-show="visibleCount === 0">{{ __('reports.workload.nothing_found') }}</div>
