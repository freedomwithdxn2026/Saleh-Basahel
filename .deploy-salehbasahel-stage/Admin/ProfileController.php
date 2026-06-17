<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteImageOverride;
use App\Support\SiteContent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class ProfileController extends Controller
{
    public function edit(): View
    {
        $media = collect(SiteContent::fileImageOverrides());

        try {
            $media = $media->merge(SiteImageOverride::pluck('path', 'key'));
        } catch (Throwable) {
            // File-backed previews remain available when the local database is offline.
        }

        return view('admin.profile', [
            'media' => $media,
            'imageFields' => $this->imageFields(),
            'videoFields' => $this->videoFields(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $rules = [];

        foreach ($this->imageFields() as $field) {
            $rules[$field['key']] = ['nullable', 'image', 'max:8192'];
        }

        foreach ($this->videoFields() as $field) {
            $rules[$field['key']] = ['nullable', 'file', 'mimetypes:video/mp4,video/webm,video/quicktime', 'max:102400'];
        }

        $request->validate($rules);

        foreach ($this->imageFields() as $field) {
            if (! $request->hasFile($field['key'])) {
                continue;
            }

            $publicPath = $this->storeOptimizedImage($request->file($field['key']), $field['key'], $field['max_width']);
            $this->saveMediaOverride($field['key'], $publicPath);
        }

        foreach ($this->videoFields() as $field) {
            if (! $request->hasFile($field['key'])) {
                continue;
            }

            $path = $request->file($field['key'])->store('admin-videos', 'public');
            $this->saveMediaOverride($field['key'], Storage::url($path));
        }

        SiteContent::clear();

        return redirect()->route('admin.profile.edit')->with('status', 'Media updated successfully.');
    }

    private function saveMediaOverride(string $key, string $publicPath): void
    {
        SiteContent::saveFileImageOverride($key, $publicPath);

        try {
            SiteImageOverride::updateOrCreate(
                ['key' => $key],
                ['path' => $publicPath]
            );
        } catch (Throwable) {
            // File-backed storage keeps uploads usable without a database.
        }
    }

    private function storeOptimizedImage(UploadedFile $file, string $key, int $maxWidth): string
    {
        if (! function_exists('imagewebp') || ! function_exists('getimagesize')) {
            return Storage::url($file->store('admin-images', 'public'));
        }

        $sourcePath = $file->getRealPath();
        $info = $sourcePath ? @getimagesize($sourcePath) : false;

        if (! is_array($info) || empty($info[0]) || empty($info[1])) {
            return Storage::url($file->store('admin-images', 'public'));
        }

        $source = match ($info[2] ?? null) {
            IMAGETYPE_JPEG => function_exists('imagecreatefromjpeg') ? @imagecreatefromjpeg($sourcePath) : false,
            IMAGETYPE_PNG => function_exists('imagecreatefrompng') ? @imagecreatefrompng($sourcePath) : false,
            IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($sourcePath) : false,
            default => false,
        };

        if (! $source) {
            return Storage::url($file->store('admin-images', 'public'));
        }

        $width = (int) $info[0];
        $height = (int) $info[1];
        $targetWidth = min($width, $maxWidth);
        $targetHeight = max(1, (int) round($height * ($targetWidth / $width)));
        $target = imagecreatetruecolor($targetWidth, $targetHeight);

        imagealphablending($target, false);
        imagesavealpha($target, true);
        imagecopyresampled($target, $source, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);

        $directory = 'admin-images';
        Storage::disk('public')->makeDirectory($directory);
        $filename = $directory . '/' . Str::slug($key) . '-' . now()->format('YmdHis') . '.webp';
        $absolutePath = Storage::disk('public')->path($filename);

        if (! @imagewebp($target, $absolutePath, 82)) {
            imagedestroy($source);
            imagedestroy($target);

            return Storage::url($file->store('admin-images', 'public'));
        }

        imagedestroy($source);
        imagedestroy($target);

        return Storage::url($filename);
    }

    private function imageFields(): array
    {
        return [
            ['key' => 'hero_image', 'label' => 'Hero side image', 'default' => '/images/hero-image.jpg', 'max_width' => 1400],
            ['key' => 'wellness_image', 'label' => 'Wellness lifestyle image', 'default' => '/images/wellness-lifestyle.webp', 'max_width' => 1200],
            ['key' => 'profile_image', 'label' => 'About Saleh profile image', 'default' => '/images/profile.jpg', 'max_width' => 900],
        ];
    }

    private function videoFields(): array
    {
        return [
            ['key' => 'overview_video', 'label' => 'Free overview video', 'default' => '/videos/free-overview.mp4'],
        ];
    }
}
