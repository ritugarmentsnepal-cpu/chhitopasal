    <div class="space-y-4">
      <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
        <div class="flex items-center justify-between mb-4">
          <div>
            <h3 class="font-black text-lg text-gray-900">Product Catalog (AI View)</h3>
            <p class="text-sm font-bold text-gray-500">This is what the AI "knows" about your products. All data is auto-injected from your product database.</p>
          </div>
          <span class="bg-blue-100 text-blue-700 font-black text-xs px-3 py-1 rounded-full">{{ $products->count() }} Products</span>
        </div>
      </div>

      @foreach($products as $product)
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
          <div class="flex items-start gap-4">
            @if($product->image_path)
              <img src="{{ asset('storage/' . $product->image_path) }}" alt="{{ $product->name }}" class="w-16 h-16 rounded-xl object-cover shrink-0">
            @else
              <div class="w-16 h-16 rounded-xl bg-gray-100 flex items-center justify-center shrink-0">
                <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
              </div>
            @endif
            <div class="flex-1 min-w-0">
              <div class="flex items-center gap-2 mb-1">
                <h4 class="font-black text-gray-900">{{ $product->name }}</h4>
                @if($product->category)
                  <span class="px-2 py-0.5 bg-gray-100 text-gray-600 rounded text-[10px] font-bold">{{ $product->category->name }}</span>
                @endif
              </div>
              <div class="flex flex-wrap gap-3 text-sm font-bold mt-1">
                <span class="text-green-600">Rs. {{ number_format($product->price) }}</span>
                <span class="{{ $product->stock > 0 ? 'text-blue-600' : 'text-red-600' }}">Stock: {{ $product->stock }}</span>
                @if($product->cost_price)
                  <span class="text-gray-400">Cost: Rs. {{ number_format($product->cost_price) }}</span>
                @endif
              </div>
              @if(!empty($product->color_options) && is_array($product->color_options))
                <div class="flex items-center gap-1 mt-2">
                  <span class="text-xs font-bold text-gray-500">Colors:</span>
                  @foreach($product->color_options as $color)
                    <span class="px-2 py-0.5 bg-gray-100 rounded text-xs font-bold text-gray-700">{{ $color }}</span>
                  @endforeach
                </div>
              @endif
              @if(!empty($product->size_options) && is_array($product->size_options))
                <div class="flex items-center gap-1 mt-1">
                  <span class="text-xs font-bold text-gray-500">Sizes:</span>
                  @foreach($product->size_options as $size)
                    <span class="px-2 py-0.5 bg-gray-100 rounded text-xs font-bold text-gray-700">{{ $size }}</span>
                  @endforeach
                </div>
              @endif
              @if(!empty($product->bundles) && is_array($product->bundles))
                <div class="flex items-center gap-1 mt-1">
                  <span class="text-xs font-bold text-gray-500">Bundles:</span>
                  @foreach($product->bundles as $bundle)
                    <span class="px-2 py-0.5 bg-mango/20 text-gray-900 rounded text-xs font-bold">{{ $bundle['qty'] }}-Pack: Rs. {{ number_format($bundle['price']) }}</span>
                  @endforeach
                </div>
              @endif
              @if($product->description)
                <p class="text-sm text-gray-500 mt-2 line-clamp-2">{{ strip_tags($product->description) }}</p>
              @endif
            </div>
          </div>
        </div>
      @endforeach
    </div>
