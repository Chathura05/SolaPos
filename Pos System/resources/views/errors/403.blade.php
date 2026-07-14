<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Access Denied — POS System</title>
    @vite(['resources/css/app.css'])
</head>
<body class="antialiased bg-gray-100 dark:bg-gray-900">
    <div class="min-h-screen flex items-center justify-center px-4">
        <div class="max-w-md w-full text-center">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8">
                <div class="text-6xl mb-4">🚫</div>
                <h1 class="text-3xl font-bold text-gray-800 dark:text-gray-200 mb-2">Access Denied</h1>
                <p class="text-gray-500 dark:text-gray-400 mb-6">
                    Sorry, you don't have permission to access this page. Please contact your administrator if you believe this is a mistake.
                </p>
                <div class="flex gap-3 justify-center">
                    <a href="{{ route('dashboard') }}"
                       class="inline-flex items-center px-5 py-2.5 bg-indigo-600 text-white font-semibold text-sm rounded-xl hover:bg-indigo-700 transition shadow-sm">
                        ← Go to Dashboard
                    </a>
                    <a href="{{ route('pos.index') }}"
                       class="inline-flex items-center px-5 py-2.5 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-semibold text-sm rounded-xl hover:bg-gray-300 dark:hover:bg-gray-600 transition">
                        ⚡ Open POS
                    </a>
                </div>
            </div>
            <p class="text-xs text-gray-400 mt-4">Error 403 — Forbidden</p>
        </div>
    </div>
</body>
</html>
