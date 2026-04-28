<form method="POST" action="{{ route('settings.store') }}" enctype="multipart/form-data">
    @csrf
    <input type="hidden" name="redirect_tab" value="frontend">
    
    <!-- Branding -->
    <div class="bg-white rounded-3xl p-8 shadow-sm border border-gray-100 mb-8">
        <h3 class="text-xl font-black text-gray-900 mb-6 flex items-center gap-2">
            Store Identity
        </h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Store Name</label>
                <input name="store_name" type="text" value="{{ setting('store_name', 'ChhitoPasal') }}" class="block w-full rounded-xl border-gray-200 bg-gray-50 py-3 font-bold" />
            </div>
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">SEO Meta Description</label>
                <input name="meta_description" type="text" value="{{ setting('meta_description') }}" class="block w-full rounded-xl border-gray-200 bg-gray-50 py-3 font-bold" placeholder="E.g. Best online store for electronics..." />
            </div>

            <div class="pt-4 border-t border-gray-100">
                <label class="block text-sm font-bold text-gray-700 mb-2">Store Logo (Appears on Navbar)</label>
                @if(setting('store_logo'))
                    <div class="mb-4 bg-gray-100 p-4 rounded-xl inline-block">
                        <img src="{{ Storage::url(setting('store_logo')) }}" class="h-12 object-contain">
                    </div>
                @endif
                <input type="file" name="store_logo" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-5 file:rounded-xl file:border-0 file:text-sm file:font-bold file:bg-gray-900 file:text-white hover:file:bg-gray-800" accept="image/*">
            </div>

            <div class="pt-4 border-t border-gray-100">
                <label class="block text-sm font-bold text-gray-700 mb-2">Favicon</label>
                @if(setting('store_favicon'))
                    <div class="mb-4 bg-gray-100 p-4 rounded-xl inline-block">
                        <img src="{{ Storage::url(setting('store_favicon')) }}" class="h-8 w-8 object-contain">
                    </div>
                @endif
                <input type="file" name="store_favicon" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-5 file:rounded-xl file:border-0 file:text-sm file:font-bold file:bg-gray-900 file:text-white hover:file:bg-gray-800" accept="image/*">
            </div>
        </div>
    </div>

    <!-- Hero Banner Settings -->
    <div class="bg-white rounded-3xl p-8 shadow-sm border border-gray-100 mb-8">
        <h3 class="text-xl font-black text-gray-900 mb-6 flex items-center gap-2">
            Hero Banner (Welcome Page)
        </h3>
        
        <div class="space-y-5">
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Hero Title</label>
                <input name="hero_title" type="text" value="{{ setting('hero_title', 'Upgrade your lifestyle.') }}" class="block w-full rounded-xl border-gray-200 bg-gray-50 py-3 font-bold" />
            </div>
            
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Hero Subtitle</label>
                <textarea name="hero_subtitle" rows="2" class="block w-full rounded-xl border-gray-200 bg-gray-50 py-3 font-medium">{{ setting('hero_subtitle', 'Discover the best tech, fashion, and home accessories delivered straight to your door.') }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Call to Action Button Text</label>
                <input name="hero_cta" type="text" value="{{ setting('hero_cta', 'Shop Collection') }}" class="block w-full rounded-xl border-gray-200 bg-gray-50 py-3 font-bold" />
            </div>

            <div class="pt-4 border-t border-gray-100">
                <label class="block text-sm font-bold text-gray-700 mb-2">Hero Background Image</label>
                @if(setting('hero_image'))
                    <div class="mb-4 aspect-video bg-gray-100 rounded-xl overflow-hidden relative">
                        <img src="{{ Storage::url(setting('hero_image')) }}" class="w-full h-full object-cover">
                    </div>
                @endif
                <input type="file" name="hero_image" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-5 file:rounded-xl file:border-0 file:text-sm file:font-bold file:bg-gray-900 file:text-white hover:file:bg-gray-800" accept="image/*">
            </div>
        </div>
    </div>

    <!-- Contact & Footer Info -->
    <div class="bg-white rounded-3xl p-8 shadow-sm border border-gray-100 mb-8">
        <h3 class="text-xl font-black text-gray-900 mb-6 flex items-center gap-2">
            Contact Information
        </h3>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Support Email</label>
                <input name="contact_email" type="email" value="{{ setting('contact_email', 'support@chhitopasal.com') }}" class="block w-full rounded-xl border-gray-200 bg-gray-50 py-3 font-bold" />
            </div>
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Support Phone</label>
                <input name="contact_phone" type="text" value="{{ setting('contact_phone', '+977 9800000000') }}" class="block w-full rounded-xl border-gray-200 bg-gray-50 py-3 font-bold" />
            </div>
            <div class="sm:col-span-2">
                <label class="block text-sm font-bold text-gray-700 mb-2">Store Address</label>
                <input name="contact_address" type="text" value="{{ setting('contact_address', 'Kathmandu, Nepal') }}" class="block w-full rounded-xl border-gray-200 bg-gray-50 py-3 font-bold" />
            </div>
        </div>
    </div>

    <div class="flex justify-end sticky bottom-8">
        <button type="submit" class="bg-mango text-gray-900 font-black py-4 px-10 rounded-2xl shadow-sm hover:bg-yellow-400 transition-colors text-lg">
            Save Frontend Settings
        </button>
    </div>
</form>
