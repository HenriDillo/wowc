<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Admin Dashboard' }}</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Alpine.js CDN -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- Axios CDN for AJAX -->
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <!-- Alpine.js Cloak Style -->
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body 
    x-data="{ 
        openEdit: false, 
        openBlock: false,
        user: {}, 
        role: '', 
        dropdownOpen: false,
        searchTerm: '',
        roleFilter: 'all',
        users: @js($users),
        get filteredUsers() {
            let filtered = this.users;

            // Filter by search term
            if (this.searchTerm) {
                filtered = filtered.filter(user => 
                    user.name.toLowerCase().includes(this.searchTerm.toLowerCase()) ||
                    user.email.toLowerCase().includes(this.searchTerm.toLowerCase())
                );
            }

            // Filter by role
            if (this.roleFilter !== 'all') {
                filtered = filtered.filter(user => user.role === this.roleFilter);
            }

            return filtered;
        },
        updateRole() {
            axios.put('/users/' + this.user.id, { role: this.role })
                .then(res => {
                    this.user.role = this.role;
                    this.openEdit = false;
                    this.user = {};
                    this.role = '';
                })
                .catch(err => console.log(err));
        },
        toggleStatus() {
            axios.patch('/users/' + this.user.id + '/toggle-status')
                .then(res => {
                    this.user.status = this.user.status === 'blocked' ? 'active' : 'blocked';
                    this.openBlock = false;
                    this.user = {};
                })
                .catch(err => console.log(err));
        }
    }"
