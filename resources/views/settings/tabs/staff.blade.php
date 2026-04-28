<div x-data="userManager()">
    <div class="flex items-center justify-between mb-8">
        <div class="flex items-center gap-4">
            <h3 class="text-xl font-black text-gray-900 flex items-center gap-2">
                Staff Roles & Access Control
            </h3>
            <a href="{{ route('users.index') }}" class="bg-gray-100 text-gray-700 hover:bg-gray-200 font-bold py-2 px-4 rounded-xl text-sm transition">
                Manage Staff Members &rarr;
            </a>
        </div>
        <button x-on:click.prevent="$dispatch('open-modal', 'add-user-modal')" class="bg-gray-900 text-white font-bold py-2.5 px-5 rounded-xl shadow-lg hover:bg-gray-800 transition duration-150 active:scale-95 flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
            <span class="hidden sm:inline">Add Staff</span>
        </button>
    </div>

    <!-- Users Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        @foreach($users as $user)
            <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 flex flex-col hover:shadow-lg transition-shadow">
                <div class="flex items-start justify-between mb-4">
                    <div class="w-14 h-14 bg-mango/20 text-mango rounded-2xl flex items-center justify-center font-black text-xl">
                        {{ strtoupper(substr($user->name, 0, 2)) }}
                    </div>
                    @if(auth()->id() !== $user->id)
                        <form action="{{ route('users.destroy', $user) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this user?');">
                            @csrf @method('DELETE')
                            <input type="hidden" name="redirect_tab" value="staff">
                            <button class="text-gray-400 hover:text-red-500 p-2 transition-colors active:scale-95">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                            </button>
                        </form>
                    @else
                        <span class="bg-blue-50 text-blue-600 text-xs font-black uppercase px-2 py-1 rounded-lg">You</span>
                    @endif
                </div>
                
                <h3 class="font-black text-xl text-gray-900 mb-1">{{ $user->name }}</h3>
                <p class="text-gray-500 font-medium text-sm mb-4">{{ $user->email }}</p>
                
                <div class="mt-auto pt-4 border-t border-gray-100 flex justify-between items-center">
                    @if($user->role === 'admin')
                        <span class="bg-purple-100 text-purple-700 text-xs font-black px-3 py-1 rounded-full uppercase tracking-wider">Admin</span>
                    @elseif($user->role === 'manager')
                        <span class="bg-blue-100 text-blue-700 text-xs font-black px-3 py-1 rounded-full uppercase tracking-wider">Manager</span>
                    @elseif($user->role === 'accountant')
                        <span class="bg-green-100 text-green-700 text-xs font-black px-3 py-1 rounded-full uppercase tracking-wider">Accountant</span>
                    @else
                        <span class="bg-gray-100 text-gray-700 text-xs font-black px-3 py-1 rounded-full uppercase tracking-wider">Op Staff</span>
                    @endif
                    <button @click="openEditModal({{ $user }})" class="text-wildOrchid font-bold text-sm hover:text-gray-900 transition-colors ml-auto">Edit Details</button>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Add User Modal -->
    <x-modal name="add-user-modal" focusable>
        <form method="POST" action="{{ route('users.store') }}" class="p-8">
            @csrf
            <input type="hidden" name="redirect_tab" value="staff">
            <h2 class="text-2xl font-black text-gray-900 mb-6">Add New Staff Member</h2>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Full Name</label>
                    <input type="text" name="name" class="w-full rounded-xl border-gray-200 bg-gray-50 py-3 focus:ring-mango focus:border-mango transition-colors" required>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Email Address</label>
                    <input type="email" name="email" class="w-full rounded-xl border-gray-200 bg-gray-50 py-3 focus:ring-mango focus:border-mango transition-colors" required>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Role</label>
                    <select name="role" class="w-full rounded-xl border-gray-200 bg-gray-50 py-3 focus:ring-mango focus:border-mango transition-colors" required>
                        <option value="operational_staff">Operational Staff (Orders/Products)</option>
                        <option value="accountant">Accountant (Financials Only)</option>
                        <option value="manager">Manager (No Delete/Edit System Settings)</option>
                        <option value="admin">Admin (Full Access)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Password</label>
                    <input type="password" name="password" class="w-full rounded-xl border-gray-200 bg-gray-50 py-3 focus:ring-mango focus:border-mango transition-colors" required>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Confirm Password</label>
                    <input type="password" name="password_confirmation" class="w-full rounded-xl border-gray-200 bg-gray-50 py-3 focus:ring-mango focus:border-mango transition-colors" required>
                </div>
            </div>

            <div class="flex justify-end gap-3 mt-8">
                <button type="button" x-on:click="$dispatch('close')" class="px-6 py-3 bg-gray-100 text-gray-700 font-bold rounded-xl hover:bg-gray-200 transition">Cancel</button>
                <button type="submit" class="px-6 py-3 bg-gray-900 text-white font-bold rounded-xl shadow-lg hover:bg-gray-800 active:scale-95 transition">Create Staff</button>
            </div>
        </form>
    </x-modal>

    <!-- Edit User Modal -->
    <div x-show="editModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="editModalOpen" x-transition.opacity class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" @click="closeEditModal()"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="editModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" class="inline-block align-bottom bg-white rounded-[2rem] text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl w-full p-8">
                
                <form :action="`{{ url('users') }}/${editingUser?.id}`" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="redirect_tab" value="staff">
                    
                    <h2 class="text-2xl font-black text-gray-900 mb-6">Edit Staff Member</h2>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Full Name</label>
                            <input type="text" name="name" x-model="formData.name" class="w-full rounded-xl border-gray-200 bg-gray-50 py-3 focus:ring-mango transition-colors" required>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Email Address</label>
                            <input type="email" name="email" x-model="formData.email" class="w-full rounded-xl border-gray-200 bg-gray-50 py-3 focus:ring-mango transition-colors" required>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Role</label>
                            <select name="role" x-model="formData.role" class="w-full rounded-xl border-gray-200 bg-gray-50 py-3 focus:ring-mango transition-colors" required>
                                <option value="operational_staff">Operational Staff (Orders/Products)</option>
                                <option value="accountant">Accountant (Financials Only)</option>
                                <option value="manager">Manager (No Delete/Edit System Settings)</option>
                                <option value="admin">Admin (Full Access)</option>
                            </select>
                        </div>
                        <div class="pt-4 border-t border-gray-100">
                            <p class="text-xs font-bold text-gray-400 mb-4">Leave passwords blank to keep current password.</p>
                            <label class="block text-sm font-bold text-gray-700 mb-1">New Password</label>
                            <input type="password" name="password" class="w-full rounded-xl border-gray-200 bg-gray-50 py-3 focus:ring-mango transition-colors mb-4">
                            
                            <label class="block text-sm font-bold text-gray-700 mb-1">Confirm New Password</label>
                            <input type="password" name="password_confirmation" class="w-full rounded-xl border-gray-200 bg-gray-50 py-3 focus:ring-mango transition-colors">
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 mt-8">
                        <button type="button" @click="closeEditModal()" class="px-6 py-3 bg-gray-100 text-gray-700 font-bold rounded-xl hover:bg-gray-200 transition">Cancel</button>
                        <button type="submit" class="px-6 py-3 bg-gray-900 text-white font-bold rounded-xl shadow-lg hover:bg-gray-800 active:scale-95 transition">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('userManager', () => ({
            editModalOpen: false,
            editingUser: null,
            formData: {
                name: '',
                email: '',
                role: 'staff'
            },

            openEditModal(user) {
                this.editingUser = user;
                this.formData.name = user.name;
                this.formData.email = user.email;
                this.formData.role = user.role;
                this.editModalOpen = true;
            },

            closeEditModal() {
                this.editModalOpen = false;
                setTimeout(() => {
                    this.editingUser = null;
                    this.formData.name = '';
                    this.formData.email = '';
                }, 300);
            }
        }));
    });
</script>
