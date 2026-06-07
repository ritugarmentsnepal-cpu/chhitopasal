<div>
  <div class="flex items-center justify-between mb-8">
    <div class="flex items-center gap-4">
      <h3 class="text-xl font-black text-gray-900 flex items-center gap-2">
        Staff Roles & Access Control
      </h3>
      <a href="{{ route('users.index') }}" class="bg-gray-100 text-gray-700 hover:bg-gray-200 font-bold py-2 px-4 rounded-xl text-sm transition flex items-center gap-2">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
        Manage Staff & Permissions &rarr;
      </a>
    </div>
  </div>

  <!-- Role Presets Summary -->
  <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4 mb-8">
    @php
      $roleColors = [
        'admin' => ['bg' => 'bg-purple-50', 'border' => 'border-purple-100', 'badge' => 'bg-purple-100 text-purple-700', 'icon' => 'text-purple-500'],
        'manager' => ['bg' => 'bg-blue-50', 'border' => 'border-blue-100', 'badge' => 'bg-blue-100 text-blue-700', 'icon' => 'text-blue-500'],
        'operational_staff' => ['bg' => 'bg-amber-50', 'border' => 'border-amber-100', 'badge' => 'bg-amber-100 text-amber-700', 'icon' => 'text-amber-500'],
        'accountant' => ['bg' => 'bg-green-50', 'border' => 'border-green-100', 'badge' => 'bg-green-100 text-green-700', 'icon' => 'text-green-500'],
      ];
      $roleLabels = ['admin' => 'Admin', 'manager' => 'Manager', 'operational_staff' => 'Op Staff', 'accountant' => 'Accountant'];
    @endphp
    @foreach(\App\Models\User::ROLE_PRESETS as $role => $perms)
      @php $colors = $roleColors[$role]; @endphp
      <div class="{{ $colors['bg'] }} {{ $colors['border'] }} border rounded-2xl p-5">
        <div class="flex items-center justify-between mb-3">
          <span class="{{ $colors['badge'] }} text-xs font-black px-3 py-1 rounded-full uppercase tracking-wider">{{ $roleLabels[$role] }}</span>
          <span class="text-xs font-bold text-gray-400">{{ count($perms) }} permissions</span>
        </div>
        <div class="flex flex-wrap gap-1">
          @foreach($perms as $perm)
            <span class="bg-white/60 text-gray-600 text-[10px] font-bold px-2 py-0.5 rounded-md border border-gray-100/50">{{ \App\Models\User::PERMISSIONS[$perm] ?? $perm }}</span>
          @endforeach
        </div>
      </div>
    @endforeach
  </div>

  <!-- Staff List -->
  <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
    @foreach($users as $user)
      <div class="bg-white rounded-[24px] p-6 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100 flex flex-col hover:shadow-lg transition-shadow">
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
        <p class="text-gray-500 font-medium text-sm mb-3">{{ $user->email }}</p>

        <!-- Permission count -->
        <p class="text-xs font-bold text-gray-400 mb-3">{{ count($user->permissions ?? []) }} permissions enabled</p>
        
        <div class="mt-auto pt-4 border-t border-gray-100 flex justify-between items-center">
          @php
            $badgeClass = $roleColors[$user->role]['badge'] ?? 'bg-rose-100 text-rose-700';
            $label = $roleLabels[$user->role] ?? str_replace('_', ' ', $user->role);
          @endphp
          <span class="{{ $badgeClass }} text-[10px] font-black px-2.5 py-1 rounded-md uppercase tracking-wider">{{ $label }}</span>
          <a href="{{ route('users.index') }}" class="text-wildOrchid font-bold text-sm hover:text-gray-900 transition-colors ml-auto">Edit Permissions &rarr;</a>
        </div>
      </div>
    @endforeach
  </div>
</div>
