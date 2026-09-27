@extends('layouts.main')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/workload.css') }}?v={{ filemtime(public_path('css/workload.css')) }}">
@endsection

@section('main')
    @php($totals = $overview['totals'])

    <div class="wl" x-data="workloadPage" x-cloak>
        <header class="wl-head">
            <div>
                <h3 class="wl-title">{{ __('reports.workload_title') }}</h3>
                <p class="wl-subtitle">{{ __('reports.workload.subtitle') }}, {{ __('reports.workload.as_of', ['date' => $overview['today']]) }}</p>
            </div>
            <nav class="wl-views">
                @foreach (__('reports.workload.variants') as $key => $label)
                    <a href="{{ route('workload', ['v' => $key]) }}" @class(['wl-view', 'is-active' => $variant === $key])>{{ $label }}</a>
                @endforeach
            </nav>
        </header>

        <section class="wl-stats">
            <div class="wl-stat">
                <span class="wl-stat-value">{{ $totals['load'] }}</span>
                <span class="wl-stat-label">{{ __('reports.workload.stats.in_work') }}</span>
                <span class="wl-stat-note">{{ __('reports.workload.stats.split', ['tasks' => $totals['tasks'], 'mails' => $totals['mails']]) }}</span>
            </div>
            <div class="wl-stat">
                <span class="wl-stat-value wl-text-late">{{ $totals['overdue'] }}</span>
                <span class="wl-stat-label">{{ __('reports.workload.stats.overdue') }}</span>
                <span class="wl-stat-note">{{ __('reports.workload.stats.people_with_overdue', ['n' => $totals['with_overdue']]) }} · {{ __('reports.workload.stats.split', ['tasks' => $totals['tasks_overdue'], 'mails' => $totals['mails_overdue']]) }}</span>
            </div>
            <div class="wl-stat">
                <span class="wl-stat-value">{{ $totals['tasks_review'] + $totals['mails_review'] }}</span>
                <span class="wl-stat-label">{{ __('reports.workload.stats.review') }}</span>
                <span class="wl-stat-note">{{ __('reports.workload.stats.split', ['tasks' => $totals['tasks_review'], 'mails' => $totals['mails_review']]) }}</span>
            </div>
            <div class="wl-stat wl-stat--levels">
                <span class="wl-stat-label">{{ __('reports.workload.stats.levels') }} · {{ $totals['employees'] }} {{ __('reports.workload.people') }}</span>
                <span class="wl-level-row">
                    @foreach (['high', 'medium', 'low', 'idle'] as $level)
                        <span class="wl-level wl-level--{{ $level }}"><b>{{ $totals[$level] }}</b>{{ __('reports.workload.levels.'.$level) }}</span>
                    @endforeach
                </span>
                <span class="wl-stat-note">{{ __('reports.workload.stats.thresholds') }}</span>
            </div>
        </section>

        @include('partials.workload.toolbar')
        @include('partials.workload.variant-'.$variant)
        @include('partials.workload.drawer')
    </div>

    <script type="application/json" id="wl-data">{!! json_encode([
        'sectors' => $overview['sectors'],
        'i18n' => __('reports.workload'),
    ], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) !!}</script>
    <script>
        (() => {
            const factory = () => ({
                sectors: [],
                i18n: {},
                filter: 'all',
                search: '',
                sort: 'role',
                collapsed: {},
                expanded: null,
                drawer: { open: false, employee: null, sector: null, tab: 'all', bucket: null },

                init() {
                    const payload = JSON.parse(document.getElementById('wl-data').textContent);
                    this.i18n = payload.i18n;
                    this.sectors = payload.sectors.map((sector) => ({
                        ...sector,
                        employees: sector.employees.map((employee, index) => ({ ...employee, order: index, sectorName: sector.name })),
                    }));
                },

                t(path, n = null) {
                    const text = path.split('.').reduce((node, key) => node?.[key], this.i18n) ?? path;
                    return n === null ? text : String(text).replace(':n', n);
                },

                get everyone() {
                    return this.sectors.flatMap((sector) => sector.employees);
                },

                passesFilter(employee, filter = this.filter) {
                    return filter === 'all'
                        || (filter === 'overdue' ? employee.counts.overdue > 0 : employee.level === filter);
                },

                matches(employee) {
                    const query = this.search.trim().toLowerCase();

                    return this.passesFilter(employee) && (query === '' || employee.name.toLowerCase().includes(query));
                },

                filterCount(filter) {
                    return this.everyone.filter((employee) => this.passesFilter(employee, filter)).length;
                },

                sorted(employees) {
                    const by = {
                        role: (a, b) => a.order - b.order,
                        load: (a, b) => b.counts.load - a.counts.load || b.counts.overdue - a.counts.overdue,
                        overdue: (a, b) => b.counts.overdue - a.counts.overdue || b.worst_overdue - a.worst_overdue,
                        name: (a, b) => a.name.localeCompare(b.name),
                    }[this.sort];

                    return [...employees].sort(by);
                },

                visible(sector) {
                    return this.sorted(sector.employees.filter((employee) => this.matches(employee)));
                },

                /** Table body blocks: a sector header followed by its visible employees. */
                get blocks() {
                    return this.sectors.flatMap((sector) => {
                        const employees = this.visible(sector);
                        if (!employees.length) return [];

                        return [
                            { key: 's' + sector.id, sector, employee: null },
                            ...employees.map((employee) => ({ key: 'e' + employee.id, sector, employee })),
                        ];
                    });
                },

                get visibleCount() {
                    return this.everyone.filter((employee) => this.matches(employee)).length;
                },

                get attention() {
                    return this.everyone
                        .filter((employee) => employee.counts.overdue > 0)
                        .sort((a, b) => b.counts.overdue - a.counts.overdue || b.worst_overdue - a.worst_overdue)
                        .slice(0, 8);
                },

                toggleSector(id) {
                    this.collapsed[id] = !this.collapsed[id];
                },

                setAllCollapsed(value) {
                    this.sectors.forEach((sector) => this.collapsed[sector.id] = value);
                },

                toggleRow(id) {
                    this.expanded = this.expanded === id ? null : id;
                },

                due(entry) {
                    const d = entry.days_left;
                    if (entry.review) return { cls: 'review', label: this.t('states.review') };
                    if (d === null) return { cls: 'none', label: '' };
                    if (d < 0) return { cls: 'late', label: this.t('days.overdue', -d) };
                    if (d === 0) return { cls: 'soon', label: this.t('days.today') };
                    if (d === 1) return { cls: 'soon', label: this.t('days.tomorrow') };
                    return { cls: d <= 6 ? 'soon' : 'ok', label: this.t('days.left', d) };
                },

                bucketCount(employee, bucket) {
                    return employee.buckets[bucket].tasks + employee.buckets[bucket].mails;
                },

                bucketSum(sector, bucket) {
                    return this.visible(sector).reduce((sum, employee) => sum + this.bucketCount(employee, bucket), 0);
                },

                sectorSum(sector, key) {
                    return this.visible(sector).reduce((sum, employee) => sum + employee.counts[key], 0);
                },

                activeEntries(employee, source) {
                    return employee[source].filter((entry) => !entry.review);
                },

                openEmployee(employee, bucket = null) {
                    this.drawer = { open: true, employee, sector: employee.sectorName, tab: 'all', bucket };
                },

                closeDrawer() {
                    this.drawer.open = false;
                },

                get drawerEntries() {
                    const employee = this.drawer.employee;
                    if (!employee) return [];
                    const tab = this.drawer.tab;
                    let entries = [...employee.tasks, ...employee.mails];

                    if (tab === 'review') {
                        entries = entries.filter((entry) => entry.review);
                    } else {
                        entries = entries.filter((entry) => !entry.review && (tab === 'all' || entry.source === tab));
                    }
                    if (this.drawer.bucket && tab !== 'review') {
                        entries = entries.filter((entry) => entry.bucket === this.drawer.bucket);
                    }

                    return entries.sort((a, b) => (a.days_left ?? 1e9) - (b.days_left ?? 1e9));
                },

                openTask(entry) {
                    if (entry.source === 'task' && window.openModal) {
                        window.openModal(entry.id);
                    }
                },
            });

            const register = () => window.Alpine.data('workloadPage', factory);
            window.Alpine ? register() : document.addEventListener('alpine:init', register);
        })();
    </script>
@endsection
