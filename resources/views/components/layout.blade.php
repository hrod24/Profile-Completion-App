<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="{{ asset('img/kanmo-logo.jpeg') }}">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="theme-color" content="#F97316">

    <title>{{ $title }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <script>document.documentElement.classList.add('js');</script>

    @vite([
        'resources/css/app.css',
        'resources/css/sidebar-shell.css',
        'resources/js/app.js',
        'resources/js/sidebar-navigation.js',
    ])

    @stack('styles')
</head>
<body>
    {{ $slot }}
    @stack('scripts')
</body>
</html>
