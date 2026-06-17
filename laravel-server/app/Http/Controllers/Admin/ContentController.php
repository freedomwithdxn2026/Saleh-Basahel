<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteContentOverride;
use App\Support\SiteContent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class ContentController extends Controller
{
    public function edit(Request $request): View
    {
        $locale = $request->query('locale', 'en') === 'ar' ? 'ar' : 'en';
        $fields = $this->fields();
        $values = collect(SiteContent::fileTextOverrides($locale));

        try {
            $databaseValues = SiteContentOverride::query()
                ->where('locale', $locale)
                ->pluck('value', 'key');

            $values = $values->merge($databaseValues);
        } catch (Throwable) {
            // File-backed editing remains available when the local database is offline.
        }

        return view('admin.content', [
            'locale' => $locale,
            'fields' => $fields,
            'values' => $values,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $locale = $request->input('locale') === 'ar' ? 'ar' : 'en';
        $data = $request->validate([
            'locale' => ['required', 'in:en,ar'],
            'content' => ['array'],
            'content.*' => ['nullable', 'string', 'max:5000'],
        ]);

        $fileValues = [];

        foreach ($this->fields() as $field) {
            $value = trim((string) data_get($data, 'content.' . $field['key'], ''));

            if ($value === '') {
                try {
                    SiteContentOverride::where('locale', $locale)->where('key', $field['key'])->delete();
                } catch (Throwable) {
                    // Ignore database writes when local MySQL is unavailable.
                }
                continue;
            }

            $fileValues[$field['key']] = $value;

            try {
                SiteContentOverride::updateOrCreate(
                    ['locale' => $locale, 'key' => $field['key']],
                    ['value' => $value]
                );
            } catch (Throwable) {
                // File-backed storage keeps the admin panel usable without a database.
            }
        }

        SiteContent::saveFileTextOverrides($locale, $fileValues);
        SiteContent::clear();

        return redirect()
            ->route('admin.content.edit', ['locale' => $locale])
            ->with('status', 'Content updated successfully.');
    }

    private function fields(): array
    {
        $content = $this->baseContent();
        $flattened = $this->flatten($content, 'site');
        $fields = [];

        foreach ($flattened as $key => $value) {
            if (! $this->isEditableKey($key) || is_array($value)) {
                continue;
            }

            $fields[] = [
                'group' => $this->groupFor($key),
                'key' => $key,
                'label' => $this->labelFor($key),
                'type' => $this->typeFor($key),
            ];
        }

        return $fields;
    }

    private function baseContent(): array
    {
        $candidates = [
            function_exists('lang_path') ? lang_path('en/site.php') : base_path('lang/en/site.php'),
            base_path('lang/en/site.php'),
            resource_path('lang/en/site.php'),
        ];

        foreach (array_unique($candidates) as $path) {
            if (is_string($path) && is_file($path)) {
                $content = require $path;

                return is_array($content) ? $content : [];
            }
        }

        return [];
    }
    private function flatten(array $items, string $prefix): array
    {
        $flat = [];

        foreach ($items as $key => $value) {
            $path = $prefix . '.' . $key;

            if (is_array($value)) {
                $flat += $this->flatten($value, $path);
                continue;
            }

            $flat[$path] = $value;
        }

        return $flat;
    }

    private function isEditableKey(string $key): bool
    {
        $allowedPrefixes = [
            'site.meta.',
            'site.hero.',
            'site.cta.',
            'site.video.',
            'site.sections.',
            'site.form.',
            'site.footer.',
        ];

        if (! Str::startsWith($key, $allowedPrefixes)) {
            return false;
        }

        $blockedFragments = [
            'site.form.countries.',
            '.slug',
            '.url',
            '.icon',
            '.href',
        ];

        return ! Str::contains($key, $blockedFragments);
    }

    private function groupFor(string $key): string
    {
        return match (true) {
            Str::startsWith($key, 'site.meta.') => 'SEO',
            Str::startsWith($key, 'site.hero.') || Str::startsWith($key, 'site.cta.') => 'Hero',
            Str::startsWith($key, 'site.video.') => 'Video',
            Str::startsWith($key, 'site.sections.how.') => 'How It Works',
            Str::startsWith($key, 'site.sections.stories.') => 'Success Stories',
            Str::startsWith($key, 'site.sections.wellness.') => 'Wellness Lifestyle',
            Str::startsWith($key, 'site.sections.business.') => 'Business Opportunity',
            Str::startsWith($key, 'site.sections.about.') => 'About Saleh',
            Str::startsWith($key, 'site.sections.faq.') => 'FAQ',
            Str::startsWith($key, 'site.form.') => 'Lead Form',
            Str::startsWith($key, 'site.footer.') => 'Footer',
            default => 'Content',
        };
    }

    private function labelFor(string $key): string
    {
        $label = Str::of($key)
            ->replaceFirst('site.', '')
            ->replace(['sections.', 'qualifier.', 'fields.', 'placeholders.'], '')
            ->replaceMatches('/\.(\d+)\./', ' item $1 ')
            ->replace(['.', '_'], ' ')
            ->squish();

        return (string) Str::of($label)->headline();
    }

    private function typeFor(string $key): string
    {
        $textareaHints = [
            'body',
            'description',
            'subtitle',
            'note',
            'extra',
            'message',
            'fallback',
            'answer',
            'consent',
            'privacy',
            'copy',
        ];

        return Str::contains($key, $textareaHints) ? 'textarea' : 'text';
    }
}


