@php
    $active = $active ?? '';
    $navItems = [
        ['key' => 'dashboard', 'label' => 'Dashboard', 'route' => route('admin.dashboard'), 'icon' => 'dashboard'],
        ['key' => 'leads', 'label' => 'Leads', 'route' => route('admin.leads.index'), 'icon' => 'leads'],
        ['key' => 'content', 'label' => 'Content Management', 'route' => route('admin.content.edit'), 'icon' => 'content'],
        ['key' => 'profile', 'label' => 'Profile Section', 'route' => route('admin.profile.edit'), 'icon' => 'profile'],
    ];
@endphp
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Admin Panel' }} | Saleh Basahel</title>
    <link rel="icon" type="image/png" href="{{ asset('favcon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('favcon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: Montserrat, Arial, sans-serif; background: #f4f8f5; color: #111; }
        a { color: inherit; text-decoration: none; }
        .shell { min-height: 100vh; display: grid; grid-template-columns: 84px minmax(0, 1fr); }
        .sidebar { width: 84px; background: #07180f; color: #fff; padding: 20px 12px; position: sticky; top: 0; z-index: 20; height: 100vh; display: flex; flex-direction: column; overflow-x: hidden; overflow-y: auto; transition: width .28s ease, box-shadow .28s ease; }
        .sidebar:hover, .sidebar:focus-within { width: 280px; box-shadow: 18px 0 40px rgba(7,24,15,.2); }
        .sidebar-toggle, .sidebar-backdrop { display: none; }
        .sidebar-top { display: contents; }
        .brand { min-height: 52px; display: flex; align-items: center; gap: 12px; margin-bottom: 30px; padding: 0 5px; }
        .mark { width: 50px; min-width: 50px; height: 50px; border-radius: 999px; display: grid; place-items: center; background: linear-gradient(135deg, #1E9447, #16763A); box-shadow: 0 14px 28px rgba(30,148,71,.25); }
        .mark svg { width: 31px; height: 31px; }
        .brand-copy, .nav-label, .logout-label { max-width: 0; opacity: 0; overflow: hidden; white-space: nowrap; transform: translateX(-8px); transition: max-width .28s ease, opacity .2s ease, transform .28s ease; }
        .sidebar:hover .brand-copy, .sidebar:focus-within .brand-copy,
        .sidebar:hover .nav-label, .sidebar:focus-within .nav-label,
        .sidebar:hover .logout-label, .sidebar:focus-within .logout-label { max-width: 190px; opacity: 1; transform: translateX(0); }
        .brand strong { display: block; font-size: 20px; line-height: 1; }
        .brand span { color: #58c47a; font-size: 12px; font-weight: 700; letter-spacing: 3px; }
        .nav { display: grid; gap: 8px; }
        .nav a { min-height: 52px; display: flex; align-items: center; gap: 14px; border-radius: 14px; padding: 14px 17px; color: rgba(255,255,255,.72); font-weight: 700; transition: background .2s ease, color .2s ease; }
        .nav-icon, .logout-icon { width: 22px; min-width: 22px; height: 22px; }
        .nav a:hover, .nav a.active { background: rgba(30,148,71,.18); color: #fff; }
        .logout { margin-top: auto; padding-top: 30px; }
        .logout button { width: 100%; min-height: 50px; display: flex; align-items: center; gap: 14px; border: 1px solid rgba(255,255,255,.15); background: rgba(255,255,255,.06); color: #fff; border-radius: 14px; padding: 12px 17px; font-weight: 700; cursor: pointer; transition: background .2s ease, border-color .2s ease; }
        .logout button:hover, .logout button:focus-visible { background: rgba(30,148,71,.18); border-color: rgba(88,196,122,.35); }
        .main { min-width: 0; padding: 34px; }
        .topbar { display: flex; justify-content: space-between; align-items: center; gap: 18px; margin-bottom: 28px; }
        .topbar h1 { margin: 0; font-size: clamp(24px, 2.4vw, 34px); font-weight: 600; line-height: 1.2; }
        .main h2 { font-size: clamp(20px, 1.8vw, 28px); font-weight: 600; line-height: 1.25; }
        .visit { background: #1E9447; color: #fff; padding: 13px 18px; border-radius: 999px; font-weight: 800; box-shadow: 0 12px 24px rgba(30,148,71,.18); }
        .card { background: #fff; border: 1px solid #e3ebe5; border-radius: 22px; box-shadow: 0 18px 44px rgba(7,24,15,.07); }
        .pad { padding: 24px; }
        .grid { display: grid; gap: 18px; }
        .stats { grid-template-columns: repeat(4, minmax(0, 1fr)); margin-bottom: 22px; }
        .stat strong { display: block; font-size: 34px; }
        .stat span { color: #66736b; font-weight: 600; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 14px 12px; border-bottom: 1px solid #edf1ee; text-align: left; vertical-align: top; }
        th { color: #66736b; font-size: 12px; text-transform: uppercase; letter-spacing: .08em; }
        .field { display: grid; gap: 8px; margin-bottom: 18px; }
        label { font-weight: 800; }
        input, textarea, select { width: 100%; border: 1px solid #dbe5df; border-radius: 14px; padding: 13px 14px; font: inherit; background: #fff; }
        textarea { min-height: 110px; resize: vertical; }
        .btn { border: 0; background: #1E9447; color: #fff; padding: 13px 18px; border-radius: 999px; font-weight: 800; cursor: pointer; }
        .muted { color: #66736b; }
        .notice { padding: 13px 16px; border-radius: 14px; background: #eaf8ef; color: #16763A; font-weight: 700; margin-bottom: 18px; }
        .tabs { display: flex; gap: 8px; margin-bottom: 18px; }
        .tabs a { padding: 10px 14px; border-radius: 999px; border: 1px solid #dbe5df; font-weight: 800; }
        .tabs a.active { background: #1E9447; color: #fff; border-color: #1E9447; }
        .image-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .preview { width: 100%; aspect-ratio: 16 / 10; object-fit: cover; border-radius: 16px; border: 1px solid #e2e8e4; background: #f7faf8; }
        .searchbar { display: flex; gap: 12px; margin-bottom: 18px; }
        .searchbar input { max-width: 420px; }
        .block { display: block; }
        .small { font-size: 12px; margin-top: 5px; }
        .eyebrow { margin: 0 0 7px; color: #16763A; font-size: 12px; font-weight: 800; letter-spacing: .14em; text-transform: uppercase; }
        .lead-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 20px; margin-bottom: 22px; }
        .lead-header h2 { margin: 0 0 8px; font-size: clamp(21px, 1.8vw, 28px); font-weight: 600; line-height: 1.25; }
        .lead-header p:last-child { margin: 0; max-width: 760px; line-height: 1.7; }
        .live-pill { display: inline-flex; align-items: center; gap: 8px; border: 1px solid #cfeadd; background: #eaf8ef; color: #16763A; border-radius: 999px; padding: 9px 13px; font-size: 12px; font-weight: 800; white-space: nowrap; }
        .live-pill span { width: 8px; height: 8px; border-radius: 50%; background: #1E9447; box-shadow: 0 0 0 5px rgba(30,148,71,.12); }
        .filters { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 18px; align-items: center; }
        .filters input { flex: 1 1 280px; }
        .filters select { flex: 1 1 145px; }
        .btn-light { background: #eef5f0; color: #111; }
        .btn-danger { background: #b42318; color: #fff; }
        .btn-small { padding: 8px 12px; font-size: 12px; white-space: nowrap; }
        .action-buttons { display: flex; align-items: center; gap: 8px; min-width: max-content; }
        .action-buttons form { margin: 0; }
        .text-link { color: #16763A; font-weight: 800; }
        .pipeline-tabs { display: flex; gap: 8px; overflow-x: auto; padding-bottom: 10px; margin-bottom: 16px; }
        .pipeline-tabs a { display: inline-flex; align-items: center; gap: 8px; border: 1px solid #dbe5df; border-radius: 999px; padding: 9px 12px; font-size: 12px; font-weight: 800; white-space: nowrap; }
        .pipeline-tabs a.active { background: #1E9447; border-color: #1E9447; color: #fff; }
        .pipeline-tabs strong { display: grid; place-items: center; min-width: 22px; height: 22px; border-radius: 50%; background: rgba(255,255,255,.22); }
        .lead-table th a { color: inherit; }
        .lead-manager { min-width: 0; max-width: 100%; overflow: hidden; }
        .table-wrap { width: 100%; max-width: 100%; overflow-x: auto; overscroll-behavior-inline: contain; scrollbar-gutter: stable; }
        .lead-table { min-width: 1380px; table-layout: auto; }
        .lead-table td { max-width: 280px; overflow-wrap: anywhere; word-break: break-word; font-size: 14px; line-height: 1.55; }
        .lead-table th:last-child, .lead-table td:last-child { position: sticky; right: 0; z-index: 1; background: #fff; box-shadow: -12px 0 20px rgba(7,24,15,.06); }
        .lead-table th:last-child { z-index: 2; }
        .source-badge, .status-badge { display: inline-flex; align-items: center; border-radius: 999px; padding: 7px 10px; font-size: 12px; font-weight: 800; white-space: nowrap; }
        .source-badge { background: #edf7f1; color: #16763A; }
        .source-whatsapp { background: #e8fff0; color: #0b7a35; }
        .source-landing-page, .source-website { background: #fff7dd; color: #755300; }
        .status-badge { background: #f1f5f3; color: #34423a; }
        .temperature-badge { display: inline-flex; border-radius: 999px; padding: 7px 10px; font-size: 12px; font-weight: 800; }
        .temperature-hot { background: #ffe8e8; color: #a41f1f; }
        .temperature-warm { background: #fff4d6; color: #745100; }
        .temperature-cold { background: #eaf3ff; color: #245b91; }
        .conversation-cell { min-width: 260px; max-width: 360px; }
        .conversation-cell details { border: 1px solid #e1e9e4; border-radius: 14px; background: #fbfdfb; padding: 10px 12px; }
        .conversation-cell summary { cursor: pointer; color: #16763A; font-weight: 800; }
        .conversation-cell pre { margin: 10px 0 0; white-space: pre-wrap; word-break: break-word; font: 12px/1.6 ui-monospace, SFMono-Regular, Consolas, monospace; color: #34423a; }
        .lead-table { min-width: 2100px; table-layout: auto; }
        .lead-table th,
        .lead-table td { white-space: nowrap; vertical-align: middle; }
        .lead-table td { max-width: none; overflow-wrap: normal; word-break: normal; font-size: 13px; line-height: 1.45; font-weight: 400; }
        .lead-table td strong { font-weight: 500; }
        .lead-table .block { display: inline; }
        .lead-table .block::before { content: " | "; color: #a8b4ad; }
        .lead-table .small { margin-top: 0; font-size: 12px; }
        .lead-table .source-badge,
        .lead-table .status-badge,
        .lead-table .temperature-badge { font-weight: 700; }
        .lead-table .conversation-cell { min-width: 260px; max-width: none; }
        .lead-table .conversation-cell details { display: inline-block; max-width: 100%; white-space: normal; }
        .lead-table .conversation-cell summary { white-space: nowrap; }
        .lead-table .conversation-cell pre { white-space: pre-wrap; word-break: normal; overflow-wrap: anywhere; }
        .pagination-wrap { margin-top: 18px; }
        .lead-detail-head { display: flex; align-items: center; justify-content: space-between; gap: 16px; margin-bottom: 18px; }
        .detail-grid { display: grid; grid-template-columns: minmax(0, 1.45fr) minmax(300px, .7fr); gap: 20px; align-items: start; }
        .form-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 0 16px; }
        .check-field { display: flex; align-items: center; gap: 10px; margin: 8px 0 20px; font-size: 14px; }
        .check-field input { width: auto; }
        .detail-side { gap: 20px; }
        .detail-list { display: grid; gap: 12px; margin: 0; }
        .detail-list div { display: flex; justify-content: space-between; gap: 12px; border-bottom: 1px solid #edf1ee; padding-bottom: 10px; }
        .detail-list dt { color: #66736b; }
        .detail-list dd { margin: 0; font-weight: 700; text-align: right; }
        .detail-grid .card > h2 { font-weight: 500; letter-spacing: 0; }
        .detail-grid label { font-weight: 600; }
        .detail-grid input,
        .detail-grid textarea,
        .detail-grid select { font-weight: 400; color: #1b211d; }
        .detail-grid .check-field { font-weight: 500; }
        .detail-list dt { font-weight: 400; }
        .detail-list dd { font-weight: 500; }
        .detail-side .status-badge,
        .detail-side .temperature-badge { font-weight: 600; }
        .conversation-full { margin: 0; white-space: pre-wrap; word-break: break-word; font: 12px/1.7 ui-monospace, SFMono-Regular, Consolas, monospace; }
        .communication-panel { margin-top: 22px; overflow: hidden; }
        .communication-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 20px; padding-bottom: 20px; border-bottom: 1px solid #edf1ee; }
        .communication-head h2 { margin: 0 0 8px; }
        .communication-head p:last-child { margin: 0; max-width: 780px; line-height: 1.65; }
        .communication-timeline { position: relative; display: grid; gap: 18px; padding: 26px 0 4px 28px; }
        .communication-timeline::before { content: ""; position: absolute; top: 0; bottom: 0; left: 8px; width: 2px; background: #dce9e0; }
        .communication-item { position: relative; max-width: min(820px, 88%); }
        .communication-item.direction-outbound { margin-left: auto; }
        .communication-marker { position: absolute; top: 22px; left: -26px; z-index: 1; width: 14px; height: 14px; border: 3px solid #fff; border-radius: 50%; background: #8fa098; box-shadow: 0 0 0 2px #dce9e0; }
        .direction-outbound .communication-marker { background: #1E9447; }
        .channel-email .communication-marker { background: #2468a2; }
        .status-failed .communication-marker { background: #b42318; }
        .communication-bubble { border: 1px solid #dfe9e2; border-radius: 18px; background: #fbfdfb; padding: 17px 18px; box-shadow: 0 12px 28px rgba(7,24,15,.05); }
        .direction-outbound .communication-bubble { background: #eef9f1; border-color: #cfe8d6; }
        .channel-email .communication-bubble { background: #f4f8fc; border-color: #d7e4ef; }
        .status-failed .communication-bubble { background: #fff8f7; border-color: #f0d4d0; }
        .communication-meta { display: flex; align-items: flex-start; justify-content: space-between; gap: 14px; margin-bottom: 12px; }
        .communication-tags { display: flex; flex-wrap: wrap; gap: 6px; }
        .communication-tags span { display: inline-flex; border-radius: 999px; background: #fff; border: 1px solid #dfe7e2; padding: 5px 8px; color: #536159; font-size: 10px; font-weight: 800; letter-spacing: .05em; text-transform: uppercase; }
        .communication-tags .communication-channel { color: #16763A; border-color: #cfe8d6; }
        .communication-tags .delivery-status { color: #16763A; }
        .status-failed .communication-tags .delivery-status { color: #b42318; border-color: #f0d4d0; }
        .communication-meta time { color: #66736b; font-size: 11px; white-space: nowrap; }
        .communication-subject { display: block; margin-bottom: 8px; }
        .communication-body { white-space: pre-wrap; overflow-wrap: anywhere; line-height: 1.7; }
        .communication-foot { display: flex; flex-wrap: wrap; gap: 8px 16px; margin-top: 14px; color: #66736b; font-size: 11px; }
        .communication-error { margin-top: 12px; border-radius: 12px; background: #ffebe8; color: #8f1d16; padding: 10px 12px; font-size: 12px; font-weight: 700; overflow-wrap: anywhere; }
        .communication-empty { display: grid; place-items: center; gap: 6px; min-height: 180px; color: #66736b; text-align: center; }
        .communication-empty strong { color: #111; }
        @media (max-width: 900px) {
            .shell { display: grid; grid-template-columns: 72px minmax(0, 1fr); min-height: 100vh; padding-left: 0; }
            .sidebar {
                position: sticky;
                top: 0;
                align-self: start;
                width: 72px;
                height: 100dvh;
                padding: 14px 9px;
                z-index: 50;
                overflow-x: hidden;
                overflow-y: auto;
                box-shadow: 10px 0 28px rgba(7,24,15,.12);
            }
            .sidebar:hover, .sidebar:focus-within, .sidebar.is-open {
                width: min(286px, calc(100vw - 22px));
                box-shadow: 22px 0 48px rgba(7,24,15,.28);
            }
            .sidebar-top {
                display: grid;
                gap: 12px;
                margin-bottom: 22px;
            }
            .brand { min-width: 0; padding: 0; margin-bottom: 0; }
            .mark { width: 50px; min-width: 50px; height: 50px; }
            .sidebar-toggle {
                display: grid;
                place-items: center;
                width: 42px;
                min-width: 42px;
                height: 42px;
                border: 1px solid rgba(255,255,255,.14);
                border-radius: 14px;
                background: rgba(255,255,255,.07);
                color: #fff;
                cursor: pointer;
                opacity: 1;
                pointer-events: auto;
                transform: translateX(4px);
                transition: opacity .22s ease, transform .28s ease, background .2s ease;
            }
            .sidebar-toggle svg { width: 21px; height: 21px; transition: transform .28s ease; }
            .sidebar:hover .sidebar-toggle,
            .sidebar:focus-within .sidebar-toggle,
            .sidebar.is-open .sidebar-toggle {
                opacity: 1;
                pointer-events: auto;
                transform: translateX(0);
            }
            .sidebar.is-open .sidebar-toggle svg { transform: rotate(90deg); }
            .sidebar-backdrop {
                position: fixed;
                inset: 0;
                z-index: 40;
                display: block;
                background: rgba(7,24,15,.28);
                opacity: 0;
                pointer-events: none;
                transition: opacity .28s ease;
            }
            .sidebar-backdrop.is-visible {
                opacity: 1;
                pointer-events: auto;
            }
            .sidebar-backdrop,
            .sidebar-backdrop.is-visible { display: none !important; opacity: 0; pointer-events: none; }
            .brand-copy, .nav-label, .logout-label,
            .sidebar:hover .brand-copy, .sidebar:focus-within .brand-copy,
            .sidebar:hover .nav-label, .sidebar:focus-within .nav-label,
            .sidebar:hover .logout-label, .sidebar:focus-within .logout-label { max-width: 0; opacity: 0; transform: translateX(-8px); }
            .sidebar:hover .brand-copy, .sidebar:focus-within .brand-copy, .sidebar.is-open .brand-copy,
            .sidebar:hover .nav-label, .sidebar:focus-within .nav-label, .sidebar.is-open .nav-label,
            .sidebar:hover .logout-label, .sidebar:focus-within .logout-label, .sidebar.is-open .logout-label { max-width: 190px; opacity: 1; transform: translateX(0); }
            .nav { grid-template-columns: 1fr; }
            .nav a { padding: 13px 16px; }
            .logout { padding-top: 22px; }
            .main { width: auto; max-width: 100%; min-width: 0; padding: 22px 16px; overflow-x: hidden; }
            .stats, .image-grid { grid-template-columns: 1fr; }
            .topbar { align-items: flex-start; flex-direction: column; }
            .lead-header { flex-direction: column; }
            .detail-grid, .form-grid { grid-template-columns: 1fr; }
            .communication-head { flex-direction: column; }
            .communication-item { max-width: 100%; }
            .communication-item.direction-outbound { margin-left: 0; }
            table { min-width: 760px; }
        }
        @media (max-width: 560px) {
            .shell { grid-template-columns: 68px minmax(0, 1fr); padding-left: 0; }
            .sidebar { width: 68px; padding-inline: 8px; }
            .main { padding: 18px 12px; }
        }
    </style>
</head>
<body>
    <div class="shell">
        <button class="sidebar-backdrop" type="button" aria-label="Close admin menu"></button>
        <aside class="sidebar" aria-label="Admin sidebar">
            <div class="sidebar-top">
                <a class="brand" href="{{ route('admin.dashboard') }}">
                    <div class="mark" aria-hidden="true">
                        <svg viewBox="0 0 48 48" fill="none">
                            <path d="M23.7 36.5c0-10.1 5.9-18.2 16.1-22.7.9-.4 1.9.3 1.8 1.3C40.5 27.6 33 35.2 23.7 36.5Z" fill="#FFFFFF"/>
                            <path d="M22.7 36.4C18 26.8 10.3 22.1 5.8 20.2c-.9-.4-1-1.7-.1-2.2 9.7-5.2 20.3-.4 25.6 9.1" stroke="#DDF6E6" stroke-width="3" stroke-linecap="round"/>
                            <path d="M24 38V10" stroke="#FFFFFF" stroke-width="3" stroke-linecap="round"/>
                            <path d="M24 10l6 6M24 10l-6 6" stroke="#FFFFFF" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <div class="brand-copy">
                        <strong>Saleh</strong>
                        <span>Basahel</span>
                    </div>
                </a>
                <button class="sidebar-toggle" type="button" aria-label="Toggle admin menu" aria-expanded="false">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 6h16"/>
                        <path d="M4 12h16"/>
                        <path d="M4 18h16"/>
                    </svg>
                </button>
            </div>
            <nav class="nav">
                @foreach ($navItems as $item)
                    <a class="{{ $active === $item['key'] ? 'active' : '' }}" href="{{ $item['route'] }}" aria-label="{{ $item['label'] }}" title="{{ $item['label'] }}">
                        @switch($item['icon'])
                            @case('dashboard')
                                <svg class="nav-icon" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect width="7" height="9" x="3" y="3" rx="1"/>
                                    <rect width="7" height="5" x="14" y="3" rx="1"/>
                                    <rect width="7" height="9" x="14" y="12" rx="1"/>
                                    <rect width="7" height="5" x="3" y="16" rx="1"/>
                                </svg>
                                @break
                            @case('leads')
                                <svg class="nav-icon" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                                    <circle cx="9" cy="7" r="4"/>
                                    <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                                </svg>
                                @break
                            @case('content')
                                <svg class="nav-icon" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/>
                                    <polyline points="14 2 14 8 20 8"/>
                                    <line x1="8" x2="16" y1="13" y2="13"/>
                                    <line x1="8" x2="16" y1="17" y2="17"/>
                                </svg>
                                @break
                            @default
                                <svg class="nav-icon" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="8" r="4"/>
                                    <path d="M4 22a8 8 0 0 1 16 0"/>
                                </svg>
                        @endswitch
                        <span class="nav-label">{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </nav>
            <form class="logout" method="post" action="{{ route('admin.logout') }}">
                @csrf
                <button type="submit" aria-label="Logout" title="Logout">
                    <svg class="logout-icon" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M10 17l5-5-5-5"/>
                        <path d="M15 12H3"/>
                        <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
                    </svg>
                    <span class="logout-label">Logout</span>
                </button>
            </form>
        </aside>
        <main class="main">
            <div class="topbar">
                <div>
                    <p class="muted" style="margin: 0 0 8px;">Admin Panel</p>
                    <h1>{{ $title ?? 'Dashboard' }}</h1>
                </div>
                <a class="visit" href="{{ url('/en') }}" target="_blank">View Website</a>
            </div>

            @if (session('status'))
                <div class="notice">{{ session('status') }}</div>
            @endif

            {{ $slot }}
        </main>
    </div>
    <script>
        (() => {
            const sidebar = document.querySelector('.sidebar');
            const toggle = document.querySelector('.sidebar-toggle');
            const backdrop = document.querySelector('.sidebar-backdrop');

            if (!sidebar || !toggle || !backdrop) {
                return;
            }

            const setOpen = (open) => {
                sidebar.classList.toggle('is-open', open);
                backdrop.classList.toggle('is-visible', open);
                toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            };

            toggle.addEventListener('click', () => setOpen(!sidebar.classList.contains('is-open')));
            backdrop.addEventListener('click', () => setOpen(false));

            sidebar.querySelectorAll('.nav a').forEach((link) => {
                link.addEventListener('click', () => setOpen(false));
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') {
                    setOpen(false);
                }
            });
        })();
    </script>
</body>
</html>
