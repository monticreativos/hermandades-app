@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'rounded-xl border border-emerald-200 bg-emerald-50 p-3 text-sm font-medium text-emerald-900']) }}>
        {{ $status }}
    </div>
@endif
