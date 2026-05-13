{{-- Compact version of Filament's repeater.blade.php --}}

<div {{ $attributes->class(['fi-fo-repeater']) }}>
    @foreach ($getChildComponentContainers() as $uuid => $item)
        <div class="fi-fo-repeater-item">
            <div class="fi-fo-repeater-item-fields">
                {{ $item }}
            </div>
            <div class="fi-fo-repeater-item-actions">
                {{ $getAction('delete', $uuid) }}
                {{ $getAction('moveUp', $uuid) }}
                {{ $getAction('moveDown', $uuid) }}
            </div>
        </div>
    @endforeach

    <div class="fi-fo-repeater-footer">
        {{ $getAction('create') }}
    </div>
</div>

<style>
/* Remove default container padding and make items more compact */
.fi-fo-repeater-item {
    display: flex;
    align-items: flex-start;
    gap: 0.5rem;
    margin-bottom: 0.25rem;
    padding: 0;
    background: none;
    border: none;
}
.fi-fo-repeater-item-fields {
    flex: 1 1 0%;
    padding: 0;
    margin: 0;
}
.fi-fo-repeater-item-actions {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
    padding: 0;
    margin: 0;
}
.fi-fo-repeater-footer {
    margin-top: 0.5rem;
    padding: 0;
}
</style>
