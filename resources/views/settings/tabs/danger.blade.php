{{-- Factory Reset Tab --}}
<div class="space-y-8">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="bg-red-600 px-8 py-6 text-white">
            <h3 class="text-xl font-black flex items-center gap-3">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                DANGER ZONE — Factory Reset
            </h3>
            <p class="text-red-100 mt-2 font-medium">This action will permanently delete ALL data from your system. This cannot be undone.</p>
        </div>
        
        <div class="p-8" x-data="{ showConfirm: false, password: '', confirmWord: '', processing: false }">
            <div class="bg-red-50 border border-red-200 rounded-xl p-6 mb-8">
                <h4 class="font-bold text-red-800 text-lg mb-3">⚠️ What will be deleted:</h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="flex items-center gap-2 text-red-700 font-medium text-sm">
                        <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                        All Orders & Order Items
                    </div>
                    <div class="flex items-center gap-2 text-red-700 font-medium text-sm">
                        <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                        All Products & Categories
                    </div>
                    <div class="flex items-center gap-2 text-red-700 font-medium text-sm">
                        <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                        All Transactions & Accounts
                    </div>
                    <div class="flex items-center gap-2 text-red-700 font-medium text-sm">
                        <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                        All Purchases & Expenses
                    </div>
                    <div class="flex items-center gap-2 text-red-700 font-medium text-sm">
                        <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                        All Parties & Activity Logs
                    </div>
                    <div class="flex items-center gap-2 text-red-700 font-medium text-sm">
                        <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                        All Settings & Uploaded Files
                    </div>
                </div>
                <div class="mt-4 bg-green-50 border border-green-200 rounded-lg p-3">
                    <p class="text-green-700 font-bold text-sm flex items-center gap-2">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                        Your admin account will be preserved.
                    </p>
                </div>
            </div>
            
            <!-- Step 1: Show Confirmation Button -->
            <div x-show="!showConfirm">
                <button @click="showConfirm = true" class="bg-red-600 hover:bg-red-700 text-white font-black px-8 py-4 rounded-xl transition active:scale-95 shadow-lg shadow-red-600/30 flex items-center gap-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    I Understand, Proceed to Factory Reset
                </button>
            </div>

            <!-- Step 2: Password + Confirmation Word -->
            <div x-show="showConfirm" x-transition class="bg-red-50 border-2 border-red-300 rounded-2xl p-6">
                <h4 class="font-black text-red-800 text-lg mb-2">Confirm your identity</h4>
                <p class="text-red-600 font-medium text-sm mb-4">Enter your admin password and type <strong>RESET</strong> to confirm the factory reset.</p>
                
                <form method="POST" action="{{ route('settings.factoryReset') }}" @submit="processing = true">
                    @csrf
                    <div class="space-y-3">
                        <input type="password" name="password" x-model="password" placeholder="Enter admin password" required
                               class="w-full bg-white border-red-300 rounded-xl focus:ring-red-500 focus:border-red-500 font-medium py-3" autocomplete="current-password">
                        <input type="text" name="confirmation_word" x-model="confirmWord" placeholder='Type "RESET" to confirm' required
                               class="w-full bg-white border-red-300 rounded-xl focus:ring-red-500 focus:border-red-500 font-medium py-3" autocomplete="off">
                        <div class="flex items-center gap-4">
                            <button type="submit" :disabled="!password || confirmWord !== 'RESET' || processing"
                                    class="bg-red-600 hover:bg-red-700 text-white font-black px-6 py-3 rounded-xl transition active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2 whitespace-nowrap">
                                <span x-show="!processing">🗑️ RESET NOW</span>
                                <span x-show="processing">Processing...</span>
                            </button>
                            <button type="button" @click="showConfirm = false; password = ''; confirmWord = ''" class="text-gray-500 hover:text-gray-900 font-bold px-4 py-3 rounded-xl bg-white border border-gray-200 transition">
                                Cancel
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
