<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title inertia>
        {{ config('app.name', 'Go Pharmacy') }}
    </title>
    <link rel="icon" type="image/x-icon" href="{{ asset('images/branding/favicon.ico') }}">

    @routes

    @vite([
        'resources/css/app.css',
        'resources/js/app.js',
    ])

    @inertiaHead
</head>

<body class="font-sans antialiased">
    @inertia
</body>
</html>