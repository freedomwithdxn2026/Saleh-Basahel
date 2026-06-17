@php
    $locale = $locale ?? app()->getLocale();
    $isRtl = $isRtl ?? $locale === 'ar';
    $section = $section ?? null;
    $nav = __('site.nav');
    $siteContent = app(\App\Support\SiteContent::class);
    $copy = fn (string $key): string => $siteContent->text($locale, $key);
    $contentArray = function (string $key) use ($locale, $siteContent): array {
        $source = __($key, [], $locale);

        if (! is_array($source)) {
            return [];
        }

        $walk = function (array $items, string $prefix) use (&$walk, $locale, $siteContent): array {
            foreach ($items as $itemKey => $value) {
                $childKey = $prefix . '.' . $itemKey;
                $items[$itemKey] = is_array($value)
                    ? $walk($value, $childKey)
                    : $siteContent->text($locale, $childKey);
            }

            return $items;
        };

        return $walk($source, $key);
    };
    $siteImage = fn (string $key, string $fallback): string => $siteContent->image($key, $fallback);
    $assetWithVersion = function (string $path): string {
        if (preg_match('/^https?:\/\//i', $path)) {
            return $path;
        }

        $cleanPath = ltrim($path, '/');
        $url = asset($cleanPath);
        $filePath = public_path($cleanPath);

        return file_exists($filePath) ? $url . '?v=' . filemtime($filePath) : $url;
    };

    $heroDefaultImage = '/images/hero-image.jpg';
    $heroImage = $siteImage('hero_image', $heroDefaultImage);
    $heroImageSrc = $assetWithVersion($heroImage);
    $heroDefaultSrcset = asset('images/hero-image.jpg') . ' 1280w';
    $heroSrcset = $heroImage === $heroDefaultImage ? $heroDefaultSrcset : $heroImageSrc;
    $heroSizes = '(min-width: 1280px) 44vw, (min-width: 1024px) 48vw, 92vw';
    $overviewVideo = $siteImage('overview_video', '/videos/free-overview.mp4');
    $overviewVideoSrc = $assetWithVersion($overviewVideo);
    $wellnessImage = $siteImage('wellness_image', '/images/wellness-lifestyle.webp');
    $wellnessImageSrc = $assetWithVersion($wellnessImage);
    $profileImage = $siteImage('profile_image', '/images/profile.jpg');
    $profileImageSrc = $assetWithVersion($profileImage);
@endphp

