@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'input-premium block shadow-sm px-4 py-2.5 disabled:opacity-50 disabled:cursor-not-allowed']) }}>
