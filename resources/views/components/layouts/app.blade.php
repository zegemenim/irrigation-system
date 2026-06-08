<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#0f766e">
        <title>{{ $title ?? config('app.name', 'Smart Solar Irrigation') }}</title>
        <link rel="manifest" href="/manifest.json">
        <link rel="apple-touch-icon" href="/icons/irrigation-icon-192.png">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="min-h-screen bg-slate-950 font-sans text-slate-100 antialiased">
        {{ $slot }}

        @livewireScripts
    </body>
</html>
