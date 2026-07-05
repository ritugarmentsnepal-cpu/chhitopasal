    <!-- Bulk Upload Spreadsheet Modal -->
    <div x-show="bulkModalOpen" x-cloak @mouseup.window="endDragFill(); endDragSelect()" class="fixed inset-0 z-50 overflow-auto" aria-labelledby="bulk-modal" role="dialog" aria-modal="true">
      <div class="flex items-start justify-center min-h-screen p-2 sm:p-4">
        <div x-show="bulkModalOpen" x-transition.opacity class="fixed inset-0 bg-gray-900/70 backdrop-blur-sm transition-opacity" @click="closeBulkModal()"></div>

        <div x-show="bulkModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" class="relative bg-white rounded-[1.5rem] shadow-2xl transform transition-all w-full max-w-[95vw] max-h-[90vh] flex flex-col z-10 my-4 select-none" style="overflow: hidden;">
          
          <!-- Header -->
          <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-blue-50/40 flex justify-between items-center flex-shrink-0">
            <div>
              <h3 class="text-xl font-black text-gray-900 flex items-center gap-2">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2" /></svg>
                Bulk Order Entry
              </h3>
              <p class="text-xs text-gray-500 mt-0.5">Fill in customer details like a spreadsheet. <span class="text-gray-400">Drag, Shift+Click, or ⌘/Ctrl+Click to select multiple cells.</span></p>
            </div>
            <div class="flex items-center gap-3">
              <span class="text-xs font-bold text-gray-500 bg-gray-100 px-3 py-1.5 rounded-lg">
                <span x-text="bulkRows.length"></span> rows
              </span>
              <button type="button" @click="closeBulkModal()" class="text-gray-400 hover:text-gray-900 transition p-1"><svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg></button>
            </div>
          </div>

          <!-- Spreadsheet Table -->
          <div class="flex-1 min-h-0" style="overflow: auto;">
            <table class="w-full text-sm border-collapse" id="bulk-spreadsheet" style="min-width: 1000px;">
              <thead class="sticky top-0 z-10">
                <tr class="bg-gray-800 text-white text-xs uppercase tracking-wider font-bold">
                  <th class="px-2 py-3 text-center w-12 border-r border-gray-700">#</th>
                  <th class="px-2 py-3 text-left min-w-[160px] border-r border-gray-700">Customer Name <span class="text-red-400">*</span></th>
                  <th class="px-2 py-3 text-left min-w-[130px] border-r border-gray-700">Phone <span class="text-red-400">*</span></th>
                  <th class="px-2 py-3 text-left min-w-[200px] border-r border-gray-700">Address <span class="text-red-400">*</span></th>
                  <th class="px-2 py-3 text-left min-w-[120px] border-r border-gray-700">City</th>
                  <th class="px-2 py-3 text-left min-w-[220px] border-r border-gray-700">Product <span class="text-red-400">*</span></th>
                  <th class="px-2 py-3 text-center min-w-[70px] border-r border-gray-700">Qty</th>
                  <th class="px-2 py-3 text-center min-w-[110px] border-r border-gray-700">Amount (Rs.)</th>
                  <th class="px-2 py-3 text-left min-w-[180px] border-r border-gray-700">Internal Remarks</th>
                  <th class="px-2 py-3 text-center w-14"></th>
                </tr>
              </thead>
              <tbody>
                <template x-for="(row, idx) in bulkRows" :key="idx">
                  <tr class="border-b border-gray-100 hover:bg-blue-50/30 transition-colors group" :class="row._error ? 'bg-red-50' : ''">
                    <td class="px-2 py-1 text-center text-xs font-bold border-r border-gray-100 relative" :class="row._error ? 'bg-red-100 text-red-600' : 'text-gray-400 bg-gray-50'">
                      <div class="flex items-center justify-center gap-1">
                        <span x-text="idx + 1"></span>
                        <template x-if="row._error">
                          <div class="relative group/err">
                            <svg class="w-3.5 h-3.5 text-red-500 cursor-help" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" /></svg>
                            <div class="absolute left-full ml-2 top-1/2 -translate-y-1/2 z-50 w-56 p-2 bg-red-600 text-white text-[11px] font-bold rounded-lg shadow-xl opacity-0 invisible group-hover/err:opacity-100 group-hover/err:visible transition-all pointer-events-none">
                              <div x-text="row._errorMsg"></div>
                              <div class="absolute right-full top-1/2 -translate-y-1/2 border-4 border-transparent border-r-red-600"></div>
                            </div>
                          </div>
                        </template>
                      </div>
                    </td>
                    <td class="px-1 py-1 border-r border-gray-100 relative group" @mousedown="cellMouseDown($event, idx, 0)" @mouseenter="cellMouseEnterSelect(idx, 0)" :class="isCellSelected(idx, 0) ? 'bg-blue-100/60 ring-1 ring-inset ring-blue-400' : ''">
                      <input type="text" x-model="row.customer_name" :data-row="idx" :data-col="0" @focus="activeCell = { row: idx, col: 0 }" @keydown.enter.prevent="moveFocusDown(idx, 0)" @paste="handlePaste($event, idx, 0)" @mouseenter="updateDragFill(idx, 0)" placeholder="Full Name" class="w-full border-0 bg-transparent focus:bg-white focus:ring-1 focus:ring-blue-400 rounded px-2 py-1.5 text-sm font-medium placeholder:text-gray-300 transition" :class="(isDraggingFill && dragFillStart?.col === 0 && idx >= Math.min(dragFillStart.row, dragFillEndRow) && idx <= Math.max(dragFillStart.row, dragFillEndRow)) ? 'bg-blue-100 ring-1 ring-blue-400' : ''">
                      <div x-show="activeCell.row === idx && activeCell.col === 0" class="absolute bottom-1 right-1 w-2.5 h-2.5 bg-blue-600 cursor-ns-resize z-20 hover:bg-blue-800 rounded-sm" @mousedown.prevent.stop="startDragFill(idx, 0, 'customer_name')"></div>
                    </td>
                    <td class="px-1 py-1 border-r border-gray-100 relative group" @mousedown="cellMouseDown($event, idx, 1)" @mouseenter="cellMouseEnterSelect(idx, 1)" :class="isCellSelected(idx, 1) ? 'bg-blue-100/60 ring-1 ring-inset ring-blue-400' : ''">
                      <input type="text" x-model="row.customer_phone" :data-row="idx" :data-col="1" @focus="activeCell = { row: idx, col: 1 }" @keydown.enter.prevent="moveFocusDown(idx, 1)" @paste="handlePaste($event, idx, 1)" @mouseenter="updateDragFill(idx, 1)" placeholder="98XXXXXXXX" class="w-full border-0 bg-transparent focus:bg-white focus:ring-1 focus:ring-blue-400 rounded px-2 py-1.5 text-sm font-medium placeholder:text-gray-300 transition" :class="(isDraggingFill && dragFillStart?.col === 1 && idx >= Math.min(dragFillStart.row, dragFillEndRow) && idx <= Math.max(dragFillStart.row, dragFillEndRow)) ? 'bg-blue-100 ring-1 ring-blue-400' : ''">
                      <div x-show="activeCell.row === idx && activeCell.col === 1" class="absolute bottom-1 right-1 w-2.5 h-2.5 bg-blue-600 cursor-ns-resize z-20 hover:bg-blue-800 rounded-sm" @mousedown.prevent.stop="startDragFill(idx, 1, 'customer_phone')"></div>
                    </td>
                    <td class="px-1 py-1 border-r border-gray-100 relative group" @mousedown="cellMouseDown($event, idx, 2)" @mouseenter="cellMouseEnterSelect(idx, 2)" :class="isCellSelected(idx, 2) ? 'bg-blue-100/60 ring-1 ring-inset ring-blue-400' : ''">
                      <input type="text" x-model="row.address" :data-row="idx" :data-col="2" @focus="activeCell = { row: idx, col: 2 }" @keydown.enter.prevent="moveFocusDown(idx, 2)" @paste="handlePaste($event, idx, 2)" @mouseenter="updateDragFill(idx, 2)" placeholder="Street address" class="w-full border-0 bg-transparent focus:bg-white focus:ring-1 focus:ring-blue-400 rounded px-2 py-1.5 text-sm font-medium placeholder:text-gray-300 transition" :class="(isDraggingFill && dragFillStart?.col === 2 && idx >= Math.min(dragFillStart.row, dragFillEndRow) && idx <= Math.max(dragFillStart.row, dragFillEndRow)) ? 'bg-blue-100 ring-1 ring-blue-400' : ''">
                      <div x-show="activeCell.row === idx && activeCell.col === 2" class="absolute bottom-1 right-1 w-2.5 h-2.5 bg-blue-600 cursor-ns-resize z-20 hover:bg-blue-800 rounded-sm" @mousedown.prevent.stop="startDragFill(idx, 2, 'address')"></div>
                    </td>
                    <td class="px-1 py-1 border-r border-gray-100 relative group" @mousedown="cellMouseDown($event, idx, 3)" @mouseenter="cellMouseEnterSelect(idx, 3)" :class="isCellSelected(idx, 3) ? 'bg-blue-100/60 ring-1 ring-inset ring-blue-400' : ''">
                      <input type="text" x-model="row.city" :data-row="idx" :data-col="3" @focus="activeCell = { row: idx, col: 3 }" @keydown.enter.prevent="moveFocusDown(idx, 3)" @paste="handlePaste($event, idx, 3)" @mouseenter="updateDragFill(idx, 3)" placeholder="City" class="w-full border-0 bg-transparent focus:bg-white focus:ring-1 focus:ring-blue-400 rounded px-2 py-1.5 text-sm font-medium placeholder:text-gray-300 transition" :class="(isDraggingFill && dragFillStart?.col === 3 && idx >= Math.min(dragFillStart.row, dragFillEndRow) && idx <= Math.max(dragFillStart.row, dragFillEndRow)) ? 'bg-blue-100 ring-1 ring-blue-400' : ''">
                      <div x-show="activeCell.row === idx && activeCell.col === 3" class="absolute bottom-1 right-1 w-2.5 h-2.5 bg-blue-600 cursor-ns-resize z-20 hover:bg-blue-800 rounded-sm" @mousedown.prevent.stop="startDragFill(idx, 3, 'city')"></div>
                    </td>
                    <td class="px-1 py-1 border-r border-gray-100 relative group" @mousedown="cellMouseDown($event, idx, 4)" @mouseenter="cellMouseEnterSelect(idx, 4)" :class="isCellSelected(idx, 4) ? 'bg-blue-100/60 ring-1 ring-inset ring-blue-400' : ''">
                      <select x-model="row.product_selection" :data-row="idx" :data-col="4" @focus="activeCell = { row: idx, col: 4 }" @keydown.enter.prevent="moveFocusDown(idx, 4)" @change="onBulkProductChange(idx)" @paste="handlePaste($event, idx, 4)" @mouseenter="updateDragFill(idx, 4)" class="w-full border-0 bg-transparent focus:bg-white focus:ring-1 focus:ring-blue-400 rounded px-1 py-1.5 text-sm font-medium transition cursor-pointer" :class="(isDraggingFill && dragFillStart?.col === 4 && idx >= Math.min(dragFillStart.row, dragFillEndRow) && idx <= Math.max(dragFillStart.row, dragFillEndRow)) ? 'bg-blue-100 ring-1 ring-blue-400' : ''">
                        <option value="">-- Select Product --</option>
                        @foreach($products as $product)
                          <option value="{{ $product->id }}:1:{{ $product->price }}">{{ $product->name }} (Rs.{{ number_format($product->price) }})</option>
                        @endforeach
                      </select>
                      <div x-show="activeCell.row === idx && activeCell.col === 4" class="absolute bottom-2 right-4 w-2.5 h-2.5 bg-blue-600 cursor-ns-resize z-20 hover:bg-blue-800 rounded-sm" @mousedown.prevent.stop="startDragFill(idx, 4, 'product_selection')"></div>
                    </td>
                    <td class="px-1 py-1 border-r border-gray-100 relative group" @mousedown="cellMouseDown($event, idx, 5)" @mouseenter="cellMouseEnterSelect(idx, 5)" :class="isCellSelected(idx, 5) ? 'bg-blue-100/60 ring-1 ring-inset ring-blue-400' : ''">
                      <input type="number" x-model.number="row.quantity" :data-row="idx" :data-col="5" @focus="activeCell = { row: idx, col: 5 }" @keydown.enter.prevent="moveFocusDown(idx, 5)" min="1" @input="onBulkQtyChange(idx)" @paste="handlePaste($event, idx, 5)" @mouseenter="updateDragFill(idx, 5)" class="w-full border-0 bg-transparent focus:bg-white focus:ring-1 focus:ring-blue-400 rounded px-2 py-1.5 text-sm font-bold text-center placeholder:text-gray-300 transition" :class="(isDraggingFill && dragFillStart?.col === 5 && idx >= Math.min(dragFillStart.row, dragFillEndRow) && idx <= Math.max(dragFillStart.row, dragFillEndRow)) ? 'bg-blue-100 ring-1 ring-blue-400' : ''">
                      <div x-show="activeCell.row === idx && activeCell.col === 5" class="absolute bottom-1 right-1 w-2.5 h-2.5 bg-blue-600 cursor-ns-resize z-20 hover:bg-blue-800 rounded-sm" @mousedown.prevent.stop="startDragFill(idx, 5, 'quantity')"></div>
                    </td>
                    <td class="px-1 py-1 border-r border-gray-100 relative group" @mousedown="cellMouseDown($event, idx, 6)" @mouseenter="cellMouseEnterSelect(idx, 6)" :class="isCellSelected(idx, 6) ? 'bg-blue-100/60 ring-1 ring-inset ring-blue-400' : ''">
                      <input type="number" x-model.number="row.amount" :data-row="idx" :data-col="6" @focus="activeCell = { row: idx, col: 6 }" @keydown.enter.prevent="moveFocusDown(idx, 6)" step="0.01" min="0" @paste="handlePaste($event, idx, 6)" @mouseenter="updateDragFill(idx, 6)" class="w-full border-0 bg-transparent focus:bg-white focus:ring-1 focus:ring-blue-400 rounded px-2 py-1.5 text-sm font-bold text-center placeholder:text-gray-300 transition" :class="(isDraggingFill && dragFillStart?.col === 6 && idx >= Math.min(dragFillStart.row, dragFillEndRow) && idx <= Math.max(dragFillStart.row, dragFillEndRow)) ? 'bg-blue-100 ring-1 ring-blue-400' : ''">
                      <div x-show="activeCell.row === idx && activeCell.col === 6" class="absolute bottom-1 right-1 w-2.5 h-2.5 bg-blue-600 cursor-ns-resize z-20 hover:bg-blue-800 rounded-sm" @mousedown.prevent.stop="startDragFill(idx, 6, 'amount')"></div>
                    </td>
                    <td class="px-1 py-1 border-r border-gray-100 relative group" @mousedown="cellMouseDown($event, idx, 7)" @mouseenter="cellMouseEnterSelect(idx, 7)" :class="isCellSelected(idx, 7) ? 'bg-blue-100/60 ring-1 ring-inset ring-blue-400' : ''">
                      <input type="text" x-model="row.remarks" :data-row="idx" :data-col="7" @focus="activeCell = { row: idx, col: 7 }" @keydown.enter.prevent="moveFocusDown(idx, 7)" @paste="handlePaste($event, idx, 7)" @mouseenter="updateDragFill(idx, 7)" placeholder="Notes..." class="w-full border-0 bg-transparent focus:bg-white focus:ring-1 focus:ring-blue-400 rounded px-2 py-1.5 text-sm font-medium placeholder:text-gray-300 transition" :class="(isDraggingFill && dragFillStart?.col === 7 && idx >= Math.min(dragFillStart.row, dragFillEndRow) && idx <= Math.max(dragFillStart.row, dragFillEndRow)) ? 'bg-blue-100 ring-1 ring-blue-400' : ''">
                      <div x-show="activeCell.row === idx && activeCell.col === 7" class="absolute bottom-1 right-1 w-2.5 h-2.5 bg-blue-600 cursor-ns-resize z-20 hover:bg-blue-800 rounded-sm" @mousedown.prevent.stop="startDragFill(idx, 7, 'remarks')"></div>
                    </td>
                    <td class="px-1 py-1 text-center">
                      <button type="button" @click="removeBulkRow(idx)" class="p-1 text-gray-300 hover:text-red-500 hover:bg-red-50 rounded transition opacity-0 group-hover:opacity-100" :class="bulkRows.length <= 1 ? 'invisible' : ''">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                      </button>
                    </td>
                  </tr>
                </template>
              </tbody>
            </table>
          </div>

          <!-- Multi-cell Selection Action Bar -->
          <div x-show="selectionActionBar && selectedCells.length > 1" x-cloak
             x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-2"
             class="sticky bottom-0 z-20 px-4 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 border-t border-blue-500 flex items-center justify-between gap-3 shadow-lg">
            <div class="flex items-center gap-2">
              <div class="flex items-center gap-1.5 text-white/90 text-xs font-bold">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" /></svg>
                <span x-text="getSelectionSummary()"></span>
              </div>
            </div>
            <div class="flex items-center gap-2">
              <!-- Clear selected cells -->
              <button type="button" @click="clearSelectedCells()" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white/15 hover:bg-white/25 text-white text-xs font-bold rounded-lg transition active:scale-95 backdrop-blur-sm border border-white/20">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                Clear
              </button>
              <!-- Copy selected cells -->
              <button type="button" @click="copySelectedCells()" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white/15 hover:bg-white/25 text-white text-xs font-bold rounded-lg transition active:scale-95 backdrop-blur-sm border border-white/20">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg>
                Copy
              </button>
              <!-- Fill down from first cell -->
              <button type="button" @click="fillSelectedFromFirst()" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white/15 hover:bg-white/25 text-white text-xs font-bold rounded-lg transition active:scale-95 backdrop-blur-sm border border-white/20">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                Fill Down
              </button>
              <!-- Deselect -->
              <button type="button" @click="clearCellSelection()" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white/10 hover:bg-white/20 text-white/70 text-xs font-bold rounded-lg transition active:scale-95 border border-white/10">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                Deselect
              </button>
            </div>
          </div>

          <!-- Toast Notification -->
          <div x-show="_toastVisible" x-cloak
             x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-4"
             class="fixed bottom-6 left-1/2 -translate-x-1/2 z-50 px-4 py-2 bg-gray-900 text-white text-sm font-bold rounded-xl shadow-xl flex items-center gap-2">
            <svg class="w-4 h-4 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
            <span x-text="_toastMsg"></span>
          </div>

          <!-- Footer -->
          <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 flex flex-wrap items-center justify-between gap-3 flex-shrink-0">
            <div class="flex items-center gap-3">
              <button type="button" @click="addBulkRow()" class="px-4 py-2 bg-white border border-gray-200 text-gray-700 font-bold rounded-xl text-sm hover:bg-gray-50 transition active:scale-95 flex items-center gap-1.5 shadow-sm">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                Add Row
              </button>
              <button type="button" @click="addBulkRows(5)" class="px-4 py-2 bg-white border border-gray-200 text-gray-700 font-bold rounded-xl text-sm hover:bg-gray-50 transition active:scale-95 shadow-sm">
                +5 Rows
              </button>
              <button type="button" @click="addBulkRows(10)" class="px-4 py-2 bg-white border border-gray-200 text-gray-700 font-bold rounded-xl text-sm hover:bg-gray-50 transition active:scale-95 shadow-sm">
                +10 Rows
              </button>
            </div>
            <div class="flex items-center gap-3">
              <div class="text-sm font-bold text-gray-600 mr-2">
                Total: <span class="text-lg text-gray-900" x-text="'Rs. ' + bulkGrandTotal().toLocaleString()"></span>
                <span class="text-xs text-gray-400 ml-1">(<span x-text="bulkFilledRows()"></span> valid orders)</span>
              </div>
              <button type="button" @click="closeBulkModal()" class="px-5 py-2.5 bg-white border border-gray-200 text-gray-700 font-bold rounded-xl hover:bg-gray-50 transition">Cancel</button>
              <button type="button" @click="submitBulkOrders()" :disabled="bulkSubmitting" class="px-6 py-2.5 bg-gray-900 text-white font-bold rounded-xl shadow-lg hover:bg-gray-800 transition active:scale-95 flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                <svg x-show="bulkSubmitting" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                <span x-text="bulkSubmitting ? 'Creating...' : 'Create All Orders'"></span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

