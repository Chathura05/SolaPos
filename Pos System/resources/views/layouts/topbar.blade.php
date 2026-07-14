<header class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-100 dark:border-gray-700 h-16 flex items-center justify-between px-4 sm:px-6 lg:px-8 z-30">
    
    <!-- Mobile Hamburger Menu -->
    <div class="flex items-center lg:hidden">
        <button @click="sidebarOpen = true" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-white focus:outline-none focus:ring-2 focus:ring-orange-500 rounded-md p-1 transition-colors">
            <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>
    </div>

    <!-- Empty div for desktop alignment -->
    <div class="hidden lg:block flex-1"></div>

    <!-- Right Side (Profile / Settings) -->
    <div class="flex items-center gap-4">
        
        <!-- Full Screen Button -->
        <button onclick="toggleFullScreen()" id="fs-btn" class="flex items-center gap-2 text-sm font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-white transition-colors" title="Toggle Full Screen">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path>
            </svg>
            <span id="fs-text">Full Screen</span>
        </button>

        <script>
            function toggleFullScreen() {
                if (!document.fullscreenElement) {
                    document.documentElement.requestFullscreen().catch(err => {
                        console.log(`Error attempting to enable full-screen mode: ${err.message}`);
                    });
                    document.getElementById('fs-text').textContent = 'Exit Full Screen';
                } else {
                    if (document.exitFullscreen) {
                        document.exitFullscreen();
                        document.getElementById('fs-text').textContent = 'Full Screen';
                    }
                }
            }
        </script>

        <!-- Profile Dropdown -->
        <x-dropdown align="right" width="48">
            <x-slot name="trigger">
                <button class="flex items-center text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white focus:outline-none transition ease-in-out duration-150">
                    <div class="flex items-center gap-2">
                        <span>{{ Auth::user()->name }}</span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider
                            {{ Auth::user()->hasRole('Admin') ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' : 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' }}">
                            {{ Auth::user()->getRoleNames()->first() ?? 'User' }}
                        </span>
                    </div>

                    <div class="ml-1">
                        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </div>
                </button>
            </x-slot>

            <x-slot name="content">
                <div class="px-4 py-2 border-b border-gray-100 dark:border-gray-700">
                    <p class="text-sm text-gray-500 dark:text-gray-400 truncate">{{ Auth::user()->email }}</p>
                </div>
                
                <x-dropdown-link :href="route('profile.edit')">
                    {{ __('Profile Settings') }}
                </x-dropdown-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-dropdown-link :href="route('logout')"
                            onclick="event.preventDefault(); this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-dropdown-link>
                </form>
            </x-slot>
        </x-dropdown>
    </div>
</header>
