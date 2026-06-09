<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Private personal-branding page for home-based and remote business inquiries.">
    <title>Saleh Basahel | Private Business Guidance</title>
    <style>
        :root {
            color-scheme: light;
            --ink: #17212b;
            --muted: #5c6875;
            --line: #d9e1e7;
            --soft: #f5f7f3;
            --paper: #ffffff;
            --teal: #0f766e;
            --teal-dark: #0b514d;
            --coral: #c75b3b;
            --gold: #b88728;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            color: var(--ink);
            background: var(--paper);
            line-height: 1.55;
        }

        a {
            color: inherit;
        }

        .nav {
            position: fixed;
            inset: 0 0 auto;
            z-index: 10;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            padding: 18px clamp(18px, 5vw, 64px);
            background: rgba(255, 255, 255, 0.88);
            border-bottom: 1px solid rgba(23, 33, 43, 0.08);
            backdrop-filter: blur(14px);
        }

        .brand {
            font-weight: 800;
            letter-spacing: 0;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 18px;
            font-size: 14px;
            color: var(--muted);
        }

        .nav-links a {
            text-decoration: none;
        }

        .button {
            display: inline-flex;
            min-height: 44px;
            align-items: center;
            justify-content: center;
            border: 0;
            border-radius: 8px;
            padding: 12px 18px;
            background: var(--teal);
            color: #fff;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
        }

        .button.secondary {
            background: #ffffff;
            color: var(--teal-dark);
            border: 1px solid rgba(15, 118, 110, 0.3);
        }

        .hero {
            min-height: 92vh;
            display: grid;
            align-items: end;
            padding: 124px clamp(18px, 5vw, 64px) 56px;
            background:
                linear-gradient(90deg, rgba(12, 24, 30, 0.78), rgba(12, 24, 30, 0.48), rgba(12, 24, 30, 0.18)),
                url("https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&w=2200&q=80") center / cover;
            color: #fff;
        }

        .hero-inner {
            max-width: 760px;
        }

        .eyebrow {
            margin: 0 0 14px;
            color: #f2d9a0;
            font-size: 14px;
            font-weight: 800;
            text-transform: uppercase;
        }

        h1 {
            margin: 0;
            max-width: 720px;
            font-size: clamp(42px, 8vw, 82px);
            line-height: 0.98;
            letter-spacing: 0;
        }

        .hero p {
            max-width: 650px;
            margin: 22px 0 0;
            color: rgba(255, 255, 255, 0.88);
            font-size: 19px;
        }

        .hero-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 30px;
        }

        section {
            padding: 72px clamp(18px, 5vw, 64px);
        }

        .section-soft {
            background: var(--soft);
        }

        .container {
            max-width: 1140px;
            margin: 0 auto;
        }

        .section-heading {
            max-width: 740px;
            margin-bottom: 34px;
        }

        h2 {
            margin: 0;
            font-size: clamp(30px, 5vw, 48px);
            line-height: 1.05;
            letter-spacing: 0;
        }

        .section-heading p {
            color: var(--muted);
            font-size: 18px;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 18px;
        }

        .item {
            min-height: 170px;
            border: 1px solid var(--line);
            border-radius: 8px;
            padding: 24px;
            background: var(--paper);
        }

        .item strong {
            display: block;
            margin-bottom: 8px;
            color: var(--teal-dark);
            font-size: 18px;
        }

        .item p {
            margin: 0;
            color: var(--muted);
        }

        .split {
            display: grid;
            grid-template-columns: minmax(0, 0.95fr) minmax(320px, 0.75fr);
            gap: 42px;
            align-items: start;
        }

        .note {
            border-left: 4px solid var(--gold);
            padding: 16px 0 16px 18px;
            color: var(--muted);
        }

        form {
            border: 1px solid var(--line);
            border-radius: 8px;
            padding: 24px;
            background: #fff;
        }

        label {
            display: block;
            margin: 14px 0 6px;
            font-weight: 700;
            font-size: 14px;
        }

        input,
        select,
        textarea {
            width: 100%;
            min-height: 44px;
            border: 1px solid #cfd8df;
            border-radius: 8px;
            padding: 11px 12px;
            font: inherit;
            color: var(--ink);
            background: #fff;
        }

        textarea {
            min-height: 110px;
            resize: vertical;
        }

        .checkbox {
            display: flex;
            gap: 10px;
            align-items: flex-start;
            margin: 16px 0;
            color: var(--muted);
            font-size: 14px;
        }

        .checkbox input {
            width: 18px;
            min-height: 18px;
            margin-top: 2px;
        }

        .alert {
            border-radius: 8px;
            padding: 12px 14px;
            margin-bottom: 14px;
            background: #e7f7f2;
            color: #145245;
            border: 1px solid #bfe8da;
        }

        .errors {
            border-radius: 8px;
            padding: 12px 14px;
            margin-bottom: 14px;
            background: #fff1f1;
            color: #8a2424;
            border: 1px solid #f1c5c5;
        }

        footer {
            padding: 28px clamp(18px, 5vw, 64px);
            background: #132026;
            color: rgba(255, 255, 255, 0.76);
            font-size: 14px;
        }

        @media (max-width: 800px) {
            .nav {
                position: absolute;
            }

            .nav-links a:not(.button) {
                display: none;
            }

            .hero {
                min-height: 88vh;
                padding-top: 112px;
            }

            .grid,
            .split {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <header class="nav">
        <div class="brand">Saleh Basahel</div>
        <nav class="nav-links" aria-label="Primary">
            <a href="#approach">Approach</a>
            <a href="#contact">Contact</a>
            <a class="button secondary" href="https://wa.me/971555574958">WhatsApp</a>
        </nav>
    </header>

    <main>
        <section class="hero">
            <div class="hero-inner">
                <p class="eyebrow">Private Guidance For Business-Minded People</p>
                <h1>Saleh Basahel</h1>
                <p>Personal guidance for people exploring ethical home-based and remote business paths, with private follow-up from the admin team.</p>
                <div class="hero-actions">
                    <a class="button" href="#contact">Request private follow-up</a>
                    <a class="button secondary" href="https://wa.me/971555574958">Message on WhatsApp</a>
                </div>
            </div>
        </section>

        <section id="approach">
            <div class="container">
                <div class="section-heading">
                    <h2>A simple private intake process.</h2>
                    <p>The page collects basic details, then the team follows up privately with the right context. It does not publish company, product, registration, or income-plan details.</p>
                </div>
                <div class="grid">
                    <div class="item">
                        <strong>Listen first</strong>
                        <p>We start by understanding your goals, location, preferred language, and the kind of guidance you are looking for.</p>
                    </div>
                    <div class="item">
                        <strong>Private guidance</strong>
                        <p>Specific business details are handled directly by the team in a private conversation.</p>
                    </div>
                    <div class="item">
                        <strong>Clear follow-up</strong>
                        <p>Your inquiry is saved securely on the server so the team can respond with the right next step.</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="section-soft" id="contact">
            <div class="container split">
                <div>
                    <div class="section-heading">
                        <h2>Request a private follow-up.</h2>
                        <p>Share a few details and the team will contact you. No public company, product, registration, or income-plan details are discussed on this page.</p>
                    </div>
                    <p class="note">This is an independent personal inquiry page. It does not represent any corporate support desk or official registration system.</p>
                </div>

                <form method="post" action="{{ route('leads.store') }}">
                    @csrf

                    @if (session('status'))
                        <div class="alert">{{ session('status') }}</div>
                    @endif

                    @if ($errors->any())
                        <div class="errors">Please check the highlighted details and try again.</div>
                    @endif

                    <label for="name">Full name</label>
                    <input id="name" name="name" value="{{ old('name') }}" required>

                    <label for="phone">WhatsApp / phone</label>
                    <input id="phone" name="phone" value="{{ old('phone') }}">

                    <label for="email">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}">

                    <label for="country">Country / city</label>
                    <input id="country" name="country" value="{{ old('country') }}">

                    <label for="interest">Main interest</label>
                    <select id="interest" name="interest">
                        <option value="">Select one</option>
                        <option value="home-based-business" @selected(old('interest') === 'home-based-business')>Home-based business</option>
                        <option value="remote-business" @selected(old('interest') === 'remote-business')>Remote business</option>
                        <option value="online-business-education" @selected(old('interest') === 'online-business-education')>Online business education</option>
                        <option value="private-meeting" @selected(old('interest') === 'private-meeting')>Private meeting</option>
                    </select>

                    <label for="message">Message</label>
                    <textarea id="message" name="message">{{ old('message') }}</textarea>

                    <label class="checkbox">
                        <input type="checkbox" name="consent" value="1" required>
                        <span>I agree that my details can be saved so the team can follow up privately.</span>
                    </label>

                    <button class="button" type="submit">Submit inquiry</button>
                </form>
            </div>
        </section>
    </main>

    <footer>
        <div class="container">© {{ date('Y') }} Saleh Basahel. Private personal-branding inquiry page.</div>
    </footer>
</body>
</html>
