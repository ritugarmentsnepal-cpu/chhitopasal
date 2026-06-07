<div>
  <form method="POST" action="{{ route('settings.store') }}" class="mb-8">
    @csrf
    <input type="hidden" name="redirect_tab" value="integrations">
    
    <div class="bg-white rounded-[24px] p-8 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100 mb-8">
      <h3 class="text-xl font-black text-gray-900 mb-6 flex items-center gap-2">
        Pathao Courier Integration
      </h3>
      
      <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
        <div class="md:col-span-2">
          <label class="block text-xs font-black text-gray-400 uppercase tracking-wider mb-2">Base URL</label>
          <input name="pathao_base_url" type="text" value="{{ setting('pathao_base_url', config('services.pathao.base_url')) }}" class="block w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm focus:border-gray-900 focus:ring focus:ring-gray-900/10 py-3 font-medium transition-colors" placeholder="https://api-hermes.pathao.com" />
          <p class="text-xs text-gray-500 mt-2">Use <strong>https://api-hermes.pathao.com</strong> for production in Nepal, or <strong>https://courier-api-sandbox.pathao.com</strong> for sandbox.</p>
        </div>
        <div>
          <label class="block text-xs font-black text-gray-400 uppercase tracking-wider mb-2">Client ID</label>
          <input name="pathao_client_id" type="text" value="{{ setting('pathao_client_id') }}" class="block w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm focus:border-gray-900 focus:ring focus:ring-gray-900/10 py-3 font-medium transition-colors" />
        </div>
        <div>
          <label class="block text-xs font-black text-gray-400 uppercase tracking-wider mb-2">Client Secret</label>
          <input name="pathao_client_secret" type="password" value="{{ setting('pathao_client_secret') }}" class="block w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm focus:border-gray-900 focus:ring focus:ring-gray-900/10 py-3 font-medium transition-colors" />
        </div>
        <div>
          <label class="block text-xs font-black text-gray-400 uppercase tracking-wider mb-2">Username / Email</label>
          <input name="pathao_username" type="text" value="{{ setting('pathao_username') }}" class="block w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm focus:border-gray-900 focus:ring focus:ring-gray-900/10 py-3 font-medium transition-colors" />
        </div>
        <div>
          <label class="block text-xs font-black text-gray-400 uppercase tracking-wider mb-2">Password</label>
          <input name="pathao_password" type="password" value="{{ setting('pathao_password') }}" class="block w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm focus:border-gray-900 focus:ring focus:ring-gray-900/10 py-3 font-medium transition-colors" />
        </div>
        <div class="md:col-span-2">
          <label class="block text-xs font-black text-gray-400 uppercase tracking-wider mb-2">Store ID</label>
          <input name="pathao_store_id" type="text" value="{{ setting('pathao_store_id') }}" class="block w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm focus:border-gray-900 focus:ring focus:ring-gray-900/10 py-3 font-medium transition-colors" />
        </div>
      </div>

      <div class="flex items-center justify-between pt-6 border-t border-gray-100">
        <div class="flex items-center">
          <input type="hidden" name="pathao_auto_sync" value="0">
          <input type="checkbox" name="pathao_auto_sync" value="1" id="pathao_auto_sync" {{ setting('pathao_auto_sync') == '1' ? 'checked' : '' }} class="rounded text-mango shadow-sm focus:border-gray-900 focus:ring focus:ring-gray-900/10 h-5 w-5 border-gray-300">
          <label for="pathao_auto_sync" class="ml-3 text-sm font-bold text-gray-900">Enable Automatic Order Sync to Pathao</label>
        </div>
        
        <button type="submit" class="bg-gray-900 text-white font-bold py-3 px-8 rounded-xl shadow-[0_8px_20px_rgb(17,24,39,0.2)] hover:bg-gray-800 active:scale-95 transition">
          Save Pathao Settings
        </button>
      </div>
    </div>
  </form>

  <div class="bg-white rounded-[24px] p-8 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100">
    <h3 class="text-xl font-black text-gray-900 mb-6">Integration Testing</h3>
    <p class="text-gray-500 font-medium mb-6">Test the connection to Pathao's API using the saved credentials above. This will attempt to authenticate and retrieve a token without creating any orders.</p>
    
    <form method="POST" action="{{ route('settings.testPathao') }}">
      @csrf
      <button type="submit" class="bg-gray-900 text-white font-black py-3 px-8 rounded-xl shadow-[0_8px_20px_rgb(17,24,39,0.2)] hover:bg-gray-800 active:scale-95 transition-colors flex items-center gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
        Test Pathao Connection
      </button>
    </form>
  </div>

  {{-- OpenRouter AI Section --}}
  <form method="POST" action="{{ route('settings.store') }}" class="mt-8">
    @csrf
    <input type="hidden" name="redirect_tab" value="integrations">
    
    <div class="bg-white rounded-[24px] p-8 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100">
      <div class="flex items-center gap-3 mb-6">
        <div class="w-10 h-10 bg-indigo-50 rounded-xl flex items-center justify-center flex-shrink-0">
          <svg class="w-5 h-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
        </div>
        <div>
          <h3 class="text-xl font-black text-gray-900 ">OpenRouter AI</h3>
          <p class="text-sm text-gray-500 font-medium">Power your product auto-generation using OpenRouter's API</p>
        </div>
      </div>
      
      <div class="mb-6">
        <label class="block text-xs font-black text-gray-400 uppercase tracking-wider mb-2">API Key</label>
        <input name="openrouter_api_key" type="password" value="{{ setting('openrouter_api_key') }}" class="block w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm focus:border-gray-900 focus:ring focus:ring-gray-900/10 py-3 font-medium transition-colors font-mono tracking-wider" placeholder="sk-or-v1-..." />
        <p class="text-xs text-gray-500 mt-2">
          Get your API key from <a href="https://openrouter.ai/keys" target="_blank" class="text-indigo-600 font-bold hover:underline">openrouter.ai/keys</a>.
        </p>
      </div>

      <div class="mb-6">
        <label class="block text-xs font-black text-gray-400 uppercase tracking-wider mb-2">AI Model</label>
        <input name="openrouter_model" type="text" value="{{ setting('openrouter_model', 'anthropic/claude-sonnet-4.6') }}" class="block w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm focus:border-gray-900 focus:ring focus:ring-gray-900/10 py-3 font-medium transition-colors font-mono tracking-wider" />
        <p class="text-xs text-gray-500 mt-2">
          Recommended: <code>anthropic/claude-sonnet-4.6</code>
        </p>
      </div>

      @if(setting('openrouter_api_key'))
        <div class="bg-green-50 text-green-700 px-4 py-3 rounded-xl text-sm font-bold flex items-center gap-2 mb-6 border border-green-100">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
          OpenRouter API Key is configured and ready to use.
        </div>
      @endif

      <div class="flex items-center justify-end pt-6 border-t border-gray-100">
        <button type="submit" class="bg-gray-900 text-white font-bold py-3 px-8 rounded-xl shadow-[0_8px_20px_rgb(17,24,39,0.2)] hover:bg-gray-800 active:scale-95 transition">
          Save AI Settings
        </button>
      </div>
    </div>
  </form>

  {{-- AI Real-Time Daemon Section --}}
  <div class="bg-white rounded-[24px] p-8 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100 mt-8">
    <h3 class="text-xl font-black text-gray-900 mb-2">Real-Time AI Processing</h3>
    <p class="text-gray-500 font-medium mb-6">If your AI model takes too long to reply and hits the server timeout, start the background daemon to process messages instantly without timing out.</p>
    
    <form method="POST" action="{{ route('ai-agent.startDaemon') }}">
      @csrf
      <button type="submit" class="bg-indigo-600 text-white font-black py-3 px-8 rounded-xl shadow-[0_8px_20px_rgb(79,70,229,0.3)] hover:bg-indigo-700 active:scale-95 transition-colors flex items-center gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
        Start Background Queue Daemon
      </button>
    </form>
  </div>

  {{-- Analytics Section --}}
  <form method="POST" action="{{ route('settings.store') }}" class="mt-8">
    @csrf
    <input type="hidden" name="redirect_tab" value="integrations">
    
    <div class="bg-white rounded-[24px] p-8 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100">
      <div class="flex items-center gap-3 mb-6">
        <div class="w-10 h-10 bg-blue-50 rounded-xl flex items-center justify-center flex-shrink-0">
          <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0c6.627 0 12 5.373 12 12s-5.373 12-12 12S0 18.627 0 12 5.373 0 12 0zm5.176 16.483c-1.125 1.543-2.923 2.502-4.945 2.502-2.736 0-5.068-1.785-5.836-4.258h10.97c.058-.458.093-.93.093-1.41 0-3.83-3.116-6.946-6.946-6.946-3.83 0-6.946 3.116-6.946 6.946 0 3.83 3.116 6.946 6.946 6.946 2.378 0 4.474-1.2 5.626-3.003l-1.962-1.277zM10.457 7.747c1.37-.508 2.924-.31 4.12.518 1.157.798 1.83 2.072 1.83 3.468H7.556c0-1.786.964-3.376 2.52-4.073l.38-.163v.25z"/></svg>
        </div>
        <div>
          <h3 class="text-xl font-black text-gray-900 ">Google Tag Manager</h3>
          <p class="text-sm text-gray-500 font-medium">Manage all your marketing and analytics tags centrally</p>
        </div>
      </div>
      
      <div class="mb-6">
        <label class="block text-xs font-black text-gray-400 uppercase tracking-wider mb-2">GTM Container ID</label>
        <input name="google_tag_manager_id" type="text" value="{{ setting('google_tag_manager_id') }}" class="block w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm focus:border-gray-900 focus:ring focus:ring-gray-900/10 py-3 font-medium transition-colors font-mono tracking-wider" placeholder="e.g. GTM-XXXXXXX" />
        <p class="text-xs text-gray-500 mt-2">
          Get your Container ID from <a href="https://tagmanager.google.com" target="_blank" class="text-blue-600 font-bold hover:underline">tagmanager.google.com</a>. Leave empty to disable GTM.
        </p>
      </div>

      @if(setting('google_tag_manager_id'))
        <div class="bg-green-50 text-green-700 px-4 py-3 rounded-xl text-sm font-bold flex items-center gap-2 mb-6 border border-green-100">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
          Google Tag Manager is active — Container ID: <code class="bg-green-100 px-2 py-0.5 rounded font-mono">{{ setting('google_tag_manager_id') }}</code>
        </div>
      @endif

      <div class="flex items-center justify-end pt-6 border-t border-gray-100">
        <button type="submit" class="bg-gray-900 text-white font-bold py-3 px-8 rounded-xl shadow-[0_8px_20px_rgb(17,24,39,0.2)] hover:bg-gray-800 active:scale-95 transition">
          Save Analytics Settings
        </button>
      </div>
    </div>

    {{-- Facebook Pixel Section --}}
    <div class="bg-white rounded-[24px] p-8 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100 mt-8">
      <div class="flex items-center gap-3 mb-6">
        <div class="w-10 h-10 bg-blue-50 rounded-xl flex items-center justify-center flex-shrink-0">
          <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
        </div>
        <div>
          <h3 class="text-xl font-black text-gray-900 ">Facebook Pixel</h3>
          <p class="text-sm text-gray-500 font-medium">Ad tracking, conversions & retargeting audiences</p>
        </div>
      </div>

      <div class="mb-6">
        <label class="block text-xs font-black text-gray-400 uppercase tracking-wider mb-2">Pixel ID</label>
        <input name="facebook_pixel_id" type="text" value="{{ setting('facebook_pixel_id') }}" class="block w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm focus:border-gray-900 focus:ring focus:ring-gray-900/10 py-3 font-medium transition-colors font-mono tracking-wider" placeholder="e.g. 123456789012345" />
        <p class="text-xs text-gray-500 mt-2">
          Get your Pixel ID from <a href="https://business.facebook.com/events_manager" target="_blank" class="text-blue-600 font-bold hover:underline">Meta Events Manager</a> → Data Sources → Your Pixel. Leave empty to disable tracking.
        </p>
      </div>

      @if(setting('facebook_pixel_id'))
        <div class="bg-green-50 text-green-700 px-4 py-3 rounded-xl text-sm font-bold flex items-center gap-2 mb-6 border border-green-100">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
          Facebook Pixel is active — Pixel ID: <code class="bg-green-100 px-2 py-0.5 rounded font-mono">{{ setting('facebook_pixel_id') }}</code>
        </div>
      @endif

      <div class="flex items-center justify-end pt-6 border-t border-gray-100">
        <button type="submit" class="bg-gray-900 text-white font-bold py-3 px-8 rounded-xl shadow-[0_8px_20px_rgb(17,24,39,0.2)] hover:bg-gray-800 active:scale-95 transition">
          Save Analytics Settings
        </button>
      </div>
    </div>
  </form>
</div>
