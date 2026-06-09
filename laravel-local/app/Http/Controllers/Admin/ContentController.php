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
        $fields = [
            ['group' => 'SEO', 'key' => 'site.meta.title', 'label' => 'Meta title', 'type' => 'text'],
            ['group' => 'SEO', 'key' => 'site.meta.description', 'label' => 'Meta description', 'type' => 'textarea'],
            ['group' => 'Hero', 'key' => 'site.hero.trust_badge', 'label' => 'Hero badge', 'type' => 'text'],
            ['group' => 'Hero', 'key' => 'site.hero.title', 'label' => 'Hero headline', 'type' => 'textarea'],
            ['group' => 'Hero', 'key' => 'site.hero.subtitle', 'label' => 'Hero description', 'type' => 'textarea'],
            ['group' => 'Hero', 'key' => 'site.cta.overview', 'label' => 'Hero CTA button', 'type' => 'text'],
            ['group' => 'Hero', 'key' => 'site.hero.fine_print', 'label' => 'Hero image note', 'type' => 'text'],
            ['group' => 'Hero', 'key' => 'site.hero.image_alt', 'label' => 'Hero image alt text', 'type' => 'text'],
            ['group' => 'Video', 'key' => 'site.video.title', 'label' => 'Video title', 'type' => 'textarea'],
            ['group' => 'Video', 'key' => 'site.video.description', 'label' => 'Video description', 'type' => 'textarea'],
            ['group' => 'Video', 'key' => 'site.video.gift_title', 'label' => 'Support box title', 'type' => 'textarea'],
            ['group' => 'Video', 'key' => 'site.video.gift_note', 'label' => 'Support box note', 'type' => 'textarea'],
            ['group' => 'Video', 'key' => 'site.video.gift_cta', 'label' => 'Support box CTA', 'type' => 'text'],
            ['group' => 'How It Works', 'key' => 'site.sections.how.title', 'label' => 'How title', 'type' => 'textarea'],
            ['group' => 'How It Works', 'key' => 'site.sections.how.body', 'label' => 'How description', 'type' => 'textarea'],
            ['group' => 'Success Stories', 'key' => 'site.sections.stories.title', 'label' => 'Stories title', 'type' => 'textarea'],
            ['group' => 'Success Stories', 'key' => 'site.sections.stories.body', 'label' => 'Stories description', 'type' => 'textarea'],
            ['group' => 'Success Stories', 'key' => 'site.sections.stories.trust_note', 'label' => 'Stories trust note', 'type' => 'textarea'],
            ['group' => 'Wellness', 'key' => 'site.sections.wellness.title', 'label' => 'Wellness title', 'type' => 'textarea'],
            ['group' => 'Wellness', 'key' => 'site.sections.wellness.body', 'label' => 'Wellness description', 'type' => 'textarea'],
            ['group' => 'Wellness', 'key' => 'site.sections.wellness.image_alt', 'label' => 'Wellness image alt text', 'type' => 'text'],
            ['group' => 'Business', 'key' => 'site.sections.business.title', 'label' => 'Business title', 'type' => 'textarea'],
            ['group' => 'Business', 'key' => 'site.sections.business.body', 'label' => 'Business description', 'type' => 'textarea'],
            ['group' => 'Business', 'key' => 'site.sections.business.graph_label', 'label' => 'Graph label', 'type' => 'text'],
            ['group' => 'Business', 'key' => 'site.sections.business.graph_badge', 'label' => 'Graph badge', 'type' => 'text'],
            ['group' => 'Business', 'key' => 'site.sections.business.graph_message', 'label' => 'Graph message', 'type' => 'textarea'],
            ['group' => 'Business', 'key' => 'site.sections.business.graph_note', 'label' => 'Graph note', 'type' => 'textarea'],
            ['group' => 'About', 'key' => 'site.sections.about.title', 'label' => 'About title', 'type' => 'textarea'],
            ['group' => 'About', 'key' => 'site.sections.about.body', 'label' => 'About body', 'type' => 'textarea'],
            ['group' => 'About', 'key' => 'site.sections.about.body_extra', 'label' => 'About extra body', 'type' => 'textarea'],
            ['group' => 'About', 'key' => 'site.sections.about.image_alt', 'label' => 'About image alt text', 'type' => 'text'],
            ['group' => 'FAQ', 'key' => 'site.sections.faq.title', 'label' => 'FAQ title', 'type' => 'text'],
            ['group' => 'Form', 'key' => 'site.form.qualifier.title', 'label' => 'Form title', 'type' => 'text'],
            ['group' => 'Form', 'key' => 'site.form.qualifier.body', 'label' => 'Form description', 'type' => 'textarea'],
            ['group' => 'Form', 'key' => 'site.form.qualifier.interest_question', 'label' => 'Step 1 question', 'type' => 'text'],
            ['group' => 'Form', 'key' => 'site.form.qualifier.interest_hint', 'label' => 'Step 1 hint', 'type' => 'text'],
            ['group' => 'Form', 'key' => 'site.form.qualifier.readiness_question', 'label' => 'Step 2 question', 'type' => 'text'],
            ['group' => 'Form', 'key' => 'site.form.qualifier.readiness_hint', 'label' => 'Step 2 hint', 'type' => 'text'],
            ['group' => 'Form', 'key' => 'site.form.qualifier.final_title', 'label' => 'Final step title', 'type' => 'text'],
            ['group' => 'Form', 'key' => 'site.form.qualifier.final_body', 'label' => 'Final step description', 'type' => 'textarea'],
            ['group' => 'Form', 'key' => 'site.form.consent', 'label' => 'Consent text', 'type' => 'textarea'],
            ['group' => 'Form', 'key' => 'site.form.submit', 'label' => 'Submit button', 'type' => 'text'],
            ['group' => 'Form', 'key' => 'site.form.success', 'label' => 'Success message', 'type' => 'textarea'],
            ['group' => 'Footer', 'key' => 'site.footer.description', 'label' => 'Footer description', 'type' => 'textarea'],
            ['group' => 'Footer', 'key' => 'site.footer.cta_title', 'label' => 'Footer CTA title', 'type' => 'text'],
            ['group' => 'Footer', 'key' => 'site.footer.cta_body', 'label' => 'Footer CTA body', 'type' => 'textarea'],
            ['group' => 'Footer', 'key' => 'site.footer.cta_button', 'label' => 'Footer CTA button', 'type' => 'text'],
            ['group' => 'Footer', 'key' => 'site.footer.note', 'label' => 'Footer compliance note', 'type' => 'textarea'],
        ];

        foreach (range(0, 3) as $index) {
            $number = $index + 1;
            $fields[] = ['group' => 'Hero Cards', 'key' => "site.hero.features.{$index}", 'label' => "Hero card {$number}", 'type' => 'text'];
            $fields[] = ['group' => 'Wellness Cards', 'key' => "site.sections.wellness.cards.{$index}.title", 'label' => "Wellness card {$number} title", 'type' => 'text'];
            $fields[] = ['group' => 'Wellness Cards', 'key' => "site.sections.wellness.cards.{$index}.body", 'label' => "Wellness card {$number} body", 'type' => 'textarea'];
            $fields[] = ['group' => 'Business Cards', 'key' => "site.sections.business.points.{$index}", 'label' => "Business point {$number}", 'type' => 'textarea'];
            $fields[] = ['group' => 'About Cards', 'key' => "site.sections.about.highlights.{$index}.title", 'label' => "About card {$number} title", 'type' => 'text'];
            $fields[] = ['group' => 'About Cards', 'key' => "site.sections.about.highlights.{$index}.body", 'label' => "About card {$number} body", 'type' => 'textarea'];
        }

        foreach (range(0, 2) as $index) {
            $number = $index + 1;
            $fields[] = ['group' => 'How Cards', 'key' => "site.sections.how.steps.{$index}.title", 'label' => "How card {$number} title", 'type' => 'text'];
            $fields[] = ['group' => 'How Cards', 'key' => "site.sections.how.steps.{$index}.body", 'label' => "How card {$number} body", 'type' => 'textarea'];
            $fields[] = ['group' => 'Testimonials', 'key' => "site.sections.stories.testimonials.{$index}.initials", 'label' => "Testimonial {$number} initials", 'type' => 'text'];
            $fields[] = ['group' => 'Testimonials', 'key' => "site.sections.stories.testimonials.{$index}.headline", 'label' => "Testimonial {$number} headline", 'type' => 'text'];
            $fields[] = ['group' => 'Testimonials', 'key' => "site.sections.stories.testimonials.{$index}.body", 'label' => "Testimonial {$number} body", 'type' => 'textarea'];
            $fields[] = ['group' => 'Testimonials', 'key' => "site.sections.stories.testimonials.{$index}.metric", 'label' => "Testimonial {$number} focus text", 'type' => 'textarea'];
            $fields[] = ['group' => 'Form Options', 'key' => "site.form.qualifier.interest_options.{$index}.title", 'label' => "Interest option {$number} title", 'type' => 'text'];
            $fields[] = ['group' => 'Form Options', 'key' => "site.form.qualifier.interest_options.{$index}.body", 'label' => "Interest option {$number} body", 'type' => 'textarea'];
            $fields[] = ['group' => 'Form Options', 'key' => "site.form.qualifier.readiness_options.{$index}.title", 'label' => "Readiness option {$number} title", 'type' => 'text'];
            $fields[] = ['group' => 'Form Options', 'key' => "site.form.qualifier.readiness_options.{$index}.body", 'label' => "Readiness option {$number} body", 'type' => 'textarea'];
        }

        foreach (range(0, 4) as $index) {
            $number = $index + 1;
            $fields[] = ['group' => 'FAQ Items', 'key' => "site.sections.faq.items.{$index}.q", 'label' => "FAQ {$number} question", 'type' => 'text'];

            foreach (range(0, 2) as $answerIndex) {
                $answerNumber = $answerIndex + 1;
                $fields[] = ['group' => 'FAQ Items', 'key' => "site.sections.faq.items.{$index}.answers.{$answerIndex}", 'label' => "FAQ {$number} answer point {$answerNumber}", 'type' => 'textarea'];
            }
        }

        foreach (range(0, 4) as $index) {
            $number = $index + 1;
            $fields[] = ['group' => 'Social Links', 'key' => "site.footer.socials.{$index}.url", 'label' => "Social link {$number} URL", 'type' => 'text'];
        }

        return $fields;
    }
}
