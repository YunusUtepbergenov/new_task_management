<div class="wl-drawer-backdrop" x-show="drawer.open" x-transition.opacity @click="closeDrawer()"></div>
<aside class="wl-drawer" x-show="drawer.open" x-transition:enter="wl-slide" x-transition:enter-start="wl-slide-out" x-transition:leave="wl-slide" x-transition:leave-end="wl-slide-out"
       @keydown.escape.window="closeDrawer()" role="dialog" aria-modal="true">
    <template x-if="drawer.employee">
        <div class="wl-drawer-inner">
            <header class="wl-drawer-head">
                <div class="wl-drawer-who">
                    <div class="wl-drawer-name" x-text="drawer.employee.name"></div>
                    <div class="wl-drawer-role"><span x-text="drawer.employee.role"></span> · <span x-text="drawer.sector"></span></div>
                </div>
                @include('partials.workload.level', ['expr' => 'drawer.employee'])
                <button type="button" class="wl-close" @click="closeDrawer()" title="{{ __('reports.workload.close') }}"><i class="la la-times"></i></button>
            </header>

            <div class="wl-drawer-stats">
                <div><b x-text="drawer.employee.counts.tasks"></b><span>{{ __('reports.workload.columns.tasks') }}</span></div>
                <div><b x-text="drawer.employee.counts.mails"></b><span>edo.ijro.uz</span></div>
                <div><b class="wl-text-late" x-text="drawer.employee.counts.overdue"></b><span>{{ __('reports.workload.columns.overdue') }}</span></div>
                <div><b class="wl-text-muted" x-text="drawer.employee.counts.tasks_review + drawer.employee.counts.mails_review"></b><span>{{ __('reports.workload.columns.review') }}</span></div>
            </div>

            <div class="wl-horizon">
                @foreach (\App\Services\WorkloadService::BUCKETS as $bucket)
                    <button type="button" class="wl-horizon-cell wl-horizon-cell--{{ $bucket }}"
                            :class="{ 'is-active': drawer.bucket === '{{ $bucket }}', 'is-empty': !bucketCount(drawer.employee, '{{ $bucket }}') }"
                            @click="drawer.bucket = drawer.bucket === '{{ $bucket }}' ? null : '{{ $bucket }}'; if (drawer.tab === 'review') drawer.tab = 'all'">
                        <b x-text="bucketCount(drawer.employee, '{{ $bucket }}')"></b>
                        <span>{{ __('reports.workload.buckets.'.$bucket) }}</span>
                    </button>
                @endforeach
            </div>

            <nav class="wl-tabs">
                <button type="button" :class="drawer.tab === 'all' && 'is-active'" @click="drawer.tab = 'all'">{{ __('reports.workload.all_items') }}</button>
                <button type="button" :class="drawer.tab === 'task' && 'is-active'" @click="drawer.tab = 'task'">{{ __('reports.workload.sources.tasks') }}</button>
                <button type="button" :class="drawer.tab === 'mail' && 'is-active'" @click="drawer.tab = 'mail'">edo.ijro.uz</button>
                <button type="button" :class="drawer.tab === 'review' && 'is-active'" @click="drawer.tab = 'review'">{{ __('reports.workload.columns.review') }}</button>
            </nav>

            <div class="wl-drawer-list">
                <template x-for="entry in drawerEntries" :key="entry.source + entry.id">
                    @include('partials.workload.entry')
                </template>
                <div class="wl-empty" x-show="drawerEntries.length === 0">{{ __('reports.workload.empty') }}</div>
            </div>
        </div>
    </template>
</aside>
