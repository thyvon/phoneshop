<!DOCTYPE html>
<html lang="en">
    @include('layouts.partials.head')
    <body class="mod-bg-1">
        <script>
            'use strict';
            var classHolder = document.getElementsByTagName("BODY")[0],
                themeSettings = (localStorage.getItem('themeSettings')) ? JSON.parse(localStorage.getItem('themeSettings')) : {},
                themeURL = themeSettings.themeURL || '',
                themeOptions = themeSettings.themeOptions || '';
            if (themeSettings.themeOptions) {
                classHolder.className = themeSettings.themeOptions;
                console.log("%c✔ Theme settings loaded", "color: #148f32");
            } else {
                console.log("%c✔ Heads up! Theme settings is empty or does not exist, loading default settings...", "color: #ed1c24");
            }
            if (themeSettings.themeURL && !document.getElementById('mytheme')) {
                var cssfile = document.createElement('link');
                cssfile.id = 'mytheme';
                cssfile.rel = 'stylesheet';
                cssfile.href = themeURL;
                document.getElementsByTagName('head')[0].appendChild(cssfile);
            } else if (themeSettings.themeURL && document.getElementById('mytheme')) {
                document.getElementById('mytheme').href = themeSettings.themeURL;
            }
            var saveSettings = function() {
                themeSettings.themeOptions = String(classHolder.className).split(/[^\w-]+/).filter(function(item) {
                    return /^(nav|header|footer|mod|display)-/i.test(item);
                }).join(' ');
                if (document.getElementById('mytheme')) {
                    themeSettings.themeURL = document.getElementById('mytheme').getAttribute("href");
                }
                localStorage.setItem('themeSettings', JSON.stringify(themeSettings));
            }
            var resetSettings = function() {
                localStorage.setItem("themeSettings", "");
            }
        </script>
        <div class="page-wrapper">
            <div class="page-inner">
                @include('layouts.partials.sidebar')
                <div class="page-content-wrapper">
                    @include('layouts.partials.navbar')
                    <main id="js-page-content" role="main" class="page-content">
                        <ol class="breadcrumb page-breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0);">SmartAdmin</a></li>
                            <li class="breadcrumb-item">Category</li>
                            <li class="breadcrumb-item">Sub-category</li>
                            <li class="breadcrumb-item active">Page Title</li>
                            <li class="position-absolute pos-top pos-right d-none d-sm-block"><span class="js-get-date"></span></li>
                        </ol>
                        @yield('content')
                    </main>
                    <div class="page-content-overlay" data-action="toggle" data-class="mobile-nav-on"></div>
                    @include('layouts.partials.footer')
                </div>
            </div>
        </div>
        @include('layouts.partials.quick')
        @include('layouts.partials.setting')
        @include('layouts.partials.script')
    </body>
</html>
