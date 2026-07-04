<x-modal name="mockup-studio-{{ $order->id }}" :show="false" maxWidth="6xl">
    <div class="p-6 bg-gray-50 flex flex-col h-[90vh]" x-data="mockupStudio({{ $order->id }}, {{ json_encode(($order->design_files ?? null) ?: []) }})">
        
        <!-- Header -->
        <div class="flex items-center justify-between mb-6 shrink-0">
            <div>
                <h3 class="text-2xl font-black text-gray-900">Mockup Studio <span class="text-gray-400 text-lg ml-2">#{{ $order->id }}</span></h3>
                <p class="text-sm font-medium text-gray-500">Overlay customer designs onto templates to generate a final mockup.</p>
            </div>
            <div class="flex gap-3">
                <button type="button" x-on:click="$dispatch('close')" class="px-5 py-2.5 rounded-xl font-bold text-gray-500 bg-white border border-gray-200 hover:bg-gray-50 transition shadow-sm">Cancel</button>
                <button type="button" @click="saveMockup()" :disabled="isSaving" class="bg-mango text-gray-900 font-black px-6 py-2.5 rounded-xl shadow-lg hover:bg-yellow-400 transition active:scale-95 disabled:opacity-50 flex items-center gap-2">
                    <span x-show="!isSaving">Save Mockup</span>
                    <span x-show="isSaving">Saving...</span>
                </button>
            </div>
        </div>

        <!-- Main Workspace -->
        <div class="flex gap-6 flex-1 min-h-0">
            <!-- Sidebar: Tools & Assets -->
            <div class="w-80 flex flex-col gap-6 shrink-0 overflow-y-auto pr-2 custom-scrollbar">
                
                <!-- Step 1: Select Template -->
                <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm">
                    <h4 class="font-bold text-gray-900 mb-3 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-full bg-gray-100 text-xs flex items-center justify-center">1</span>
                        Base Template
                    </h4>
                    
                    <select x-model="selectedTemplateId" @change="loadTemplate()" class="w-full rounded-xl border-gray-200 bg-gray-50 text-sm font-medium">
                        <option value="">-- Select Template --</option>
                        @php
                            try {
                                $templates = \App\Models\MockupTemplate::all();
                            } catch (\Throwable $e) {
                                $templates = collect();
                            }
                        @endphp
                        @foreach($templates as $template)
                            <option value="{{ $template->id }}" data-url="{{ '/storage/' . $template->image_path }}">
                                {{ $template->name }} ({{ ucfirst($template->product_type) }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Step 2: Uploaded Designs -->
                <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm">
                    <h4 class="font-bold text-gray-900 mb-3 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-full bg-gray-100 text-xs flex items-center justify-center">2</span>
                        Customer Designs
                    </h4>
                    <p class="text-xs text-gray-500 mb-3">Click a design to add it to the canvas.</p>
                    
                    <div class="grid grid-cols-2 gap-3">
                        <template x-for="(url, position) in designFiles" :key="position">
                            <div @click="addDesignToCanvas(url)" class="aspect-square bg-gray-100 rounded-xl border-2 border-transparent hover:border-mango cursor-pointer transition flex items-center justify-center p-2 relative group overflow-hidden">
                                <img :src="'/storage/' + url" class="max-w-full max-h-full object-contain">
                                <div class="absolute bottom-0 inset-x-0 bg-black/60 text-white text-[10px] font-bold text-center py-1 opacity-0 group-hover:opacity-100 transition">
                                    <span x-text="position"></span>
                                </div>
                            </div>
                        </template>
                        <div x-show="Object.keys(designFiles || {}).length === 0" class="col-span-2 text-center py-6 text-gray-400 text-xs font-bold bg-gray-50 rounded-xl border border-dashed border-gray-200">
                            No designs uploaded for this order.
                        </div>
                    </div>
                </div>

                <!-- Canvas Controls -->
                <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm" x-show="canvas">
                    <h4 class="font-bold text-gray-900 mb-3 flex items-center gap-2">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path></svg>
                        Adjust Layer
                    </h4>
                    
                    <div class="space-y-4">
                        <button type="button" @click="deleteSelected()" class="w-full py-2 bg-red-50 text-red-600 font-bold text-xs rounded-lg hover:bg-red-100 transition">
                            Delete Selected Layer
                        </button>
                        <button type="button" @click="bringToFront()" class="w-full py-2 bg-gray-100 text-gray-700 font-bold text-xs rounded-lg hover:bg-gray-200 transition">
                            Bring to Front
                        </button>
                        <button type="button" @click="clearCanvas()" class="w-full py-2 bg-gray-100 text-gray-700 font-bold text-xs rounded-lg hover:bg-gray-200 transition">
                            Clear Canvas
                        </button>
                    </div>
                </div>
            </div>

            <!-- Canvas Area -->
            <div class="flex-1 bg-white rounded-2xl border border-gray-200 shadow-inner overflow-hidden flex items-center justify-center relative bg-[url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyMCIgaGVpZ2h0PSIyMCI+CjxyZWN0IHdpZHRoPSIyMCIgaGVpZ2h0PSIyMCIgZmlsbD0iI2ZmZiIvPgo8cmVjdCB3aWR0aD0iMTAiIGhlaWdodD0iMTAiIGZpbGw9IiNmM2Y0ZjYiLz4KPHJlY3QgeD0iMTAiIHk9IjEwIiB3aWR0aD0iMTAiIGhlaWdodD0iMTAiIGZpbGw9IiNmM2Y0ZjYiLz4KPC9zdmc+')]">
                <!-- Wrapper to constraint canvas to aspect ratio/size -->
                <div class="relative w-full h-full flex items-center justify-center" id="canvas-wrapper-{{ $order->id }}">
                    <canvas id="mockup-canvas-{{ $order->id }}"></canvas>
                </div>
                
                <div x-show="!selectedTemplateId" class="absolute inset-0 flex items-center justify-center bg-white/80 backdrop-blur-sm z-10 pointer-events-none">
                    <div class="text-center">
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        </div>
                        <p class="font-bold text-gray-500">Please select a base template to start.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-modal>

@once
<script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/5.3.1/fabric.min.js"></script>
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('mockupStudio', (orderId, designFiles) => ({
            orderId: orderId,
            designFiles: designFiles || {},
            selectedTemplateId: '',
            canvas: null,
            isSaving: false,
            
            init() {
                // Initialize canvas only when modal is opened to ensure dimensions are correct
                window.addEventListener('open-modal', (e) => {
                    if (e.detail === `mockup-studio-${this.orderId}`) {
                        setTimeout(() => this.initCanvas(), 100);
                    }
                });
            },
            
            initCanvas() {
                if (this.canvas) return; // already init
                
                const wrapper = document.getElementById(`canvas-wrapper-${this.orderId}`);
                // Make canvas fill the wrapper
                const width = wrapper.clientWidth - 40;
                const height = wrapper.clientHeight - 40;
                
                this.canvas = new fabric.Canvas(`mockup-canvas-${this.orderId}`, {
                    width: width,
                    height: height,
                    preserveObjectStacking: true
                });
            },
            
            loadTemplate() {
                if (!this.selectedTemplateId || !this.canvas) return;
                
                // Get URL from the selected option
                const select = this.$el.querySelector('select');
                const option = select.options[select.selectedIndex];
                const url = option.dataset.url;
                
                this.canvas.clear();
                
                fabric.Image.fromURL(url, (img) => {
                    // Scale image to fit canvas
                    const scale = Math.min(
                        this.canvas.width / img.width,
                        this.canvas.height / img.height
                    );
                    
                    img.set({
                        originX: 'center',
                        originY: 'center',
                        left: this.canvas.width / 2,
                        top: this.canvas.height / 2,
                        scaleX: scale * 0.9, // 90% of canvas
                        scaleY: scale * 0.9,
                        selectable: false, // Background shouldn't move
                        evented: false,
                        crossOrigin: 'anonymous'
                    });
                    
                    this.canvas.setBackgroundImage(img, this.canvas.renderAll.bind(this.canvas));
                }, { crossOrigin: 'anonymous' });
            },
            
            addDesignToCanvas(url) {
                if (!this.canvas || !this.selectedTemplateId) return;
                
                const fullUrl = '/storage/' + url;
                
                fabric.Image.fromURL(fullUrl, (img) => {
                    // Start it reasonably sized
                    const scale = Math.min(
                        (this.canvas.width * 0.3) / img.width,
                        (this.canvas.height * 0.3) / img.height
                    );
                    
                    img.set({
                        originX: 'center',
                        originY: 'center',
                        left: this.canvas.width / 2,
                        top: this.canvas.height / 2,
                        scaleX: scale,
                        scaleY: scale,
                        cornerColor: '#FACC15',
                        cornerStrokeColor: '#1F2937',
                        borderColor: '#1F2937',
                        cornerSize: 12,
                        transparentCorners: false,
                        crossOrigin: 'anonymous'
                    });
                    
                    this.canvas.add(img);
                    this.canvas.setActiveObject(img);
                    this.canvas.renderAll();
                }, { crossOrigin: 'anonymous' });
            },
            
            deleteSelected() {
                if (!this.canvas) return;
                const activeObjects = this.canvas.getActiveObjects();
                if (activeObjects.length) {
                    this.canvas.discardActiveObject();
                    activeObjects.forEach((obj) => this.canvas.remove(obj));
                }
            },
            
            bringToFront() {
                if (!this.canvas) return;
                const activeObject = this.canvas.getActiveObject();
                if (activeObject) {
                    activeObject.bringToFront();
                    this.canvas.renderAll();
                }
            },
            
            clearCanvas() {
                if (!this.canvas) return;
                this.canvas.clear();
                this.selectedTemplateId = ''; // Reset template selection
            },
            
            saveMockup() {
                if (!this.canvas) return;
                
                // Deselect active objects to hide borders in final image
                this.canvas.discardActiveObject();
                this.canvas.renderAll();
                
                this.isSaving = true;
                
                // Get base64 data
                const base64Image = this.canvas.toDataURL({
                    format: 'png',
                    quality: 1,
                    multiplier: 2 // Export at 2x resolution for sharpness
                });
                
                // Send to backend
                fetch(`/orders/${this.orderId}/save-mockup`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        image: base64Image,
                        template_id: this.selectedTemplateId || null
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Mockup saved successfully!');
                        window.location.reload();
                    } else {
                        alert('Failed to save mockup.');
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert('An error occurred while saving.');
                })
                .finally(() => {
                    this.isSaving = false;
                });
            }
        }));
    });
</script>
@endonce
