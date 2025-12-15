<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title inertia>{{ config('app.name', 'IFTUT - Дистанционное обучение') }}</title>

        <!-- Favicon -->
        <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🎓</text></svg>">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        
        <!-- Material Design Icons -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font@7.4.47/css/materialdesignicons.min.css">

        <!-- Web App Manifest -->
        <link rel="manifest" href="/manifest.json">

        <!-- Глобальные переводы для Vue i18n -->
        @php
            $locale = app()->getLocale();
            $translationsPath = lang_path("{$locale}.json");
            $translations = file_exists($translationsPath) 
                ? json_decode(file_get_contents($translationsPath), true) 
                : [];
        @endphp
        <script>
            window.__LOCALE__ = '{{ $locale }}';
            window.__TRANSLATIONS__ = @json($translations);
        </script>

        <!-- Scripts -->
        @routes
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
