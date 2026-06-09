<x-admin.layout title="Profile Section" active="profile">
    <form class="card pad" method="post" action="{{ route('admin.profile.update') }}" enctype="multipart/form-data">
        @csrf
        <p class="muted" style="margin-top: 0;">Upload replacement images for the landing page. The current design will stay the same, but these images will override the default files.</p>

        <div class="grid image-grid">
            @foreach ($imageFields as $field)
                @php
                    $path = $images[$field['key']] ?? $field['default'];
                @endphp
                <div class="field">
                    <label for="{{ $field['key'] }}">{{ $field['label'] }}</label>
                    <img class="preview" src="{{ asset(ltrim($path, '/')) }}" alt="{{ $field['label'] }}">
                    <input id="{{ $field['key'] }}" type="file" name="{{ $field['key'] }}" accept="image/*">
                    @error($field['key'])
                        <small style="color:#b42318;">{{ $message }}</small>
                    @enderror
                </div>
            @endforeach
        </div>

        <button class="btn" type="submit">Save Images</button>
    </form>
</x-admin.layout>
