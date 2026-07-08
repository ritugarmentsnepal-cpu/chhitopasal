    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
      <form action="{{ route('settings.store') }}" method="POST">
        @csrf
        <input type="hidden" name="redirect_url" value="{{ route('ai-agent.index', ['tab' => 'settings']) }}">
        <div class="p-6 border-b border-gray-100">
          <h3 class="font-black text-xl text-gray-900">AI Agent Settings</h3>
          <p class="text-sm font-bold text-gray-500">Configure your AI agent behavior</p>
        </div>
        <div class="p-6 space-y-6">
          {{-- Master Toggle --}}
          <div class="flex items-center justify-between bg-gray-50 rounded-xl p-4 border border-gray-200">
            <div>
              <h4 class="font-black text-gray-900">AI Agent Status</h4>
              <p class="text-sm text-gray-500 font-bold">Enable or disable the AI agent globally</p>
            </div>
            <div x-data="{ enabled: {{ $settings['ai_agent_enabled'] ? 'true' : 'false' }} }" class="flex items-center">
              <input type="hidden" name="ai_agent_enabled" :value="enabled ? '1' : '0'">
              <button type="button" @click="enabled = !enabled"
                :class="enabled ? 'bg-gray-900' : 'bg-gray-300'"
                class="relative inline-flex h-7 w-14 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-gray-900 focus:ring-offset-2">
                <span :class="enabled ? 'translate-x-8' : 'translate-x-1'"
                  class="inline-block h-5 w-5 transform rounded-full bg-white transition duration-200 ease-in-out shadow-sm">
                </span>
              </button>
            </div>
          </div>

          {{-- Model --}}
          <div>
            <label class="block text-sm font-bold text-gray-700 mb-1">AI Model</label>
            <select name="ai_agent_model" class="w-full border-gray-200 rounded-xl text-sm focus:ring-gray-900 focus:border-gray-900">
              <option value="google/gemini-2.5-flash" {{ $settings['ai_agent_model'] === 'google/gemini-2.5-flash' ? 'selected' : '' }}>Google Gemini 2.5 Flash (Recommended)</option>
              <option value="google/gemini-2.5-pro" {{ $settings['ai_agent_model'] === 'google/gemini-2.5-pro' ? 'selected' : '' }}>Google Gemini 2.5 Pro</option>
              <option value="anthropic/claude-sonnet-4.6" {{ $settings['ai_agent_model'] === 'anthropic/claude-sonnet-4.6' ? 'selected' : '' }}>Claude Sonnet 4.6</option>
              <option value="openai/gpt-4o" {{ $settings['ai_agent_model'] === 'openai/gpt-4o' ? 'selected' : '' }}>GPT-4o</option>
            </select>
            <p class="text-xs text-gray-400 mt-1">Uses the OpenRouter API key from Settings → Integrations</p>
          </div>

          <div class="grid grid-cols-2 gap-6">
            {{-- Max Messages --}}
            <div>
              <label class="block text-sm font-bold text-gray-700 mb-1">Max AI Messages per Thread</label>
              <input type="number" name="ai_agent_max_messages" value="{{ $settings['ai_agent_max_messages'] }}" min="1" max="100" class="w-full border-gray-200 rounded-xl text-sm focus:ring-gray-900 focus:border-gray-900">
              <p class="text-xs text-gray-400 mt-1">AI stops replying after this many messages in a single thread</p>
            </div>

            {{-- Response Delay --}}
            <div>
              <label class="block text-sm font-bold text-gray-700 mb-1">Response Delay (seconds)</label>
              <input type="number" name="ai_agent_response_delay" value="{{ $settings['ai_agent_response_delay'] }}" min="0" max="30" class="w-full border-gray-200 rounded-xl text-sm focus:ring-gray-900 focus:border-gray-900">
              <p class="text-xs text-gray-400 mt-1">Simulates human typing time (0 = instant reply)</p>
            </div>

            {{-- Working Hours Start --}}
            <div>
              <label class="block text-sm font-bold text-gray-700 mb-1">Working Hours Start (Nepal Time)</label>
              <select name="ai_agent_working_hours_start" class="w-full border-gray-200 rounded-xl text-sm focus:ring-gray-900 focus:border-gray-900">
                @for($h = 0; $h < 24; $h++)
                  <option value="{{ $h }}" {{ (int)$settings['ai_agent_working_hours_start'] === $h ? 'selected' : '' }}>{{ str_pad($h, 2, '0', STR_PAD_LEFT) }}:00</option>
                @endfor
              </select>
            </div>

            {{-- Working Hours End --}}
            <div>
              <label class="block text-sm font-bold text-gray-700 mb-1">Working Hours End (Nepal Time)</label>
              <select name="ai_agent_working_hours_end" class="w-full border-gray-200 rounded-xl text-sm focus:ring-gray-900 focus:border-gray-900">
                @for($h = 0; $h < 24; $h++)
                  <option value="{{ $h }}" {{ (int)$settings['ai_agent_working_hours_end'] === $h ? 'selected' : '' }}>{{ str_pad($h, 2, '0', STR_PAD_LEFT) }}:00</option>
                @endfor
              </select>
            </div>
          </div>

          {{-- Custom Greeting --}}
          <div>
            <label class="block text-sm font-bold text-gray-700 mb-1">Custom Greeting (Optional)</label>
            <textarea name="ai_agent_greeting" rows="3" class="w-full border-gray-200 rounded-xl text-sm focus:ring-gray-900 focus:border-gray-900" placeholder="Custom greeting message the AI should use...">{{ $settings['ai_agent_greeting'] }}</textarea>
          </div>
        </div>
        <div class="p-6 border-t border-gray-100 flex justify-end">
          <button type="submit" class="bg-gray-900 text-white font-black px-8 py-2.5 rounded-xl hover:bg-gray-800 transition">Save Settings</button>
        </div>
      </form>
    </div>

