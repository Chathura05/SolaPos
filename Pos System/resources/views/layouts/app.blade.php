<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="flex h-screen bg-gray-100 dark:bg-gray-900 overflow-hidden" x-data="{ sidebarOpen: false }">
            <!-- Sidebar Area -->
            @include('layouts.sidebar')

            <!-- Main Content Area -->
            <div class="flex-1 flex flex-col overflow-hidden w-full relative z-0">
                <!-- Topbar -->
                @include('layouts.topbar')

                <!-- Page Content Scrollable Area -->
                <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 dark:bg-gray-900 relative">
                    <!-- Page Heading (Optional) -->
                    @isset($header)
                        <header class="bg-white/50 dark:bg-gray-800/50 backdrop-blur-md shadow-sm sticky top-0 z-10 border-b border-gray-100 dark:border-gray-700">
                            <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
                                {{ $header }}
                            </div>
                        </header>
                    @endisset

                    <!-- Slot Content -->
                    <div class="relative z-0">
                        {{ $slot }}
                    </div>
                </main>
            </div>
        </div>
        
        @stack('scripts')
    </body>
</html>
