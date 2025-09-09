<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        {{-- chat/index.blade.php から渡される独自の<head>要素を読み込む --}}
        @if (isset($header_scripts))
            {{ $header_scripts }}
        @endif
    </head>
    <body class="font-sans antialiased">
        {{-- ▼▼▼ シンプルなmainタグに変更 ▼▼▼ --}}
        <main>
            {{ $slot }}
        </main>

        {{-- chat/index.blade.php から渡される独自の<script>要素を読み込む --}}
        @if (isset($footer_scripts))
            {{ $footer_scripts }}
        @endif
    </body>
</html>