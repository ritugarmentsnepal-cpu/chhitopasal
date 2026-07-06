<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Design Approval — {{ setting('store_name', 'Chhito Pasal') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 min-h-screen font-sans antialiased">
    <div class="max-w-lg mx-auto px-4 py-8">

        {{-- Brand header --}}
        <div class="text-center mb-6">
            @if(setting('store_logo'))
                <img src="{{ '/storage/' . setting('store_logo') }}" alt="{{ setting('store_name') }}" class="h-12 mx-auto mb-2 object-contain">
            @endif
            <h1 class="text-xl font-black text-gray-900">{{ setting('store_name', 'Chhito Pasal') }}</h1>
            <p class="text-sm font-medium text-gray-500 mt-1">
                Design mockup{{ $mockup->order_id ? ' for Order #' . $mockup->order_id : '' }}
            </p>
        </div>

        {{-- Mockup image --}}
        <div class="bg-white rounded-3xl border border-gray-100 shadow-lg overflow-hidden mb-6">
            <div class="bg-gray-50 p-4 flex items-center justify-center">
                <img src="{{ '/storage/' . $mockup->image_path }}" alt="{{ $mockup->title }}" class="max-w-full rounded-xl">
            </div>
            <div class="px-5 py-4 border-t border-gray-50">
                <h2 class="font-bold text-gray-900">{{ $mockup->title }}</h2>
                @if($mockup->order)
                    <p class="text-xs font-medium text-gray-400 mt-0.5">Prepared for {{ $mockup->order->customer_name }}</p>
                @endif
            </div>
        </div>

        @if($mockup->approval_status === 'approved')
            {{-- Already approved --}}
            <div class="bg-emerald-50 border border-emerald-200 rounded-3xl p-6 text-center">
                <div class="w-14 h-14 bg-emerald-500 rounded-full flex items-center justify-center mx-auto mb-3">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <h3 class="text-lg font-black text-emerald-700">Design Approved!</h3>
                <p class="text-sm font-medium text-emerald-600 mt-1">
                    Thank you{{ $mockup->approval_responded_at ? ' — approved ' . $mockup->approval_responded_at->format('M j, Y') : '' }}.
                    We'll start preparing your order right away.
                </p>
            </div>
        @elseif($mockup->approval_status === 'changes_requested')
            {{-- Changes requested --}}
            <div class="bg-amber-50 border border-amber-200 rounded-3xl p-6 text-center mb-6">
                <h3 class="text-lg font-black text-amber-700">Change Request Received</h3>
                <p class="text-sm font-medium text-amber-600 mt-1">We got your feedback and will send you an updated design soon.</p>
                @if($mockup->approval_feedback)
                    <p class="text-xs font-medium text-amber-500 bg-white/60 rounded-xl px-4 py-2 mt-3">"{{ $mockup->approval_feedback }}"</p>
                @endif
            </div>
            <p class="text-center text-xs font-medium text-gray-400">Changed your mind? You can still approve below.</p>
            @include('mockups.partials.approval_form', ['token' => $mockup->share_token])
        @else
            {{-- Pending decision --}}
            <p class="text-center text-sm font-bold text-gray-600 mb-4">Does this design look right to you?</p>
            @include('mockups.partials.approval_form', ['token' => $mockup->share_token])
        @endif

        <p class="text-center text-[11px] font-medium text-gray-300 mt-8">
            {{ setting('store_name', 'Chhito Pasal') }}{{ setting('contact_phone') ? ' · ' . setting('contact_phone') : '' }}
        </p>
    </div>
</body>
</html>
