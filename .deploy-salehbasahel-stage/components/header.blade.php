@props(['locale' => 'en', 'nav' => [], 'initialSection' => null])

@php
    $isRtl = $locale === 'ar';
    $sections = [
        'how-it-works' => $nav['how'] ?? 'How It Works',
        'success-stories' => $nav['stories'] ?? 'Success Stories',
        'wellness-lifestyle' => $nav['wellness'] ?? 'Wellness Lifestyle',
        'business-opportunity' => $nav['business'] ?? 'Business Opportunity',
        'about-saleh' => $nav['about'] ?? 'About Saleh',
        'faq' => $nav['faq'] ?? 'FAQ',
    ];
@endphp

<header
    x-data="{
        open: false,
        active: @js($initialSection ?: 'hero'),
        initialSection: @js($initialSection),
        sectionUrl(id) {
            return id === 'hero' ? '/{{ $locale }}' : '/{{ $locale }}/' + id;
        },
        updateUrl(id, mode = 'replace') {
            const nextUrl = this.sectionUrl(id);

            if (window.location.pathname === nextUrl) return;

            if (mode === 'push') {
                history.pushState(null, '', nextUrl);
                return;
            }

            history.replaceState(null, '', nextUrl);
        },
        scrollToSection(id) {
            const target = document.getElementById(id);

            if (!target) return;

            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            this.updateUrl(id, 'push');
            this.active = id;
            this.open = false;
        },
        init() {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        this.active = entry.target.id;
                        this.updateUrl(entry.target.id, 'replace');
                    }
                });
            }, { rootMargin: '-32% 0px -58% 0px', threshold: 0.01 });

            document.querySelectorAll('section[id]').forEach((section) => observer.observe(section));

            if (this.initialSection) {
                requestAnimationFrame(() => {
                    const target = document.getElementById(this.initialSection);
                    if (target) target.scrollIntoView({ behavior: 'auto', block: 'start' });
                });
            }
        }
    }"
    class="fixed inset-x-0 top-0 z-50 border-b border-[#E5E5E5] bg-white shadow-sm"
>
    <div class="mx-auto flex h-[74px] w-full max-w-7xl items-center justify-between gap-3 px-5 sm:h-[86px] sm:px-8 xl:gap-4 xl:px-10">
        <div class="shrink-0"><x-brand-logo /></div>

        <nav class="hidden min-w-0 flex-1 justify-end xl:flex {{ $isRtl ? 'pl-5' : 'pr-5' }}" aria-label="{{ __('site.nav.aria') }}">
            <div class="flex min-w-0 items-center gap-4 2xl:gap-5 {{ $isRtl ? 'flex-row-reverse' : '' }}">
                @foreach ($sections as $id => $label)
                    <a
                        href="{{ url('/' . $locale . '/' . $id) }}"
                        @click.prevent="scrollToSection('{{ $id }}')"
                        class="relative whitespace-nowrap py-3 text-[14px] font-bold text-[#111111] transition duration-300 after:absolute after:inset-x-0 after:bottom-1 after:h-0.5 after:origin-center after:rounded-full after:bg-[#1E9447] after:transition after:duration-300 hover:text-[#1E9447] 2xl:text-[15px]"
                        :class="active === '{{ $id }}' ? 'text-[#1E9447] after:scale-x-100' : 'after:scale-x-0'"
                    >{{ $label }}</a>
                @endforeach
            </div>
        </nav>

        <div class="ml-auto flex shrink-0 items-center justify-end gap-2 sm:gap-4">
            <a href="{{ url('/' . $locale . '/contact') }}" @click.prevent="scrollToSection('contact')" class="hidden min-h-11 whitespace-nowrap rounded-full bg-gradient-to-r from-[#1E9447] to-[#16763A] px-6 text-[15px] font-extrabold text-white shadow-lg shadow-[#1E9447]/25 transition duration-300 hover:scale-105 hover:from-[#16763A] hover:to-[#1E9447] hover:shadow-xl hover:shadow-[#1E9447]/30 focus:outline-none focus:ring-4 focus:ring-[#1E9447]/25 sm:inline-flex sm:items-center">
                {{ __('site.cta.start') }}
            </a>

            <div class="flex shrink-0 rounded-full border border-[#E5E5E5] bg-white p-1 shadow-sm {{ $isRtl ? 'flex-row-reverse' : '' }}" aria-label="{{ __('site.lang.label') }}">
                <a href="{{ url('/en') }}" class="whitespace-nowrap rounded-full px-3 py-2 text-sm font-black transition {{ $locale === 'en' ? 'bg-[#1E9447] text-white shadow-sm' : 'bg-white text-[#111111] hover:text-[#1E9447]' }}">EN</a>
                <a href="{{ url('/ar') }}" class="whitespace-nowrap rounded-full px-3 py-2 text-sm font-black transition {{ $locale === 'ar' ? 'bg-[#1E9447] text-white shadow-sm' : 'bg-white text-[#111111] hover:text-[#1E9447]' }}">AR</a>
            </div>

            <button
                type="button"
                class="inline-flex h-11 w-11 items-center justify-center rounded-full border border-[#E5E5E5] text-[#111111] transition hover:border-[#1E9447] hover:text-[#1E9447] xl:hidden"
                aria-label="{{ __('site.nav.menu') }}"
                :aria-expanded="open.toString()"
                @click="open = !open"
            >
                <svg x-show="!open" viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M4 7h16M4 12h16M4 17h16" stroke-linecap="round"/>
                </svg>
                <svg x-cloak x-show="open" viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M6 6l12 12M18 6 6 18" stroke-linecap="round"/>
                </svg>
            </button>
        </div>
    </div>

    <div x-cloak x-show="open" x-transition class="border-t border-[#E5E5E5] bg-white px-5 py-4 xl:hidden">
        <nav class="mx-auto flex w-full max-w-7xl flex-col gap-2" aria-label="{{ __('site.nav.mobile_aria') }}">
            @foreach ($sections as $id => $label)
                <a href="{{ url('/' . $locale . '/' . $id) }}" class="whitespace-nowrap rounded-lg px-3 py-3 text-base font-semibold text-[#111111] transition hover:bg-[#EAF8EF] hover:text-[#1E9447]" @click.prevent="scrollToSection('{{ $id }}')">{{ $label }}</a>
            @endforeach
            <a href="{{ url('/' . $locale . '/contact') }}" @click.prevent="scrollToSection('contact')" class="mt-2 inline-flex min-h-12 items-center justify-center whitespace-nowrap rounded-full bg-gradient-to-r from-[#1E9447] to-[#16763A] px-7 text-base font-extrabold text-white shadow-lg shadow-[#1E9447]/25 transition duration-300 hover:scale-[1.02] hover:from-[#16763A] hover:to-[#1E9447]">{{ __('site.cta.start') }}</a>
        </nav>
    </div>
</header>
