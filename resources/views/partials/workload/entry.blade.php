{{-- One open task or edo.ijro.uz item; rendered inside an Alpine x-for over `entry`. --}}
<a class="wl-entry" tabindex="0"
   :href="entry.source === 'mail' ? entry.url : null"
   @click="if (entry.source === 'task') { $event.preventDefault(); openTask(entry) }"
   @keydown.enter="openTask(entry)">
    <span class="wl-entry-main">
        <span class="wl-entry-title" x-text="entry.title || '—'"></span>
        <span class="wl-entry-meta">
            @if ($showSource ?? true)
                <span x-text="t('sources.' + entry.source)"></span>
            @endif
            <template x-if="entry.meta"><span x-text="entry.meta"></span></template>
            <template x-if="entry.source === 'mail'"><span x-text="entry.main ? t('main') : t('co')"></span></template>
            <template x-if="entry.state === 'new' || entry.state === 'rework'"><span x-text="t('states.' + entry.state)"></span></template>
        </span>
    </span>
    <span class="wl-entry-due" :class="'is-' + due(entry).cls">
        <span class="wl-entry-date" x-text="entry.deadline || t('days.none')"></span>
        <span class="wl-entry-days" x-text="due(entry).label"></span>
        <template x-if="entry.open_deadlines > 1"><span class="wl-entry-total" x-text="t('deadlines_total', entry.open_deadlines)"></span></template>
    </span>
</a>
