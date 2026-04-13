<button {{ $attributes->merge(['type' => 'submit', 'class' => 'btn-primary inline-flex items-center justify-center px-5 py-2.5 text-sm font-semibold shadow-sm transition']) }}>
    {{ $slot }}
</button>
