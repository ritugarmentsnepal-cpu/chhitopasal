<div x-data="{ requesting: false }">
    {{-- Approve --}}
    <form method="POST" action="{{ route('mockups.approval.respond', $token) }}" x-show="!requesting">
        @csrf
        <input type="hidden" name="decision" value="approve">
        <button type="submit" class="w-full bg-emerald-500 text-white font-black text-lg py-4 rounded-2xl shadow-lg hover:bg-emerald-600 transition active:scale-95 flex items-center justify-center gap-2">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
            Approve This Design
        </button>
        <button type="button" @click="requesting = true" class="w-full mt-3 bg-white border border-gray-200 text-gray-600 font-bold py-3.5 rounded-2xl hover:bg-gray-50 transition active:scale-95">
            Request Changes
        </button>
    </form>

    {{-- Request changes --}}
    <form method="POST" action="{{ route('mockups.approval.respond', $token) }}" x-show="requesting" x-cloak>
        @csrf
        <input type="hidden" name="decision" value="request_changes">
        <textarea name="feedback" rows="4" required maxlength="1000" placeholder="Tell us what you'd like changed — e.g. make the logo bigger, move it to the left side..." class="w-full rounded-2xl border-gray-200 bg-white text-sm font-medium p-4 shadow-sm focus:border-amber-400 focus:ring focus:ring-amber-200/50"></textarea>
        <button type="submit" class="w-full mt-3 bg-amber-500 text-white font-black py-4 rounded-2xl shadow-lg hover:bg-amber-600 transition active:scale-95">
            Send Change Request
        </button>
        <button type="button" @click="requesting = false" class="w-full mt-3 text-gray-400 font-bold py-2 text-sm hover:text-gray-600 transition">
            ← Back
        </button>
    </form>
</div>
