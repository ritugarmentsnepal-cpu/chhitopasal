<div>
    <form method="POST" action="{{ route('settings.store') }}" class="mb-8">
        @csrf
        <input type="hidden" name="redirect_tab" value="integrations">
        
        <div class="bg-white rounded-3xl p-8 shadow-sm border border-gray-100 mb-8">
            <h3 class="text-xl font-black text-gray-900 mb-6 flex items-center gap-2">
                Pathao Courier Integration
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Client ID</label>
                    <input name="pathao_client_id" type="text" value="{{ setting('pathao_client_id') }}" class="block w-full rounded-xl border-gray-200 bg-gray-50 py-3 font-bold" />
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Client Secret</label>
                    <input name="pathao_client_secret" type="password" value="{{ setting('pathao_client_secret') }}" class="block w-full rounded-xl border-gray-200 bg-gray-50 py-3 font-bold" />
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Username / Email</label>
                    <input name="pathao_username" type="text" value="{{ setting('pathao_username') }}" class="block w-full rounded-xl border-gray-200 bg-gray-50 py-3 font-bold" />
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Password</label>
                    <input name="pathao_password" type="password" value="{{ setting('pathao_password') }}" class="block w-full rounded-xl border-gray-200 bg-gray-50 py-3 font-bold" />
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-bold text-gray-700 mb-2">Store ID</label>
                    <input name="pathao_store_id" type="text" value="{{ setting('pathao_store_id') }}" class="block w-full rounded-xl border-gray-200 bg-gray-50 py-3 font-bold" />
                </div>
            </div>

            <div class="flex items-center justify-between pt-6 border-t border-gray-100">
                <div class="flex items-center">
                    <input type="hidden" name="pathao_auto_sync" value="0">
                    <input type="checkbox" name="pathao_auto_sync" value="1" id="pathao_auto_sync" {{ setting('pathao_auto_sync') == '1' ? 'checked' : '' }} class="rounded text-mango focus:ring-mango h-5 w-5 border-gray-300">
                    <label for="pathao_auto_sync" class="ml-3 text-sm font-bold text-gray-900">Enable Automatic Order Sync to Pathao</label>
                </div>
                
                <button type="submit" class="bg-mango text-gray-900 font-black py-3 px-8 rounded-xl shadow-sm hover:bg-yellow-400 transition-colors">
                    Save Pathao Settings
                </button>
            </div>
        </div>
    </form>

    <div class="bg-white rounded-3xl p-8 shadow-sm border border-gray-100">
        <h3 class="text-xl font-black text-gray-900 mb-6">Integration Testing</h3>
        <p class="text-gray-500 font-medium mb-6">Test the connection to Pathao's API using the saved credentials above. This will attempt to authenticate and retrieve a token without creating any orders.</p>
        
        <form method="POST" action="{{ route('settings.testPathao') }}">
            @csrf
            <button type="submit" class="bg-gray-900 text-white font-black py-3 px-8 rounded-xl shadow-sm hover:bg-gray-800 transition-colors flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                Test Pathao Connection
            </button>
        </form>
    </div>
</div>
