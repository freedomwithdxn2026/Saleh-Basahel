<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Login | Saleh Basahel</title>
    <link rel="icon" type="image/png" href="{{ asset('favcon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('favcon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; display: grid; place-items: center; background: #eaf8ef; font-family: Montserrat, Arial, sans-serif; color: #111; }
        .panel { width: min(430px, calc(100vw - 32px)); background: #fff; border: 1px solid #dce8e0; border-radius: 24px; padding: 34px; box-shadow: 0 24px 70px rgba(7,24,15,.12); }
        .mark { width: 58px; height: 58px; border-radius: 999px; display: grid; place-items: center; background: #1E9447; color: #fff; font-weight: 800; margin-bottom: 18px; }
        h1 { margin: 0 0 8px; font-size: 27px; font-weight: 600; line-height: 1.2; }
        p { margin: 0 0 24px; color: #66736b; line-height: 1.7; }
        label { display: grid; gap: 8px; font-weight: 800; }
        input { width: 100%; border: 1px solid #dbe5df; border-radius: 14px; padding: 14px; font: inherit; }
        button { width: 100%; margin-top: 18px; border: 0; border-radius: 999px; padding: 14px; background: #1E9447; color: #fff; font-weight: 800; font: inherit; cursor: pointer; }
        .error { color: #b42318; font-size: 14px; margin-top: 10px; }
        .warning { margin: 0 0 18px; padding: 12px 14px; border-radius: 14px; background: #fff7e6; color: #8a5200; font-size: 13px; font-weight: 700; line-height: 1.5; }
    </style>
</head>
<body>
    <form class="panel" method="post" action="{{ route('admin.login.store') }}">
        @csrf
        <div class="mark">SB</div>
        <h1>Admin Login</h1>
        <p>Manage landing page content, images, profile details, and collected leads.</p>
        @if (blank(config('admin.password')))
            <div class="warning">ADMIN_PASSWORD is not configured in the Laravel .env file yet.</div>
        @endif
        <label>
            Admin password
            <input type="password" name="password" required autofocus autocomplete="current-password">
        </label>
        @error('password')
            <div class="error">{{ $message }}</div>
        @enderror
        <button type="submit">Enter Admin Panel</button>
    </form>
</body>
</html>
