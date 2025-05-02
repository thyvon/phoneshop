<head>
    <meta charset="utf-8">
    <title>
        Page Title - Category - SmartAdmin v4.5.1
    </title>
    <meta name="description" content="Page Title">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no, user-scalable=no, minimal-ui">
    <!-- Call App Mode on ios devices -->
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <!-- Remove Tap Highlight on Windows Phone IE -->
    <meta name="msapplication-tap-highlight" content="no">
    <meta name="mobile-web-app-capable" content="yes">
    <!-- base css -->
    <link id="vendorsbundle" rel="stylesheet" media="screen, print" href="{{ asset ('template/css/vendors.bundle.css') }}">
    <link id="appbundle" rel="stylesheet" media="screen, print" href="{{ asset ('template/css/app.bundle.css') }}">
    <link id="mytheme" rel="stylesheet" media="screen, print" href="#">
    <link id="myskin" rel="stylesheet" media="screen, print" href="{{ asset ('template/css/skins/skin-master.css') }}">
    <!-- Place favicon.ico in the root directory -->
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('template/img/favicon/apple-touch-icon.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('template/img/favicon/favicon-32x32.png') }}">
    <link rel="mask-icon" href="{{ asset('template/img/favicon/safari-pinned-tab.svg') }}" color="#5bbad5">
    <!-- You can add your own stylesheet here to override any styles that comes before it
    <link rel="stylesheet" media="screen, print" href="{{ asset ('template/css/your_styles.css') }}">-->

    @vite(['resources/css/app.css', 'resources/js/app.js']) {{-- Assuming you use Vite --}}
</head>