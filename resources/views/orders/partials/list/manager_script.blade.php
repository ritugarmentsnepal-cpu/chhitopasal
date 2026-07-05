  <!-- AlpineJS Logic -->
  <script>
    document.addEventListener('alpine:init', () => {
      Alpine.data('orderManager', () => ({
        selectedOrders: [],
        confirmModalOpen: false,
        selectedOrder: null,
        
        paymentModalOpen: false,
        paymentOrder: null,
        paymentAmount: 0,
        paymentMethod: 'cod',

        // Return Verification Modal State
        returnModalOpen: false,
        returnOrder: null,
        returnItems: [],
        returnNotes: '',
        returnSubmitting: false,

        trackingModalOpen: false,
        trackingData: null,
        trackingLoading: false,
        trackingOrderId: null,
        trackingSteps: ['Pickup', 'In Transit', 'At Hub', 'Out for Delivery', 'Delivered'],
        refreshCooldown: 0,
        refreshTimer: null,
        autoRefreshRunning: false,
        autoRefreshProgress: '',
        
        productPrices: {
          @foreach($products as $product)
            '{{ $product->id }}:1': {{ $product->price }},
            @if($product->bundles)
              @foreach($product->bundles as $bundle)
                '{{ $product->id }}:{{ $bundle['qty'] }}': {{ $bundle['price'] / $bundle['qty'] }},
              @endforeach
            @endif
          @endforeach
        },
        cities: [],
        zones: [],
        areas: [],

        formData: {
          customer_name: '',
          customer_phone: '',
          address: '',
          pathao_city_id: '',
          pathao_zone_id: ''
        },

        editModalOpen: false,
        selectedEditOrder: null,
        isConfirming: false,
        editFormData: {
          customer_name: '',
          customer_phone: '',
          city: '',
          address: '',
          pathao_city_id: '',
          pathao_zone_id: '',
          status: '',
          items: [],
          delivery_charge: 0,
          remarks: ''
        },
        editZones: [],

        // Bulk Spreadsheet State
        bulkModalOpen: false,
        bulkSubmitting: false,
        bulkProcessing: false, // UX-05: Loading state for bulk ship/delete
        bulkRows: [],

        // Google Sheets Features State
        activeCell: { row: null, col: null },
        isDraggingFill: false,
        dragFillStart: null,
        dragFillEndRow: null,

        // Multi-cell selection state
        selectedCells: [],      // Array of {row, col} objects
        selectionAnchor: null,    // Starting cell for shift-click range
        isDragSelecting: false,   // Whether user is drag-selecting
        dragSelectStart: null,    // Start cell of drag selection
        selectionActionBar: false,  // Show floating action bar
        _colFields: ['customer_name', 'customer_phone', 'address', 'city', 'product_selection', 'quantity', 'amount', 'remarks'],

        async init() {
          // Pre-fetch cities when dashboard loads
          try {
            const res = await fetch('{{ url("api/pathao/cities") }}');
            this.cities = await res.json();
          } catch (e) {
            console.error('Failed to load cities', e);
          }
          // Listen for bulk modal open event from header button
          window.addEventListener('open-bulk-modal', () => this.openBulkModal());

          // Auto-refresh Pathao statuses on shipped tab
          @if($status === 'shipped')
            this.autoRefreshPathaoStatuses();
          @endif

          // Keyboard shortcuts for multi-cell selection in bulk modal
          document.addEventListener('keydown', (e) => {
            if (!this.bulkModalOpen || this.selectedCells.length === 0) return;
            const active = document.activeElement;
            const isTyping = active && (active.tagName === 'INPUT' || active.tagName === 'TEXTAREA' || active.tagName === 'SELECT');

            // Delete/Backspace: clear selected cells (only when not actively typing)
            if ((e.key === 'Delete' || e.key === 'Backspace') && !isTyping) {
              e.preventDefault();
              this.clearSelectedCells();
            }
            // Ctrl/Cmd + C: copy selected cells
            if ((e.ctrlKey || e.metaKey) && e.key === 'c' && !isTyping) {
              e.preventDefault();
              this.copySelectedCells();
            }
            // Escape: clear selection
            if (e.key === 'Escape') {
              this.clearCellSelection();
            }
          });
        },

        selectAll() {
          const checkboxes = document.querySelectorAll('.order-checkbox');
          this.selectedOrders = Array.from(checkboxes).map(cb => cb.value);
        },

        deselectAll() {
          this.selectedOrders = [];
        },

        calculateEditGrandTotal() {
          let itemsTotal = 0;
          if (this.editFormData.items) {
            this.editFormData.items.forEach(item => {
              itemsTotal += parseFloat(item.total_price || 0);
            });
          }
          let delivery = parseFloat(this.editFormData.delivery_charge || 0);
          return (itemsTotal + delivery).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        },

        addProduct() {
          this.editFormData.items.push({
            id: null,
            product_id: '',
            selection: '',
            quantity: 1,
            unit_price: 0,
            total_price: 0
          });
        },

        removeProduct(index) {
          this.editFormData.items.splice(index, 1);
        },

        onProductChange(index) {
          const item = this.editFormData.items[index];
          if (!item.selection) return;
          const [productId, bundleQty] = item.selection.split(':');
          item.product_id = productId;
          const unitPrice = this.productPrices[item.selection] || 0;
          const qty = parseInt(bundleQty) || 1;
          item.unit_price = unitPrice;
          item.quantity = qty;
          item.total_price = unitPrice * qty;
        },

        onQuantityChange(index) {
          const item = this.editFormData.items[index];
          const qty = parseInt(item.quantity) || 1;
          if (item.unit_price) {
            item.total_price = item.unit_price * qty;
          }
        },

        openEditModal(order) {
          this.selectedEditOrder = order;
          this.editFormData.customer_name = order.customer_name;
          this.editFormData.customer_phone = order.customer_phone;
          this.editFormData.city = order.city || '';
          this.editFormData.address = order.address || '';
          this.editFormData.pathao_city_id = order.pathao_city_id || '';
          this.editFormData.pathao_zone_id = order.pathao_zone_id || '';
          this.editFormData.status = order.status || '';
          this.editFormData.delivery_charge = parseFloat(order.delivery_charge || 0);
          this.editFormData.remarks = order.remarks || '';
          
          if(this.editFormData.pathao_city_id) {
            this.fetchEditZones();
          }
          
          this.editFormData.items = order.order_items.map(item => {
            const exactSelection = `${item.product_id}:${item.quantity}`;
            // Check if this exact combo exists in productPrices (i.e., it's a valid bundle or single)
            const selection = this.productPrices[exactSelection] !== undefined ? exactSelection : `${item.product_id}:1`;
            return {
              id: item.id,
              product_id: String(item.product_id),
              selection: selection,
              quantity: item.quantity,
              unit_price: item.price_at_purchase,
              total_price: item.quantity * item.price_at_purchase,
              color: item.color || '',
              size: item.size || ''
            };
          });
          
          this.editModalOpen = true;
        },

        closeEditModal() {
          this.editModalOpen = false;
          setTimeout(() => { this.selectedEditOrder = null; this.isConfirming = false; }, 300);
        },

        validateEditForm(e) {
          const isStatusConfirmed = this.editFormData.status === 'confirmed' || this.editFormData.status === 'shipped' || this.editFormData.status === 'delivered';
          
          if (this.isConfirming || isStatusConfirmed) {
            if (!this.editFormData.pathao_city_id || !this.editFormData.pathao_zone_id) {
              e.preventDefault();
              alert('Pathao City and Zone are required when confirming or shipping an order.');
              return false;
            }
          }
        },

        openPaymentModal(order) {
          this.paymentOrder = order;
          let remaining = parseFloat(order.total_amount) - parseFloat(order.paid_amount || 0);
          this.paymentAmount = remaining > 0 ? remaining : order.total_amount;
          this.paymentMethod = order.payment_method || 'cod';
          if (remaining <= 0) {
            this.paymentMethod = 'paid';
          }
          this.paymentModalOpen = true;
        },

        closePaymentModal() {
          this.paymentModalOpen = false;
          setTimeout(() => { this.paymentOrder = null; }, 300);
        },

        async fetchZones() {
          this.zones = [];
          this.formData.pathao_zone_id = '';
          if (!this.formData.pathao_city_id) return;

          try {
            const res = await fetch(`{{ url('api/pathao/zones') }}/${this.formData.pathao_city_id}`);
            this.zones = await res.json();
          } catch (e) { console.error(e); }
        },

        async fetchEditZones() {
          this.editZones = [];
          const savedZone = this.editFormData.pathao_zone_id;
          if (!this.editFormData.pathao_city_id) return;

          try {
            const res = await fetch(`{{ url('api/pathao/zones') }}/${this.editFormData.pathao_city_id}`);
            this.editZones = await res.json();
            // Restore the saved zone after dropdown options are loaded
            this.$nextTick(() => { this.editFormData.pathao_zone_id = savedZone; });
          } catch (e) { console.error(e); }
        },

        // === Tracking Modal Methods ===
        async openTrackingModal(orderId) {
          this.trackingOrderId = orderId;
          this.trackingModalOpen = true;
          this.trackingLoading = true;
          this.trackingData = null;
          try {
            const res = await fetch(`{{ url('orders') }}/${orderId}/pathao-details`);
            this.trackingData = await res.json();
          } catch (e) {
            console.error('Failed to load tracking data', e);
          }
          this.trackingLoading = false;
        },

        closeTrackingModal() {
          this.trackingModalOpen = false;
          this.refreshCooldown = 0;
          if (this.refreshTimer) { clearInterval(this.refreshTimer); this.refreshTimer = null; }
          setTimeout(() => { this.trackingData = null; this.trackingOrderId = null; }, 300);
        },

        async refreshTrackingStatus() {
          if (!this.trackingOrderId || this.refreshCooldown > 0) return;
          this.trackingLoading = true;
          try {
            const res = await fetch(`{{ url('orders') }}/${this.trackingOrderId}/pathao-details`);
            this.trackingData = await res.json();
          } catch (e) { console.error(e); }
          this.trackingLoading = false;

          // Start 10-second cooldown
          this.refreshCooldown = 10;
          this.refreshTimer = setInterval(() => {
            this.refreshCooldown--;
            if (this.refreshCooldown <= 0) {
              clearInterval(this.refreshTimer);
              this.refreshTimer = null;
            }
          }, 1000);
        },

        // Progressive auto-refresh: fetch live Pathao status for each visible order
        async autoRefreshPathaoStatuses() {
          const badges = document.querySelectorAll('.pathao-status-badge');
          if (badges.length === 0) return;

          this.autoRefreshRunning = true;
          const total = badges.length;
          let current = 0;

          const badgeClassMap = {
            'delivered': 'bg-green-50 text-green-700 border-green-200',
            'successful': 'bg-green-50 text-green-700 border-green-200',
            'transit': 'bg-indigo-50 text-indigo-700 border-indigo-200',
            'in transit': 'bg-indigo-50 text-indigo-700 border-indigo-200',
            'picked': 'bg-blue-50 text-blue-700 border-blue-200',
            'pickup': 'bg-blue-50 text-blue-700 border-blue-200',
            'hub': 'bg-purple-50 text-purple-700 border-purple-200',
            'sorting': 'bg-purple-50 text-purple-700 border-purple-200',
            'last mile': 'bg-purple-50 text-purple-700 border-purple-200',
            'out for': 'bg-orange-50 text-orange-700 border-orange-200',
            'return': 'bg-red-50 text-red-700 border-red-200',
            'cancel': 'bg-gray-100 text-gray-600 border-gray-200',
          };
          const defaultClass = 'bg-yellow-50 text-yellow-700 border-yellow-200';
          const allBadgeClasses = [...new Set(Object.values(badgeClassMap).concat([defaultClass]).join(' ').split(' '))];

          for (const badge of badges) {
            current++;
            this.autoRefreshProgress = `Syncing ${current}/${total}...`;
            const orderId = badge.dataset.orderId;
            if (!orderId) continue;

            try {
              const res = await fetch(`{{ url('orders') }}/${orderId}/pathao-details`);
              if (res.ok) {
                const data = await res.json();
                const status = data?.pathao?.status || 'Awaiting Pickup';
                const statusLower = status.toLowerCase();

                // Update badge text
                const textEl = badge.querySelector('.pathao-status-text');
                if (textEl) textEl.textContent = status;

                // Update badge color
                badge.classList.remove(...allBadgeClasses);
                let matchedClass = defaultClass;
                for (const [key, cls] of Object.entries(badgeClassMap)) {
                  if (statusLower.includes(key)) { matchedClass = cls; break; }
                }
                badge.classList.add(...matchedClass.split(' '));

                // Update timestamp
                const updatedEl = document.getElementById(`pathao-updated-${orderId}`);
                if (updatedEl) updatedEl.textContent = data?.status_updated_at || 'just now';
              }
            } catch (e) {
              console.error(`Failed to refresh status for order ${orderId}`, e);
            }

            // 1.5 second delay between API calls
            if (current < total) {
              await new Promise(r => setTimeout(r, 1500));
            }
          }

          this.autoRefreshRunning = false;
          this.autoRefreshProgress = '';
        },

        getStepIndex() {
          const status = (this.trackingData?.pathao?.status || '').toLowerCase();
          if (status.includes('delivered') || status.includes('successful')) return 4;
          if (status.includes('out for')) return 3;
          if (status.includes('hub') || status.includes('sorting') || status.includes('last mile')) return 2;
          if (status.includes('transit') || status.includes('in transit')) return 1;
          if (status.includes('picked') || status.includes('pickup')) return 0;
          return -1; // awaiting pickup
        },

        getStatusBadgeClass(status) {
          const s = (status || '').toLowerCase();
          if (s.includes('delivered')) return 'bg-green-100 text-green-800';
          if (s.includes('transit')) return 'bg-indigo-100 text-indigo-800';
          if (s.includes('picked') || s.includes('pickup')) return 'bg-blue-100 text-blue-800';
          if (s.includes('hub') || s.includes('sorting')) return 'bg-purple-100 text-purple-800';
          if (s.includes('out for')) return 'bg-orange-100 text-orange-800';
          if (s.includes('return')) return 'bg-red-100 text-red-800';
          if (s.includes('cancel')) return 'bg-gray-100 text-gray-600';
          return 'bg-yellow-100 text-yellow-800';
        },

        copyTrackingId() {
          const id = this.trackingData?.order?.pathao_consignment_id;
          if (id) {
            navigator.clipboard.writeText(id);
            alert('Tracking ID copied: ' + id);
          }
        },

        // === Bulk Spreadsheet Methods ===
        emptyBulkRow() {
          return { customer_name: '', customer_phone: '', address: '', city: '', product_selection: '', product_id: '', quantity: 1, amount: 0, unit_price: 0, remarks: '', _error: false, _errorMsg: '' };
        },

        openBulkModal() {
          if (this.bulkRows.length === 0) {
            this.bulkRows = Array.from({ length: 5 }, () => this.emptyBulkRow());
          }
          this.bulkModalOpen = true;
        },

        closeBulkModal() {
          this.bulkModalOpen = false;
          this.clearCellSelection();
        },

        addBulkRow() {
          this.bulkRows.push(this.emptyBulkRow());
        },

        addBulkRows(n) {
          for (let i = 0; i < n; i++) this.bulkRows.push(this.emptyBulkRow());
        },

        removeBulkRow(idx) {
          if (this.bulkRows.length > 1) this.bulkRows.splice(idx, 1);
        },

        onBulkProductChange(idx) {
          const row = this.bulkRows[idx];
          if (!row.product_selection) { row.product_id = ''; row.amount = 0; row.unit_price = 0; return; }
          const parts = row.product_selection.split(':');
          row.product_id = parts[0];
          const bundleQty = parseInt(parts[1]) || 1;
          const bundlePrice = parseFloat(parts[2]) || 0;
          row.quantity = bundleQty;
          row.unit_price = bundleQty > 0 ? bundlePrice / bundleQty : 0;
          row.amount = bundlePrice;
        },

        onBulkQtyChange(idx) {
          const row = this.bulkRows[idx];
          if (row.unit_price > 0) {
            row.amount = row.unit_price * (parseInt(row.quantity) || 1);
          }
        },

        bulkGrandTotal() {
          return this.bulkRows.reduce((sum, r) => sum + (parseFloat(r.amount) || 0), 0);
        },

        bulkFilledRows() {
          return this.bulkRows.filter(r => r.customer_name && r.customer_phone && r.address && r.product_id).length;
        },

        moveFocusDown(rowIdx, colIdx) {
          const nextRowIdx = rowIdx + 1;
          if (nextRowIdx >= this.bulkRows.length) {
            this.addBulkRow();
          }
          this.$nextTick(() => {
            const nextInput = document.querySelector(`[data-row="${nextRowIdx}"][data-col="${colIdx}"]`);
            if (nextInput) {
              nextInput.focus();
              if (nextInput.tagName === 'INPUT' && nextInput.type !== 'number') {
                try { nextInput.select(); } catch(e) {}
              }
            }
          });
        },

        startDragFill(rowIdx, colIdx, field) {
          this.isDraggingFill = true;
          this.dragFillStart = { row: rowIdx, col: colIdx, field: field };
          this.dragFillEndRow = rowIdx;
        },

        updateDragFill(rowIdx, colIdx) {
          if (this.isDraggingFill && this.dragFillStart && colIdx === this.dragFillStart.col) {
            this.dragFillEndRow = rowIdx;
          }
        },

        endDragFill() {
          if (!this.isDraggingFill) return;
          this.isDraggingFill = false;
          
          if (!this.dragFillStart || this.dragFillEndRow === null) return;

          const startRow = Math.min(this.dragFillStart.row, this.dragFillEndRow);
          const endRow = Math.max(this.dragFillStart.row, this.dragFillEndRow);
          
          if (startRow === endRow) return;

          const field = this.dragFillStart.field;
          const valueToCopy = this.bulkRows[this.dragFillStart.row][field];

          for (let i = startRow; i <= endRow; i++) {
            if (i === this.dragFillStart.row) continue;
            this.bulkRows[i][field] = valueToCopy;
            if (field === 'quantity') this.onBulkQtyChange(i);
            if (field === 'product_selection') this.onBulkProductChange(i);
          }
          this.dragFillStart = null;
          this.dragFillEndRow = null;
        },

        // === Multi-cell Selection Methods ===
        isCellSelected(row, col) {
          return this.selectedCells.some(c => c.row === row && c.col === col);
        },

        cellMouseDown(e, row, col) {
          // Don't interfere with drag-fill handle
          if (this.isDraggingFill) return;
          // Don't start selection if clicking inside an input/select that's already focused
          const target = e.target;
          if (target.tagName === 'INPUT' || target.tagName === 'SELECT' || target.tagName === 'TEXTAREA') {
            // If already focused on this cell, let native behavior work
            if (this.activeCell.row === row && this.activeCell.col === col && !e.shiftKey && !e.ctrlKey && !e.metaKey) {
              return;
            }
          }

          if (e.shiftKey && this.selectionAnchor) {
            // Shift+click: extend selection from anchor to current cell
            e.preventDefault();
            this.selectRange(this.selectionAnchor.row, this.selectionAnchor.col, row, col);
          } else if (e.ctrlKey || e.metaKey) {
            // Ctrl/Cmd+click: toggle individual cell
            e.preventDefault();
            this.toggleCellSelection(row, col);
          } else {
            // Normal click: start fresh selection + begin drag
            this.selectedCells = [{ row, col }];
            this.selectionAnchor = { row, col };
            this.isDragSelecting = true;
            this.dragSelectStart = { row, col };
          }
          this.selectionActionBar = this.selectedCells.length > 1;
        },

        cellMouseEnterSelect(row, col) {
          if (!this.isDragSelecting || !this.dragSelectStart) return;
          this.selectRange(this.dragSelectStart.row, this.dragSelectStart.col, row, col);
        },

        endDragSelect() {
          if (this.isDragSelecting) {
            this.isDragSelecting = false;
            this.selectionActionBar = this.selectedCells.length > 1;
          }
        },

        selectRange(r1, c1, r2, c2) {
          const minR = Math.min(r1, r2), maxR = Math.max(r1, r2);
          const minC = Math.min(c1, c2), maxC = Math.max(c1, c2);
          this.selectedCells = [];
          for (let r = minR; r <= maxR; r++) {
            for (let c = minC; c <= maxC; c++) {
              this.selectedCells.push({ row: r, col: c });
            }
          }
          this.selectionActionBar = this.selectedCells.length > 1;
        },

        toggleCellSelection(row, col) {
          const idx = this.selectedCells.findIndex(c => c.row === row && c.col === col);
          if (idx >= 0) {
            this.selectedCells.splice(idx, 1);
          } else {
            this.selectedCells.push({ row, col });
            this.selectionAnchor = { row, col };
          }
          this.selectionActionBar = this.selectedCells.length > 1;
        },

        clearCellSelection() {
          this.selectedCells = [];
          this.selectionAnchor = null;
          this.selectionActionBar = false;
        },

        clearSelectedCells() {
          this.selectedCells.forEach(({ row, col }) => {
            const field = this._colFields[col];
            if (!field) return;
            if (field === 'quantity') {
              this.bulkRows[row][field] = 1;
            } else if (field === 'amount') {
              this.bulkRows[row][field] = 0;
            } else if (field === 'product_selection') {
              this.bulkRows[row][field] = '';
              this.bulkRows[row].product_id = '';
              this.bulkRows[row].unit_price = 0;
              this.bulkRows[row].amount = 0;
            } else {
              this.bulkRows[row][field] = '';
            }
          });
        },

        copySelectedCells() {
          if (this.selectedCells.length === 0) return;
          // Determine bounding box
          const rows = this.selectedCells.map(c => c.row);
          const cols = this.selectedCells.map(c => c.col);
          const minR = Math.min(...rows), maxR = Math.max(...rows);
          const minC = Math.min(...cols), maxC = Math.max(...cols);

          let tsv = '';
          for (let r = minR; r <= maxR; r++) {
            let rowParts = [];
            for (let c = minC; c <= maxC; c++) {
              const field = this._colFields[c];
              rowParts.push(field ? (this.bulkRows[r][field] ?? '') : '');
            }
            tsv += rowParts.join('\t') + '\n';
          }
          navigator.clipboard.writeText(tsv.trim()).then(() => {
            this._showToast('Copied ' + this.selectedCells.length + ' cells');
          });
        },

        fillSelectedCells(value) {
          this.selectedCells.forEach(({ row, col }) => {
            const field = this._colFields[col];
            if (!field) return;
            if (field === 'quantity') {
              this.bulkRows[row][field] = parseInt(value) || 1;
              this.onBulkQtyChange(row);
            } else if (field === 'amount') {
              this.bulkRows[row][field] = parseFloat(value) || 0;
            } else if (field === 'product_selection') {
              this.bulkRows[row][field] = value;
              this.onBulkProductChange(row);
            } else {
              this.bulkRows[row][field] = value;
            }
          });
        },

        fillSelectedFromFirst() {
          if (this.selectedCells.length < 2) return;
          const first = this.selectedCells[0];
          const field = this._colFields[first.col];
          if (!field) return;
          const value = this.bulkRows[first.row][field];
          // Only fill cells in the same column
          this.selectedCells.forEach(({ row, col }) => {
            if (row === first.row && col === first.col) return;
            if (col !== first.col) return; // only same column
            const f = this._colFields[col];
            if (!f) return;
            if (f === 'quantity') {
              this.bulkRows[row][f] = parseInt(value) || 1;
              this.onBulkQtyChange(row);
            } else if (f === 'amount') {
              this.bulkRows[row][f] = parseFloat(value) || 0;
            } else if (f === 'product_selection') {
              this.bulkRows[row][f] = value;
              this.onBulkProductChange(row);
            } else {
              this.bulkRows[row][f] = value;
            }
          });
          this._showToast('Filled ' + (this.selectedCells.length - 1) + ' cells');
        },

        getSelectionSummary() {
          if (this.selectedCells.length === 0) return '';
          const rows = new Set(this.selectedCells.map(c => c.row));
          const cols = new Set(this.selectedCells.map(c => c.col));
          if (rows.size === 1) return this.selectedCells.length + ' cells in row ' + ([...rows][0] + 1);
          if (cols.size === 1) return this.selectedCells.length + ' cells in column';
          return this.selectedCells.length + ' cells (' + rows.size + ' rows × ' + cols.size + ' cols)';
        },

        _toastMsg: '',
        _toastVisible: false,
        _showToast(msg) {
          this._toastMsg = msg;
          this._toastVisible = true;
          setTimeout(() => { this._toastVisible = false; }, 1800);
        },

        parseTSV(text) {
          let rows = [];
          let currentRow = [];
          let currentCell = '';
          let inQuotes = false;
          
          for (let i = 0; i < text.length; i++) {
            const char = text[i];
            const nextChar = text[i + 1];
            
            if (char === '"') {
              if (inQuotes && nextChar === '"') {
                currentCell += '"';
                i++; // skip escaped quote
              } else {
                inQuotes = !inQuotes;
              }
            } else if (char === '\t' && !inQuotes) {
              currentRow.push(currentCell);
              currentCell = '';
            } else if ((char === '\r' || char === '\n') && !inQuotes) {
              if (char === '\r' && nextChar === '\n') i++; 
              currentRow.push(currentCell);
              rows.push(currentRow);
              currentRow = [];
              currentCell = '';
            } else {
              currentCell += char;
            }
          }
          if (currentCell !== '' || currentRow.length > 0) {
            currentRow.push(currentCell);
            rows.push(currentRow);
          }
          if (rows.length > 0 && rows[rows.length - 1].length === 1 && rows[rows.length - 1][0] === '') {
            rows.pop();
          }
          return rows;
        },

        handlePaste(e, startRowIdx, startColIdx) {
          const clipboardData = e.clipboardData || window.clipboardData;
          const pastedText = clipboardData.getData('text');
          if (!pastedText) return;

          const rowsData = this.parseTSV(pastedText);

          // If it's a 1x1 paste, let the browser handle it natively so we don't break simple copy/paste
          if (rowsData.length === 1 && rowsData[0].length === 1) {
            return;
          }

          e.preventDefault();

          const colMap = ['customer_name', 'customer_phone', 'address', 'city', 'product_selection', 'quantity', 'amount'];

          const neededRows = startRowIdx + rowsData.length;
          while (this.bulkRows.length < neededRows) {
            this.bulkRows.push(this.emptyBulkRow());
          }

          rowsData.forEach((cells, rOffset) => {
            const targetRowIdx = startRowIdx + rOffset;
            cells.forEach((cellText, cOffset) => {
              const targetColIdx = startColIdx + cOffset;
              if (targetColIdx < colMap.length) {
                const field = colMap[targetColIdx];
                let text = cellText.trim();
                
                if (field === 'quantity' || field === 'amount') {
                  this.bulkRows[targetRowIdx][field] = Number(text) || (field === 'quantity' ? 1 : 0);
                  if (field === 'quantity') this.onBulkQtyChange(targetRowIdx);
                } else if (field === 'product_selection') {
                  // Attempt to match dropdown options
                  let matchedValue = this.findProductSelectionByText(text);
                  this.bulkRows[targetRowIdx][field] = matchedValue;
                  this.onBulkProductChange(targetRowIdx);
                } else {
                  this.bulkRows[targetRowIdx][field] = text;
                }
              }
            });
          });
        },

        findProductSelectionByText(text) {
          if (!this._productOptionsTextMap) {
            this._productOptionsTextMap = [];
            const select = document.querySelector('#bulk-spreadsheet select');
            if (select) {
              Array.from(select.options).forEach(opt => {
                if (opt.value) {
                  this._productOptionsTextMap.push({ text: opt.text.trim().toLowerCase(), value: opt.value });
                }
              });
            }
          }
          if (!text) return '';
          if (!this._productOptionsTextMap) return text;
          const search = text.trim().toLowerCase();
          const exact = this._productOptionsTextMap.find(o => o.text === search);
          if (exact) return exact.value;
          const partial = this._productOptionsTextMap.find(o => o.text.includes(search) || search.includes(o.text));
          if (partial) return partial.value;
          return text; 
        },

        async submitBulkOrders() {
          // Reset all error states
          this.bulkRows.forEach(r => { r._error = false; r._errorMsg = ''; });

          // Build payload from ALL non-empty rows (user must delete empty rows)
          const allRows = this.bulkRows.map((r, idx) => ({
            _idx: idx,
            customer_name: (r.customer_name || '').trim(),
            customer_phone: (r.customer_phone || '').trim(),
            address: (r.address || '').trim(),
            city: (r.city || '').trim(),
            product_id: r.product_id || '',
            quantity: parseInt(r.quantity) || 1,
            amount: parseFloat(r.amount) || 0,
            remarks: r.remarks || '',
          }));

          if (allRows.length === 0) {
            alert('Please add at least one order row.');
            return;
          }

          this.bulkSubmitting = true;
          try {
            const res = await fetch('{{ route("orders.bulkManualStore") }}', {
              method: 'POST',
              headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
              body: JSON.stringify({ orders: allRows.map(({ _idx, ...r }) => r) })
            });
            const data = await res.json();
            if (res.ok) {
              window.location.href = '{{ route("orders.index", ["status" => "pending"]) }}';
            } else if (data.row_errors) {
              // Per-row errors from backend — mark each row
              for (const [rowIdx, messages] of Object.entries(data.row_errors)) {
                const idx = parseInt(rowIdx);
                if (this.bulkRows[idx]) {
                  this.bulkRows[idx]._error = true;
                  this.bulkRows[idx]._errorMsg = messages.join(' ');
                }
              }
              // Scroll to first error row
              const firstErrIdx = Object.keys(data.row_errors)[0];
              const firstErrEl = document.querySelector(`[data-row="${firstErrIdx}"]`);
              if (firstErrEl) firstErrEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
            } else {
              alert(data.message || 'Failed to create orders.');
            }
          } catch (e) {
            console.error(e);
            alert('Network error. Please try again.');
          }
          this.bulkSubmitting = false;
        },

        async bulkDeleteOrders() {
          if (this.bulkProcessing) return;
          if (!confirm('Are you sure you want to delete ' + this.selectedOrders.length + ' pending orders? This cannot be undone.')) return;
          this.bulkProcessing = true;
          try {
            const res = await fetch('{{ route("orders.bulkDelete") }}', {
              method: 'POST',
              headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
              body: JSON.stringify({ order_ids: this.selectedOrders })
            });
            const data = await res.json();
            if (res.ok) {
              window.location.reload();
            } else {
              alert(data.message || 'Failed to delete orders.');
            }
          } catch (e) {
            console.error(e);
            alert('Network error. Please try again.');
          }
          this.bulkProcessing = false;
        },

        async bulkShipOrders() {
          if (this.bulkProcessing) return;
          if (!confirm('Ship ' + this.selectedOrders.length + ' orders via Pathao? This will create consignments for all selected orders.')) return;
          this.bulkProcessing = true;
          try {
            const res = await fetch('{{ route("orders.bulkShip") }}', {
              method: 'POST',
              headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
              body: JSON.stringify({ order_ids: this.selectedOrders })
            });
            const data = await res.json();
            if (res.ok) {
              let msg = data.message || 'Orders shipped successfully!';
              if (data.errors && data.errors.length > 0) {
                msg += '\n\nFailed orders:\n' + data.errors.join('\n');
              }
              alert(msg);
              window.location.href = '{{ route("orders.index", ["status" => "shipped"]) }}';
            } else {
              let msg = data.message || 'Failed to ship orders.';
              if (data.errors && data.errors.length > 0) {
                msg += '\n\nReasons:\n' + data.errors.join('\n');
              }
              alert(msg);
            }
          } catch (e) {
            console.error(e);
            alert('Network error. Please try again.');
          }
          this.bulkProcessing = false;
        },

        // Return Verification Modal Functions
        openReturnModal(order) {
          this.returnOrder = order;
          this.returnNotes = '';
          this.returnSubmitting = false;
          this.returnItems = (order.order_items || []).map(item => ({
            order_item_id: item.id,
            product_name: item.product?.name || 'Unknown Product',
            original_qty: item.quantity,
            unit_price: item.price_at_purchase,
            good_qty: item.quantity,
            damaged_qty: 0,
            color: item.color || null,
            size: item.size || null,
          }));
          this.returnModalOpen = true;
        },

        closeReturnModal() {
          this.returnModalOpen = false;
          setTimeout(() => {
            this.returnOrder = null;
            this.returnItems = [];
            this.returnNotes = '';
          }, 300);
        },

        setAllGood() {
          this.returnItems.forEach(item => {
            item.good_qty = item.original_qty;
            item.damaged_qty = 0;
          });
        },

        setAllDamaged() {
          this.returnItems.forEach(item => {
            item.good_qty = 0;
            item.damaged_qty = item.original_qty;
          });
        },

        clampReturnQty(item, field) {
          let val = parseInt(item[field]) || 0;
          if (val < 0) val = 0;
          item[field] = val;
          const other = field === 'good_qty' ? 'damaged_qty' : 'good_qty';
          const otherVal = parseInt(item[other]) || 0;
          if (val + otherVal > item.original_qty) {
            item[other] = item.original_qty - val;
            if (item[other] < 0) item[other] = 0;
          }
        },

        get returnSummary() {
          let totalGood = 0, totalDamaged = 0, totalNotReturned = 0;
          (this.returnItems || []).forEach(item => {
            const good = parseInt(item.good_qty) || 0;
            const damaged = parseInt(item.damaged_qty) || 0;
            totalGood += good;
            totalDamaged += damaged;
            totalNotReturned += (item.original_qty - good - damaged);
          });
          return { totalGood, totalDamaged, totalNotReturned, refundAmount: this.returnOrder?.total_amount || 0 };
        },

        get returnHasErrors() {
          return (this.returnItems || []).some(item => {
            const good = parseInt(item.good_qty) || 0;
            const damaged = parseInt(item.damaged_qty) || 0;
            return good + damaged > item.original_qty || good < 0 || damaged < 0;
          });
        },

        async submitReturn() {
          if (this.returnSubmitting || this.returnHasErrors) return;
          if (!confirm('Confirm return verification? Stock will be updated and financial entries reversed.')) return;

          this.returnSubmitting = true;
          try {
            const res = await fetch(`{{ url('orders') }}/${this.returnOrder.id}/verify-return`, {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
              },
              body: JSON.stringify({
                items: this.returnItems.map(item => ({
                  order_item_id: item.order_item_id,
                  good_qty: parseInt(item.good_qty) || 0,
                  damaged_qty: parseInt(item.damaged_qty) || 0,
                })),
                return_notes: this.returnNotes,
              })
            });
            const data = await res.json();
            if (res.ok && data.success) {
              this.closeReturnModal();
              window.location.reload();
            } else {
              alert(data.message || 'Failed to verify return.');
            }
          } catch (e) {
            console.error(e);
            alert('Network error. Please try again.');
          }
          this.returnSubmitting = false;
        },

        async bulkRejectOrders() {
          if (!confirm('Reject ' + this.selectedOrders.length + ' confirmed orders? This cannot be undone easily.')) return;
          try {
            const res = await fetch('{{ route("orders.bulkStatusUpdate") }}', {
              method: 'POST',
              headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
              body: JSON.stringify({ order_ids: this.selectedOrders, status: 'rejected' })
            });
            const data = await res.json();
            if (res.ok) {
              window.location.reload();
            } else {
              alert(data.message || 'Failed to reject orders.');
            }
          } catch (e) {
            console.error(e);
            alert('Network error. Please try again.');
          }
        }
      }));
    });
  </script>
