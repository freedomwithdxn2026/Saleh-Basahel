<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteImageOverride;
use App\Support\SiteContent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Throwable;

class ProfileController extends Controller
{
    public function edit(): View
    {
        $images = collect(SiteContent::fileImageOverrides());

        try {
            $images = SiteImageOverride::pluck('path', 'key')->merge($images);
        } catch (Throwable) {
            // File-backed image previews remain available when the local database is offline.
        }

        return view('admin.profile', [
            'images' => $images,
            'imageFields' => $this->imageFields(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $rules = [];

        foreach ($this->imageFields() as $field) {
            $rules[$field['key']] = ['nullable', 'image', 'max:4096'];
        }

        $request->validate($rules);

        foreach ($this->imageFields() as $field) {
            if (! $request->hasFile($field['key'])) {
                continue;
            }

            $path = $request->file($field['key'])->store('admin-images', 'public');
            $publicPath = Storage::url($path);

            SiteContent::saveFileImageOverride($field['key'], $publicPath);

            try {
                SiteImageOverride::updateOrCreate(
                    ['key' => $field['key']],
                    ['path' => $publicPath]
                );
            } catch (Throwable) {
                // File-backed storage keeps uploads usable without a database.
            }
        }

        SiteContent::clear();

        return redirect()->route('admin.profile.edit')->with('status', 'Images updated successfully.');
    }

    private function imageFields(): array
    {
        return [
            ['key' => 'hero_image', 'label' => 'Hero side image', 'default' => '/images/hero-aside-image-1280.webp'],
            ['key' => 'wellness_image', 'label' => 'Wellness lifestyle image', 'default' => '/images/wellnesslifestyle.png'],
            ['key' => 'profile_image', 'label' => 'About Saleh profile image', 'default' => '/images/profile.jpg'],
        ];
    }
}
