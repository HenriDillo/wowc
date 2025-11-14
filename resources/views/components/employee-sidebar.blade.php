<!-- Sidebar -->
<div class="w-64 bg-[#c49b6e] flex flex-col shadow-lg">
    <div class="p-6 border-b border-[#b08a5c]">
        <span class="text-white font-semibold text-lg">Wow Carmen</span>
    </div>
    <nav class="flex-1 p-4 space-y-1">
        <a href="{{ route('dashboard') }}" class="flex items-center space-x-3 p-3 text-white {{ request()->routeIs('dashboard') ? 'bg-[#b08a5c]' : 'hover:bg-[#b08a5c]' }} rounded-lg transition-colors">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                <path d="M10 2l8 6v9a1 1 0 01-1 1h-5v-5H8v5H3a1 1 0 01-1-1V8l8-6z"></path>
            </svg>
            <span class="font-medium">Dashboard</span>
        </a>
        <a href="{{ route('employee.raw-materials') }}" class="flex items-center space-x-3 p-3 text-white {{ request()->routeIs('employee.raw-materials') || request()->routeIs('employee.materials.*') ? 'bg-[#b08a5c]' : 'hover:bg-[#b08a5c]' }} rounded-lg transition-colors">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                <path d="M4 3h12a1 1 0 011 1v3H3V4a1 1 0 011-1z"></path>
                <path d="M3 8h14v8a1 1 0 01-1 1H4a1 1 0 01-1-1V8z"></path>
            </svg>
            <span class="font-medium">Raw Materials</span>
        </a>
        <a href="{{ route('employee.items') }}" class="flex items-center space-x-3 p-3 text-white {{ request()->routeIs('employee.items*') ? 'bg-[#b08a5c]' : 'hover:bg-[#b08a5c]' }} rounded-lg transition-colors">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                <path d="M6 3a1 1 0 00-1 1v12a1 1 0 001 1h8a1 1 0 001-1V4a1 1 0 00-1-1H6z"></path>
            </svg>
            <span class="font-medium">Production</span>
        </a>
        <a href="{{ route('employee.orders') }}" class="flex items-center space-x-3 p-3 text-white {{ request()->routeIs('employee.orders*') ? 'bg-[#b08a5c]' : 'hover:bg-[#b08a5c]' }} rounded-lg transition-colors">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                <path d="M3 3h14a1 1 0 011 1v3H2V4a1 1 0 011-1z"></path>
                <path d="M2 8h16v8a1 1 0 01-1 1H3a1 1 0 01-1-1V8z"></path>
            </svg>
            <span class="font-medium">Orders</span>
        </a>
        <a href="{{ route('employee.reports') }}" class="flex items-center space-x-3 p-3 text-white {{ request()->routeIs('employee.reports*') ? 'bg-[#b08a5c]' : 'hover:bg-[#b08a5c]' }} rounded-lg transition-colors">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"></path>
            </svg>
            <span class="font-medium">Reports</span>
        </a>
    </nav>
</div>
