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
        <a href="{{ route('employee.returns.index') }}" class="flex items-center space-x-3 p-3 text-white {{ request()->routeIs('employee.returns*') ? 'bg-[#b08a5c]' : 'hover:bg-[#b08a5c]' }} rounded-lg transition-colors">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"></path>
            </svg>
            <span class="font-medium">Return Requests</span>
        </a>
        <a href="{{ route('employee.cancellations.index') }}" class="flex items-center space-x-3 p-3 text-white {{ request()->routeIs('employee.cancellations*') ? 'bg-[#b08a5c]' : 'hover:bg-[#b08a5c]' }} rounded-lg transition-colors">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
            </svg>
            <span class="font-medium">Cancellation Requests</span>
        </a>
        <a href="{{ route('employee.reports') }}" class="flex items-center space-x-3 p-3 text-white {{ request()->routeIs('employee.reports*') ? 'bg-[#b08a5c]' : 'hover:bg-[#b08a5c]' }} rounded-lg transition-colors">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"></path>
            </svg>
            <span class="font-medium">Reports</span>
        </a>
    </nav>
</div>
