<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteContentOverride;
use App\Support\SiteContent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

            $values = $databaseValues->merge($values);
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
        return [
            ['group' => 'SEO', 'key' => 'site.meta.title', 'label' => 'Meta title', 'type' => 'text'],
            ['group' => 'SEO', 'key' => 'site.meta.description', 'label' => 'Meta description', 'type' => 'textarea'],
            ['group' => 'Hero', 'key' => 'site.hero.trust_badge', 'label' => 'Hero badge', 'type' => 'text'],
            ['group' => 'Hero', 'key' => 'site.hero.title', 'label' => 'Hero headline', 'type' => 'textarea'],
            ['group' => 'Hero', 'key' => 'site.hero.subtitle', 'label' => 'Hero description', 'type' => 'textarea'],
            ['group' => 'Hero', 'key' => 'site.cta.overview', 'label' => 'Hero CTA button', 'type' => 'text'],
            ['group' => 'Hero', 'key' => 'site.hero.fine_print', 'label' => 'Hero image note', 'type' => 'text'],
            ['group' => 'Video', 'key' => 'site.video.title', 'label' => 'Video title', 'type' => 'textarea'],
            ['group' => 'Video', 'key' => 'site.video.description', 'label' => 'Video description', 'type' => 'textarea'],
            ['group' => 'How It Works', 'key' => 'site.sections.how.title', 'label' => 'How title', 'type' => 'textarea'],
            ['group' => 'How It Works', 'key' => 'site.sections.how.body', 'label' => 'How description', 'type' => 'textarea'],
            ['group' => 'Success Stories', 'key' => 'site.sections.stories.title', 'label' => 'Stories title', 'type' => 'textarea'],
            ['group' => 'Success Stories', 'key' => 'site.sections.stories.body', 'label' => 'Stories description', 'type' => 'textarea'],
            ['group' => 'Wellness', 'key' => 'site.sections.wellness.title', 'label' => 'Wellness title', 'type' => 'textarea'],
            ['group' => 'Wellness', 'key' => 'site.sections.wellness.body', 'label' => 'Wellness description', 'type' => 'textarea'],
            ['group' => 'Business', 'key' => 'site.sections.business.title', 'label' => 'Business title', 'type' => 'textarea'],
            ['group' => 'Business', 'key' => 'site.sections.business.body', 'label' => 'Business description', 'type' => 'textarea'],
            ['group' => 'Business', 'key' => 'site.sections.business.graph_message', 'label' => 'Graph message', 'type' => 'textarea'],
            ['group' => 'About', 'key' => 'site.sections.about.title', 'label' => 'About title', 'type' => 'textarea'],
            ['group' => 'About', 'key' => 'site.sections.about.body', 'label' => 'About body', 'type' => 'textarea'],
            ['group' => 'About', 'key' => 'site.sections.about.body_extra', 'label' => 'About extra body', 'type' => 'textarea'],
            ['group' => 'FAQ', 'key' => 'site.sections.faq.title', 'label' => 'FAQ title', 'type' => 'text'],
            ['group' => 'Form', 'key' => 'site.form.qualifier.title', 'label' => 'Form title', 'type' => 'text'],
            ['group' => 'Form', 'key' => 'site.form.qualifier.body', 'label' => 'Form description', 'type' => 'textarea'],
            ['group' => 'Footer', 'key' => 'site.footer.description', 'label' => 'Footer description', 'type' => 'textarea'],
            ['group' => 'Footer', 'key' => 'site.footer.cta_title', 'label' => 'Footer CTA title', 'type' => 'text'],
            ['group' => 'Footer', 'key' => 'site.footer.cta_body', 'label' => 'Footer CTA body', 'type' => 'textarea'],
        ];
    }
}
