<x-app-layout>
  <x-slot name="header">
    <div class="flex items-center justify-between">
      <h2 class="font-black text-2xl text-gray-900 leading-tight tracking-tight">
        {{ __('Staff Management') }}
      </h2>
      <div class="flex gap-3">
        <button x-data="" x-on:click.prevent="$dispatch('open-modal', 'add-user-modal')" class="bg-gray-900 text-white font-bold py-2.5 px-5 rounded-xl shadow-[0_8px_20px_rgb(17,24,39,0.2)] hover:bg-gray-800 transition duration-150 active:scale-95 flex items-center gap-2">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
          <span class="hidden sm:inline">Add Staff</span>
        </button>
      </div>
    </div>
  </x-slot>

  <div class="py-6" x-data="userManager()">
    <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8">
      
      @if (session('success'))
        <div class="mb-6 bg-green-50 text-green-700 border border-green-100 rounded-2xl px-6 py-4 shadow-sm flex items-center gap-3">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
          <span class="font-bold">{{ session('success') }}</span>
        </div>
      @endif
      @if (session('error'))
        <div class="mb-6 bg-red-50 text-red-700 border border-red-100 rounded-2xl px-6 py-4 shadow-sm flex items-center gap-3">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
          <span class="font-bold">{{ session('error') }}</span>
        </div>
      @endif
      @if ($errors->any())
        <div class="mb-6 bg-red-50 text-red-700 border border-red-100 rounded-2xl px-6 py-4 shadow-sm">
          <div class="flex items-center gap-3 mb-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
            <span class="font-bold">Please fix the following errors:</span>
          </div>
          <ul class="list-disc list-inside text-sm font-medium ml-2">
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      <!-- Role Filter Tabs -->
      <div class="flex items-center gap-2 mb-6 overflow-x-auto pb-2 no-scrollbar">
        <button @click="roleFilter = 'all'" :class="roleFilter === 'all' ? 'bg-gray-900 text-white shadow-lg' : 'bg-white text-gray-600 hover:bg-gray-50 border border-gray-200'" class="px-4 py-2 rounded-xl text-sm font-bold transition whitespace-nowrap">All <span class="text-xs opacity-60" x-text="'(' + allUsers.length + ')'"></span></button>
        <button @click="roleFilter = 'admin'" :class="roleFilter === 'admin' ? 'bg-purple-600 text-white shadow-lg' : 'bg-white text-gray-600 hover:bg-gray-50 border border-gray-200'" class="px-4 py-2 rounded-xl text-sm font-bold transition whitespace-nowrap">Admin</button>
        <button @click="roleFilter = 'manager'" :class="roleFilter === 'manager' ? 'bg-blue-600 text-white shadow-lg' : 'bg-white text-gray-600 hover:bg-gray-50 border border-gray-200'" class="px-4 py-2 rounded-xl text-sm font-bold transition whitespace-nowrap">Manager</button>
        <button @click="roleFilter = 'operational_staff'" :class="roleFilter === 'operational_staff' ? 'bg-amber-600 text-white shadow-lg' : 'bg-white text-gray-600 hover:bg-gray-50 border border-gray-200'" class="px-4 py-2 rounded-xl text-sm font-bold transition whitespace-nowrap">Op Staff</button>
        <button @click="roleFilter = 'accountant'" :class="roleFilter === 'accountant' ? 'bg-green-600 text-white shadow-lg' : 'bg-white text-gray-600 hover:bg-gray-50 border border-gray-200'" class="px-4 py-2 rounded-xl text-sm font-bold transition whitespace-nowrap">Accountant</button>
        <button @click="roleFilter = 'custom'" :class="roleFilter === 'custom' ? 'bg-rose-600 text-white shadow-lg' : 'bg-white text-gray-600 hover:bg-gray-50 border border-gray-200'" class="px-4 py-2 rounded-xl text-sm font-bold transition whitespace-nowrap">Custom</button>
      </div>

      <!-- Users Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <template x-for="user in filteredUsers" :key="user.id">
          <div class="bg-white rounded-[24px] p-6 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100 flex flex-col hover:shadow-lg transition-shadow">
            <div class="flex items-start justify-between mb-4">
              <div class="w-14 h-14 bg-mango/20 text-mango rounded-2xl flex items-center justify-center font-black text-xl" x-text="user.name.substring(0, 2).toUpperCase()"></div>
              <template x-if="user.id !== currentUserId">
                <form :action="`{{ url('users') }}/${user.id}`" method="POST" onsubmit="return confirm('Are you sure you want to delete this user?');">
                  @csrf @method('DELETE')
                  <button class="text-gray-400 hover:text-red-500 p-2 transition-colors active:scale-95">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                  </button>
                </form>
              </template>
              <template x-if="user.id === currentUserId">
                <span class="bg-blue-50 text-blue-600 text-xs font-black uppercase px-2 py-1 rounded-lg">You</span>
              </template>
            </div>
            
            <h3 class="font-black text-xl text-gray-900 mb-1" x-text="user.name"></h3>
            <p class="text-gray-500 font-medium text-sm mb-3" x-text="user.email"></p>

            <!-- Permission Badges -->
            <div class="flex flex-wrap gap-1.5 mb-4">
              <template x-for="perm in (user.permissions || []).slice(0, 5)" :key="perm">
                <span class="bg-gray-100 text-gray-600 text-[10px] font-bold px-2 py-0.5 rounded-md" x-text="permissionLabels[perm] || perm"></span>
              </template>
              <template x-if="(user.permissions || []).length > 5">
                <span class="bg-gray-100 text-gray-400 text-[10px] font-bold px-2 py-0.5 rounded-md" x-text="'+' + ((user.permissions || []).length - 5) + ' more'"></span>
              </template>
            </div>
            
            <div class="mt-auto pt-4 border-t border-gray-100 flex justify-between items-center">
              <span class="text-xs font-black px-3 py-1 rounded-full uppercase tracking-wider"
                 :class="getRoleBadgeClass(user.role)" x-text="getRoleLabel(user.role)"></span>
              <button @click="openEditModal(user)" class="text-mango font-bold text-sm hover:text-gray-900 transition-colors ml-auto">Edit & Permissions</button>
            </div>
          </div>
        </template>
      </div>

      <div class="mt-8">
        {{ $users->links() }}
      </div>
    </div>

    <!-- Add User Modal -->
    <x-modal name="add-user-modal" focusable>
      <form method="POST" action="{{ route('users.store') }}" class="p-8" x-data="{ addRole: 'operational_staff', addCustomRole: false, addPermissions: {{ json_encode(\App\Models\User::ROLE_PRESETS['operational_staff']) }} }">
        @csrf
        <h2 class="text-2xl font-black text-gray-900 mb-6">Add New Staff Member</h2>
        
        <div class="space-y-4">
          <div>
            <label class="block text-xs font-black text-gray-400 uppercase tracking-wider mb-2">Full Name</label>
            <input type="text" name="name" class="w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm py-3 focus:border-gray-900 focus:ring focus:ring-gray-900/10 font-medium transition-colors" required>
          </div>
          <div>
            <label class="block text-xs font-black text-gray-400 uppercase tracking-wider mb-2">Email Address</label>
            <input type="email" name="email" class="w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm py-3 focus:border-gray-900 focus:ring focus:ring-gray-900/10 font-medium transition-colors" required>
          </div>
          <div>
            <label class="block text-xs font-black text-gray-400 uppercase tracking-wider mb-2">Role</label>
            <div class="flex items-center gap-2">
              <template x-if="!addCustomRole">
                <select name="role" x-model="addRole" @change="addPermissions = rolePresets[addRole] || rolePresets['operational_staff']" class="w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm py-3 focus:border-gray-900 focus:ring focus:ring-gray-900/10 font-medium transition-colors" required>
                  <option value="operational_staff">Operational Staff</option>
                  <option value="accountant">Accountant</option>
                  <option value="manager">Manager</option>
                  <option value="admin">Admin (Full Access)</option>
                </select>
              </template>
              <template x-if="addCustomRole">
                <input type="text" name="role" placeholder="e.g. warehouse_staff" class="w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm py-3 focus:border-gray-900 focus:ring focus:ring-gray-900/10 font-medium transition-colors" required>
              </template>
              <button type="button" @click="addCustomRole = !addCustomRole" class="shrink-0 px-3 py-3 text-xs font-bold rounded-xl border border-gray-200 text-gray-500 hover:bg-gray-50 transition" x-text="addCustomRole ? 'Preset' : 'Custom'"></button>
            </div>
          </div>

          <!-- Permissions Editor for Add Modal -->
          <div class="pt-4 border-t border-gray-100">
            <label class="block text-xs font-black text-gray-400 uppercase tracking-wider mb-3">Permissions</label>
            <div class="space-y-4 max-h-64 overflow-y-auto pr-2">
              @foreach(\App\Models\User::PERMISSION_GROUPS as $group => $perms)
                <div>
                  <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">{{ $group }}</p>
                  <div class="space-y-1.5">
                    @foreach($perms as $perm)
                      <label class="flex items-center justify-between py-1.5 px-3 rounded-lg hover:bg-gray-50 transition cursor-pointer group">
                        <span class="text-sm font-bold text-gray-700 group-hover:text-gray-900">{{ \App\Models\User::PERMISSIONS[$perm] }}</span>
                        <input type="checkbox" name="permissions[]" value="{{ $perm }}" :checked="addPermissions.includes('{{ $perm }}')" @change="addPermissions.includes('{{ $perm }}') ? addPermissions = addPermissions.filter(p => p !== '{{ $perm }}') : addPermissions.push('{{ $perm }}')" class="w-5 h-5 rounded-lg border-gray-300 text-gray-900 focus:ring-gray-900/20 transition cursor-pointer">
                      </label>
                    @endforeach
                  </div>
                </div>
              @endforeach
            </div>
          </div>

          <div>
            <label class="block text-xs font-black text-gray-400 uppercase tracking-wider mb-2">Password</label>
            <input type="password" name="password" class="w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm py-3 focus:border-gray-900 focus:ring focus:ring-gray-900/10 font-medium transition-colors" required>
          </div>
          <div>
            <label class="block text-xs font-black text-gray-400 uppercase tracking-wider mb-2">Confirm Password</label>
            <input type="password" name="password_confirmation" class="w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm py-3 focus:border-gray-900 focus:ring focus:ring-gray-900/10 font-medium transition-colors" required>
          </div>
        </div>

        <div class="flex justify-end gap-3 mt-8">
          <button type="button" x-on:click="$dispatch('close')" class="px-6 py-3 bg-white border border-gray-200 text-gray-700 font-bold rounded-xl hover:bg-gray-50 transition">Cancel</button>
          <button type="submit" class="px-6 py-3 bg-gray-900 text-white font-bold rounded-xl shadow-[0_8px_20px_rgb(17,24,39,0.2)] hover:bg-gray-800 active:scale-95 transition">Create Staff</button>
        </div>
      </form>
    </x-modal>

    <!-- Edit User Modal with Permissions -->
    <div x-show="editModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
      <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div x-show="editModalOpen" x-transition.opacity class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" @click="closeEditModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div x-show="editModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" class="inline-block align-bottom bg-white rounded-[2rem] text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl w-full">
          
          <form :action="`{{ url('users') }}/${editingUser?.id}`" method="POST">
            @csrf
            @method('PUT')
            
            <!-- Header -->
            <div class="px-8 pt-8 pb-4 border-b border-gray-100 flex items-center justify-between">
              <div>
                <h2 class="text-2xl font-black text-gray-900">Edit Staff Member</h2>
                <p class="text-sm text-gray-500 mt-0.5">Update details and configure access permissions</p>
              </div>
              <button type="button" @click="closeEditModal()" class="text-gray-400 hover:text-gray-900 transition p-2">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
              </button>
            </div>

            <div class="px-8 py-6 max-h-[70vh] overflow-y-auto space-y-6">
              <!-- Basic Info -->
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="block text-xs font-black text-gray-400 uppercase tracking-wider mb-2">Full Name</label>
                  <input type="text" name="name" x-model="formData.name" class="w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm py-3 focus:border-gray-900 focus:ring focus:ring-gray-900/10 font-medium transition-colors" required>
                </div>
                <div>
                  <label class="block text-xs font-black text-gray-400 uppercase tracking-wider mb-2">Email Address</label>
                  <input type="email" name="email" x-model="formData.email" class="w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm py-3 focus:border-gray-900 focus:ring focus:ring-gray-900/10 font-medium transition-colors" required>
                </div>
              </div>

              <!-- Role -->
              <div>
                <label class="block text-xs font-black text-gray-400 uppercase tracking-wider mb-2">Role</label>
                <div class="flex items-center gap-2">
                  <template x-if="!editCustomRole">
                    <select name="role" x-model="formData.role" @change="applyRolePreset(formData.role)" class="w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm py-3 focus:border-gray-900 focus:ring focus:ring-gray-900/10 font-medium transition-colors" required>
                      <option value="operational_staff">Operational Staff</option>
                      <option value="accountant">Accountant</option>
                      <option value="manager">Manager</option>
                      <option value="admin">Admin (Full Access)</option>
                    </select>
                  </template>
                  <template x-if="editCustomRole">
                    <input type="text" name="role" x-model="formData.role" placeholder="e.g. warehouse_staff" class="w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm py-3 focus:border-gray-900 focus:ring focus:ring-gray-900/10 font-medium transition-colors" required>
                  </template>
                  <button type="button" @click="editCustomRole = !editCustomRole" class="shrink-0 px-3 py-3 text-xs font-bold rounded-xl border border-gray-200 text-gray-500 hover:bg-gray-50 transition" x-text="editCustomRole ? 'Preset' : 'Custom'"></button>
                </div>
                <p class="text-xs text-gray-400 mt-1.5" x-show="formData.role === 'admin'">
                  <svg class="w-3.5 h-3.5 inline text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                  Admin users always have full permissions.
                </p>
              </div>

              <!-- Quick Apply Presets -->
              <div x-show="formData.role !== 'admin'">
                <label class="block text-xs font-black text-gray-400 uppercase tracking-wider mb-2">Quick Apply Preset</label>
                <div class="flex flex-wrap gap-2">
                  <button type="button" @click="applyRolePreset('operational_staff')" class="px-3 py-1.5 bg-amber-50 text-amber-700 text-xs font-bold rounded-lg hover:bg-amber-100 transition active:scale-95 border border-amber-200/50">Op Staff</button>
                  <button type="button" @click="applyRolePreset('accountant')" class="px-3 py-1.5 bg-green-50 text-green-700 text-xs font-bold rounded-lg hover:bg-green-100 transition active:scale-95 border border-green-200/50">Accountant</button>
                  <button type="button" @click="applyRolePreset('manager')" class="px-3 py-1.5 bg-blue-50 text-blue-700 text-xs font-bold rounded-lg hover:bg-blue-100 transition active:scale-95 border border-blue-200/50">Manager</button>
                  <button type="button" @click="formData.permissions = []; selectAllPerms = false" class="px-3 py-1.5 bg-red-50 text-red-700 text-xs font-bold rounded-lg hover:bg-red-100 transition active:scale-95 border border-red-200/50">Clear All</button>
                  <button type="button" @click="formData.permissions = Object.keys(permissionLabels); selectAllPerms = true" class="px-3 py-1.5 bg-gray-100 text-gray-700 text-xs font-bold rounded-lg hover:bg-gray-200 transition active:scale-95 border border-gray-200/50">Select All</button>
                </div>
              </div>

              <!-- Permissions Grid -->
              <div x-show="formData.role !== 'admin'" class="border border-gray-100 rounded-2xl overflow-hidden">
                <div class="bg-gray-50 px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                  <h4 class="text-sm font-black text-gray-700 flex items-center gap-2">
                    <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                    Access Permissions
                  </h4>
                  <span class="text-xs font-bold text-gray-400" x-text="formData.permissions.length + '/' + Object.keys(permissionLabels).length + ' enabled'"></span>
                </div>
                <div class="divide-y divide-gray-50">
                  @foreach(\App\Models\User::PERMISSION_GROUPS as $group => $perms)
                    <div class="px-4 py-3">
                      <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2.5">{{ $group }}</p>
                      <div class="grid grid-cols-1 sm:grid-cols-2 gap-1">
                        @foreach($perms as $perm)
                          <label class="flex items-center justify-between py-2 px-3 rounded-lg hover:bg-gray-50 transition cursor-pointer group">
                            <div class="flex items-center gap-2.5">
                              <span class="w-2 h-2 rounded-full transition-colors" :class="formData.permissions.includes('{{ $perm }}') ? 'bg-green-500' : 'bg-gray-300'"></span>
                              <span class="text-sm font-bold text-gray-700 group-hover:text-gray-900">{{ \App\Models\User::PERMISSIONS[$perm] }}</span>
                            </div>
                            <input type="checkbox" name="permissions[]" value="{{ $perm }}" :checked="formData.permissions.includes('{{ $perm }}')" @change="togglePermission('{{ $perm }}')" class="w-5 h-5 rounded-lg border-gray-300 text-gray-900 focus:ring-gray-900/20 transition cursor-pointer">
                          </label>
                        @endforeach
                      </div>
                    </div>
                  @endforeach
                </div>
              </div>

              <!-- Admin full access notice -->
              <div x-show="formData.role === 'admin'" class="bg-purple-50 border border-purple-100 rounded-2xl p-4 flex items-start gap-3">
                <svg class="w-5 h-5 text-purple-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                <div>
                  <p class="text-sm font-bold text-purple-800">Admin — Full Access</p>
                  <p class="text-xs text-purple-600 mt-0.5">Admin users automatically have all permissions enabled. Individual permission toggles are not applicable.</p>
                </div>
              </div>

              <!-- Password -->
              <div class="pt-4 border-t border-gray-100">
                <p class="text-xs font-bold text-gray-400 mb-4">Leave passwords blank to keep current password.</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div>
                    <label class="block text-xs font-black text-gray-400 uppercase tracking-wider mb-2">New Password</label>
                    <input type="password" name="password" class="w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm py-3 focus:border-gray-900 focus:ring focus:ring-gray-900/10 font-medium transition-colors">
                  </div>
                  <div>
                    <label class="block text-xs font-black text-gray-400 uppercase tracking-wider mb-2">Confirm New Password</label>
                    <input type="password" name="password_confirmation" class="w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm py-3 focus:border-gray-900 focus:ring focus:ring-gray-900/10 font-medium transition-colors">
                  </div>
                </div>
              </div>
            </div>

            <div class="px-8 py-5 bg-gray-50 border-t border-gray-100 flex justify-end gap-3">
              <button type="button" @click="closeEditModal()" class="px-6 py-3 bg-white border border-gray-200 text-gray-700 font-bold rounded-xl hover:bg-gray-50 transition">Cancel</button>
              <button type="submit" class="px-6 py-3 bg-gray-900 text-white font-bold rounded-xl shadow-[0_8px_20px_rgb(17,24,39,0.2)] hover:bg-gray-800 active:scale-95 transition">Save Changes</button>
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
        editCustomRole: false,
        selectAllPerms: false,
        roleFilter: 'all',
        currentUserId: {{ auth()->id() }},
        allUsers: @json($users->items()),
        formData: {
          name: '',
          email: '',
          role: 'operational_staff',
          permissions: []
        },

        // Permission labels
        permissionLabels: @json(\App\Models\User::PERMISSIONS),

        // Role presets
        rolePresets: @json(\App\Models\User::ROLE_PRESETS),

        get filteredUsers() {
          if (this.roleFilter === 'all') return this.allUsers;
          if (this.roleFilter === 'custom') {
            const presetRoles = ['admin', 'manager', 'operational_staff', 'accountant'];
            return this.allUsers.filter(u => !presetRoles.includes(u.role));
          }
          return this.allUsers.filter(u => u.role === this.roleFilter);
        },

        getRoleBadgeClass(role) {
          const map = {
            'admin': 'bg-purple-100 text-purple-700',
            'manager': 'bg-blue-100 text-blue-700',
            'accountant': 'bg-green-100 text-green-700',
            'operational_staff': 'bg-gray-100 text-gray-700',
          };
          return map[role] || 'bg-rose-100 text-rose-700';
        },

        getRoleLabel(role) {
          const map = {
            'admin': 'Admin',
            'manager': 'Manager',
            'accountant': 'Accountant',
            'operational_staff': 'Op Staff',
          };
          return map[role] || role.replace(/_/g, ' ');
        },

        openEditModal(user) {
          this.editingUser = user;
          this.formData.name = user.name;
          this.formData.email = user.email;
          this.formData.role = user.role;
          this.formData.permissions = [...(user.permissions || [])];
          this.editCustomRole = !['admin', 'manager', 'operational_staff', 'accountant'].includes(user.role);
          this.editModalOpen = true;
        },

        closeEditModal() {
          this.editModalOpen = false;
          setTimeout(() => {
            this.editingUser = null;
            this.formData.name = '';
            this.formData.email = '';
            this.formData.role = 'operational_staff';
            this.formData.permissions = [];
            this.editCustomRole = false;
          }, 300);
        },

        togglePermission(perm) {
          const idx = this.formData.permissions.indexOf(perm);
          if (idx >= 0) {
            this.formData.permissions.splice(idx, 1);
          } else {
            this.formData.permissions.push(perm);
          }
        },

        applyRolePreset(role) {
          const presets = this.rolePresets[role];
          if (presets) {
            this.formData.permissions = [...presets];
          }
        }
      }));
    });
  </script>

</x-app-layout>
