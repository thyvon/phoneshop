<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $header ?? 'Page' }} | {{ config('app.name', 'Laravel') }}</title>
    <meta name="description" content="Page Title">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport"
          content="width=device-width, initial-scale=1, shrink-to-fit=no, user-scalable=no, minimal-ui">

    <!-- Mobile Optimizations -->
    <meta name="apple-mobile-web-app-capable" content="yes"/>
    <meta name="msapplication-tap-highlight" content="no">
    <meta name="mobile-web-app-capable" content="yes">

    <!-- Base CSS -->
    <link id="vendorsbundle" rel="stylesheet" href="{{ asset('template/css/vendors.bundle.css') }}">
    <link id="appbundle" rel="stylesheet" href="{{ asset('template/css/app.bundle.css') }}">
    <link id="myskin" rel="stylesheet" href="{{ asset('template/css/skins/skin-master.css') }}">
    <link id="mytheme" rel="stylesheet" href="#">

    <!-- Page-specific Vite (for Vue) -->
    @stack('vite')

    <!-- Page-specific styles (e.g. datatables) -->
    @stack('styles')

    <!-- Favicon -->
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('template/img/favicon/apple-touch-icon.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('template/img/favicon/favicon-32x32.png') }}">
    <link rel="mask-icon" href="{{ asset('template/img/favicon/safari-pinned-tab.svg') }}" color="#5bbad5">
</head>

<body class="mod-bg-1">
    <script>
        'use strict';
        const classHolder = document.body;
        const themeSettings = JSON.parse(localStorage.getItem('themeSettings') || '{}');
        const themeURL = themeSettings.themeURL || '';
        const themeOptions = themeSettings.themeOptions || '';

        if (themeOptions) {
            classHolder.className = themeOptions;
            console.log("%c✔ Theme settings loaded", "color: #148f32");
        } else {
            console.log("%c✔ Heads up! Theme settings is empty or does not exist, loading default settings...", "color: #ed1c24");
        }

        if (themeURL) {
            let link = document.getElementById('mytheme') || document.createElement('link');
            link.id = 'mytheme';
            link.rel = 'stylesheet';
            link.href = themeURL;
            document.head.appendChild(link);
        }

        const saveSettings = () => {
            themeSettings.themeOptions = Array.from(classHolder.classList)
                .filter(cls => /^(nav|header|footer|mod|display)-/i.test(cls))
                .join(' ');
            if (document.getElementById('mytheme')) {
                themeSettings.themeURL = document.getElementById('mytheme').getAttribute("href");
            }
            localStorage.setItem('themeSettings', JSON.stringify(themeSettings));
        }

        const resetSettings = () => {
            localStorage.removeItem("themeSettings");
        }
    </script>

    <div class="page-wrapper">
        <div class="page-inner">
            @include('layouts.partials.sidebar')

            <div class="page-content-wrapper">
                @include('layouts.partials.navbar')

                <main id="js-page-content" role="main" class="page-content">
                    <div id="app">
                        @yield('content')
                    </div>
                </main>

                <div class="page-content-overlay" data-action="toggle" data-class="mobile-nav-on"></div>

                @include('layouts.partials.footer')
            </div>
        </div>
    </div>

    @include('layouts.partials.quick')
    @include('layouts.partials.setting')

    <!-- Base JS -->
    <script src="{{ asset('template/js/vendors.bundle.js') }}"></script>
    <script src="{{ asset('template/js/app.bundle.js') }}"></script>

    <!-- Page-specific scripts -->
    @stack('scripts')
</body>
</html>
