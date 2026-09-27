@props(['model', 'options', 'multiple' => false, 'placeholder' => ''])

{{--
    Searchable employee picker grouped by sector, bound to a Livewire property via entangle.
    Options: list of {id, label, group, initials, left}. Extra menu actions go in the $actions slot.
--}}
<div class="mx-picker"
     x-data="{
        open: false,
        search: '',
        active: 0,
        multiple: @js((bool) $multiple),
        value: $wire.entangle('{{ $model }}'),
        options: @js($options),
        get selectedIds() {
            if (this.multiple) { return (this.value || []).map(String); }
            return this.value ? [String(this.value)] : [];
        },
        get filtered() {
            const term = this.search.trim().toLowerCase();
            return term ? this.options.filter(o => o.label.toLowerCase().includes(term) || o.group.toLowerCase().includes(term)) : this.options;
        },
        get groups() {
            const groups = new Map();
            this.filtered.forEach((option, index) => {
                if (! groups.has(option.group)) { groups.set(option.group, []); }
                groups.get(option.group).push({ ...option, index });
            });
            return [...groups].map(([name, items]) => ({ name, items }));
        },
        isSelected(id) { return this.selectedIds.includes(String(id)); },
        option(id) { return this.options.find(o => String(o.id) === String(id)) || { label: id, initials: '?' }; },
        pick(option) {
            const id = String(option.id);
            if (this.multiple) {
                this.value = this.isSelected(id) ? this.selectedIds.filter(i => i !== id) : [...this.selectedIds, id];
                this.$refs.search.focus();
            } else {
                this.value = id;
                this.close();
            }
        },
        remove(id) { this.value = this.multiple ? this.selectedIds.filter(i => i !== String(id)) : null; },
        show() { this.open = true; this.active = 0; this.$nextTick(() => this.$refs.search.focus()); },
        close() { this.open = false; this.search = ''; },
        move(step) {
            const count = this.filtered.length;
            if (! count) { return; }
            this.active = (this.active + step + count) % count;
            this.$nextTick(() => this.$refs.list.querySelector('[data-active=true]')?.scrollIntoView({ block: 'nearest' }));
        },
        choose() { const option = this.filtered[this.active]; if (option) { this.pick(option); } },
     }"
     x-init="$watch('search', () => active = 0)"
     :class="{ 'is-open': open }"
     @click.outside="close()"
     @keydown.escape.stop="close()">

    <div class="mx-picker-control" role="combobox" tabindex="0" :aria-expanded="open" aria-haspopup="listbox"
         @click="open ? close() : show()" @keydown.enter.prevent="show()" @keydown.down.prevent="show()">
        <template x-if="! multiple && selectedIds.length">
            <span class="mx-picker-value">
                <span class="mx-avatar mx-avatar--sm" x-text="option(selectedIds[0]).initials"></span>
                <span class="mx-picker-value-text">
                    <span x-text="option(selectedIds[0]).label"></span>
                    <small x-text="option(selectedIds[0]).group"></small>
                </span>
            </span>
        </template>
        <template x-if="multiple">
            <span class="mx-picker-tokens">
                <template x-for="id in selectedIds" :key="id">
                    <span class="mx-token">
                        <span class="mx-avatar mx-avatar--xs" x-text="option(id).initials"></span>
                        <span x-text="option(id).label"></span>
                        <button type="button" @click.stop="remove(id)" aria-label="{{ __('mails.actions.delete') }}">&times;</button>
                    </span>
                </template>
            </span>
        </template>
        <span class="mx-picker-placeholder" x-show="! selectedIds.length">{{ $placeholder }}</span>
        <span class="mx-picker-icons">
            <button type="button" class="mx-picker-clear" x-show="! multiple && selectedIds.length" @click.stop="remove(selectedIds[0])" aria-label="{{ __('mails.picker.clear') }}">&times;</button>
            <i class="fa fa-angle-down"></i>
        </span>
    </div>

    <div class="mx-picker-menu" x-show="open" x-transition.opacity.duration.100ms style="display: none;">
        <div class="mx-picker-search">
            <i class="fa fa-search"></i>
            <input type="text" x-ref="search" x-model="search" placeholder="{{ __('mails.picker.search') }}"
                   @keydown.down.prevent="move(1)" @keydown.up.prevent="move(-1)" @keydown.enter.prevent="choose()"
                   aria-label="{{ __('mails.picker.search') }}">
        </div>

        {{ $actions ?? '' }}

        <div class="mx-picker-list" x-ref="list" role="listbox" :aria-multiselectable="multiple">
            <template x-for="group in groups" :key="group.name">
                <div>
                    <div class="mx-picker-group" x-text="group.name"></div>
                    <template x-for="item in group.items" :key="item.id">
                        <button type="button" class="mx-picker-option" role="option"
                                :aria-selected="isSelected(item.id)"
                                :data-active="active === item.index"
                                :class="{ 'is-active': active === item.index, 'is-selected': isSelected(item.id) }"
                                @click="pick(item)" @mouseenter="active = item.index">
                            <span class="mx-avatar mx-avatar--sm" x-text="item.initials"></span>
                            <span class="mx-picker-label" x-text="item.label"></span>
                            <span class="mx-picker-left" x-show="item.left">{{ __('mails.fields.left') }}</span>
                            <i class="fa fa-check mx-picker-check"></i>
                        </button>
                    </template>
                </div>
            </template>
            <div class="mx-picker-empty" x-show="! filtered.length">{{ __('mails.picker.empty') }}</div>
        </div>

        <div class="mx-picker-foot" x-show="multiple">
            <span><b x-text="selectedIds.length"></b> {{ __('mails.picker.selected') }}</span>
            <span style="display: flex; gap: 4px;">
                <button type="button" class="mx-btn mx-btn--link" x-show="selectedIds.length" @click="value = []">{{ __('mails.picker.clear') }}</button>
                <button type="button" class="mx-btn mx-btn--primary" style="height: 30px;" @click="close()">{{ __('mails.picker.done') }}</button>
            </span>
        </div>
    </div>
</div>
