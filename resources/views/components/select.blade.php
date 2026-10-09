@props([
    'name',
    'options' => [],
    'value' => '',
    'submit' => false,
    'width' => '',
    'required' => false,
])

@php
    $opts = collect($options)->map(fn ($val, $label) => [
        'value' => (string) $val,
        'label' => (string) $label,
    ])->values()->all();
    $val = (string) old($name, $value);
    $current = collect($opts)->firstWhere('value', $val) ?? ($opts[0] ?? ['label' => '', 'value' => '']);
@endphp

<div class="relative {{ $width }}"
     x-data="{
        open: false,
        val: @js($val),
        label: @js($current['label']),
        choose(o) {
            this.val = o.value; this.label = o.label; this.open = false;
            if (@js((bool) $submit)) { this.$nextTick(() => this.$el.closest('form').requestSubmit()) }
        }
     }"
     @click.outside="open = false" @keydown.escape="open = false">
    <input type="hidden" name="{{ $name }}" :value="val" @if ($required) required @endif>
    <button type="button" @click="open = !open"
            class="input flex w-full items-center justify-between gap-2 text-left"
            :class="open ? 'border-brand-500 ring-4 ring-brand-500/15' : ''">
        <span class="truncate" :class="label ? 'text-slate-800' : 'text-slate-400'" x-text="label || '— pilih —'"></span>
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
             stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 shrink-0 text-slate-400 transition"
             :class="open ? 'rotate-180' : ''"><path d="m6 9 6 6 6-6"/></svg>
    </button>
    <div x-cloak x-show="open" x-transition.origin.top.duration.120ms
         class="absolute z-30 mt-1.5 max-h-64 w-full overflow-auto rounded-xl border border-slate-200 bg-white p-1.5 shadow-lg">
        <template x-for="o in @js($opts)" :key="o.value">
            <button type="button" @click="choose(o)"
                    class="flex w-full items-center justify-between gap-2 rounded-lg px-3 py-2 text-left text-sm transition"
                    :class="val === o.value ? 'bg-brand-50 font-semibold text-brand-700' : 'text-slate-600 hover:bg-slate-100'">
                <span x-text="o.label"></span>
                <svg x-show="val === o.value" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"
                     class="h-4 w-4 text-brand-600"><path d="M20 6 9 17l-5-5"/></svg>
            </button>
        </template>
    </div>
</div>
