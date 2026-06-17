@php
    $locale = $locale ?? app()->getLocale();
    $isRtl = $isRtl ?? $locale === 'ar';
    $effectiveDate = 'June 16, 2026';
@endphp

<!doctype html>
<html lang="{{ $locale }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Privacy Policy for Saleh Basahel, including how inquiry details, WhatsApp messages, email follow-up, scheduling activity, and lead records are handled.">
    <link rel="canonical" href="{{ url('/' . $locale . '/privacy-policy') }}">
    <link rel="alternate" hreflang="en" href="{{ url('/en/privacy-policy') }}">
    <link rel="alternate" hreflang="ar" href="{{ url('/ar/privacy-policy') }}">
    <link rel="icon" type="image/png" href="{{ asset('favcon.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('favcon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/saleh-landing.css') }}">
    <title>Privacy Policy | Saleh Basahel</title>
</head>
<body class="bg-[#F7FBF8] font-sans text-[#111111] antialiased">
    <header class="border-b border-[#E5E5E5] bg-white shadow-sm">
        <div class="mx-auto flex min-h-[84px] w-full max-w-7xl items-center justify-between gap-4 px-5 sm:px-8 lg:px-10">
            <a href="/{{ $locale }}" class="inline-flex items-center gap-3" aria-label="Saleh Basahel home">
                <span class="flex h-12 w-12 items-center justify-center rounded-full bg-gradient-to-br from-[#1E9447] to-[#16763A] shadow-lg shadow-[#1E9447]/20">
                    <svg viewBox="0 0 48 48" class="h-8 w-8" fill="none" aria-hidden="true">
                        <path d="M23.7 36.5c0-10.1 5.9-18.2 16.1-22.7.9-.4 1.9.3 1.8 1.3C40.5 27.6 33 35.2 23.7 36.5Z" fill="#FFFFFF"/>
                        <path d="M22.7 36.4C18 26.8 10.3 22.1 5.8 20.2c-.9-.4-1-1.7-.1-2.2 9.7-5.2 20.3-.4 25.6 9.1" stroke="#DDF6E6" stroke-width="3" stroke-linecap="round"/>
                        <path d="M24 38V10" stroke="#FFFFFF" stroke-width="3" stroke-linecap="round"/>
                        <path d="M24 10l6 6M24 10l-6 6" stroke="#FFFFFF" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
                <span class="leading-none">
                    <span class="block whitespace-nowrap text-[23px] font-bold tracking-normal text-[#111111]">Saleh</span>
                    <span class="block whitespace-nowrap text-[14px] font-bold tracking-[0.18em] text-[#1E9447]">Basahel</span>
                </span>
            </a>
            <a href="/{{ $locale }}" class="inline-flex min-h-11 items-center justify-center rounded-full bg-[#1E9447] px-5 text-sm font-semibold text-white shadow-lg shadow-[#1E9447]/20 transition hover:bg-[#16763A]">Back to Website</a>
        </div>
    </header>

    <main class="px-5 py-12 sm:px-8 lg:px-10 lg:py-16">
        <article class="mx-auto max-w-4xl rounded-[28px] border border-[#DCE8DF] bg-white p-6 shadow-2xl shadow-[#07130B]/8 sm:p-10 lg:p-12">
            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-[#1E9447]">Privacy Policy</p>
            <h1 class="mt-4 text-4xl font-semibold leading-tight text-[#111111] sm:text-5xl">How we protect your information</h1>
            <p class="mt-5 text-base leading-8 text-[#555555]">Effective date: {{ $effectiveDate }}</p>
            <p class="mt-6 text-lg leading-9 text-[#333333]">This Privacy Policy explains how Saleh Basahel collects, uses, stores, and protects information shared through this website, landing page forms, WhatsApp conversations, email communication, and private scheduling workflows.</p>

            <div class="mt-10 grid gap-8">
                <section>
                    <h2 class="text-2xl font-semibold text-[#111111]">1. Information we collect</h2>
                    <ul class="mt-4 list-disc space-y-3 pl-6 text-base leading-8 text-[#444444]">
                        <li>Contact details such as name, phone number, email address, country, and occupation.</li>
                        <li>Inquiry details such as your main interest, preferred time, goals, and messages you choose to share.</li>
                        <li>Communication history from website forms, WhatsApp conversations, email replies, follow-ups, and scheduling messages.</li>
                        <li>Basic technical data such as browser type, device type, page interactions, and form submission activity.</li>
                    </ul>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-[#111111]">2. How we use your information</h2>
                    <ul class="mt-4 list-disc space-y-3 pl-6 text-base leading-8 text-[#444444]">
                        <li>To respond to your inquiry and provide private follow-up.</li>
                        <li>To understand your interests and guide you toward the most relevant next step.</li>
                        <li>To send welcome messages, helpful follow-ups, reminders, and scheduling links when appropriate.</li>
                        <li>To manage leads, notes, statuses, and conversation history inside the private admin system.</li>
                        <li>To improve website performance, user experience, and communication quality.</li>
                    </ul>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-[#111111]">3. WhatsApp, email, and scheduling communication</h2>
                    <p class="mt-4 text-base leading-8 text-[#444444]">When you submit your details or message us, we may contact you through WhatsApp, email, or a scheduling link. Messages are intended to be respectful, relevant, and related to your inquiry. You can ask us to stop contacting you at any time.</p>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-[#111111]">4. How we store and protect data</h2>
                    <p class="mt-4 text-base leading-8 text-[#444444]">Lead information is stored in a private admin database and may be backed up for continuity. Reasonable technical and organizational measures are used to protect information from unauthorized access, loss, misuse, or disclosure.</p>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-[#111111]">5. Sharing of information</h2>
                    <p class="mt-4 text-base leading-8 text-[#444444]">We do not sell your personal information. Information may be shared only with trusted team members or service providers who help operate the website, communication tools, scheduling process, hosting, email, analytics, or lead management system. These parties should use the information only for the intended business purpose.</p>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-[#111111]">6. Cookies and analytics</h2>
                    <p class="mt-4 text-base leading-8 text-[#444444]">The website may use basic cookies, logs, or analytics tools to understand page performance, user experience, and form activity. You can manage cookies through your browser settings.</p>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-[#111111]">7. Data retention</h2>
                    <p class="mt-4 text-base leading-8 text-[#444444]">We keep inquiry and communication records only as long as reasonably needed for follow-up, relationship management, legal, security, and operational purposes. You may request deletion of your information where applicable.</p>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-[#111111]">8. Your choices</h2>
                    <ul class="mt-4 list-disc space-y-3 pl-6 text-base leading-8 text-[#444444]">
                        <li>You can request access to the information we hold about you.</li>
                        <li>You can ask us to correct inaccurate information.</li>
                        <li>You can ask us to delete your information where legally and operationally possible.</li>
                        <li>You can opt out of follow-up messages at any time.</li>
                    </ul>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-[#111111]">9. No guarantees or professional advice</h2>
                    <p class="mt-4 text-base leading-8 text-[#444444]">Information shared on this website or through follow-up communication is for general educational and private inquiry purposes only. It does not guarantee any health, income, financial, business, or personal result and should not be treated as professional medical, legal, financial, or tax advice.</p>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-[#111111]">10. Contact</h2>
                    <p class="mt-4 text-base leading-8 text-[#444444]">For privacy requests or questions, contact us through WhatsApp at <a class="font-semibold text-[#1E9447] underline" href="https://wa.me/971555574958">+971 55 557 4958</a>.</p>
                </section>
            </div>
        </article>
    </main>
</body>
</html>