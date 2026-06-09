@php
    $groups = collect($fields)->groupBy('group');
@endphp

<x-admin.layout title="Content Management" active="content">
    <div class="tabs">
        <a class="{{ $locale === 'en' ? 'active' : '' }}" href="{{ route('admin.content.edit', ['locale' => 'en']) }}">English</a>
        <a class="{{ $locale === 'ar' ? 'active' : '' }}" href="{{ route('admin.content.edit', ['locale' => 'ar']) }}">Arabic</a>
    </div>

    <form class="card pad" method="post" action="{{ route('admin.content.update') }}">
        @csrf
        <input type="hidden" name="locale" value="{{ $locale }}">

        @foreach ($groups as $group => $items)
            <section style="margin-bottom: 28px;">
                <h2 style="margin: 0 0 16px;">{{ $group }}</h2>
                <div class="grid" style="grid-template-columns: repeat(2, minmax(0, 1fr));">
                    @foreach ($items as $field)
                        @php
                            $current = old('content.' . $field['key'], $values[$field['key']] ?? '');
                            $fallback = __($field['key'], [], $locale);
                        @endphp
                        <div class="field" style="{{ $field['type'] === 'textarea' ? 'grid-column: span 2;' : '' }}">
                            <label for="{{ md5($field['key']) }}">{{ $field['label'] }}</label>
                            @if ($field['type'] === 'textarea')
                                <textarea id="{{ md5($field['key']) }}" name="content[{{ $field['key'] }}]" placeholder="{{ $fallback }}">{{ $current }}</textarea>
                            @else
                                <input id="{{ md5($field['key']) }}" type="text" name="content[{{ $field['key'] }}]" value="{{ $current }}" placeholder="{{ $fallback }}">
                            @endif
                            <small class="muted">Leave blank to use the default translation.</small>
                        </div>
                    @endforeach
                </div>
            </section>
        @endforeach

        <button class="btn" type="submit">Save Content</button>
    </form>
</x-admin.layout>
