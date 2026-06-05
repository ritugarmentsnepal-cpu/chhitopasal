<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Pathao Financial Reconciliation') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                
                <h3 class="text-lg font-medium text-gray-900 mb-4">Import Pathao Settlement Statement</h3>
                <p class="text-sm text-gray-600 mb-6">
                    Upload the CSV or Excel file provided by Pathao for your weekly settlement. The system will automatically parse the Consignment IDs, match them with your delivered orders, and highlight any discrepancies in collected COD amounts or delivery fees.
                </p>

                <form action="{{ route('pathao.reconcile.preview') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Statement File (.csv, .xlsx)</label>
                        <input type="file" name="statement_file" accept=".csv,.xlsx,.xls" required
                            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                        @error('statement_file')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end">
                        <a href="{{ route('pathao.index') }}" class="mr-4 px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300">Cancel</a>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded shadow hover:bg-indigo-700">Preview Reconciliation</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>
