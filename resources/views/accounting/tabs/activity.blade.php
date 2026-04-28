<div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="px-6 py-5 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
        <h3 class="font-black text-gray-900 text-lg flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            System Activity Log
        </h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50/50 text-gray-500 text-sm">
                    <th class="py-4 px-6 font-bold w-48">Timestamp</th>
                    <th class="py-4 px-6 font-bold">User</th>
                    <th class="py-4 px-6 font-bold">Action</th>
                    <th class="py-4 px-6 font-bold">Module / ID</th>
                    <th class="py-4 px-6 font-bold">Details</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($data['logs'] as $log)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="py-4 px-6 text-sm font-medium text-gray-500">
                            {{ $log->created_at->format('M d, Y h:i A') }}
                        </td>
                        <td class="py-4 px-6">
                            @if($log->user)
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-full bg-mango text-gray-900 flex items-center justify-center font-bold text-xs">
                                        {{ substr($log->user->name, 0, 1) }}
                                    </div>
                                    <span class="font-bold text-gray-900 text-sm">{{ $log->user->name }}</span>
                                </div>
                            @else
                                <span class="text-sm font-bold text-gray-400">System</span>
                            @endif
                        </td>
                        <td class="py-4 px-6">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold
                                {{ $log->action === 'created' ? 'bg-green-50 text-green-700' : ($log->action === 'updated' ? 'bg-blue-50 text-blue-700' : 'bg-red-50 text-red-700') }}">
                                {{ strtoupper($log->action) }}
                            </span>
                        </td>
                        <td class="py-4 px-6 text-sm">
                            <span class="font-bold text-gray-900">{{ class_basename($log->model_type) }}</span>
                            <span class="text-gray-500">#{{ $log->model_id }}</span>
                        </td>
                        <td class="py-4 px-6 text-sm text-gray-500">
                            <button x-data @click="$dispatch('open-modal', 'log-details-{{ $log->id }}')" class="text-wildOrchid hover:text-gray-900 font-bold underline decoration-2 underline-offset-2">View Changes</button>
                            
                            <!-- Log Details Modal -->
                            <x-modal name="log-details-{{ $log->id }}" :show="false" maxWidth="2xl">
                                <div class="p-6">
                                    <h3 class="font-black text-xl text-gray-900 mb-4">Activity Details</h3>
                                    <div class="bg-gray-900 rounded-xl p-4 overflow-x-auto">
                                        <pre class="text-xs text-green-400 font-mono">{{ json_encode($log->details, JSON_PRETTY_PRINT) }}</pre>
                                    </div>
                                    <div class="mt-6 flex justify-end">
                                        <button @click="$dispatch('close')" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-xl font-bold hover:bg-gray-200">Close</button>
                                    </div>
                                </div>
                            </x-modal>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-8 text-center text-gray-500 font-medium">No activity logs found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($data['logs']->hasPages())
        <div class="p-4 border-t border-gray-100">
            {{ $data['logs']->links() }}
        </div>
    @endif
</div>