>
    <!-- Main Layout Container -->
    <div class="flex min-h-screen bg-gray-50">
        <!-- Sidebar Start -->
        <div class="w-64 bg-[#c49b6e] flex flex-col shadow-lg">
            <!-- Logo Section -->
            <div class="p-6 border-b border-[#b08a5c]">
                <div class="flex items-center space-x-2">
                    <div class="w-8 h-8 bg-white/20 rounded-full"></div>
                    <span class="text-white font-semibold text-lg">Wow Carmen</span>
                </div>
            </div>
            <!-- Sidebar Navigation -->
            <nav class="flex-1 p-4 space-y-1">
                <div class="flex items-center space-x-3 p-3 text-white bg-[#b08a5c] rounded-lg">
                    <div class="w-5 h-5 bg-white/30 rounded"></div>
                    <span class="font-medium">Users</span>
                </div>
            </nav>
        </div>
        <!-- Sidebar End -->

        <!-- Main Content Area -->
        <div class="flex-1 bg-white">
            <!-- Top Header Bar -->
            <div class="bg-white border-b border-gray-200 px-6 py-4 flex justify-between items-center">
                <h1 class="text-2xl font-semibold text-gray-800">{{ $pageTitle ?? 'Users' }}</h1>
                <div class="relative">
                    <button 
                        @click="dropdownOpen = !dropdownOpen"
                        class="flex items-center space-x-2 text-gray-600 hover:text-gray-800 focus:outline-none"
                    >
                        <span>Hello, {{ Auth::user()->name ?? 'Admin' }}</span>
                        <div class="w-8 h-8 bg-gray-300 rounded-full"></div>
                        <svg class="w-4 h-4 transition-transform duration-200" 
                             :class="{ 'rotate-180': dropdownOpen }"
                             fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                        </svg>
                    </button>
                    <div 
                        x-show="dropdownOpen"
                        x-cloak
                        x-transition
                        @click.outside="dropdownOpen = false"
                        class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-50"
                    >
                        <form action="{{ route('logout') }}" method="POST" class="block">
                            @csrf
                            <button type="submit" class="w-full text-left px-4 py-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                                Log Out
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Main Dashboard Content -->
            <div class="p-6">
                <div class="bg-white rounded-lg border border-gray-200">
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-4 sm:space-y-0">
                            <h2 class="text-lg font-semibold text-gray-800">Users Management</h2>
                            <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-4">
                                <div class="relative">
                                    <input 
                                        type="text" 
                                        x-model="searchTerm"
                                        placeholder="Search users..."
                                        class="w-full sm:w-64 px-4 py-2 pl-10 pr-4 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#c49b6e] focus:border-transparent"
                                    >
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <select 
                                    x-model="roleFilter"
                                    class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#c49b6e] focus:border-transparent"
                                >
                                    <option value="all">All Roles</option>
                                    <option value="admin">Admin Only</option>
                                    <option value="employee">Employee Only</option>
                                    <option value="customer">Customer Only</option>
                                </select>
                            </div>
                        </div>
                        <div class="mt-4 text-sm text-gray-600">
                            Showing <span x-text="filteredUsers.length"></span> of <span x-text="users.length"></span> users
                        </div>
                    </div>

                    <!-- Users Table/Grid -->
                    <div class="p-6">
                        <div class="grid grid-cols-5 gap-4 pb-4 border-b border-gray-200 text-gray-500 text-sm font-medium uppercase">
                            <div>Name</div>
                            <div>Email</div>
                            <div>Role</div>
                            <div>Status</div>
                            <div>Actions</div>
                        </div>
                        <template x-for="user in filteredUsers" :key="user.id">
                            <div class="grid grid-cols-5 gap-4 py-4 border-b border-gray-100 items-center hover:bg-gray-50 transition-colors">
                                <div class="text-gray-800 font-medium" x-text="user.name"></div>
                                <div class="text-gray-600" x-text="user.email"></div>
                                <div class="text-gray-600">
                                    <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full"
                                          :class="user.role === 'admin' ? 'bg-blue-100 text-blue-800' : (user.role === 'employee' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800')"
                                          x-text="user.role.charAt(0).toUpperCase() + user.role.slice(1)">
                                    </span>
                                </div>
                                <div class="text-gray-600">
                                    <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full"
                                          :class="user.status === 'blocked' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'"
                                          x-text="user.status === 'blocked' ? 'Blocked' : 'Active'">
                                    </span>
                                </div>
                                <div class="flex space-x-2">
                                    <button 
                                        class="text-blue-500 hover:text-blue-700 hover:underline text-sm font-medium transition-colors"
                                        @click="openEdit = true; user = user; role = user.role">
                                        Edit
                                    </button>
                                    <button 
                                        class="hover:underline text-sm font-medium transition-colors"
                                        :class="user.status === 'blocked' ? 'text-green-500 hover:text-green-700' : 'text-red-500 hover:text-red-700'"
                                        @click="openBlock = true; user = user"
                                        x-text="user.status === 'blocked' ? 'Unblock' : 'Block'">
                                    </button>
                                </div>
                            </div>
                        </template>
                        <div x-show="filteredUsers.length === 0" class="text-center text-gray-400 py-8">
                            <div class="text-lg font-medium mb-2">No users found</div>
                            <p class="text-sm" x-show="searchTerm || roleFilter !== 'all'">Try adjusting your search or filter criteria.</p>
                            <p class="text-sm" x-show="!searchTerm && roleFilter === 'all'">There are currently no users in the system.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Block/Unblock Modal -->
    <div x-show="openBlock" x-cloak x-transition.opacity class="fixed inset-0 flex items-center justify-center bg-black/50 z-50">
        <div @click.outside="openBlock = false" x-transition.scale.origin.center class="bg-white p-6 rounded-lg w-96 max-w-md mx-4 shadow-xl text-center">
            <h2 class="text-xl font-semibold mb-4 text-gray-800" x-text="user.status === 'blocked' ? 'Unblock User' : 'Block User'"></h2>
            <p class="mb-6 text-gray-600">
                Are you sure you want to <strong x-text="user.status === 'blocked' ? 'unblock' : 'block'"></strong> <strong x-text="user.name"></strong>?
                <span x-show="user.status !== 'blocked'" class="block mt-2 text-sm text-red-600">This will prevent the user from accessing their account.</span>
                <span x-show="user.status === 'blocked'" class="block mt-2 text-sm text-green-600">This will restore the user's access to their account.</span>
            </p>
            <div class="flex justify-center space-x-3">
                <button type="button" @click="openBlock = false; user = {}" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors">Cancel</button>
                <button type="button" @click="toggleStatus()" :class="user.status === 'blocked' ? 'bg-green-600 text-white hover:bg-green-700' : 'bg-red-600 text-white hover:bg-red-700'" class="px-4 py-2 rounded-lg transition-colors" x-text="user.status === 'blocked' ? 'Unblock User' : 'Block User'"></button>
            </div>
        </div>
    </div>

    <!-- Edit Role Modal -->
    <div x-show="openEdit" x-cloak x-transition.opacity class="fixed inset-0 flex items-center justify-center bg-black/50 z-50">
        <div @click.outside="openEdit = false" x-transition.scale.origin.center class="bg-white p-6 rounded-lg w-96 max-w-md mx-4 shadow-xl">
            <h2 class="text-xl font-semibold mb-4 text-gray-800">Edit User Role</h2>
            <form @submit.prevent="updateRole()">
                <div class="mb-4">
                    <label class="block text-gray-700 font-medium mb-2">Name</label>
                    <input type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-gray-100 text-gray-500" x-model="user.name" disabled>
                </div>
                <div class="mb-6">
                    <label for="role" class="block text-gray-700 font-medium mb-2">Role</label>
                    <select id="role" name="role" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#c49b6e] focus:border-transparent" x-model="role">
                        <option value="admin">Admin</option>
                        <option value="employee">Employee</option>
                        <option value="customer">Customer</option>
                    </select>
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" @click="openEdit = false; user = {}; role = ''" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-[#c49b6e] text-white rounded-lg hover:bg-[#b08a5c] transition-colors">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Flash Messages -->
    @if(session('success'))
    <div x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 3000)" class="fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50">
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 3000)" class="fixed top-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg z-50">
        {{ session('error') }}
    </div>
    @endif
</body>
</html>
