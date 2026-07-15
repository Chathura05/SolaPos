<!-- Sidebar Backdrop (Mobile) -->
<div x-show="sidebarOpen" x-transition.opacity
    class="fixed inset-0 z-40 bg-gray-900/50 backdrop-blur-sm lg:hidden"
    @click="sidebarOpen = false" style="display: none;"></div>

<!-- Sidebar -->
<aside class="fixed inset-y-0 left-0 z-50 w-64 bg-orange-600 text-white shadow-xl transition-transform duration-300 ease-in-out lg:static lg:translate-x-0"
       :class="{'translate-x-0': sidebarOpen, '-translate-x-full': !sidebarOpen}">
    
    <!-- Logo Area -->
    <div class="flex items-center justify-center h-16 border-b border-white/20">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
            @if(setting('company_logo'))
                <img src="{{ Storage::url(setting('company_logo')) }}" alt="Company Logo" class="h-9 w-auto object-contain drop-shadow-md">
            @else
                <x-application-logo class="block h-9 w-auto fill-current text-white drop-shadow-md" />
            @endif
            <span class="font-bold text-xl tracking-wide drop-shadow-md">{{ config('app.name', 'Sola Pos') }}</span>
        </a>
    </div>

    <!-- Navigation Links -->
    <nav class="p-4 space-y-1.5 overflow-y-auto h-[calc(100vh-4rem)] custom-scrollbar">
        
        <!-- Dashboard -->
        <a href="{{ route('dashboard') }}" class="flex items-center px-4 py-2.5 rounded-lg transition-colors {{ request()->routeIs('dashboard') ? 'bg-white/20 font-semibold shadow-inner' : 'hover:bg-white/10 text-white/90 hover:text-white' }}">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
            {{ __('Dashboard') }}
        </a>

        <div class="pt-4 pb-2">
            <p class="px-4 text-xs font-bold text-white/60 uppercase tracking-wider">Operations</p>
        </div>

        <!-- POS -->
        <a href="{{ route('pos.index') }}" class="flex items-center px-4 py-2.5 rounded-lg transition-colors {{ request()->routeIs('pos.index') ? 'bg-white/20 font-semibold shadow-inner' : 'hover:bg-white/10 text-white/90 hover:text-white' }}">
            <span class="text-lg mr-3">⚡</span> POS Terminal
        </a>

        <!-- Sales History -->
        <a href="{{ route('pos.history') }}" class="flex items-center px-4 py-2.5 rounded-lg transition-colors {{ request()->routeIs('pos.history') ? 'bg-white/20 font-semibold shadow-inner' : 'hover:bg-white/10 text-white/90 hover:text-white' }}">
            <span class="text-lg mr-3">🧾</span> Sales History
        </a>

        <!-- Quotes -->
        <a href="{{ route('quotes.index') }}" class="flex items-center px-4 py-2.5 rounded-lg transition-colors {{ request()->routeIs('quotes.*') ? 'bg-white/20 font-semibold shadow-inner' : 'hover:bg-white/10 text-white/90 hover:text-white' }}">
            <span class="text-lg mr-3">📄</span> Quotations
        </a>

        <!-- Dispatches -->
        <a href="{{ route('dispatches.index') }}" class="flex items-center px-4 py-2.5 rounded-lg transition-colors {{ request()->routeIs('dispatches.*') ? 'bg-white/20 font-semibold shadow-inner' : 'hover:bg-white/10 text-white/90 hover:text-white' }}">
            <span class="text-lg mr-3">📦</span> Dispatches
        </a>

        <!-- Returns -->
        <a href="{{ route('returns.index') }}" class="flex items-center px-4 py-2.5 rounded-lg transition-colors {{ request()->routeIs('returns.*') ? 'bg-white/20 font-semibold shadow-inner' : 'hover:bg-white/10 text-white/90 hover:text-white' }}">
            <span class="text-lg mr-3">🔙</span> Returns
        </a>

        <div class="pt-4 pb-2">
            <p class="px-4 text-xs font-bold text-white/60 uppercase tracking-wider">Administration</p>
        </div>

        <!-- Reports Dropdown -->
        <div x-data="{ open: {{ request()->routeIs('reports.*') ? 'true' : 'false' }} }">
            <button @click="open = !open" class="w-full flex items-center justify-between px-4 py-2.5 rounded-lg transition-colors {{ request()->routeIs('reports.*') ? 'bg-white/10 font-semibold' : 'hover:bg-white/10 text-white/90 hover:text-white' }}">
                <div class="flex items-center">
                    <span class="text-lg mr-3">📊</span> Reports
                </div>
                <svg :class="{'rotate-180': open}" class="w-4 h-4 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </button>
            <div x-show="open" x-collapse class="pl-11 pr-4 py-2 space-y-1">
                @role('Admin')
                    <a href="{{ route('reports.sales') }}" class="block py-1.5 text-sm {{ request()->routeIs('reports.sales') ? 'text-white font-semibold' : 'text-white/70 hover:text-white' }}">Sales Report</a>
                    <a href="{{ route('reports.cashier') }}" class="block py-1.5 text-sm {{ request()->routeIs('reports.cashier') ? 'text-white font-semibold' : 'text-white/70 hover:text-white' }}">Cashier Report</a>
                    <a href="{{ route('reports.customers') }}" class="block py-1.5 text-sm {{ request()->routeIs('reports.customers') ? 'text-white font-semibold' : 'text-white/70 hover:text-white' }}">Customer Report</a>
                @endrole
                <a href="{{ route('reports.inventory') }}" class="block py-1.5 text-sm {{ request()->routeIs('reports.inventory') ? 'text-white font-semibold' : 'text-white/70 hover:text-white' }}">Inventory Report</a>
            </div>
        </div>

        <!-- Manage Dropdown -->
        @php
            $isManageActive = request()->routeIs('products.*') || request()->routeIs('categories.*') || request()->routeIs('sub-categories.*') || request()->routeIs('inventory.*') || request()->routeIs('suppliers.*') || request()->routeIs('customers.*') || request()->routeIs('showrooms.*') || request()->routeIs('users.*') || request()->routeIs('settings.*') || request()->routeIs('promos.*');
        @endphp
        <div x-data="{ open: {{ $isManageActive ? 'true' : 'false' }} }">
            <button @click="open = !open" class="w-full flex items-center justify-between px-4 py-2.5 rounded-lg transition-colors {{ $isManageActive ? 'bg-white/10 font-semibold' : 'hover:bg-white/10 text-white/90 hover:text-white' }}">
                <div class="flex items-center">
                    <span class="text-lg mr-3">⚙️</span> Manage
                </div>
                <svg :class="{'rotate-180': open}" class="w-4 h-4 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </button>
            <div x-show="open" x-collapse class="pl-11 pr-4 py-2 space-y-3">
                @role('Admin')
                    <div>
                        <p class="text-[10px] font-bold text-white/50 uppercase tracking-widest mb-1">Catalog</p>
                        <a href="{{ route('categories.index') }}" class="block py-1 text-sm {{ request()->routeIs('categories.*') ? 'text-white font-semibold' : 'text-white/70 hover:text-white' }}">Categories</a>
                        <a href="{{ route('sub-categories.index') }}" class="block py-1 text-sm {{ request()->routeIs('sub-categories.*') ? 'text-white font-semibold' : 'text-white/70 hover:text-white' }}">Sub-categories</a>
                        <a href="{{ route('products.index') }}" class="block py-1 text-sm {{ request()->routeIs('products.*') ? 'text-white font-semibold' : 'text-white/70 hover:text-white' }}">Products</a>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-white/50 uppercase tracking-widest mb-1 mt-2">People</p>
                        <a href="{{ route('suppliers.index') }}" class="block py-1 text-sm {{ request()->routeIs('suppliers.*') ? 'text-white font-semibold' : 'text-white/70 hover:text-white' }}">Suppliers</a>
                        <a href="{{ route('customers.index') }}" class="block py-1 text-sm {{ request()->routeIs('customers.*') ? 'text-white font-semibold' : 'text-white/70 hover:text-white' }}">Customers</a>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-white/50 uppercase tracking-widest mb-1 mt-2">System</p>
                        <a href="{{ route('showrooms.index') }}" class="block py-1 text-sm {{ request()->routeIs('showrooms.*') ? 'text-white font-semibold' : 'text-white/70 hover:text-white' }}">Showrooms</a>
                        <a href="{{ route('users.index') }}" class="block py-1 text-sm {{ request()->routeIs('users.*') ? 'text-white font-semibold' : 'text-white/70 hover:text-white' }}">Users & Cashiers</a>
                        <a href="{{ route('promos.index') }}" class="block py-1 text-sm {{ request()->routeIs('promos.*') ? 'text-white font-semibold' : 'text-white/70 hover:text-white' }}">Promotions</a>
                        <a href="{{ route('settings.index') }}" class="block py-1 text-sm {{ request()->routeIs('settings.*') ? 'text-white font-semibold' : 'text-white/70 hover:text-white' }}">Settings</a>
                    </div>
                @else
                    <a href="{{ route('inventory.index') }}" class="block py-1 text-sm {{ request()->routeIs('inventory.*') ? 'text-white font-semibold' : 'text-white/70 hover:text-white' }}">Inventory</a>
                    <a href="{{ route('customers.index') }}" class="block py-1 text-sm {{ request()->routeIs('customers.*') ? 'text-white font-semibold' : 'text-white/70 hover:text-white' }}">Customers</a>
                @endrole
            </div>
        </div>
    </nav>
</aside>
<style>
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.2); border-radius: 4px; }
.custom-scrollbar:hover::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.4); }
</style>
