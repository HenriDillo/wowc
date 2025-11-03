<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Admin Dashboard' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <style>[x-cloak]{ display: none !important; }</style>
</head>
<body x-data="userManagement()">

<script>
function userManagement() {
    return {
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
            if (this.searchTerm) {
                filtered = filtered.filter(u =>
                    (u.first_name + ' ' + u.last_name).toLowerCase().includes(this.searchTerm.toLowerCase()) ||
                    u.email.toLowerCase().includes(this.searchTerm.toLowerCase())
                );
            }
            if (this.roleFilter !== 'all') {
                filtered = filtered.filter(u => u.role === this.roleFilter);
            }
            return filtered;
        },

        editUser(u) {
            this.user = {...u}; // clone to avoid Alpine reactivity issues
            this.role = u.role;
            this.openEdit = true;
        },

        blockUser(u) {
            this.user = {...u};
            this.openBlock = true;
        },

        updateRole() {
            axios.put('/admin/users/' + this.user.id + '/role', { role: this.role })
                .then(res => {
                    this.users = this.users.map(u => u.id === this.user.id ? {...u, role: this.role} : u);
                    this.openEdit = false;
                    this.user = {};
                    this.role = '';
                })
                .catch(err => {
                    alert(err.response?.data?.message || 'Failed to update role.');
                });
        },

        toggleStatus() {
            axios.patch('/admin/users/' + this.user.id + '/toggle-status')
                .then(res => {
                    const updatedStatus = res.data.status;
                    this.users = this.users.map(u => u.id === this.user.id ? {...u, status: updatedStatus} : u);
                    this.openBlock = false;
                    this.user = {};
                })
                .catch(err => {
                    alert(err.response?.data?.message || 'Failed to update status.');
                });
        }

    }
}
</script>

<div class="flex min-h-screen bg-gray-50">
    <!-- Sidebar -->
    <div class="w-64 bg-[#c49b6e] flex flex-col shadow-lg">
        <div class="p-6 border-b border-[#b08a5c]">
            <span class="text-white font-semibold text-lg">Wow Carmen</span>
        </div>
        <nav class="flex-1 p-4 space-y-1">
            <div class="flex items-center space-x-3 p-3 text-white bg-[#b08a5c] rounded-lg">
                <span class="font-medium">Users</span>
            </div>
            
        </nav>
    </div>

    <!-- Main -->
    <div class="flex-1 bg-white">
        <div class="bg-white border-b border-gray-200 px-6 py-4 flex justify-between items-center">
            <h1 class="text-2xl font-semibold text-gray-800">Users</h1>
            <div class="relative">
                <button @click="dropdownOpen = !dropdownOpen" class="flex items-center space-x-2 text-gray-600 hover:text-gray-800">
                    <span>Hello, {{ Auth::user()->first_name ?? 'Admin' }}</span>
                    <div class="w-8 h-8 bg-gray-300 rounded-full"></div>
                </button>
                <div x-show="dropdownOpen" x-cloak x-transition @click.outside="dropdownOpen=false" class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-50">
                <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg">
                    Profile
                </a>
                    <form action="{{ route('logout') }}" method="POST">@csrf
                        <button type="submit" class="w-full text-left px-4 py-2 text-red-600 hover:bg-red-50 rounded-lg">Log Out</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="p-6">
            <div class="bg-white rounded-lg border border-gray-200">
                <!-- Filters -->
                <div class="p-6 border-b border-gray-200 flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-4 sm:space-y-0">
                    <div>
                        <input type="text" x-model="searchTerm" placeholder="Search users..." class="px-4 py-2 border rounded-lg">
                    </div>
                    <select x-model="roleFilter" class="px-4 py-2 border rounded-lg">
                        <option value="all">All Roles</option>
                        <option value="admin">Admin</option>
                        <option value="employee">Employee</option>
                        <option value="customer">Customer</option>
                    </select>
                </div>

                <!-- Users Table -->
                <div class="p-6">
                    <div class="grid grid-cols-5 gap-4 font-medium text-gray-500 uppercase border-b pb-2">
                        <div>Name</div><div>Email</div><div>Role</div><div>Status</div><div>Actions</div>
                    </div>
                    <template x-for="u in filteredUsers" :key="u.id">
                        <div class="grid grid-cols-5 gap-4 py-4 border-b items-center">
                            <div x-text="u.first_name + ' ' + u.last_name"></div>
                            <div x-text="u.email"></div>
                            <div><span :class="u.role==='admin'?'bg-blue-100 text-blue-800':u.role==='employee'?'bg-yellow-100 text-yellow-800':'bg-gray-100 text-gray-800'" class="px-2 py-1 rounded-full text-xs font-medium" x-text="u.role.charAt(0).toUpperCase()+u.role.slice(1)"></span></div>
                            <div><span :class="u.status==='blocked'?'bg-red-100 text-red-800':'bg-green-100 text-green-800'" class="px-2 py-1 rounded-full text-xs font-medium" x-text="u.status==='blocked'?'Blocked':'Active'"></span></div>
                            <div class="flex gap-2">
                                <button @click="editUser(u)" class="px-3 py-1.5 text-sm bg-blue-600 text-white rounded hover:bg-blue-700">Edit</button>
                                <button @click="blockUser(u)" class="px-3 py-1.5 text-sm text-white rounded" :class="u.status==='blocked' ? 'bg-green-600 hover:bg-green-700' : 'bg-red-600 hover:bg-red-700'" x-text="u.status==='blocked'?'Unblock':'Block'"></button>
                            </div>
                        </div>
                    </template>
                    <div x-show="filteredUsers.length===0" class="text-center py-8 text-gray-400">
                        <p>No users found</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Role Modal -->
