<x-admin.layout title="Profile Section" active="profile">
    <form class="card pad" method="post" action="{{ route('admin.profile.update') }}" enctype="multipart/form-data">
        @csrf
        <p class="muted" style="margin-top: 0; max-width: 860px; line-height: 1.7;">
            Upload the landing page media from here. Images are resized and converted to fast WebP files when the server supports it, so the page stays quick after you change visuals.
        </p>

        <h2 style="margin: 24px 0 14px;">Landing Page Images</h2>
        <div class="grid image-grid">
            @foreach ($imageFields as $field)
                @php
                    $path = $media[$field['key']] ?? $field['default'];
                    $previewFile = public_path(ltrim($path, '/'));

                    if (! file_exists($previewFile) && ! str_starts_with($path, 'http')) {
                        $path = $field['default'];
                        $previewFile = public_path(ltrim($path, '/'));
                    }

                    $previewSrc = str_starts_with($path, 'http') ? $path : asset(ltrim($path, '/'));
                    if (file_exists($previewFile)) {
                        $previewSrc .= '?v=' . filemtime($previewFile);
                    }
                @endphp
                <div class="field">
                    <label for="{{ $field['key'] }}">{{ $field['label'] }}</label>
                    <img class="preview" src="{{ $previewSrc }}" alt="{{ $field['label'] }}" loading="lazy" decoding="async" onerror="this.onerror=null;this.src='{{ asset(ltrim($field['default'], '/')) }}';">
                    <input id="{{ $field['key'] }}" type="file" name="{{ $field['key'] }}" accept="image/jpeg,image/png,image/webp">
                    <small class="muted">Recommended: JPG, PNG, or WebP. Large files are optimized automatically where possible.</small>
                    @error($field['key'])
                        <small style="color:#b42318;">{{ $message }}</small>
                    @enderror
                </div>
            @endforeach
        </div>

        <h2 style="margin: 30px 0 14px;">Landing Page Video</h2>
        <div class="grid image-grid">
            @foreach ($videoFields as $field)
                @php
                    $path = $media[$field['key']] ?? $field['default'];
                    $previewFile = public_path(ltrim($path, '/'));

                    if (! file_exists($previewFile) && ! str_starts_with($path, 'http')) {
                        $path = $field['default'];
                        $previewFile = public_path(ltrim($path, '/'));
                    }

                    $previewSrc = str_starts_with($path, 'http') ? $path : asset(ltrim($path, '/'));
                    if (file_exists($previewFile)) {
                        $previewSrc .= '?v=' . filemtime($previewFile);
                    }
                @endphp
                <div class="field">
                    <label for="{{ $field['key'] }}">{{ $field['label'] }}</label>
                    <video class="preview video-preview" src="{{ $previewSrc }}" controls preload="metadata"></video>
                    <input id="{{ $field['key'] }}" type="file" name="{{ $field['key'] }}" accept="video/mp4,video/webm,video/quicktime">
                    <small class="muted">Use compressed MP4 or WebM for best mobile performance. Max upload: 100 MB.</small>
                    @error($field['key'])
                        <small style="color:#b42318;">{{ $message }}</small>
                    @enderror
                </div>
            @endforeach
        </div>

        <button class="btn" type="submit">Save Media</button>
    </form>
</x-admin.layout>


