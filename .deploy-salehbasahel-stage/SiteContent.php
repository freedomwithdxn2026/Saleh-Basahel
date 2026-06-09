<?php

namespace App\Support;

use App\Models\SiteContentOverride;
use App\Models\SiteImageOverride;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

class SiteContent
{
    private static bool $databaseUnavailable = false;
    private static ?array $fileContent = null;
    private static ?array $fileImages = null;

    public function text(string $locale, string $key): string
    {
        $fallback = __($key, [], $locale);
        $fileValue = data_get(self::fileContent(), "{$locale}.{$key}");

        if (filled($fileValue)) {
            return (string) $fileValue;
        }

        if (self::$databaseUnavailable) {
            return (string) $fallback;
        }

        try {
            return Cache::rememberForever("site_content.{$locale}.{$key}", function () use ($locale, $key, $fallback) {
                $value = SiteContentOverride::query()
                    ->where('locale', $locale)
                    ->where('key', $key)
                    ->value('value');

                return filled($value) ? (string) $value : (string) $fallback;
            });
        } catch (\Throwable) {
            self::$databaseUnavailable = true;

            return (string) $fallback;
        }
    }

    public function image(string $key, string $fallback): string
    {
        $fileValue = data_get(self::fileImages(), $key);

        if (filled($fileValue) && self::publicAssetExists((string) $fileValue)) {
            return (string) $fileValue;
        }

        if (self::$databaseUnavailable) {
            return $fallback;
        }

        try {
            return Cache::rememberForever("site_image.{$key}", function () use ($key, $fallback) {
                $path = SiteImageOverride::query()->where('key', $key)->value('path');

                return filled($path) && self::publicAssetExists((string) $path) ? (string) $path : $fallback;
            });
        } catch (\Throwable) {
            self::$databaseUnavailable = true;

            return $fallback;
        }
    }

    public static function clear(): void
    {
        Cache::flush();
        self::$fileContent = null;
        self::$fileImages = null;
    }

    public static function fileTextOverrides(string $locale): array
    {
        return (array) data_get(self::fileContent(), $locale, []);
    }

    public static function saveFileTextOverrides(string $locale, array $values): void
    {
        $content = self::fileContent();
        $content[$locale] = $values;

        File::ensureDirectoryExists(dirname(self::contentFilePath()));
        File::put(self::contentFilePath(), json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        self::$fileContent = $content;
    }

    public static function fileImageOverrides(): array
    {
        return self::fileImages();
    }

    public static function saveFileImageOverride(string $key, string $path): void
    {
        $images = self::fileImages();
        $images[$key] = $path;

        File::ensureDirectoryExists(dirname(self::imageFilePath()));
        File::put(self::imageFilePath(), json_encode($images, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        self::$fileImages = $images;
    }

    private static function fileContent(): array
    {
        if (self::$fileContent !== null) {
            return self::$fileContent;
        }

        self::$fileContent = self::readJson(self::contentFilePath());

        return self::$fileContent;
    }

    private static function fileImages(): array
    {
        if (self::$fileImages !== null) {
            return self::$fileImages;
        }

        self::$fileImages = self::readJson(self::imageFilePath());

        return self::$fileImages;
    }

    private static function readJson(string $path): array
    {
        if (! File::exists($path)) {
            return [];
        }

        $decoded = json_decode((string) File::get($path), true);

        return is_array($decoded) ? $decoded : [];
    }

    private static function publicAssetExists(string $path): bool
    {
        if (preg_match('/^https?:\/\//i', $path)) {
            return true;
        }

        return File::exists(public_path(ltrim($path, '/')));
    }

    private static function contentFilePath(): string
    {
        return storage_path('app/admin/site-content.json');
    }

    private static function imageFilePath(): string
    {
        return storage_path('app/admin/site-images.json');
    }
}
