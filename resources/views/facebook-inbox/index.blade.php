<x-app-layout>
    <div x-data="facebookInbox()" x-init="init()" class="flex h-[calc(100vh-72px)] bg-white dark:bg-gray-900 border-t border-gray-100 dark:border-gray-800">
        
        <!-- Sidebar: Threads -->
        <div class="w-80 border-r border-gray-100 dark:border-gray-800 flex flex-col bg-gray-50 dark:bg-gray-900 shrink-0">
            <!-- Header & Pages Dropdown -->
            <div class="p-4 border-b border-gray-100 dark:border-gray-800 bg-white dark:bg-gray-900">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-black text-lg text-gray-900 dark:text-white flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                        Inbox
                    </h2>
                    <a href="{{ route('facebook.login') }}" class="text-xs font-bold text-blue-600 bg-blue-50 hover:bg-blue-100 px-2.5 py-1.5 rounded-lg transition">
                        Connect Pages
                    </a>
                </div>

                @if($pages->count() > 0)
                    <select x-model="selectedPageId" @change="fetchConversations()" class="w-full bg-gray-50 border border-gray-200 rounded-xl text-sm font-bold text-gray-700 py-2.5 pl-3 pr-8 focus:border-blue-500 focus:ring-blue-500/20">
                        <option value="">Select a Page...</option>
                        @foreach($pages as $page)
                            <option value="{{ $page->page_id }}">{{ $page->page_name }}</option>
                        @endforeach
                    </select>
                @else
                    <div class="text-xs text-gray-500 bg-yellow-50 p-3 rounded-lg border border-yellow-100">
                        No pages connected. Click "Connect Pages" to authenticate with Facebook.
                    </div>
                @endif
            </div>

            <!-- Conversations List -->
            <div class="flex-1 overflow-y-auto no-scrollbar relative p-2 space-y-1">
                <template x-if="loadingConversations">
                    <div class="flex justify-center p-8">
                        <svg class="animate-spin h-6 w-6 text-blue-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                    </div>
                </template>
                
                <template x-if="!loadingConversations && conversations.length === 0 && selectedPageId">
                    <div class="text-center p-8 text-gray-400 text-sm font-bold">
                        No conversations found.
                    </div>
                </template>

                <template x-for="conv in conversations" :key="conv.id">
                    <button @click="selectConversation(conv)" 
                            class="w-full text-left p-3 rounded-xl transition-all duration-200"
                            :class="selectedConversation?.id === conv.id ? 'bg-blue-50 border border-blue-100' : 'hover:bg-gray-100 border border-transparent'">
                        <div class="flex justify-between items-start mb-1">
                            <span class="font-bold text-sm text-gray-900 truncate" x-text="getParticipantName(conv)"></span>
                            <span class="text-[10px] text-gray-400 font-medium whitespace-nowrap ml-2" x-text="formatDate(conv.updated_time)"></span>
                        </div>
                        <p class="text-xs text-gray-500 truncate" x-text="getLastMessageText(conv)"></p>
                    </button>
                </template>
            </div>
        </div>

        <!-- Middle: Chat Area -->
        <div class="flex-1 flex flex-col relative bg-white dark:bg-gray-900 min-w-0">
            <template x-if="!selectedConversation">
                <div class="flex-1 flex items-center justify-center flex-col text-gray-400">
                    <svg class="w-16 h-16 mb-4 text-gray-200" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                    <p class="font-bold">Select a conversation to start messaging</p>
                </div>
            </template>

            <template x-if="selectedConversation">
                <div class="flex-1 flex flex-col h-full">
                    <!-- Chat Header -->
                    <div class="h-16 border-b border-gray-100 flex items-center px-6 bg-white shrink-0 shadow-sm z-10 justify-between">
                        <div class="font-black text-lg text-gray-900 flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-sm">
                                <span x-text="getParticipantName(selectedConversation).charAt(0)"></span>
                            </div>
                            <span x-text="getParticipantName(selectedConversation)"></span>
                        </div>
                        <button @click="fillOrderCustomer()" class="text-xs bg-indigo-50 text-indigo-600 hover:bg-indigo-100 font-bold px-3 py-1.5 rounded-lg transition hidden md:block">
                            Copy to Order Form
                        </button>
                    </div>

                    <!-- Messages -->
                    <div class="flex-1 overflow-y-auto p-6 space-y-4 bg-gray-50/50" id="chat-messages-container">
                        <template x-if="loadingMessages">
                            <div class="flex justify-center py-4">
                                <svg class="animate-spin h-5 w-5 text-blue-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                            </div>
                        </template>

                        <template x-for="msg in reversedMessages" :key="msg.id">
                            <div class="flex flex-col" :class="msg.from.id === selectedPageId ? 'items-end' : 'items-start'">
                                <div class="max-w-[75%] px-4 py-2.5 rounded-2xl text-sm"
                                     :class="msg.from.id === selectedPageId ? 'bg-blue-600 text-white rounded-br-sm' : 'bg-white border border-gray-200 text-gray-900 rounded-bl-sm'">
                                    <span x-text="msg.message" style="white-space: pre-wrap;"></span>
                                </div>
                                <span class="text-[10px] text-gray-400 mt-1 font-medium" x-text="formatDate(msg.created_time)"></span>
                            </div>
                        </template>
                    </div>

                    <!-- Input -->
                    <div class="p-4 bg-white border-t border-gray-100 shrink-0">
                        <form @submit.prevent="sendMessage" class="flex gap-2">
                            <input type="text" x-model="newMessage" placeholder="Type a reply..." 
                                   class="flex-1 bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:border-blue-500 focus:ring-blue-500/20"
                                   :disabled="sendingMessage">
                            <button type="submit" :disabled="sendingMessage || !newMessage.trim()"
                                    class="bg-blue-600 text-white px-6 py-3 rounded-xl font-bold hover:bg-blue-700 transition disabled:opacity-50 flex items-center gap-2">
                                <svg x-show="sendingMessage" class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                <span x-show="!sendingMessage">Send</span>
                            </button>
                        </form>
                    </div>
                </div>
            </template>
        </div>

        <!-- Right Sidebar: Quick Order Form -->
        <div class="w-80 border-l border-gray-100 bg-white flex flex-col shrink-0 overflow-y-auto">
            <div class="p-4 border-b border-gray-100 bg-gray-50/50 sticky top-0 z-10 backdrop-blur-md">
                <h3 class="font-black text-gray-900">Quick Order</h3>
                <p class="text-[10px] text-gray-500">Create an order from this chat</p>
            </div>
            <div class="p-4">
                <form action="{{ route('orders.store') }}" method="POST" id="quick-order-form" @submit="submitOrder">
                    @csrf
                    <input type="hidden" name="source" value="facebook">
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Customer Name *</label>
                            <input type="text" name="customer_name" x-model="orderForm.name" required class="w-full border-gray-200 rounded-lg text-sm bg-gray-50 py-2">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Phone Number *</label>
                            <input type="text" name="customer_phone" x-model="orderForm.phone" required class="w-full border-gray-200 rounded-lg text-sm bg-gray-50 py-2">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Address *</label>
                            <input type="text" name="address" x-model="orderForm.address" required class="w-full border-gray-200 rounded-lg text-sm bg-gray-50 py-2">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Product *</label>
                            <select name="product_id" x-model="orderForm.product_id" required class="w-full border-gray-200 rounded-lg text-sm bg-gray-50 py-2">
                                <option value="">Select a product...</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}">{{ $product->name }} (Rs. {{ $product->price }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Quantity *</label>
                            <input type="number" name="quantity" x-model="orderForm.quantity" required min="1" value="1" class="w-full border-gray-200 rounded-lg text-sm bg-gray-50 py-2 font-bold text-gray-900">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Remarks / Items</label>
                            <textarea name="remarks" x-model="orderForm.remarks" rows="2" class="w-full border-gray-200 rounded-lg text-sm bg-gray-50 py-2" placeholder="e.g. 1x Red T-Shirt XL"></textarea>
                        </div>

                        <button type="submit" class="w-full bg-blue-600 text-white font-bold py-3 rounded-xl hover:bg-blue-700 transition shadow-[0_4px_14px_0_rgba(0,0,0,0.1)] active:scale-95">
                            Create Order
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    <script>
        function facebookInbox() {
            return {
                selectedPageId: '',
                conversations: [],
                loadingConversations: false,
                selectedConversation: null,
                messages: [],
                loadingMessages: false,
                newMessage: '',
                sendingMessage: false,
                orderForm: {
                    name: '',
                    phone: '',
                    address: '',
                    product_id: '',
                    quantity: 1,
                    remarks: ''
                },

                get reversedMessages() {
                    // Graph API returns messages newest first usually, we need them oldest first to display top-down
                    return [...this.messages].reverse();
                },

                init() {
                    // Try to auto-select first page if exists
                    const select = document.querySelector('select[x-model="selectedPageId"]');
                    if(select && select.options.length > 1) {
                        this.selectedPageId = select.options[1].value;
                        this.fetchConversations();
                    }
                },

                async fetchConversations() {
                    if (!this.selectedPageId) return;
                    
                    this.loadingConversations = true;
                    this.conversations = [];
                    this.selectedConversation = null;
                    
                    try {
                        const res = await fetch(`/api/facebook/pages/${this.selectedPageId}/conversations`);
                        const data = await res.json();
                        if(data.data) {
                            this.conversations = data.data;
                        }
                    } catch (err) {
                        console.error("Failed to fetch conversations", err);
                    } finally {
                        this.loadingConversations = false;
                    }
                },

                async selectConversation(conv) {
                    this.selectedConversation = conv;
                    this.messages = [];
                    this.loadingMessages = true;
                    
                    try {
                        const res = await fetch(`/api/facebook/pages/${this.selectedPageId}/conversations/${conv.id}/messages`);
                        const data = await res.json();
                        if(data.data) {
                            this.messages = data.data;
                            this.scrollToBottom();
                        }
                    } catch (err) {
                        console.error("Failed to fetch messages", err);
                    } finally {
                        this.loadingMessages = false;
                    }
                },

                async sendMessage() {
                    if(!this.newMessage.trim() || !this.selectedConversation) return;
                    
                    const msgText = this.newMessage;
                    this.newMessage = '';
                    this.sendingMessage = true;

                    try {
                        const res = await fetch(`/api/facebook/pages/${this.selectedPageId}/conversations/${this.selectedConversation.id}/messages`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({ message: msgText })
                        });
                        const data = await res.json();
                        
                        if(data.success) {
                            // Optimistically add message
                            this.messages.unshift({
                                id: Date.now(),
                                message: msgText,
                                created_time: new Date().toISOString(),
                                from: { id: this.selectedPageId }
                            });
                            this.scrollToBottom();
                        } else {
                            alert("Failed to send message: " + (data.error || 'Unknown error'));
                        }
                    } catch (err) {
                        console.error("Failed to send message", err);
                        alert("Error sending message.");
                    } finally {
                        this.sendingMessage = false;
                    }
                },

                getParticipantName(conv) {
                    if(!conv.participants || !conv.participants.data) return 'Unknown';
                    const other = conv.participants.data.find(p => p.id !== this.selectedPageId);
                    return other ? other.name : 'Unknown';
                },

                getLastMessageText(conv) {
                    if(conv.messages && conv.messages.data && conv.messages.data.length > 0) {
                        return conv.messages.data[0].message;
                    }
                    return '';
                },

                formatDate(dateString) {
                    const d = new Date(dateString);
                    return d.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                },

                scrollToBottom() {
                    setTimeout(() => {
                        const container = document.getElementById('chat-messages-container');
                        if(container) {
                            container.scrollTop = container.scrollHeight;
                        }
                    }, 50);
                },

                fillOrderCustomer() {
                    if(this.selectedConversation) {
                        this.orderForm.name = this.getParticipantName(this.selectedConversation);
                    }
                },

                submitOrder(e) {
                    // You can add extra validation or AJAX submission here if needed
                    // For now, it will submit normally as a standard form
                }
            }
        }
    </script>
</x-app-layout>
