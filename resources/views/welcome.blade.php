<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Laravel Chat</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="antialiased font-sans">
    <div
        class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-purple-50 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900">

        <!-- Header -->
        <header
            class="bg-white/90 dark:bg-gray-800/90 backdrop-blur-lg border-b border-gray-200 dark:border-gray-700 sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-14 sm:h-16">
                    <!-- Logo -->
                    <div class="flex items-center gap-2 sm:gap-3">
                        <div
                            class="w-8 h-8 sm:w-10 sm:h-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center">
                            <svg class="w-4 h-4 sm:w-6 sm:h-6 text-white" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z">
                                </path>
                            </svg>
                        </div>
                        <h1 class="text-base sm:text-xl font-semibold text-gray-900 dark:text-white">Laravel Chat</h1>
                    </div>

                    <!-- Navigation -->
                    @if (Route::has('login'))
                        <div class="flex items-center gap-2 sm:gap-4">
                            @auth
                                <a href="{{ url('/dashboard') }}"
                                    class="flex items-center gap-1 sm:gap-2 px-3 sm:px-4 py-1.5 sm:py-2 bg-blue-600 text-white text-sm sm:text-base rounded-full hover:bg-blue-700 transition">
                                    <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z">
                                        </path>
                                    </svg>
                                    <span class="hidden xs:inline">Open</span>
                                </a>
                            @else
                                <a href="{{ route('login') }}"
                                    class="px-3 sm:px-4 py-1.5 sm:py-2 text-sm sm:text-base text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition">
                                    Log in
                                </a>
                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}"
                                        class="px-3 sm:px-6 py-1.5 sm:py-2 text-sm sm:text-base bg-blue-600 text-white rounded-full hover:bg-blue-700 transition">
                                        Sign up
                                    </a>
                                @endif
                            @endauth
                        </div>
                    @endif
                </div>
            </div>
        </header>

        <!-- Hero Section -->
        <main class="relative overflow-hidden">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16 lg:py-20">
                <div class="text-center mb-12 sm:mb-16">
                    <div
                        class="inline-flex items-center gap-2 px-3 sm:px-4 py-1.5 sm:py-2 bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-full mb-4 sm:mb-6 text-sm sm:text-base">
                        <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path
                                d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z">
                            </path>
                        </svg>
                        <span class="text-xs sm:text-sm font-medium">Real-time Messaging</span>
                    </div>

                    <h1
                        class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-bold text-gray-900 dark:text-white mb-4 sm:mb-6 px-4">
                        Stay Connected with
                        <span
                            class="bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent block sm:inline mt-2 sm:mt-0">
                            Laravel Chat</span>
                    </h1>

                    <p
                        class="text-base sm:text-lg md:text-xl text-gray-600 dark:text-gray-300 max-w-2xl mx-auto mb-6 sm:mb-8 px-4">
                        A modern, real-time messaging platform built with Laravel. Connect with friends, share moments,
                        and stay in touch.
                    </p>

                    <div class="flex flex-col sm:flex-row gap-3 sm:gap-4 justify-center px-4">
                        @auth
                            <a href="{{ url('/dashboard') }}"
                                class="px-6 sm:px-8 py-3 sm:py-4 bg-blue-600 text-white rounded-full hover:bg-blue-700 transition font-semibold shadow-lg shadow-blue-500/50 text-sm sm:text-base">
                                Open Messenger
                            </a>
                        @else
                            <a href="{{ route('register') }}"
                                class="px-6 sm:px-8 py-3 sm:py-4 bg-blue-600 text-white rounded-full hover:bg-blue-700 transition font-semibold shadow-lg shadow-blue-500/50 text-sm sm:text-base">
                                Get Started
                            </a>
                            <a href="{{ route('login') }}"
                                class="px-6 sm:px-8 py-3 sm:py-4 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-full hover:bg-gray-50 dark:hover:bg-gray-700 transition font-semibold border-2 border-gray-200 dark:border-gray-700 text-sm sm:text-base">
                                Sign In
                            </a>
                        @endauth
                    </div>
                </div>

                <!-- Features Grid -->
                <div
                    class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 sm:gap-6 lg:gap-8 mt-12 sm:mt-16 lg:mt-20 px-4">
                    <!-- Feature 1 -->
                    <div
                        class="bg-white dark:bg-gray-800 rounded-xl sm:rounded-2xl p-6 sm:p-8 shadow-lg hover:shadow-xl transition border border-gray-100 dark:border-gray-700">
                        <div
                            class="w-10 h-10 sm:w-12 sm:h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg sm:rounded-xl flex items-center justify-center mb-3 sm:mb-4">
                            <svg class="w-5 h-5 sm:w-6 sm:h-6 text-white" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg sm:text-xl font-semibold text-gray-900 dark:text-white mb-2">Instant
                            Messaging</h3>
                        <p class="text-sm sm:text-base text-gray-600 dark:text-gray-300">Send and receive messages in
                            real-time with lightning-fast delivery.</p>
                    </div>

                    <!-- Feature 2 -->
                    <div
                        class="bg-white dark:bg-gray-800 rounded-xl sm:rounded-2xl p-6 sm:p-8 shadow-lg hover:shadow-xl transition border border-gray-100 dark:border-gray-700">
                        <div
                            class="w-10 h-10 sm:w-12 sm:h-12 bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg sm:rounded-xl flex items-center justify-center mb-3 sm:mb-4">
                            <svg class="w-5 h-5 sm:w-6 sm:h-6 text-white" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                                </path>
                            </svg>
                        </div>
                        <h3 class="text-lg sm:text-xl font-semibold text-gray-900 dark:text-white mb-2">Connect with
                            Anyone</h3>
                        <p class="text-sm sm:text-base text-gray-600 dark:text-gray-300">Find and chat with friends,
                            colleagues, and new connections easily.</p>
                    </div>

                    <!-- Feature 3 -->
                    <div
                        class="bg-white dark:bg-gray-800 rounded-xl sm:rounded-2xl p-6 sm:p-8 shadow-lg hover:shadow-xl transition border border-gray-100 dark:border-gray-700 sm:col-span-2 md:col-span-1">
                        <div
                            class="w-10 h-10 sm:w-12 sm:h-12 bg-gradient-to-br from-pink-500 to-pink-600 rounded-lg sm:rounded-xl flex items-center justify-center mb-3 sm:mb-4">
                            <svg class="w-5 h-5 sm:w-6 sm:h-6 text-white" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                                </path>
                            </svg>
                        </div>
                        <h3 class="text-lg sm:text-xl font-semibold text-gray-900 dark:text-white mb-2">Secure & Private
                        </h3>
                        <p class="text-sm sm:text-base text-gray-600 dark:text-gray-300">Your conversations are
                            protected with end-to-end encryption.</p>
                    </div>
                </div>

                <!-- CTA Section -->
                <div
                    class="mt-12 sm:mt-16 lg:mt-20 bg-gradient-to-br from-blue-600 to-purple-600 rounded-2xl sm:rounded-3xl p-6 sm:p-8 md:p-12 text-white mx-4">
                    <div class="max-w-3xl mx-auto text-center">
                        <h2 class="text-2xl sm:text-3xl md:text-4xl font-bold mb-3 sm:mb-4">Ready to start chatting?
                        </h2>
                        <p class="text-base sm:text-lg text-blue-100 mb-6 sm:mb-8">Join thousands of users already
                            connecting on Laravel Chat</p>
                        @guest
                            <a href="{{ route('register') }}"
                                class="inline-block px-6 sm:px-8 py-3 sm:py-4 bg-white text-blue-600 rounded-full hover:bg-blue-50 transition font-semibold shadow-lg text-sm sm:text-base">
                                Create Free Account
                            </a>
                        @else
                            <a href="{{ url('/dashboard') }}"
                                class="inline-block px-6 sm:px-8 py-3 sm:py-4 bg-white text-blue-600 rounded-full hover:bg-blue-50 transition font-semibold shadow-lg text-sm sm:text-base">
                                Go to Dashboard
                            </a>
                        @endguest
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer
            class="border-t border-gray-200 dark:border-gray-700 bg-white/50 dark:bg-gray-800/50 backdrop-blur-lg mt-8 sm:mt-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
                <div class="text-center text-xs sm:text-sm text-gray-600 dark:text-gray-400">
                    <p>Laravel v{{ Illuminate\Foundation\Application::VERSION }} (PHP v{{ PHP_VERSION }})</p>
                    <p class="mt-2">Built with ❤️ using Laravel</p>
                </div>
            </div>
        </footer>
    </div>
</body>

</html>