<div x-show="openEdit" x-cloak class="fixed inset-0 flex items-center justify-center bg-black/50 z-50">
    <div @click.outside="openEdit=false" class="bg-white p-6 rounded-lg w-96">
        <h2 class="text-xl font-semibold mb-4">Edit User Role</h2>
        <select x-model="role" class="w-full border rounded-lg px-3 py-2 mb-4">
            <option value="admin">Admin</option>
            <option value="employee">Employee</option>
            <option value="customer">Customer</option>
        </select>
        <div class="flex justify-end gap-2">
            <button @click="openEdit=false;user={};role=''" class="px-3 py-1.5 text-sm border rounded hover:bg-gray-50">Cancel</button>
            <button @click="updateRole()" class="px-3 py-1.5 text-sm bg-blue-600 text-white rounded hover:bg-blue-700">Save</button>
        </div>
    </div>
</div>

<!-- Block/Unblock Modal -->
<div x-show="openBlock" x-cloak class="fixed inset-0 flex items-center justify-center bg-black/50 z-50">
    <div @click.outside="openBlock=false" class="bg-white p-6 rounded-lg w-96 text-center">
        <h2 class="text-xl font-semibold mb-4" x-text="user.status==='blocked'?'Unblock User':'Block User'"></h2>
        <p class="mb-6">Are you sure you want to <strong x-text="user.status==='blocked'?'unblock':'block'"></strong> <strong x-text="user.first_name + ' ' + user.last_name"></strong>?</p>
        <div class="flex justify-center gap-2">
            <button @click="openBlock=false;user={}" class="px-3 py-1.5 text-sm border rounded hover:bg-gray-50">Cancel</button>
            <button @click="toggleStatus()" class="px-3 py-1.5 text-sm text-white rounded" :class="user.status==='blocked'?'bg-green-600 hover:bg-green-700':'bg-red-600 hover:bg-red-700'" x-text="user.status==='blocked'?'Unblock':'Block'"></button>
        </div>
    </div>
</div>
</body>
</html>
