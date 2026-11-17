<button {{ $attributes->merge(['type' => 'submit', 'class' => 'voyage-primary-button']) }}>
    <span>{{ $slot }}</span>
    <span class="voyage-action-arrow" aria-hidden="true">
        <svg viewBox="0 0 24 24"><path d="M7 17 17 7M7 7h10v10"/></svg>
    </span>
</button>
