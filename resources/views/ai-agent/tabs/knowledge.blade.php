    <div class="space-y-6">
      <div class="flex justify-between items-center">
        <div class="flex gap-2">
          <a href="{{ route('ai-agent.index', ['tab' => 'knowledge']) }}" class="px-3 py-1.5 rounded-lg text-sm font-bold {{ !$categoryFilter ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }} transition">All</a>
          @foreach($categories as $catKey => $catLabel)
            <a href="{{ route('ai-agent.index', ['tab' => 'knowledge', 'category' => $catKey]) }}" class="px-3 py-1.5 rounded-lg text-sm font-bold {{ $categoryFilter === $catKey ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }} transition">{{ $catLabel }}</a>
          @endforeach
        </div>
        <button onclick="document.getElementById('knowledge-modal').classList.remove('hidden')" class="bg-gray-900 text-white font-black px-5 py-2.5 rounded-xl hover:bg-gray-800 transition flex items-center gap-2">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
          Add Entry
        </button>
      </div>

      <div class="grid gap-4">
        @forelse($knowledgeEntries as $entry)
          <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex items-start justify-between gap-4 {{ !$entry->is_active ? 'opacity-50' : '' }}">
            <div class="flex-1 min-w-0">
              <div class="flex items-center gap-2 mb-1">
                <span class="px-2 py-0.5 bg-blue-100 text-blue-700 rounded text-[10px] font-bold uppercase tracking-wider">{{ $categories[$entry->category] ?? $entry->category }}</span>
                @if(!$entry->is_active)
                  <span class="px-2 py-0.5 bg-gray-100 text-gray-500 rounded text-[10px] font-bold">DISABLED</span>
                @endif
              </div>
              <h4 class="font-black text-gray-900">{{ $entry->title }}</h4>
              <p class="text-sm text-gray-600 mt-1 line-clamp-3">{{ $entry->content }}</p>
            </div>
            <div class="flex items-center gap-2 shrink-0">
              <button onclick="toggleKnowledge({{ $entry->id }})" class="p-2 rounded-lg {{ $entry->is_active ? 'bg-green-100 text-green-700 hover:bg-green-200' : 'bg-gray-100 text-gray-500 hover:bg-gray-200' }} transition" title="{{ $entry->is_active ? 'Disable' : 'Enable' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $entry->is_active ? 'M5 13l4 4L19 7' : 'M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636' }}"></path></svg>
              </button>
              <button onclick="editKnowledge({{ json_encode($entry) }})" class="p-2 rounded-lg bg-gray-100 text-gray-500 hover:bg-gray-200 transition" title="Edit">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
              </button>
              <form action="{{ route('ai-agent.deleteKnowledge', $entry->id) }}" method="POST" onsubmit="return confirm('Delete this entry?')">
                @csrf @method('DELETE')
                <button type="submit" class="p-2 rounded-lg bg-red-50 text-red-500 hover:bg-red-100 transition" title="Delete">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                </button>
              </form>
            </div>
          </div>
        @empty
          <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center">
            <p class="text-4xl mb-3">📚</p>
            <p class="font-bold text-gray-500">No knowledge entries yet. Add FAQs, policies, and product info to train the AI.</p>
          </div>
        @endforelse
      </div>
    </div>

    {{-- Add/Edit Knowledge Modal --}}
    <div id="knowledge-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 backdrop-blur-sm">
      <div class="bg-white rounded-3xl shadow-2xl w-full max-w-lg mx-4">
        <form action="{{ route('ai-agent.storeKnowledge') }}" method="POST">
          @csrf
          <input type="hidden" name="id" id="knowledge-id" value="">
          <div class="p-6 border-b border-gray-100">
            <h3 class="font-black text-xl text-gray-900" id="knowledge-modal-title">Add Knowledge Entry</h3>
          </div>
          <div class="p-6 space-y-4">
            <div>
              <label class="block text-sm font-bold text-gray-700 mb-1">Category</label>
              <select name="category" id="knowledge-category" class="w-full border-gray-200 rounded-xl text-sm focus:ring-gray-900 focus:border-gray-900" required>
                @foreach($categories as $catKey => $catLabel)
                  <option value="{{ $catKey }}">{{ $catLabel }}</option>
                @endforeach
              </select>
            </div>
            <div>
              <label class="block text-sm font-bold text-gray-700 mb-1">Title</label>
              <input type="text" name="title" id="knowledge-title" class="w-full border-gray-200 rounded-xl text-sm focus:ring-gray-900 focus:border-gray-900" placeholder="e.g., Return Policy" required>
            </div>
            <div>
              <label class="block text-sm font-bold text-gray-700 mb-1">Content</label>
              <textarea name="content" id="knowledge-content" rows="6" class="w-full border-gray-200 rounded-xl text-sm focus:ring-gray-900 focus:border-gray-900" placeholder="Enter the knowledge content that the AI will use..." required></textarea>
            </div>
          </div>
          <div class="p-6 border-t border-gray-100 flex justify-end gap-3">
            <button type="button" onclick="document.getElementById('knowledge-modal').classList.add('hidden')" class="px-5 py-2.5 rounded-xl font-bold text-gray-500 hover:bg-gray-100 transition">Cancel</button>
            <button type="submit" class="bg-gray-900 text-white font-black px-6 py-2.5 rounded-xl hover:bg-gray-800 transition">Save</button>
          </div>
        </form>
      </div>
    </div>

    <script>
      function toggleKnowledge(id) {
        fetch(`/ai-agent/knowledge/${id}/toggle`, { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' } })
          .then(r => r.json()).then(() => window.location.reload());
      }
      function editKnowledge(entry) {
        document.getElementById('knowledge-id').value = entry.id;
        document.getElementById('knowledge-category').value = entry.category;
        document.getElementById('knowledge-title').value = entry.title;
        document.getElementById('knowledge-content').value = entry.content;
        document.getElementById('knowledge-modal-title').textContent = 'Edit Knowledge Entry';
        document.getElementById('knowledge-modal').classList.remove('hidden');
      }
    </script>