<!doctype html>
<html lang="{{ $locale }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{{ $copy('site.meta.description') }}">
    <link rel="canonical" href="{{ url('/' . $locale . ($section ? '/' . $section : '')) }}">
    <link rel="alternate" hreflang="en" href="{{ url('/en' . ($section ? '/' . $section : '')) }}">
    <link rel="alternate" hreflang="ar" href="{{ url('/ar' . ($section ? '/' . $section : '')) }}">
    <link rel="icon" type="image/png" href="{{ asset('favcon.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('favcon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/saleh-landing.css') }}">
    <link
        rel="preload"
        as="image"
        href="{{ $heroImageSrc }}"
        imagesrcset="{{ $heroSrcset }}"
        imagesizes="{{ $heroSizes }}"
        fetchpriority="high"
    >
    <title>{{ $copy('site.meta.title') }}</title>
    <script defer src="{{ asset('js/alpine.min.js') }}"></script>
    <style>
        html {
            scroll-behavior: smooth;
        }

        [x-cloak] {
            display: none !important;
        }

        .hero-visual {
            background:
                radial-gradient(circle at 20% 20%, rgba(30, 148, 71, 0.18), transparent 34%),
                linear-gradient(135deg, #ffffff, #eef8f1);
        }

        @keyframes support-box-shake {
            0%, 100% {
                transform: rotate(0deg) translateY(0);
            }

            20% {
                transform: rotate(-0.35deg) translateY(-1px);
            }

            40% {
                transform: rotate(0.35deg) translateY(1px);
            }

            60% {
                transform: rotate(-0.25deg) translateY(0);
            }

            80% {
                transform: rotate(0.25deg) translateY(-1px);
            }
        }

        .support-box-shake {
            animation: support-box-shake 2.8s ease-in-out infinite;
            transform-origin: center;
        }

        @keyframes section-reveal {
            from {
                opacity: 0;
                transform: translateY(24px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .reveal-up {
            animation: section-reveal 0.75s ease both;
            animation-timeline: view();
            animation-range: entry 0% cover 28%;
        }

        @keyframes reveal-from-left {
            from {
                opacity: 0;
                transform: translateX(-64px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes reveal-from-right {
            from {
                opacity: 0;
                transform: translateX(64px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .reveal-left {
            animation: reveal-from-left 0.85s cubic-bezier(0.22, 1, 0.36, 1) both;
            animation-timeline: view();
            animation-range: entry 0% cover 30%;
        }

        .reveal-right {
            animation: reveal-from-right 0.85s cubic-bezier(0.22, 1, 0.36, 1) both;
            animation-timeline: view();
            animation-range: entry 0% cover 30%;
        }

        .section-clip-x {
            overflow-x: clip;
        }

        main > section:not(#hero) {
            content-visibility: auto;
            contain-intrinsic-size: 900px;
        }

        .sr-only {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border: 0;
        }

        @keyframes growth-line-draw {
            0% {
                stroke-dashoffset: 420;
                opacity: 0.45;
            }

            42%, 72% {
                stroke-dashoffset: 0;
                opacity: 1;
            }

            100% {
                stroke-dashoffset: -420;
                opacity: 0.7;
            }
        }

        @keyframes growth-bar-rise {
            0%, 100% {
                transform: scaleY(0.62);
            }

            50% {
                transform: scaleY(1);
            }
        }

        @keyframes growth-point-pulse {
            0%, 100% {
                transform: scale(0.95);
                opacity: 0.72;
            }

            50% {
                transform: scale(1.18);
                opacity: 1;
            }
        }

        .growth-graph-line {
            animation: growth-line-draw 5.6s ease-in-out infinite;
            stroke-dasharray: 420;
        }

        .growth-graph-bar {
            animation: growth-bar-rise 3.4s ease-in-out infinite;
            transform-box: fill-box;
            transform-origin: bottom;
        }

        .growth-graph-point {
            animation: growth-point-pulse 2.8s ease-in-out infinite;
            transform-box: fill-box;
            transform-origin: center;
        }

        @media (prefers-reduced-motion: reduce) {
            html {
                scroll-behavior: auto;
            }

            .support-box-shake {
                animation: none;
            }

            .reveal-up,
            .reveal-left,
            .reveal-right,
            .growth-graph-line,
            .growth-graph-bar,
            .growth-graph-point {
                animation: none;
            }
        }
    </style>
</head>
<body class="{{ $isRtl ? 'font-arabic' : 'font-sans' }} bg-white text-ink antialiased">
    <x-header :locale="$locale" :nav="$nav" :initial-section="$section" />

    <main>
        <section id="hero" class="min-h-screen bg-[#EAF8EF] px-5 pt-24 pb-10 text-[#111111] sm:px-8 sm:pt-28 sm:pb-12 lg:px-16 lg:pt-28 lg:pb-12">
            <div class="mx-auto grid min-h-[calc(100vh-10rem)] w-full max-w-7xl items-center gap-8 lg:grid-cols-[1.05fr_0.9fr] lg:gap-10">
                <div class="max-w-3xl">
                    <div class="inline-flex items-center gap-3 rounded-full border border-white/80 bg-white/45 px-4 py-2 text-sm font-normal text-[#111111] shadow-sm">
                        <span class="h-3 w-3 rounded-full bg-[#1E9447] shadow-[0_0_0_8px_rgba(30,148,71,0.12)]"></span>
                        {{ $copy('site.hero.trust_badge') }}
                    </div>
                    <h1 class="mt-6 max-w-3xl text-4xl font-semibold leading-[1.22] tracking-normal text-[#111111] sm:text-5xl lg:text-[3rem]">
                        {{ $copy('site.hero.title') }}
                    </h1>
                    <p class="mt-5 max-w-2xl text-[17px] leading-8 text-[#303030] sm:text-lg">
                        {{ $copy('site.hero.subtitle') }}
                    </p>
                    <div class="mt-7 grid max-w-xl grid-cols-1 gap-4 sm:grid-cols-2">
                        @foreach ($contentArray('site.hero.features') as $feature)
                            <div class="flex min-h-14 items-center justify-center rounded-lg border border-[#D9E9DE] bg-white/70 px-5 text-center text-base font-normal text-[#111111] shadow-sm">
                                {{ $copy('site.hero.features.' . $loop->index) }}
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-7">
                        <a
                            href="{{ url('/' . $locale . '/video-overview') }}"
                            onclick="event.preventDefault(); document.getElementById('video-overview')?.scrollIntoView({ behavior: 'smooth', block: 'start' }); history.pushState(null, '', this.href);"
                            class="inline-flex min-h-[52px] items-center justify-center rounded-full bg-[#1E9447] px-8 text-base font-bold text-white shadow-lg shadow-[#1E9447]/25 transition duration-300 hover:scale-105 hover:bg-[#16763A] hover:shadow-xl hover:shadow-[#1E9447]/30 focus:outline-none focus:ring-4 focus:ring-[#1E9447]/25"
                        >
                            {{ $copy('site.cta.overview') }}
                        </a>
                    </div>
                </div>

                <div>
                    <div class="overflow-hidden rounded-[24px] bg-white shadow-2xl shadow-[#16763A]/10">
                        <div class="relative aspect-[1731/909] w-full overflow-hidden rounded-[24px] bg-white">
                            <picture class="absolute inset-0 block h-full w-full">
                                <source
                                    type="image/webp"
                                    srcset="{{ $heroSrcset }}"
                                    sizes="{{ $heroSizes }}"
                                >
                                <img
                                    src="{{ $heroImageSrc }}"
                                    srcset="{{ $heroSrcset }}"
                                    sizes="{{ $heroSizes }}"
                                    width="1731"
                                    height="909"
                                    alt="{{ $copy('site.hero.image_alt') }}"
                                    loading="eager"
                                    decoding="async"
                                    fetchpriority="high"
                                    class="h-full w-full object-contain object-center"
                                >
                            </picture>
                        </div>
                    </div>
                    <p class="mt-5 text-center text-sm text-[#111111]">{{ $copy('site.hero.fine_print') }}</p>
                </div>
            </div>
        </section>

        <section id="video-overview" class="scroll-mt-28 bg-[#FBFAF5] px-5 py-20 sm:px-8 lg:px-16">
            <div class="mx-auto w-full max-w-7xl">
                <div class="mx-auto max-w-4xl text-center">
                    <p class="mb-4 text-sm font-semibold uppercase tracking-[0.16em] text-[#16763A]">{{ $copy('site.video.eyebrow') }}</p>
                    <h2 class="text-3xl font-bold tracking-normal text-[#111111] sm:text-4xl">{{ $copy('site.video.title') }}</h2>
                    <p class="mx-auto mt-5 max-w-3xl text-lg leading-8 text-[#111111]">{{ $copy('site.video.description') }}</p>
                </div>

                <div class="mx-auto mt-12 max-w-5xl rounded-[18px] bg-gradient-to-r from-[#1E9447] via-[#16763A] to-[#F5A623] p-[2px] shadow-2xl shadow-[#16763A]/15">
                    <video
                        class="aspect-video w-full rounded-[16px] bg-black object-cover"
                        controls
                        controlslist="nodownload noplaybackrate"
                        disablepictureinpicture
                        playsinline
                        preload="none"
                        poster="{{ asset('images/video-overview-poster.svg') }}"
                        oncontextmenu="return false"
                        onclick="this.paused ? this.play() : this.pause()"
                        aria-label="{{ $copy('site.video.title') }}"
                    >
                        <source src="{{ $overviewVideoSrc }}" type="video/mp4">
                        {{ $copy('site.video.fallback') }}
                    </video>
                </div>

                <div
                    x-data="{ open: false }"
                    :class="open ? '' : 'support-box-shake'"
                    class="mx-auto mt-8 max-w-5xl rounded-[18px] border border-[#B9E6C8] bg-white text-center shadow-xl shadow-[#16763A]/10 transition duration-300"
                >
                    <button
                        type="button"
                        class="group flex w-full flex-col items-center justify-center gap-5 rounded-[18px] p-6 text-center focus:outline-none focus:ring-4 focus:ring-[#1E9447]/20"
                        x-bind:aria-expanded="open.toString()"
                        aria-controls="special-support-content"
                        @click="open = !open"
                    >
                        <div class="text-center">
                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[#16763A]">{{ $copy('site.video.gift_eyebrow') }}</p>
                            <h3 class="mt-3 text-2xl font-bold text-[#111111]">{{ $copy('site.video.gift_title') }}</h3>
                        </div>
                        <span
                            class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-[#FFF4DA] text-[#1E9447] transition duration-300 group-hover:scale-105 group-hover:bg-[#EAF8EF]"
                            aria-hidden="true"
                        >
                            <svg viewBox="0 0 24 24" class="h-8 w-8" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 12v8a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-8"/>
                                <path d="M2 7h20v5H2z"/>
                                <path d="M12 7v14"/>
                                <path d="M12 7H7.5A2.5 2.5 0 1 1 10 4.5C10 6 12 7 12 7Z"/>
                                <path d="M12 7h4.5A2.5 2.5 0 1 0 14 4.5C14 6 12 7 12 7Z"/>
                            </svg>
                        </span>
                    </button>

                    <div
                        id="special-support-content"
                        x-cloak
                        x-show="open"
                        x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 -translate-y-3"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-200"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 -translate-y-3"
                        class="relative px-6 pb-6 pt-6 sm:pt-7"
                    >
                        <div class="relative z-10 grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
                            @foreach ($contentArray('site.video.gift_items') as $item)
                                <div class="flex min-h-16 items-center justify-center rounded-lg border border-[#E5E5E5] bg-[#FBFCFA] px-4 text-center text-sm font-medium text-[#111111]">
                                    {{ $copy('site.video.gift_items.' . $loop->index) }}
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-6 flex flex-col items-center gap-5 border-t border-[#E5E5E5] pt-6 text-center">
                            <p class="mx-auto max-w-3xl text-sm leading-7 text-[#666666]">{{ $copy('site.video.gift_note') }}</p>
                            <a href="{{ url('/' . $locale . '/contact') }}" class="inline-flex min-h-12 shrink-0 items-center justify-center rounded-full bg-[#1E9447] px-7 text-sm font-bold text-white shadow-lg shadow-[#1E9447]/25 transition duration-300 hover:scale-105 hover:bg-[#16763A] focus:outline-none focus:ring-4 focus:ring-[#1E9447]/25">
                                {{ $copy('site.video.gift_cta') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="how-it-works" class="scroll-mt-28 bg-white px-5 py-20 sm:px-8 lg:px-16">
            <div class="mx-auto w-full max-w-7xl">
                <div class="mx-auto max-w-3xl text-center">
                    <p class="mb-3 text-sm font-semibold uppercase tracking-[0.16em] text-brand">{{ $copy('site.sections.how.eyebrow') }}</p>
                    <h2 class="text-3xl font-bold leading-tight tracking-normal text-ink sm:text-4xl">{{ $copy('site.sections.how.title') }}</h2>
                    <p class="mt-5 text-lg leading-8 text-muted">{{ $copy('site.sections.how.body') }}</p>
                </div>
                <div class="mt-10 grid gap-5 md:grid-cols-3">
                    @foreach ($contentArray('site.sections.how.steps') as $step)
                        <div class="rounded-lg border border-line bg-white p-6 text-center shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-xl">
                            <h3 class="text-xl font-semibold text-ink">{{ $copy('site.sections.how.steps.' . $loop->index . '.title') }}</h3>
                            <p class="mt-3 leading-7 text-muted">{{ $copy('site.sections.how.steps.' . $loop->index . '.body') }}</p>
                        </div>
                    @endforeach
                </div>
                <div class="mx-auto mt-8 max-w-5xl rounded-2xl border border-brand/20 bg-[#EAF8EF] p-6 text-center shadow-[0_18px_50px_rgba(30,148,71,0.10)] sm:p-8">
                    <p class="text-sm font-semibold uppercase tracking-[0.16em] text-brand">{{ $copy('site.sections.how.training.eyebrow') }}</p>
                    <h3 class="mt-3 text-2xl font-semibold leading-snug text-ink sm:text-3xl">{{ $copy('site.sections.how.training.title') }}</h3>
                    <p class="mx-auto mt-4 max-w-3xl text-base leading-8 text-muted sm:text-lg">{{ $copy('site.sections.how.training.body') }}</p>
                    <div class="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                        @foreach ($contentArray('site.sections.how.training.points') as $point)
                            <div class="rounded-xl border border-brand/15 bg-white px-4 py-4 text-sm font-medium text-ink shadow-sm">
                                {{ $copy('site.sections.how.training.points.' . $loop->index) }}
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        <section id="success-stories" class="scroll-mt-28 overflow-hidden bg-[#EAF8EF] px-5 py-20 sm:px-8 lg:px-16 lg:py-24">
            @php
                $storyTestimonials = $contentArray('site.sections.stories.testimonials');
                $storyTrustNote = $copy('site.sections.stories.trust_note');

                if (! is_array($storyTestimonials)) {
                    $storyTestimonials = trans('site.sections.stories.testimonials', [], 'en');
                }

                if ($storyTrustNote === 'site.sections.stories.trust_note') {
                    $storyTrustNote = trans('site.sections.stories.trust_note', [], 'en');
                }
            @endphp
            <div class="mx-auto w-full max-w-7xl text-center">
                <div class="mx-auto max-w-4xl reveal-up">
                    <p class="mb-3 text-sm font-semibold uppercase tracking-[0.18em] text-brand">{{ $copy('site.sections.stories.eyebrow') }}</p>
                    <h2 class="mx-auto max-w-5xl text-center text-3xl font-semibold leading-tight tracking-normal text-ink [text-wrap:balance] sm:text-4xl">{{ $copy('site.sections.stories.title') }}</h2>
                    <p class="mx-auto mt-5 max-w-3xl text-lg leading-8 text-muted">{{ $copy('site.sections.stories.body') }}</p>
                </div>

                <div class="mt-12 grid gap-6 md:grid-cols-3">
                    @foreach ($storyTestimonials as $testimonial)
                        <article class="reveal-up group relative overflow-hidden rounded-[28px] border border-[#D9EDE1] bg-white p-7 text-center shadow-xl shadow-[#16763A]/10 transition duration-300 hover:-translate-y-2 hover:border-[#E6C26A]/60 hover:shadow-2xl hover:shadow-[#16763A]/16" style="animation-delay: {{ 180 + ($loop->index * 110) }}ms">
                            @php
                                $testimonialIndex = $loop->index;
                            @endphp
                            <div class="absolute inset-x-0 top-0 h-1.5 bg-gradient-to-r from-[#1E9447] via-[#E6C26A] to-[#1E9447]"></div>
                            <div class="absolute -right-10 -top-10 h-32 w-32 rounded-full bg-[#EAF8EF] blur-2xl rtl:-left-10 rtl:right-auto"></div>
                            <div class="relative mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-[#1E9447] text-lg font-extrabold text-white shadow-lg shadow-[#1E9447]/20 transition duration-300 group-hover:scale-105">
                                {{ $copy('site.sections.stories.testimonials.' . $testimonialIndex . '.initials') }}
                            </div>
                            <div class="relative mt-5 flex justify-center gap-1 text-[#E6C26A]" aria-label="{{ $testimonial['rating_label'] }}">
                                @for ($i = 0; $i < 5; $i++)
                                    <span aria-hidden="true">&#9733;</span>
                                @endfor
                            </div>
                            <p class="relative mt-5 text-xl font-semibold leading-7 text-[#111111]">{{ $copy('site.sections.stories.testimonials.' . $testimonialIndex . '.headline') }}</p>
                            <p class="relative mt-4 leading-7 text-[#444444]">{{ $copy('site.sections.stories.testimonials.' . $testimonialIndex . '.body') }}</p>
                            <div class="relative mt-7 rounded-2xl border border-[#E6C26A]/45 bg-[#FFF8E4] px-4 py-4">
                                <p class="text-sm font-semibold leading-6 text-[#6F5413]">{{ $copy('site.sections.stories.testimonials.' . $testimonialIndex . '.metric') }}</p>
                            </div>
                            <div class="relative mt-5 border-t border-[#E5E5E5] pt-4">
                                <p class="font-semibold text-[#111111]">{{ $copy('site.sections.stories.testimonials.' . $testimonialIndex . '.name') }}</p>
                                <p class="mt-1 text-sm text-[#666666]">{{ $copy('site.sections.stories.testimonials.' . $testimonialIndex . '.role') }}</p>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="reveal-up mx-auto mt-10 max-w-5xl rounded-[26px] border border-[#D9EDE1] bg-white p-6 text-center shadow-xl shadow-[#16763A]/10 sm:p-8">
                    <p class="text-base leading-8 text-[#333333]">{{ $copy('site.sections.stories.trust_note') }}</p>
                </div>
            </div>
        </section>

        <section id="wellness-lifestyle" class="section-clip-x scroll-mt-28 bg-[#FBFAF5] px-5 py-20 sm:px-8 lg:px-16 lg:py-24">
            @php
                $wellnessCards = $contentArray('site.sections.wellness.cards');
                $wellnessImageAlt = $copy('site.sections.wellness.image_alt');

                if (! is_array($wellnessCards)) {
                    $wellnessCards = trans('site.sections.wellness.cards', [], 'en');
                }

                if ($wellnessImageAlt === 'site.sections.wellness.image_alt') {
                    $wellnessImageAlt = trans('site.sections.wellness.image_alt', [], 'en');
                }
            @endphp
            <div class="mx-auto w-full max-w-7xl">
                <div class="reveal-up mx-auto max-w-7xl text-center">
                    <p class="mb-3 text-sm font-semibold uppercase tracking-[0.16em] text-brand">{{ $copy('site.sections.wellness.eyebrow') }}</p>
                    <h2 class="mx-auto max-w-full text-center text-[clamp(1.9rem,2.35vw,2.35rem)] font-bold leading-tight tracking-normal text-ink [text-wrap:balance] xl:whitespace-nowrap">{{ $copy('site.sections.wellness.title') }}</h2>
                    <p class="mx-auto mt-5 max-w-4xl text-lg leading-8 text-muted">{{ $copy('site.sections.wellness.body') }}</p>
                </div>

                <div class="mt-12 grid gap-10 lg:grid-cols-[0.9fr_1.1fr] lg:items-start">
                    <div class="lg:self-stretch">
                        <div class="lg:sticky lg:top-28">
                            <div class="reveal-left overflow-hidden rounded-[24px] bg-brand-light shadow-2xl shadow-[#16763A]/10">
                                <img
                                    src="{{ $wellnessImageSrc }}"
                                    alt="{{ $wellnessImageAlt }}"
                                    loading="lazy"
                                    decoding="async"
                                    class="aspect-[4/3] w-full object-cover object-center transition duration-500 hover:scale-[1.03]"
                                >
                            </div>
                        </div>
                    </div>

                    <div class="reveal-right grid gap-5 sm:grid-cols-2 lg:min-h-[980px] lg:auto-rows-fr">
                        @foreach ($wellnessCards as $card)
                            <article class="flex min-h-[260px] flex-col justify-center rounded-[18px] border border-line bg-white p-6 text-center shadow-lg shadow-black/5 transition duration-300 hover:-translate-y-1 hover:border-[#B9E6C8] hover:shadow-2xl hover:shadow-[#16763A]/10 lg:min-h-[320px]" style="animation-delay: {{ $loop->index * 90 }}ms">
                                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-brand-light text-brand">
                                    <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path d="M12 21c4.5-3.2 7-7.1 7-11.2A7 7 0 0 0 5 9.8C5 13.9 7.5 17.8 12 21Z"/>
                                        <path d="M9 10.5c1.6.2 2.7.9 3 2.3.3-1.4 1.4-2.1 3-2.3"/>
                                    </svg>
                                </div>
                                <h3 class="mt-4 text-xl font-semibold text-ink">{{ $card['title'] }}</h3>
                                <p class="mt-3 leading-7 text-muted">{{ $card['body'] }}</p>
                            </article>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        <section id="business-opportunity" class="section-clip-x scroll-mt-28 bg-[#F8FAF8] px-5 py-16 sm:px-8 lg:px-16 lg:py-20">
            <div class="mx-auto w-full max-w-7xl">
                <div class="reveal-up mx-auto max-w-4xl text-center">
                    <p class="mb-3 text-sm font-semibold uppercase tracking-[0.16em] text-brand">{{ $copy('site.sections.business.eyebrow') }}</p>
                    <h2 class="reveal-up mx-auto max-w-4xl text-center text-3xl font-bold leading-tight tracking-normal text-ink [text-wrap:balance] sm:text-4xl" style="animation-delay: 0.3s">{{ $copy('site.sections.business.title') }}</h2>
                    <p class="reveal-up mx-auto mt-5 max-w-3xl text-lg leading-8 text-muted" style="animation-delay: 0.5s">{{ $copy('site.sections.business.body') }}</p>
                </div>

                <div class="mt-12 grid gap-8 lg:grid-cols-[0.95fr_1.05fr] lg:items-stretch">
                    <div class="growth-graph reveal-left overflow-hidden rounded-[22px] border border-[#CDEED8] bg-white p-5 text-left shadow-xl shadow-[#16763A]/8 rtl:text-right">
                        <div class="flex items-center justify-between gap-4">
                            <p class="text-sm font-semibold uppercase tracking-[0.14em] text-brand-dark">{{ $copy('site.sections.business.graph_label') }}</p>
                            <span class="rounded-full bg-brand-light px-3 py-1 text-xs font-semibold text-brand">{{ $copy('site.sections.business.graph_badge') }}</span>
                        </div>
                        <p class="mt-3 max-w-xl text-xl font-semibold leading-snug text-ink sm:text-2xl">
                            {{ $copy('site.sections.business.graph_message') }}
                        </p>
                        <div class="relative mt-5 overflow-hidden rounded-[18px] bg-gradient-to-br from-white via-[#F7FCF8] to-[#EAF8EF] p-4">
                            <svg class="h-64 w-full" viewBox="0 0 520 260" role="img" aria-label="{{ $copy('site.sections.business.graph_label') }}" preserveAspectRatio="none">
                                <defs>
                                    <linearGradient id="growthFill" x1="0" x2="0" y1="0" y2="1">
                                        <stop offset="0%" stop-color="#1E9447" stop-opacity="0.22"/>
                                        <stop offset="100%" stop-color="#1E9447" stop-opacity="0.02"/>
                                    </linearGradient>
                                    <linearGradient id="growthStroke" x1="0" x2="1" y1="0" y2="0">
                                        <stop offset="0%" stop-color="#16763A"/>
                                        <stop offset="100%" stop-color="#35B76A"/>
                                    </linearGradient>
                                </defs>
                                <g opacity="0.45" stroke="#DDEFE4" stroke-width="1">
                                    <path d="M24 64H496"/>
                                    <path d="M24 112H496"/>
                                    <path d="M24 160H496"/>
                                    <path d="M24 208H496"/>
                                </g>
                                <g fill="#DFF4E7">
                                    <rect class="growth-graph-bar" x="58" y="176" width="34" height="46" rx="10" style="animation-delay: 0s"/>
                                    <rect class="growth-graph-bar" x="128" y="154" width="34" height="68" rx="10" style="animation-delay: 0.2s"/>
                                    <rect class="growth-graph-bar" x="198" y="134" width="34" height="88" rx="10" style="animation-delay: 0.4s"/>
                                    <rect class="growth-graph-bar" x="268" y="110" width="34" height="112" rx="10" style="animation-delay: 0.6s"/>
                                </g>
                                <path d="M42 216 C96 196 128 177 176 162 C230 145 260 152 308 124 C360 94 398 93 474 66 L474 224 L42 224Z" fill="url(#growthFill)"/>
                                <path class="growth-graph-line" d="M42 216 C96 196 128 177 176 162 C230 145 260 152 308 124 C360 94 398 93 474 66" fill="none" stroke="url(#growthStroke)" stroke-width="6" stroke-linecap="round"/>
                                <g fill="#1E9447">
                                    <circle class="growth-graph-point" cx="176" cy="162" r="7" style="animation-delay: 0.15s"/>
                                    <circle class="growth-graph-point" cx="308" cy="124" r="7" style="animation-delay: 0.35s"/>
                                    <circle class="growth-graph-point" cx="474" cy="66" r="8" style="animation-delay: 0.55s"/>
                                </g>
                            </svg>
                        </div>
                        <p class="mt-4 text-center text-sm leading-6 text-muted">{{ $copy('site.sections.business.graph_note') }}</p>
                    </div>

                    <div class="reveal-right grid gap-4 sm:grid-cols-2 lg:gap-5">
                        @foreach ($contentArray('site.sections.business.points') as $point)
                            <article
                                class="group flex min-h-[118px] items-start gap-4 rounded-[18px] border border-line bg-white p-5 text-left shadow-md shadow-black/5 transition duration-300 hover:-translate-y-[5px] hover:border-[#B9E6C8] hover:bg-white hover:shadow-2xl hover:shadow-[#16763A]/10 rtl:text-right"
                                style="animation-delay: {{ 0.2 + ($loop->index * 0.2) }}s"
                            >
                                <span class="mt-1 flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-brand-light text-base font-bold text-brand transition duration-300 group-hover:scale-110 group-hover:bg-brand group-hover:text-white">&#10003;</span>
                                <span class="text-base leading-7 text-muted">{{ $point }}</span>
                            </article>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        <section id="about-saleh" class="section-clip-x scroll-mt-28 bg-white px-5 py-20 sm:px-8 lg:px-16 lg:py-24">
            <div class="mx-auto w-full max-w-7xl">
                <div class="mx-auto text-center">
                    <p class="mb-3 text-sm font-semibold uppercase tracking-[0.16em] text-brand">{{ $copy('site.sections.about.eyebrow') }}</p>
                    <h2 class="mx-auto max-w-full text-center text-[clamp(1.25rem,2.35vw,2.35rem)] font-bold leading-tight tracking-normal text-ink [text-wrap:balance] lg:whitespace-nowrap">{{ $copy('site.sections.about.title') }}</h2>
                </div>

                <div class="mt-12 grid items-center gap-10 lg:grid-cols-[0.9fr_1.1fr] lg:gap-14">
                    <div class="reveal-left">
                        <div class="overflow-hidden rounded-[26px] border border-[#CDEED8] bg-brand-light p-3 shadow-2xl shadow-[#16763A]/10">
                            <img
                                src="{{ $profileImageSrc }}"
                                alt="{{ $copy('site.sections.about.image_alt') }}"
                                loading="lazy"
                                decoding="async"
                                class="aspect-[4/5] w-full rounded-[20px] object-cover object-center"
                            >
                        </div>
                    </div>

                    <div class="reveal-right text-center lg:text-left rtl:lg:text-right">
                        <p class="text-lg leading-8 text-muted">{{ $copy('site.sections.about.body') }}</p>
                        <p class="mt-5 text-lg leading-8 text-muted">{{ $copy('site.sections.about.body_extra') }}</p>

                        <div class="mt-8 grid gap-4 sm:grid-cols-2">
                            @foreach ($contentArray('site.sections.about.highlights') as $highlight)
                                <article class="rounded-[18px] border border-line bg-[#F8FAF8] p-5 text-center shadow-md shadow-black/5 transition duration-300 hover:-translate-y-1 hover:border-[#B9E6C8] hover:shadow-xl hover:shadow-[#16763A]/10">
                                    <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-full bg-brand-light text-brand">
                                        <span aria-hidden="true">&#10003;</span>
                                    </div>
                                    <h3 class="mt-4 text-lg font-semibold text-ink">{{ $highlight['title'] }}</h3>
                                    <p class="mt-2 text-sm leading-6 text-muted">{{ $highlight['body'] }}</p>
                                </article>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="faq" class="scroll-mt-28 bg-[#F8FAF8] px-5 py-20 sm:px-8 lg:px-16">
            <div class="mx-auto w-full max-w-7xl">
                <div class="mx-auto max-w-5xl text-center">
                    <div class="mb-10">
                        <p class="mb-3 text-sm font-semibold uppercase tracking-[0.16em] text-brand">{{ $copy('site.sections.faq.eyebrow') }}</p>
                        <h2 class="text-3xl font-bold leading-tight tracking-normal text-ink sm:text-4xl">{{ $copy('site.sections.faq.title') }}</h2>
                    </div>
                    <div x-data="{ open: null }" class="space-y-4">
                        @foreach ($contentArray('site.sections.faq.items') as $item)
                            @php
                                $answers = $item['answers'] ?? (isset($item['a']) ? [$item['a']] : []);
                                $panelId = 'faq-panel-'.$loop->index;
                            @endphp
                            <div class="overflow-hidden rounded-2xl border border-line bg-[#FFFDF8] text-left shadow-lg shadow-black/[0.03] transition duration-300 hover:-translate-y-0.5 hover:border-brand/30 hover:shadow-xl hover:shadow-brand/10 rtl:text-right" :class="{ 'border-brand/30 shadow-xl shadow-brand/10': open === {{ $loop->index }} }">
                                <button
                                    type="button"
                                    class="flex w-full items-center justify-between gap-5 px-6 py-5 text-left text-lg font-semibold text-ink transition hover:text-brand focus:outline-none focus:ring-4 focus:ring-brand/10 rtl:text-right sm:px-7"
                                    x-bind:aria-expanded="open === {{ $loop->index }} ? 'true' : 'false'"
                                    aria-controls="{{ $panelId }}"
                                    @click="open = open === {{ $loop->index }} ? null : {{ $loop->index }}"
                                >
                                    <span>{{ $item['q'] }}</span>
                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-brand-light text-brand transition duration-300" :class="{ 'rotate-180 bg-brand text-white': open === {{ $loop->index }} }">
                                        <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="m6 9 6 6 6-6"/>
                                        </svg>
                                    </span>
                                </button>
                                <div
                                    id="{{ $panelId }}"
                                    class="grid transition-all duration-500 ease-out"
                                    :class="open === {{ $loop->index }} ? 'grid-rows-[1fr] opacity-100' : 'grid-rows-[0fr] opacity-0'"
                                >
                                    <div class="min-h-0 overflow-hidden">
                                        <div class="border-t border-line px-6 pb-6 pt-5 sm:px-7">
                                            <ul class="space-y-3 text-base leading-7 text-muted">
                                                @foreach ($answers as $answer)
                                                    <li class="flex gap-3">
                                                        <span class="mt-2 h-2 w-2 shrink-0 rounded-full bg-brand"></span>
                                                        <span>{{ $answer }}</span>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        <section id="contact" class="scroll-mt-28 bg-[#EAF8EF] px-5 py-20 sm:px-8 lg:px-16">
            @php
                $countryDialOptions = [
                    ['iso' => 'AF', 'name' => 'Afghanistan', 'dial' => '+93'], ['iso' => 'AL', 'name' => 'Albania', 'dial' => '+355'], ['iso' => 'DZ', 'name' => 'Algeria', 'dial' => '+213'], ['iso' => 'AS', 'name' => 'American Samoa', 'dial' => '+1-684'], ['iso' => 'AD', 'name' => 'Andorra', 'dial' => '+376'], ['iso' => 'AO', 'name' => 'Angola', 'dial' => '+244'], ['iso' => 'AI', 'name' => 'Anguilla', 'dial' => '+1-264'], ['iso' => 'AG', 'name' => 'Antigua and Barbuda', 'dial' => '+1-268'], ['iso' => 'AR', 'name' => 'Argentina', 'dial' => '+54'], ['iso' => 'AM', 'name' => 'Armenia', 'dial' => '+374'], ['iso' => 'AW', 'name' => 'Aruba', 'dial' => '+297'], ['iso' => 'AU', 'name' => 'Australia', 'dial' => '+61'], ['iso' => 'AT', 'name' => 'Austria', 'dial' => '+43'], ['iso' => 'AZ', 'name' => 'Azerbaijan', 'dial' => '+994'],
                    ['iso' => 'BS', 'name' => 'Bahamas', 'dial' => '+1-242'], ['iso' => 'BH', 'name' => 'Bahrain', 'dial' => '+973'], ['iso' => 'BD', 'name' => 'Bangladesh', 'dial' => '+880'], ['iso' => 'BB', 'name' => 'Barbados', 'dial' => '+1-246'], ['iso' => 'BY', 'name' => 'Belarus', 'dial' => '+375'], ['iso' => 'BE', 'name' => 'Belgium', 'dial' => '+32'], ['iso' => 'BZ', 'name' => 'Belize', 'dial' => '+501'], ['iso' => 'BJ', 'name' => 'Benin', 'dial' => '+229'], ['iso' => 'BM', 'name' => 'Bermuda', 'dial' => '+1-441'], ['iso' => 'BT', 'name' => 'Bhutan', 'dial' => '+975'], ['iso' => 'BO', 'name' => 'Bolivia', 'dial' => '+591'], ['iso' => 'BA', 'name' => 'Bosnia and Herzegovina', 'dial' => '+387'], ['iso' => 'BW', 'name' => 'Botswana', 'dial' => '+267'], ['iso' => 'BR', 'name' => 'Brazil', 'dial' => '+55'], ['iso' => 'BN', 'name' => 'Brunei', 'dial' => '+673'], ['iso' => 'BG', 'name' => 'Bulgaria', 'dial' => '+359'], ['iso' => 'BF', 'name' => 'Burkina Faso', 'dial' => '+226'], ['iso' => 'BI', 'name' => 'Burundi', 'dial' => '+257'],
                    ['iso' => 'KH', 'name' => 'Cambodia', 'dial' => '+855'], ['iso' => 'CM', 'name' => 'Cameroon', 'dial' => '+237'], ['iso' => 'CA', 'name' => 'Canada', 'dial' => '+1'], ['iso' => 'CV', 'name' => 'Cape Verde', 'dial' => '+238'], ['iso' => 'KY', 'name' => 'Cayman Islands', 'dial' => '+1-345'], ['iso' => 'CF', 'name' => 'Central African Republic', 'dial' => '+236'], ['iso' => 'TD', 'name' => 'Chad', 'dial' => '+235'], ['iso' => 'CL', 'name' => 'Chile', 'dial' => '+56'], ['iso' => 'CN', 'name' => 'China', 'dial' => '+86'], ['iso' => 'CO', 'name' => 'Colombia', 'dial' => '+57'], ['iso' => 'KM', 'name' => 'Comoros', 'dial' => '+269'], ['iso' => 'CG', 'name' => 'Congo', 'dial' => '+242'], ['iso' => 'CD', 'name' => 'Congo DR', 'dial' => '+243'], ['iso' => 'CK', 'name' => 'Cook Islands', 'dial' => '+682'], ['iso' => 'CR', 'name' => 'Costa Rica', 'dial' => '+506'], ['iso' => 'CI', 'name' => 'Cote d Ivoire', 'dial' => '+225'], ['iso' => 'HR', 'name' => 'Croatia', 'dial' => '+385'], ['iso' => 'CU', 'name' => 'Cuba', 'dial' => '+53'], ['iso' => 'CY', 'name' => 'Cyprus', 'dial' => '+357'], ['iso' => 'CZ', 'name' => 'Czech Republic', 'dial' => '+420'],
                    ['iso' => 'DK', 'name' => 'Denmark', 'dial' => '+45'], ['iso' => 'DJ', 'name' => 'Djibouti', 'dial' => '+253'], ['iso' => 'DM', 'name' => 'Dominica', 'dial' => '+1-767'], ['iso' => 'DO', 'name' => 'Dominican Republic', 'dial' => '+1-809'], ['iso' => 'EC', 'name' => 'Ecuador', 'dial' => '+593'], ['iso' => 'EG', 'name' => 'Egypt', 'dial' => '+20'], ['iso' => 'SV', 'name' => 'El Salvador', 'dial' => '+503'], ['iso' => 'GQ', 'name' => 'Equatorial Guinea', 'dial' => '+240'], ['iso' => 'ER', 'name' => 'Eritrea', 'dial' => '+291'], ['iso' => 'EE', 'name' => 'Estonia', 'dial' => '+372'], ['iso' => 'ET', 'name' => 'Ethiopia', 'dial' => '+251'],
                    ['iso' => 'FJ', 'name' => 'Fiji', 'dial' => '+679'], ['iso' => 'FI', 'name' => 'Finland', 'dial' => '+358'], ['iso' => 'FR', 'name' => 'France', 'dial' => '+33'], ['iso' => 'GF', 'name' => 'French Guiana', 'dial' => '+594'], ['iso' => 'PF', 'name' => 'French Polynesia', 'dial' => '+689'], ['iso' => 'GA', 'name' => 'Gabon', 'dial' => '+241'], ['iso' => 'GM', 'name' => 'Gambia', 'dial' => '+220'], ['iso' => 'GE', 'name' => 'Georgia', 'dial' => '+995'], ['iso' => 'DE', 'name' => 'Germany', 'dial' => '+49'], ['iso' => 'GH', 'name' => 'Ghana', 'dial' => '+233'], ['iso' => 'GI', 'name' => 'Gibraltar', 'dial' => '+350'], ['iso' => 'GR', 'name' => 'Greece', 'dial' => '+30'], ['iso' => 'GL', 'name' => 'Greenland', 'dial' => '+299'], ['iso' => 'GD', 'name' => 'Grenada', 'dial' => '+1-473'], ['iso' => 'GP', 'name' => 'Guadeloupe', 'dial' => '+590'], ['iso' => 'GU', 'name' => 'Guam', 'dial' => '+1-671'], ['iso' => 'GT', 'name' => 'Guatemala', 'dial' => '+502'], ['iso' => 'GN', 'name' => 'Guinea', 'dial' => '+224'], ['iso' => 'GW', 'name' => 'Guinea-Bissau', 'dial' => '+245'], ['iso' => 'GY', 'name' => 'Guyana', 'dial' => '+592'],
                    ['iso' => 'HT', 'name' => 'Haiti', 'dial' => '+509'], ['iso' => 'HN', 'name' => 'Honduras', 'dial' => '+504'], ['iso' => 'HK', 'name' => 'Hong Kong', 'dial' => '+852'], ['iso' => 'HU', 'name' => 'Hungary', 'dial' => '+36'], ['iso' => 'IS', 'name' => 'Iceland', 'dial' => '+354'], ['iso' => 'IN', 'name' => 'India', 'dial' => '+91'], ['iso' => 'ID', 'name' => 'Indonesia', 'dial' => '+62'], ['iso' => 'IR', 'name' => 'Iran', 'dial' => '+98'], ['iso' => 'IQ', 'name' => 'Iraq', 'dial' => '+964'], ['iso' => 'IE', 'name' => 'Ireland', 'dial' => '+353'], ['iso' => 'IL', 'name' => 'Israel', 'dial' => '+972'], ['iso' => 'IT', 'name' => 'Italy', 'dial' => '+39'],
                    ['iso' => 'JM', 'name' => 'Jamaica', 'dial' => '+1-876'], ['iso' => 'JP', 'name' => 'Japan', 'dial' => '+81'], ['iso' => 'JO', 'name' => 'Jordan', 'dial' => '+962'], ['iso' => 'KZ', 'name' => 'Kazakhstan', 'dial' => '+7'], ['iso' => 'KE', 'name' => 'Kenya', 'dial' => '+254'], ['iso' => 'KI', 'name' => 'Kiribati', 'dial' => '+686'], ['iso' => 'KR', 'name' => 'South Korea', 'dial' => '+82'], ['iso' => 'KW', 'name' => 'Kuwait', 'dial' => '+965'], ['iso' => 'KG', 'name' => 'Kyrgyzstan', 'dial' => '+996'],
                    ['iso' => 'LA', 'name' => 'Laos', 'dial' => '+856'], ['iso' => 'LV', 'name' => 'Latvia', 'dial' => '+371'], ['iso' => 'LB', 'name' => 'Lebanon', 'dial' => '+961'], ['iso' => 'LS', 'name' => 'Lesotho', 'dial' => '+266'], ['iso' => 'LR', 'name' => 'Liberia', 'dial' => '+231'], ['iso' => 'LY', 'name' => 'Libya', 'dial' => '+218'], ['iso' => 'LI', 'name' => 'Liechtenstein', 'dial' => '+423'], ['iso' => 'LT', 'name' => 'Lithuania', 'dial' => '+370'], ['iso' => 'LU', 'name' => 'Luxembourg', 'dial' => '+352'],
                    ['iso' => 'MO', 'name' => 'Macau', 'dial' => '+853'], ['iso' => 'MG', 'name' => 'Madagascar', 'dial' => '+261'], ['iso' => 'MW', 'name' => 'Malawi', 'dial' => '+265'], ['iso' => 'MY', 'name' => 'Malaysia', 'dial' => '+60'], ['iso' => 'MV', 'name' => 'Maldives', 'dial' => '+960'], ['iso' => 'ML', 'name' => 'Mali', 'dial' => '+223'], ['iso' => 'MT', 'name' => 'Malta', 'dial' => '+356'], ['iso' => 'MH', 'name' => 'Marshall Islands', 'dial' => '+692'], ['iso' => 'MQ', 'name' => 'Martinique', 'dial' => '+596'], ['iso' => 'MR', 'name' => 'Mauritania', 'dial' => '+222'], ['iso' => 'MU', 'name' => 'Mauritius', 'dial' => '+230'], ['iso' => 'MX', 'name' => 'Mexico', 'dial' => '+52'], ['iso' => 'FM', 'name' => 'Micronesia', 'dial' => '+691'], ['iso' => 'MD', 'name' => 'Moldova', 'dial' => '+373'], ['iso' => 'MC', 'name' => 'Monaco', 'dial' => '+377'], ['iso' => 'MN', 'name' => 'Mongolia', 'dial' => '+976'], ['iso' => 'ME', 'name' => 'Montenegro', 'dial' => '+382'], ['iso' => 'MS', 'name' => 'Montserrat', 'dial' => '+1-664'], ['iso' => 'MA', 'name' => 'Morocco', 'dial' => '+212'], ['iso' => 'MZ', 'name' => 'Mozambique', 'dial' => '+258'], ['iso' => 'MM', 'name' => 'Myanmar', 'dial' => '+95'],
                    ['iso' => 'NA', 'name' => 'Namibia', 'dial' => '+264'], ['iso' => 'NR', 'name' => 'Nauru', 'dial' => '+674'], ['iso' => 'NP', 'name' => 'Nepal', 'dial' => '+977'], ['iso' => 'NL', 'name' => 'Netherlands', 'dial' => '+31'], ['iso' => 'NC', 'name' => 'New Caledonia', 'dial' => '+687'], ['iso' => 'NZ', 'name' => 'New Zealand', 'dial' => '+64'], ['iso' => 'NI', 'name' => 'Nicaragua', 'dial' => '+505'], ['iso' => 'NE', 'name' => 'Niger', 'dial' => '+227'], ['iso' => 'NG', 'name' => 'Nigeria', 'dial' => '+234'], ['iso' => 'NU', 'name' => 'Niue', 'dial' => '+683'], ['iso' => 'NO', 'name' => 'Norway', 'dial' => '+47'],
                    ['iso' => 'OM', 'name' => 'Oman', 'dial' => '+968'], ['iso' => 'PK', 'name' => 'Pakistan', 'dial' => '+92'], ['iso' => 'PW', 'name' => 'Palau', 'dial' => '+680'], ['iso' => 'PS', 'name' => 'Palestine', 'dial' => '+970'], ['iso' => 'PA', 'name' => 'Panama', 'dial' => '+507'], ['iso' => 'PG', 'name' => 'Papua New Guinea', 'dial' => '+675'], ['iso' => 'PY', 'name' => 'Paraguay', 'dial' => '+595'], ['iso' => 'PE', 'name' => 'Peru', 'dial' => '+51'], ['iso' => 'PH', 'name' => 'Philippines', 'dial' => '+63'], ['iso' => 'PL', 'name' => 'Poland', 'dial' => '+48'], ['iso' => 'PT', 'name' => 'Portugal', 'dial' => '+351'], ['iso' => 'PR', 'name' => 'Puerto Rico', 'dial' => '+1-787'], ['iso' => 'QA', 'name' => 'Qatar', 'dial' => '+974'],
                    ['iso' => 'RE', 'name' => 'Reunion', 'dial' => '+262'], ['iso' => 'RO', 'name' => 'Romania', 'dial' => '+40'], ['iso' => 'RU', 'name' => 'Russia', 'dial' => '+7'], ['iso' => 'RW', 'name' => 'Rwanda', 'dial' => '+250'], ['iso' => 'WS', 'name' => 'Samoa', 'dial' => '+685'], ['iso' => 'SM', 'name' => 'San Marino', 'dial' => '+378'], ['iso' => 'ST', 'name' => 'Sao Tome and Principe', 'dial' => '+239'], ['iso' => 'SA', 'name' => 'Saudi Arabia', 'dial' => '+966'], ['iso' => 'SN', 'name' => 'Senegal', 'dial' => '+221'], ['iso' => 'RS', 'name' => 'Serbia', 'dial' => '+381'], ['iso' => 'SC', 'name' => 'Seychelles', 'dial' => '+248'], ['iso' => 'SL', 'name' => 'Sierra Leone', 'dial' => '+232'], ['iso' => 'SG', 'name' => 'Singapore', 'dial' => '+65'], ['iso' => 'SK', 'name' => 'Slovakia', 'dial' => '+421'], ['iso' => 'SI', 'name' => 'Slovenia', 'dial' => '+386'], ['iso' => 'SB', 'name' => 'Solomon Islands', 'dial' => '+677'], ['iso' => 'SO', 'name' => 'Somalia', 'dial' => '+252'], ['iso' => 'ZA', 'name' => 'South Africa', 'dial' => '+27'], ['iso' => 'SS', 'name' => 'South Sudan', 'dial' => '+211'], ['iso' => 'ES', 'name' => 'Spain', 'dial' => '+34'], ['iso' => 'LK', 'name' => 'Sri Lanka', 'dial' => '+94'], ['iso' => 'SD', 'name' => 'Sudan', 'dial' => '+249'], ['iso' => 'SR', 'name' => 'Suriname', 'dial' => '+597'], ['iso' => 'SE', 'name' => 'Sweden', 'dial' => '+46'], ['iso' => 'CH', 'name' => 'Switzerland', 'dial' => '+41'], ['iso' => 'SY', 'name' => 'Syria', 'dial' => '+963'],
                    ['iso' => 'TW', 'name' => 'Taiwan', 'dial' => '+886'], ['iso' => 'TJ', 'name' => 'Tajikistan', 'dial' => '+992'], ['iso' => 'TZ', 'name' => 'Tanzania', 'dial' => '+255'], ['iso' => 'TH', 'name' => 'Thailand', 'dial' => '+66'], ['iso' => 'TL', 'name' => 'Timor-Leste', 'dial' => '+670'], ['iso' => 'TG', 'name' => 'Togo', 'dial' => '+228'], ['iso' => 'TO', 'name' => 'Tonga', 'dial' => '+676'], ['iso' => 'TT', 'name' => 'Trinidad and Tobago', 'dial' => '+1-868'], ['iso' => 'TN', 'name' => 'Tunisia', 'dial' => '+216'], ['iso' => 'TR', 'name' => 'Turkey', 'dial' => '+90'], ['iso' => 'TM', 'name' => 'Turkmenistan', 'dial' => '+993'], ['iso' => 'TC', 'name' => 'Turks and Caicos Islands', 'dial' => '+1-649'], ['iso' => 'TV', 'name' => 'Tuvalu', 'dial' => '+688'],
                    ['iso' => 'UG', 'name' => 'Uganda', 'dial' => '+256'], ['iso' => 'UA', 'name' => 'Ukraine', 'dial' => '+380'], ['iso' => 'AE', 'name' => 'United Arab Emirates', 'dial' => '+971'], ['iso' => 'GB', 'name' => 'United Kingdom', 'dial' => '+44'], ['iso' => 'US', 'name' => 'United States', 'dial' => '+1'], ['iso' => 'UY', 'name' => 'Uruguay', 'dial' => '+598'], ['iso' => 'UZ', 'name' => 'Uzbekistan', 'dial' => '+998'], ['iso' => 'VU', 'name' => 'Vanuatu', 'dial' => '+678'], ['iso' => 'VA', 'name' => 'Vatican City', 'dial' => '+39'], ['iso' => 'VE', 'name' => 'Venezuela', 'dial' => '+58'], ['iso' => 'VN', 'name' => 'Vietnam', 'dial' => '+84'], ['iso' => 'VG', 'name' => 'British Virgin Islands', 'dial' => '+1-284'], ['iso' => 'VI', 'name' => 'U.S. Virgin Islands', 'dial' => '+1-340'], ['iso' => 'YE', 'name' => 'Yemen', 'dial' => '+967'], ['iso' => 'ZM', 'name' => 'Zambia', 'dial' => '+260'], ['iso' => 'ZW', 'name' => 'Zimbabwe', 'dial' => '+263'],
                ];

                $flagFor = static function (string $iso): string {
                    return collect(str_split(strtoupper($iso)))->map(fn ($letter) => '&#'.(127397 + ord($letter)).';')->implode('');
                };
            @endphp

            <div class="mx-auto w-full max-w-7xl text-center">
                <div class="mx-auto max-w-4xl">
                    <p class="mb-3 text-sm font-semibold uppercase tracking-[0.16em] text-brand">{{ $copy('site.form.qualifier.eyebrow') }}</p>
                    <h2 class="text-4xl font-semibold leading-tight tracking-normal text-ink sm:text-5xl">{{ $copy('site.form.qualifier.title') }}</h2>
                    <p class="mt-5 text-lg leading-8 text-muted">{{ $copy('site.form.qualifier.body') }}</p>
                </div>

                @php
    $leadFlowOptions = [
        [
            'value' => 'better-health',
            'title' => 'Better Health',
            'body' => 'Improve daily wellness, energy, and long-term habits.',
            'subcategories' => [
                [
                    'value' => 'energy-wellness',
                    'title' => 'More energy & better daily wellness',
                    'body' => 'Support your daily routine and natural energy.',
                    'details' => [
                        ['value' => 'low-energy-morning', 'title' => 'Low energy in the morning'],
                        ['value' => 'afternoon-tiredness', 'title' => 'Afternoon tiredness'],
                        ['value' => 'stress-busy-lifestyle', 'title' => 'Stress and busy lifestyle'],
                    ],
                ],
                [
                    'value' => 'weight-fitness',
                    'title' => 'Weight management & fitness',
                    'body' => 'Build better consistency around food, movement, and routine.',
                    'details' => [
                        ['value' => 'controlling-food-cravings', 'title' => 'Controlling food cravings'],
                        ['value' => 'staying-consistent', 'title' => 'Staying consistent'],
                        ['value' => 'low-energy-exercise', 'title' => 'Low energy for exercise'],
                    ],
                ],
                [
                    'value' => 'immunity-overall-health',
                    'title' => 'Better immunity & overall health',
                    'body' => 'Focus on steady wellness habits for long-term well-being.',
                    'details' => [
                        ['value' => 'daily-immune-support', 'title' => 'Daily immune support'],
                        ['value' => 'natural-wellness-products', 'title' => 'Natural wellness products'],
                        ['value' => 'long-term-health-habits', 'title' => 'Better long-term health habits'],
                    ],
                ],
            ],
        ],
        [
            'value' => 'extra-income',
            'title' => 'Extra Income',
            'body' => 'Explore a flexible path for extra monthly support.',
            'subcategories' => [
                [
                    'value' => 'extra-side-income',
                    'title' => 'Extra side income',
                    'body' => 'Start with a realistic additional income goal.',
                    'details' => [
                        ['value' => 'small-monthly-support', 'title' => 'Small monthly support'],
                        ['value' => 'serious-second-income', 'title' => 'A serious second income'],
                        ['value' => 'grow-step-by-step', 'title' => 'I want to grow step by step'],
                    ],
                ],
                [
                    'value' => 'work-from-home',
                    'title' => 'Work from home opportunity',
                    'body' => 'Learn about flexible work that can fit around your life.',
                    'details' => [
                        ['value' => 'more-time-freedom', 'title' => 'More time freedom'],
                        ['value' => 'flexible-part-time-work', 'title' => 'Flexible part-time work'],
                        ['value' => 'income-around-schedule', 'title' => 'Build income around my schedule'],
                    ],
                ],
                [
                    'value' => 'financial-freedom',
                    'title' => 'Financial freedom',
                    'body' => 'Move toward better money habits and long-term goals.',
                    'details' => [
                        ['value' => 'less-monthly-pressure', 'title' => 'Less monthly pressure'],
                        ['value' => 'more-savings', 'title' => 'More savings'],
                        ['value' => 'long-term-passive-income', 'title' => 'Build long-term passive income'],
                    ],
                ],
            ],
        ],
        [
            'value' => 'health-and-income',
            'title' => 'Both - Health and Income',
            'body' => 'Improve wellness while exploring a flexible income path.',
            'subcategories' => [
                [
                    'value' => 'health-energy',
                    'title' => 'Better health & more energy',
                    'body' => 'Feel better and support the people around you.',
                    'details' => [
                        ['value' => 'feel-active-daily', 'title' => 'Feel more active daily'],
                        ['value' => 'improve-wellness-naturally', 'title' => 'Improve wellness naturally'],
                        ['value' => 'support-family-better', 'title' => 'Support my family better'],
                    ],
                ],
                [
                    'value' => 'extra-monthly-income',
                    'title' => 'Extra monthly income',
                    'body' => 'Create breathing room for expenses, family, and savings.',
                    'details' => [
                        ['value' => 'monthly-expenses', 'title' => 'Monthly expenses'],
                        ['value' => 'family-support', 'title' => 'Family support'],
                        ['value' => 'savings-future-goals', 'title' => 'Savings and future goals'],
                    ],
                ],
                [
                    'value' => 'health-income-together',
                    'title' => 'Health and income together',
                    'body' => 'Build both areas with a simple guided process.',
                    'details' => [
                        ['value' => 'better-personal-wellness', 'title' => 'Better personal wellness'],
                        ['value' => 'extra-monthly-income-detail', 'title' => 'Extra monthly income'],
                        ['value' => 'build-both-step-by-step', 'title' => 'Build both step by step'],
                    ],
                ],
            ],
        ],
    ];

    $leadTimeOptions = [
        ['value' => 'guidance-today', 'title' => 'I want guidance today', 'body' => 'I am ready for a private follow-up soon.'],
        ['value' => 'this-week', 'title' => 'This week is good for me', 'body' => 'I can make time for a private conversation this week.'],
        ['value' => 'just-exploring', 'title' => "I'm just exploring for now", 'body' => 'I want to understand first without pressure.'],
    ];
@endphp

                <form
                    method="post"
                    action="{{ route('leads.store', ['locale' => $locale]) }}"
                    @submit.prevent="submitLead($event)"
                    x-data="{
                        step: {{ $errors->any() ? 5 : 1 }},
                        submitting: false,
                        submitted: false,
                        submitError: '',
                        selectedCategory: null,
                        selectedSubcategory: null,
                        selectedDetail: null,
                        selectedTime: null,
                        selectedCountry: 'AE|+971|United Arab Emirates',
                        countryOpen: false,
                        countrySearch: '',
                        phoneLocal: '',
                        flowOptions: @js($leadFlowOptions),
                        timeOptions: @js($leadTimeOptions),
                        get progressPercent() {
                            return Math.round((this.step / 5) * 100);
                        },
                        get countryParts() {
                            const parts = this.selectedCountry.split('|');
                            return { iso: parts[0], dial: parts[1], name: parts.slice(2).join('|') };
                        },
                        get subcategoryOptions() {
                            return this.selectedCategory ? this.selectedCategory.subcategories : [];
                        },
                        get detailOptions() {
                            return this.selectedSubcategory ? this.selectedSubcategory.details : [];
                        },
                        flagUrl(iso) {
                            return `https://flagcdn.com/w40/${iso.toLowerCase()}.png`;
                        },
                        chooseCategory(option) {
                            this.selectedCategory = option;
                            this.selectedSubcategory = null;
                            this.selectedDetail = null;
                            this.selectedTime = null;
                            this.step = 2;
                        },
                        chooseSubcategory(option) {
                            this.selectedSubcategory = option;
                            this.selectedDetail = null;
                            this.selectedTime = null;
                            this.step = 3;
                        },
                        chooseDetail(option) {
                            this.selectedDetail = option;
                            this.selectedTime = null;
                            this.step = 4;
                        },
                        chooseTime(option) {
                            this.selectedTime = option;
                            this.step = 5;
                            this.$nextTick(() => this.$refs.nameInput?.focus());
                        },
                        goBack() {
                            if (this.step > 1) {
                                this.step -= 1;
                            }
                        },
                        chooseCountry(value) {
                            this.selectedCountry = value;
                            this.countryOpen = false;
                            this.countrySearch = '';
                        },
                        async submitLead(event) {
                            this.submitting = true;
                            this.submitError = '';

                            try {
                                const response = await fetch(event.target.action, {
                                    method: 'POST',
                                    headers: {
                                        'Accept': 'application/json',
                                        'X-Requested-With': 'XMLHttpRequest',
                                    },
                                    body: new FormData(event.target),
                                });

                                if (! response.ok) {
                                    const data = await response.json().catch(() => ({}));
                                    const firstError = data.errors ? Object.values(data.errors).flat()[0] : null;
                                    throw new Error(firstError || data.message || 'Please check the form and try again.');
                                }

                                this.submitted = true;
                                event.target.reset();
                            } catch (error) {
                                this.submitError = error.message || 'Something went wrong. Please try again.';
                            } finally {
                                this.submitting = false;
                            }
                        }
                    }"
                    class="mx-auto mt-10 max-w-xl rounded-3xl border border-line bg-white p-6 text-left shadow-2xl shadow-brand/10 rtl:text-right sm:p-8"
                >
                    @csrf

                    @if (session('status'))
                        <div class="mb-4 rounded-lg border border-brand/20 bg-brand-light p-4 text-sm font-bold text-brand-dark">{{ session('status') }}</div>
                    @endif

                    @if ($errors->any())
                        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-4 text-sm font-bold text-red-700">{{ $copy('site.form.errors') }}</div>
                    @endif

                    <div x-cloak x-show="submitted" x-transition class="rounded-2xl border border-brand/20 bg-brand-light p-6 text-center">
                        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-brand text-2xl font-bold text-white">&#10003;</div>
                        <h3 class="mt-4 text-2xl font-semibold text-ink">Successfully sent</h3>
                        <p class="mt-2 text-sm leading-6 text-muted">Thank you. Your details were received, and the team will follow up with you privately.</p>
                    </div>

                    <div x-cloak x-show="submitError" x-transition class="mb-4 rounded-lg border border-red-200 bg-red-50 p-4 text-sm font-bold text-red-700" x-text="submitError"></div>

                    <div x-show="!submitted">
                    <div class="mb-8 flex items-center justify-between gap-4 text-sm text-muted">
                        <button type="button" x-show="step > 1" @click="goBack()" class="font-semibold text-brand transition hover:text-brand-dark">{{ $copy('site.form.qualifier.back') }}</button>
                        <span x-show="step === 1">Step 1 of 5</span>
                        <span x-show="step === 2">Step 2 of 5</span>
                        <span x-show="step === 3">Step 3 of 5</span>
                        <span x-show="step === 4">Step 4 of 5</span>
                        <span x-show="step === 5">Step 5 of 5</span>
                        <span x-text="`${progressPercent}%`"></span>
                    </div>
                    <div class="mb-8 h-2 overflow-hidden rounded-full bg-[#EDF1EC]" role="progressbar" aria-label="{{ $copy('site.form.qualifier.step') }}" aria-valuemin="0" aria-valuemax="100" x-bind:aria-valuenow="progressPercent">
                        <div class="h-full rounded-full bg-[#1E9447] shadow-[0_2px_8px_rgba(30,148,71,0.24)] transition-all duration-700 ease-out" :style="`width: ${progressPercent}%`"></div>
                    </div>

                    <div x-show="step === 1" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-3" x-transition:enter-end="opacity-100 translate-y-0">
                        <h3 class="text-2xl font-semibold text-ink">What are you most interested in?</h3>
                        <p class="mt-2 text-muted">Choose the main category that matches your current goal.</p>
                        <div class="mt-6 grid gap-4">
                            <template x-for="option in flowOptions" :key="option.value">
                                <button type="button" @click="chooseCategory(option)" class="group rounded-2xl border border-line bg-[#FFFDF8] p-5 text-left transition duration-300 hover:-translate-y-1 hover:border-brand/40 hover:bg-brand-light hover:shadow-xl hover:shadow-brand/10 rtl:text-right">
                                    <span class="block text-lg font-semibold text-ink" x-text="option.title"></span>
                                    <span class="mt-2 block text-sm leading-6 text-muted" x-text="option.body"></span>
                                </button>
                            </template>
                        </div>
                    </div>

                    <div x-show="step === 2" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-3" x-transition:enter-end="opacity-100 translate-y-0">
                        <h3 class="text-2xl font-semibold text-ink">Which area matches you best?</h3>
                        <p class="mt-2 text-muted">This helps the team understand your priority before the private follow-up.</p>
                        <div class="mt-6 grid gap-4">
                            <template x-for="option in subcategoryOptions" :key="option.value">
                                <button type="button" @click="chooseSubcategory(option)" class="group rounded-2xl border border-line bg-[#FFFDF8] p-5 text-left transition duration-300 hover:-translate-y-1 hover:border-brand/40 hover:bg-brand-light hover:shadow-xl hover:shadow-brand/10 rtl:text-right">
                                    <span class="block text-lg font-semibold text-ink" x-text="option.title"></span>
                                    <span class="mt-2 block text-sm leading-6 text-muted" x-text="option.body"></span>
                                </button>
                            </template>
                        </div>
                    </div>

                    <div x-show="step === 3" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-3" x-transition:enter-end="opacity-100 translate-y-0">
                        <h3 class="text-2xl font-semibold text-ink">What describes your situation?</h3>
                        <p class="mt-2 text-muted">Pick the detailed option that feels closest to where you are now.</p>
                        <div class="mt-6 grid gap-4">
                            <template x-for="option in detailOptions" :key="option.value">
                                <button type="button" @click="chooseDetail(option)" class="group rounded-2xl border border-line bg-[#FFFDF8] p-5 text-left transition duration-300 hover:-translate-y-1 hover:border-brand/40 hover:bg-brand-light hover:shadow-xl hover:shadow-brand/10 rtl:text-right">
                                    <span class="block text-lg font-semibold text-ink" x-text="option.title"></span>
                                </button>
                            </template>
                        </div>
                    </div>

                    <div x-show="step === 4" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-3" x-transition:enter-end="opacity-100 translate-y-0">
                        <h3 class="text-2xl font-semibold text-ink">{{ $copy('site.form.qualifier.time_question') }}</h3>
                        <p class="mt-2 text-muted">{{ $copy('site.form.qualifier.time_hint') }}</p>
                        <div class="mt-6 grid gap-4">
                            <template x-for="option in timeOptions" :key="option.value">
                                <button type="button" @click="chooseTime(option)" class="group rounded-2xl border border-line bg-[#FFFDF8] p-5 text-left transition duration-300 hover:-translate-y-1 hover:border-brand/40 hover:bg-brand-light hover:shadow-xl hover:shadow-brand/10 rtl:text-right">
                                    <span class="block text-lg font-semibold text-ink" x-text="option.title"></span>
                                    <span class="mt-2 block text-sm leading-6 text-muted" x-text="option.body"></span>
                                </button>
                            </template>
                        </div>
                    </div>

                    <div x-show="step === 5" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-3" x-transition:enter-end="opacity-100 translate-y-0">
                        <h3 class="text-2xl font-semibold text-ink">{{ $copy('site.form.qualifier.details_title') }}</h3>
                        <p class="mt-2 text-muted">{{ $copy('site.form.qualifier.details_body') }}</p>

                        <input type="hidden" name="interest" :value="selectedCategory ? selectedCategory.title : @js(old('interest'))">
                        <input type="hidden" name="lead_category" :value="selectedCategory ? selectedCategory.title : @js(old('lead_category'))">
                        <input type="hidden" name="lead_subcategory" :value="selectedSubcategory ? selectedSubcategory.title : @js(old('lead_subcategory'))">
                        <input type="hidden" name="lead_detail_option" :value="selectedDetail ? selectedDetail.title : @js(old('lead_detail_option'))">
                        <input type="hidden" name="preferred_time_interest" :value="selectedTime ? selectedTime.title : @js(old('preferred_time_interest'))">
                        <input type="hidden" name="message" :value="selectedCategory && selectedSubcategory && selectedDetail && selectedTime ? `Category: ${selectedCategory.title} | Subcategory: ${selectedSubcategory.title} | Detail: ${selectedDetail.title} | Preferred: ${selectedTime.title}` : @js(old('message'))">
                        <input type="hidden" name="country" :value="countryParts.name">
                        <input type="hidden" name="phone" :value="phoneLocal ? `${countryParts.dial} ${phoneLocal}` : ''">

                        <label for="name" class="mt-6 block text-sm font-semibold text-ink">{{ $copy('site.form.fields.name') }}</label>
                        <input id="name" x-ref="nameInput" name="name" value="{{ old('name') }}" required placeholder="{{ $copy('site.form.placeholders.name') }}" class="mt-2 min-h-12 w-full rounded-lg border border-line px-4 text-base outline-none transition focus:border-brand focus:ring-4 focus:ring-brand/10">

                        <label for="email" class="mt-5 block text-sm font-semibold text-ink">{{ $copy('site.form.fields.email') }}</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required placeholder="{{ $copy('site.form.placeholders.email') }}" class="mt-2 min-h-12 w-full rounded-lg border border-line px-4 text-base outline-none transition focus:border-brand focus:ring-4 focus:ring-brand/10">

                        <label for="occupation" class="mt-5 block text-sm font-semibold text-ink">{{ $copy('site.form.fields.occupation') }}</label>
                        <input id="occupation" name="occupation" value="{{ old('occupation') }}" required placeholder="{{ $copy('site.form.placeholders.occupation') }}" class="mt-2 min-h-12 w-full rounded-lg border border-line px-4 text-base outline-none transition focus:border-brand focus:ring-4 focus:ring-brand/10">

                        <label for="phone_local" class="mt-5 block text-sm font-semibold text-ink">{{ $copy('site.form.fields.phone') }}</label>
                        <div class="mt-2 grid gap-3 sm:grid-cols-[0.95fr_1fr]">
                            <div class="relative" @click.outside="countryOpen = false">
                                <button type="button" @click="countryOpen = ! countryOpen" x-bind:aria-expanded="countryOpen" aria-controls="country-selector-options" class="flex min-h-12 w-full items-center justify-between gap-3 rounded-lg border border-line bg-white px-3 text-left text-sm outline-none transition focus:border-brand focus:ring-4 focus:ring-brand/10 rtl:text-right">
                                    <span class="flex min-w-0 items-center gap-2">
                                        <img :src="flagUrl(countryParts.iso)" :alt="`${countryParts.name} flag`" class="h-4 w-6 shrink-0 rounded-[2px] object-cover shadow-sm">
                                        <span class="truncate" x-text="`${countryParts.name} ${countryParts.dial}`"></span>
                                    </span>
                                    <svg viewBox="0 0 24 24" class="h-4 w-4 shrink-0 transition" :class="{ 'rotate-180': countryOpen }" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="m6 9 6 6 6-6"/>
                                    </svg>
                                </button>
                                <template x-if="countryOpen">
                                    <div id="country-selector-options" x-cloak x-show="countryOpen" x-transition class="absolute left-0 right-0 z-30 mt-2 overflow-hidden rounded-2xl border border-line bg-white shadow-2xl shadow-black/10">
                                    <div class="p-3">
                                        <input x-model="countrySearch" type="search" placeholder="{{ $copy('site.form.qualifier.country_search') }}" class="min-h-11 w-full rounded-lg border border-line px-3 text-sm outline-none transition focus:border-brand focus:ring-4 focus:ring-brand/10">
                                    </div>
                                    <div class="max-h-64 overflow-y-auto pb-2">
                                        @foreach ($countryDialOptions as $country)
                                            @php
                                                $countrySearchValue = strtolower($country['iso'].' '.$country['name'].' '.$country['dial']);
                                                $countryValue = $country['iso'].'|'.$country['dial'].'|'.$country['name'];
                                            @endphp
                                            <button
                                                type="button"
                                                x-show="'{{ $countrySearchValue }}'.includes(countrySearch.toLowerCase())"
                                                @click="chooseCountry('{{ $countryValue }}')"
                                                class="flex w-full items-center gap-3 px-4 py-2.5 text-left text-sm transition hover:bg-brand-light rtl:text-right"
                                            >
                                                <img src="https://flagcdn.com/w40/{{ strtolower($country['iso']) }}.png" alt="{{ $country['name'] }} flag" class="h-4 w-6 shrink-0 rounded-[2px] object-cover shadow-sm">
                                                <span class="min-w-0 flex-1 truncate">{{ $country['name'] }}</span>
                                                <span class="font-semibold text-brand">{{ $country['dial'] }}</span>
                                            </button>
                                        @endforeach
                                    </div>
                                    </div>
                                </template>
                            </div>
                            <input id="phone_local" x-model="phoneLocal" required inputmode="tel" autocomplete="tel" placeholder="{{ $copy('site.form.placeholders.phone') }}" class="min-h-12 rounded-lg border border-line px-4 text-base outline-none transition focus:border-brand focus:ring-4 focus:ring-brand/10">
                        </div>
                        <p class="mt-2 text-xs leading-5 text-muted">{{ $copy('site.form.qualifier.phone_hint') }}</p>

                        <label class="mt-5 flex gap-3 text-sm leading-6 text-muted {{ $isRtl ? 'flex-row-reverse text-right' : '' }}">
                            <input type="checkbox" name="consent" value="1" required class="mt-1 h-5 w-5 shrink-0 rounded border-line text-brand focus:ring-brand">
                            <span>{{ $copy('site.form.consent') }}</span>
                        </label>

                        <button class="mt-6 inline-flex min-h-12 w-full items-center justify-center rounded-full bg-[#1E9447] px-7 text-base font-bold text-white shadow-lg shadow-[#1E9447]/25 transition duration-300 hover:scale-[1.02] hover:bg-[#16763A] hover:shadow-xl hover:shadow-[#1E9447]/30 focus:outline-none focus:ring-4 focus:ring-[#1E9447]/25 disabled:cursor-not-allowed disabled:opacity-70" type="submit" :disabled="submitting">
                            <span x-show="!submitting">{{ $copy('site.form.submit') }}</span>
                            <span x-show="submitting">{{ $copy('site.form.qualifier.sending') }}</span>
                        </button>
                        <p class="mt-4 text-center text-xs leading-5 text-muted">{{ $copy('site.form.qualifier.privacy') }}</p>
                    </div>
                    </div>
                </form>
            </div>
        </section>
    </main>

    <footer class="bg-[#07130B] px-5 pt-16 pb-8 text-white sm:px-8 lg:px-16">
        @php
            $footerLinks = __('site.footer.links');
            $footerSocials = __('site.footer.socials');

            if (! is_array($footerLinks)) {
                $footerLinks = [];
            }

            if (! is_array($footerSocials)) {
                $footerSocials = [];
            }
        @endphp
        <div class="mx-auto w-full max-w-7xl">
            <div class="grid gap-10 lg:grid-cols-[1.15fr_0.8fr_0.9fr]">
                <div>
                    <div class="inline-flex items-center gap-3">
                        <a href="/{{ $locale }}" class="inline-flex items-center gap-3" aria-label="Saleh Basahel home">
                            <span class="flex h-12 w-12 items-center justify-center rounded-full bg-gradient-to-br from-brand to-brand-dark shadow-lg shadow-brand/20">
                                <svg viewBox="0 0 48 48" class="h-8 w-8" fill="none" aria-hidden="true">
                                    <path d="M23.7 36.5c0-10.1 5.9-18.2 16.1-22.7.9-.4 1.9.3 1.8 1.3C40.5 27.6 33 35.2 23.7 36.5Z" fill="#FFFFFF"/>
                                    <path d="M22.7 36.4C18 26.8 10.3 22.1 5.8 20.2c-.9-.4-1-1.7-.1-2.2 9.7-5.2 20.3-.4 25.6 9.1" stroke="#DDF6E6" stroke-width="3" stroke-linecap="round"/>
                                    <path d="M24 38V10" stroke="#FFFFFF" stroke-width="3" stroke-linecap="round"/>
                                    <path d="M24 10l6 6M24 10l-6 6" stroke="#FFFFFF" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </span>
                            <span class="leading-none">
                                <span class="block whitespace-nowrap text-[23px] font-black tracking-normal text-white">Saleh</span>
                                <span class="block whitespace-nowrap text-[14px] font-extrabold tracking-[0.18em] text-brand">Basahel</span>
                            </span>
                        </a>
                    </div>
                    <p class="mt-5 max-w-xl text-base leading-8 text-white/80">{{ $copy('site.footer.description') }}</p>

                    <div class="mt-6 flex flex-wrap gap-3">
                        @foreach ($footerSocials as $social)
                            <a
                                href="{{ $social['url'] }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                aria-label="{{ $social['label'] }}"
                                class="group flex h-12 w-12 items-center justify-center rounded-full border border-white/10 bg-white/[0.08] text-white transition duration-300 hover:-translate-y-1 hover:border-brand hover:bg-brand hover:shadow-xl hover:shadow-brand/20"
                            >
                                @switch($social['icon'])
                                    @case('facebook')
                                        <svg viewBox="0 0 24 24" class="h-5 w-5" fill="currentColor" aria-hidden="true"><path d="M14 8.2V6.7c0-.7.5-.9 1-.9h2V2.2L14.8 2C11.6 2 10 3.9 10 6.3v1.9H7v3.9h3V22h4v-9.9h3.1l.5-3.9H14Z"/></svg>
                                        @break
                                    @case('instagram')
                                        <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><path d="M17.5 6.5h.01"/></svg>
                                        @break
                                    @case('linkedin')
                                        <svg viewBox="0 0 24 24" class="h-5 w-5" fill="currentColor" aria-hidden="true"><path d="M6.9 8.8H3.4V21h3.5V8.8ZM5.1 3A2.05 2.05 0 1 0 5 7.1 2.05 2.05 0 0 0 5.1 3Zm15.5 11c0-3.3-1.8-5.4-4.6-5.4-2 0-3 .9-3.5 1.7V8.8H9.1V21h3.5v-6.8c0-1.8 1-2.8 2.4-2.8s2.1.9 2.1 2.8V21h3.5v-7Z"/></svg>
                                        @break
                                    @case('youtube')
                                        <svg viewBox="0 0 24 24" class="h-5 w-5" fill="currentColor" aria-hidden="true"><path d="M22 12s0-3.4-.4-5c-.2-.9-.9-1.6-1.8-1.8C18.2 4.8 12 4.8 12 4.8s-6.2 0-7.8.4c-.9.2-1.6.9-1.8 1.8C2 8.6 2 12 2 12s0 3.4.4 5c.2.9.9 1.6 1.8 1.8 1.6.4 7.8.4 7.8.4s6.2 0 7.8-.4c.9-.2 1.6-.9 1.8-1.8.4-1.6.4-5 .4-5Zm-12 3.1V8.9l5.2 3.1-5.2 3.1Z"/></svg>
                                        @break
                                    @default
                                        <svg viewBox="0 0 24 24" class="h-5 w-5" fill="currentColor" aria-hidden="true"><path d="M16.6 14.7c-.3-.2-1.8-.9-2.1-1-.3-.1-.5-.2-.7.2-.2.3-.8 1-.9 1.1-.2.2-.4.2-.7.1a8.1 8.1 0 0 1-4-3.5c-.2-.3 0-.5.1-.7l.5-.5c.1-.2.2-.3.3-.5.1-.2.1-.4 0-.6l-1-2.3c-.2-.6-.5-.5-.7-.5h-.6c-.2 0-.6.1-.9.4-.3.3-1.1 1.1-1.1 2.6 0 1.6 1.1 3.1 1.3 3.3.2.2 2.2 3.4 5.4 4.7.8.3 1.4.5 1.8.6.8.2 1.5.2 2.1.1.6-.1 1.8-.7 2.1-1.5.3-.7.3-1.4.2-1.5-.1-.2-.3-.3-.6-.5ZM12 2a10 10 0 0 0-8.5 15.3L2.3 22l4.8-1.3A10 10 0 1 0 12 2Z"/></svg>
                                @endswitch
                            </a>
                        @endforeach
                    </div>
                </div>

                <div>
                    <h3 class="text-sm font-semibold uppercase tracking-[0.16em] text-[#B9E6C8]">{{ $copy('site.footer.quick_title') }}</h3>
                    <nav class="mt-5 grid gap-3 text-sm text-white/80" aria-label="{{ $copy('site.footer.quick_title') }}">
                        @foreach ($footerLinks as $link)
                            <a class="transition hover:translate-x-1 hover:text-white rtl:hover:-translate-x-1" href="/{{ $locale }}/{{ $link['slug'] }}">{{ $link['label'] }}</a>
                        @endforeach
                    </nav>
                </div>

                <div class="rounded-[22px] border border-white/10 bg-white/[0.06] p-6 shadow-2xl shadow-black/20">
                    <h3 class="text-xl font-semibold text-white">{{ $copy('site.footer.cta_title') }}</h3>
                    <p class="mt-3 text-sm leading-7 text-white/80">{{ $copy('site.footer.cta_body') }}</p>
                    <a href="https://wa.me/971555574958" target="_blank" rel="noopener noreferrer" class="mt-6 inline-flex min-h-12 items-center justify-center rounded-full bg-[#1E9447] px-6 text-sm font-semibold text-white shadow-lg shadow-[#1E9447]/25 transition duration-300 hover:scale-[1.03] hover:bg-[#16763A] hover:shadow-xl hover:shadow-[#1E9447]/30">
                        {{ $copy('site.footer.cta_button') }}
                    </a>
                </div>
            </div>

            <div class="mt-12 border-t border-white/10 pt-6">
                <div class="flex flex-col items-center justify-between gap-4 text-center text-sm text-white/75 lg:flex-row lg:text-left rtl:lg:text-right">
                    <p>{{ strtr($copy('site.footer.copy'), [':year' => date('Y'), '{year}' => date('Y')]) }}</p>
                    <p class="max-w-3xl">{{ $copy('site.footer.note') }}</p>
                </div>
            </div>
        </div>
    </footer>

    <a
        href="https://wa.me/971555574958"
        target="_blank"
        rel="noopener noreferrer"
        aria-label="WhatsApp"
        class="fixed bottom-6 right-6 z-40 flex h-16 w-16 items-center justify-center rounded-full bg-[#1E9447] text-white shadow-2xl shadow-[#1E9447]/30 ring-4 ring-white transition duration-300 hover:scale-105 hover:bg-[#16763A] focus:outline-none focus:ring-4 focus:ring-[#1E9447]/25"
    >
        <svg viewBox="0 0 32 32" class="h-9 w-9" fill="currentColor" aria-hidden="true">
            <path d="M16.04 3C9.04 3 3.34 8.64 3.34 15.57c0 2.3.63 4.54 1.83 6.48L3.25 29l7.13-1.86a12.82 12.82 0 0 0 5.66 1.31c7 0 12.7-5.64 12.7-12.57S23.04 3 16.04 3Zm0 23.31c-1.79 0-3.54-.47-5.07-1.35l-.36-.2-4.23 1.1 1.13-4.06-.24-.42a10.4 10.4 0 0 1-1.58-5.5c0-5.74 4.65-10.43 10.35-10.43s10.35 4.69 10.35 10.43-4.65 10.43-10.35 10.43Zm5.66-7.8c-.31-.15-1.84-.9-2.13-1-.28-.1-.49-.15-.7.15-.2.31-.8 1-.98 1.2-.18.2-.36.23-.67.08-.31-.15-1.31-.48-2.5-1.53a9.38 9.38 0 0 1-1.73-2.15c-.18-.31-.02-.48.14-.63.14-.14.31-.36.46-.54.15-.18.2-.31.31-.51.1-.2.05-.38-.03-.54-.08-.15-.7-1.67-.95-2.28-.25-.6-.51-.52-.7-.53h-.59c-.2 0-.54.08-.82.38-.28.31-1.08 1.05-1.08 2.56s1.1 2.97 1.26 3.18c.15.2 2.17 3.31 5.26 4.64.74.31 1.31.5 1.76.64.74.23 1.41.2 1.94.12.59-.09 1.84-.75 2.1-1.48.26-.72.26-1.34.18-1.48-.08-.13-.28-.2-.59-.36Z"/>
        </svg>
    </a>
</body>
</html>









