<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
      {{ __('Reconciliation Preview') }}
    </h2>
  </x-slot>

  <div class="py-12">
    <div class="max-w-full mx-auto sm:px-6 lg:px-8">
      <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
        
        <div class="flex justify-between items-center mb-6">
          <div>
            <h3 class="text-lg font-medium text-gray-900">Statement Preview</h3>
            <p class="text-sm text-gray-600">
              Found {{ $totalOrdersFound }} matching orders. {{ $totalDiscrepancies }} discrepancies detected.
            </p>
          </div>
          <div>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 mr-2">Matched</span>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 mr-2">Warning</span>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Discrepancy / Not Found</span>
          </div>
        </div>

        <form action="{{ route('pathao.reconcile.process') }}" method="POST">
          @csrf

          <div class="overflow-x-auto mb-6">
            <table class="min-w-full divide-y divide-gray-200 border">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Consignment ID</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order ID</th>
                  <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Pathao COD</th>
                  <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Expected COD</th>
                  <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Pathao Fee</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status/Reason</th>
                  <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                @foreach($previewData as $row)
                  @php
                    $bgColor = '';
                    if ($row['status_color'] === 'green') $bgColor = 'bg-green-50';
                    elseif ($row['status_color'] === 'yellow') $bgColor = 'bg-yellow-50';
                    elseif ($row['status_color'] === 'red') $bgColor = 'bg-red-50';
                  @endphp
                  <tr class="{{ $bgColor }}">
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ $row['consignment_id'] }}</td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                      @if($row['order'])
                        <a href="{{ route('orders.show', $row['order']->id) }}" target="_blank" class="text-blue-600 hover:underline">#{{ $row['order']->id }}</a>
                      @else
                        <span class="text-gray-400">N/A</span>
                      @endif
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-right font-medium text-gray-900">
                      Rs. {{ number_format($row['collected'], 2) }}
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-right text-gray-500">
                      @if($row['order'])
                        Rs. {{ number_format($row['order']->total_amount, 2) }}
                      @else
                        -
                      @endif
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-right text-gray-500">
                      Rs. {{ number_format($row['delivery_fee'], 2) }}
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-700">
                      {{ $row['reasons'] ?: 'Matched OK' }}
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-center text-sm">
                      @if($row['order'])
                        <select name="actions[{{ $row['row_index'] }}]" class="text-sm rounded border-gray-300">
                          @if($row['status_color'] === 'green')
                            <option value="settle" selected>Settle</option>
                            <option value="dispute">Dispute</option>
                            <option value="ignore">Ignore</option>
                          @else
                            <option value="dispute" selected>Dispute</option>
                            <option value="settle">Force Settle</option>
                            <option value="ignore">Ignore</option>
                          @endif
                        </select>
                      @else
                        <span class="text-gray-400 italic">Not Found</span>
                      @endif
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>

          <div class="bg-gray-50 p-4 rounded border mt-4">
            <h4 class="font-medium text-gray-900 mb-2">Finalize Accounting</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700">Settlement Date</label>
                <input type="date" name="date" required value="{{ date('Y-m-d') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">Deposit Bank Account</label>
                <select name="account_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                  @foreach(\App\Models\Account::all() as $acc)
                    <option value="{{ $acc->id }}">{{ $acc->name }} (Balance: Rs. {{ number_format($acc->balance, 2) }})</option>
                  @endforeach
                </select>
              </div>
            </div>
          </div>

          <div class="mt-6 flex justify-end">
            <a href="{{ route('pathao.reconcile.index') }}" class="mr-4 px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300">Cancel & Go Back</a>
            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded shadow hover:bg-indigo-700">Process Selected Rows</button>
          </div>
        </form>

      </div>
    </div>
  </div>
</x-app-layout>
