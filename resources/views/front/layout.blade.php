<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

@php $brParam = isset($brParam) ? $brParam : []; @endphp

<head>
    <x-front.layouts.head :brParam="$brParam" />
</head>

<body>
    <x-front.layouts.loading-screen />

    <main class="container overflow-hidden">
        {{-- JAVASCRIPT REQUIRED WARNING --}}
        <x-noscript-warning />

        {{-- BUTTONS --}}
        <x-front.btn-github />

        {{-- NAVIGATION --}}
        <x-front.layouts.nav :brParam="$brParam" :gameModel="isset($gameModel) ? $gameModel : null"
            :gameModels="isset($gameModels) ? $gameModels : []" :folderModels="isset($folderModels) ? $folderModels : []"
            :tagModels="isset($tagModels) ? $tagModels : []" :music="isset($gameModel) && $gameModel->music ? $music : null" />

        {{-- MAIN CONTENT --}}
        @yield('content')
    </main>

    {{-- FOOTER --}}
    <x-front.layouts.footer />

    {{-- TOAST MESSAGES CONTAINER --}}
    <x-front.toast-container />

    {{-- OTHER --}}
    <x-front.window-system />
    @vite(['resources/ts/fo/front.ts'])
    @stack('scripts')
</body>

</html>
