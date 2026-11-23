<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" @class(['dark' => ($appearance ?? 'system') === 'dark'])>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
        @yield('head')
        @stack('head')
    </head>
    <body class="font-sans antialiased">
        @yield('content')
        @stack('scripts')
    </body>
</html>
