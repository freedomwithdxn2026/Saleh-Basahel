@props(['dark' => false])

<a href="{{ url('/' . app()->getLocale()) }}" @click.prevent="scrollToSection('hero')" class="group inline-flex items-center gap-3" aria-label="Saleh Basahel home">
    <span class="relative flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-[#1E9447] to-[#16763A] shadow-md shadow-[#1E9447]/15 sm:h-12 sm:w-12">
        <svg viewBox="0 0 48 48" class="h-7 w-7 sm:h-8 sm:w-8" fill="none" aria-hidden="true">
            <path d="M23.7 36.5c0-10.1 5.9-18.2 16.1-22.7.9-.4 1.9.3 1.8 1.3C40.5 27.6 33 35.2 23.7 36.5Z" fill="#FFFFFF"/>
            <path d="M22.7 36.4C18 26.8 10.3 22.1 5.8 20.2c-.9-.4-1-1.7-.1-2.2 9.7-5.2 20.3-.4 25.6 9.1" stroke="#DDF6E6" stroke-width="3" stroke-linecap="round"/>
            <path d="M24 38V10" stroke="#FFFFFF" stroke-width="3" stroke-linecap="round"/>
            <path d="M24 10l6 6M24 10l-6 6" stroke="#FFFFFF" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
    </span>
    <span class="leading-none">
        <span class="block whitespace-nowrap text-[18px] font-black tracking-normal sm:text-[23px] {{ $dark ? 'text-white' : 'text-ink' }}">Saleh</span>
        <span class="block whitespace-nowrap text-[12px] font-extrabold tracking-[0.18em] sm:text-[14px] {{ $dark ? 'text-white/80' : 'text-[#16763A]' }}">Basahel</span>
    </span>
</a>
