<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        
        <script>
            // КРИТИЧЕСКИ ВАЖНО: Устанавливаем cookie с языком из localStorage СРАЗУ
            // Это должно быть выполнено ДО того, как Laravel обработает запрос
            // Но так как это выполняется на клиенте, нужно использовать другой подход
            
            // Устанавливаем cookie при первой загрузке, если она есть в localStorage
            (function() {
                try {
                    const storedLocale = localStorage.getItem('locale');
                    if (storedLocale && (storedLocale === 'ru' || storedLocale === 'tg')) {
                        // Устанавливаем cookie с максимальным приоритетом
                        document.cookie = 'locale=' + storedLocale + '; path=/; max-age=31536000; SameSite=Lax';
                        console.log('🌍 Locale cookie set from localStorage:', storedLocale);
                    } else {
                        // Если нет в localStorage, устанавливаем default
                        const defaultLocale = 'ru';
                        document.cookie = 'locale=' + defaultLocale + '; path=/; max-age=31536000; SameSite=Lax';
                        localStorage.setItem('locale', defaultLocale);
                        console.log('🌍 Default locale cookie set:', defaultLocale);
                    }
                } catch (e) {
                    console.error('❌ Failed to set locale cookie:', e);
                }
            })();
        </script>

        <title inertia>{{ config('app.name', 'IFTUT - Дистанционное обучение') }}</title>

        <!-- Favicon -->
        <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🎓</text></svg>">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        
        <!-- Material Design Icons -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font@7.4.47/css/materialdesignicons.min.css">

        <!-- Scripts -->
        @routes
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
