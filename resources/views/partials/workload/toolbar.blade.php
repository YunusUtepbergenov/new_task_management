<div class="wl-toolbar">
    <label class="wl-search">
        <i class="la la-search"></i>
        <input type="search" x-model="search" placeholder="{{ __('reports.workload.filters.search') }}">
    </label>

    <div class="wl-segmented" role="group">
        @foreach (['all', 'high', 'medium', 'low', 'overdue'] as $filterKey)
            <button type="button" :class="filter === '{{ $filterKey }}' && 'is-active'" @click="filter = '{{ $filterKey }}'">
                {{ __('reports.workload.filters.'.$filterKey) }}
                <span class="wl-segmented-count" x-text="filterCount('{{ $filterKey }}')"></span>
            </button>
        @endforeach
    </div>

    <div class="wl-toolbar-end">
        <select class="wl-select" x-model="sort">
            @foreach (__('reports.workload.sort') as $sortKey => $sortLabel)
                <option value="{{ $sortKey }}">{{ $sortLabel }}</option>
            @endforeach
        </select>
        @if ($variant !== 'b')
            <button type="button" class="wl-text-btn" @click="setAllCollapsed(false)">{{ __('reports.workload.expand_all') }}</button>
            <button type="button" class="wl-text-btn" @click="setAllCollapsed(true)">{{ __('reports.workload.collapse_all') }}</button>
        @endif
    </div>
</div>
