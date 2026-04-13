<button {{ $attributes->merge(['type' => 'button', 'class' => 'btn-soft inline-flex items-center disabled:opacity-40 disabled:cursor-not-allowed']) }}>
    {{ $slot }}
</button>
