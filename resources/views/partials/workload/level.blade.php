{{-- Load level pill: the count and the level word, coloured green / orange / red. `$expr` is the JS expression for the employee. --}}
<span class="wl-level" :class="'wl-level--' + {{ $expr }}.level">
    <b x-text="{{ $expr }}.counts.load"></b>
    <span x-text="t('levels.' + {{ $expr }}.level)"></span>
</span>
