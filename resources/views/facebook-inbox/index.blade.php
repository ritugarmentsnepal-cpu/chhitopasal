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

                <!-- Search Input -->
                <div class="mt-3 relative">
                    <input type="text" x-model="searchQuery" placeholder="Search name or message..." 
                           class="w-full bg-gray-100 border-transparent focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 rounded-xl text-sm py-2 pl-9 pr-3 transition">
                    <svg class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 transform -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    
                    <button x-show="searchQuery" @click="searchQuery = ''" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
            </div>

            <!-- Conversations List -->
            <div class="flex-1 overflow-y-auto no-scrollbar relative p-2 space-y-1">
                <template x-if="loadingConversations">
                    <div class="flex justify-center p-8">
                        <svg class="animate-spin h-6 w-6 text-blue-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                    </div>
                </template>
                
                <template x-if="!loadingConversations && filteredConversations.length === 0 && selectedPageId">
                    <div class="text-center p-8 text-gray-400 text-sm font-bold">
                        <span x-show="!searchQuery">No conversations found.</span>
                        <span x-show="searchQuery">No matches found for "<span x-text="searchQuery"></span>"</span>
                    </div>
                </template>

                <template x-for="conv in filteredConversations" :key="conv.id">
                    <button @click="selectConversation(conv)" 
                            class="w-full text-left p-3 rounded-xl transition-all duration-200 relative"
                            :class="selectedConversation?.id === conv.id ? 'bg-blue-50 border border-blue-100' : 'hover:bg-gray-100 border border-transparent'">
                        <div class="flex justify-between items-start mb-1">
                            <span class="text-sm truncate" :class="conv.unread_count > 0 ? 'font-black text-gray-900' : 'font-bold text-gray-700'" x-text="getParticipantName(conv)"></span>
                            <span class="text-[10px] font-medium whitespace-nowrap ml-2" :class="conv.unread_count > 0 ? 'text-blue-600' : 'text-gray-400'" x-text="formatDate(conv.updated_time)"></span>
                        </div>
                        <p class="text-xs truncate pr-4" :class="conv.unread_count > 0 ? 'text-gray-900 font-medium' : 'text-gray-500'" x-text="getLastMessageText(conv)"></p>
                        <template x-if="conv.unread_count > 0">
                            <div class="absolute right-3 top-1/2 transform -translate-y-1/2 w-2.5 h-2.5 bg-blue-600 rounded-full"></div>
                        </template>
                    </button>
                </template>

                <!-- Load More Conversations -->
                <template x-if="nextConversationCursor">
                    <button @click="loadMoreConversations()" class="w-full p-3 text-sm text-blue-600 font-bold hover:bg-blue-50 rounded-xl transition flex justify-center items-center gap-2">
                        <svg x-show="loadingMoreConversations" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                        <span x-text="loadingMoreConversations ? 'Loading...' : 'Load older conversations'"></span>
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
                <div class="flex-1 flex flex-col h-full relative">
                    <!-- Chat Header -->
                    <div class="h-16 border-b border-gray-100 flex items-center px-6 bg-white shrink-0 shadow-sm z-10 justify-between">
                        <div class="font-black text-lg text-gray-900 flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-sm overflow-hidden">
                                <img x-show="getParticipantAvatar(selectedConversation)" :src="getParticipantAvatar(selectedConversation)" class="w-full h-full object-cover">
                                <span x-show="!getParticipantAvatar(selectedConversation)" x-text="getParticipantName(selectedConversation).charAt(0)"></span>
                            </div>
                            <span x-text="getParticipantName(selectedConversation)"></span>
                        </div>
                        
                        <!-- Header Actions (Native FB UI) -->
                        <div class="flex items-center gap-2">
                            <button @click="showToast('Conversation reported as spam')" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition" title="Spam/Report">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            </button>
                            <button @click="showToast('Conversation deleted')" class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition" title="Delete">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                            <button @click="toggleStar()" class="p-2 transition rounded-lg" :class="isStarred ? 'text-yellow-400 hover:bg-yellow-50' : 'text-gray-400 hover:text-gray-600 hover:bg-gray-100'" title="Star">
                                <svg class="w-5 h-5" :fill="isStarred ? 'currentColor' : 'none'" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path></svg>
                            </button>
                            <button @click="showToast('Marked as unread')" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition" title="Mark Unread">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                            </button>
                            <button @click="showToast('Marked as done')" class="p-2 text-gray-400 hover:text-green-600 hover:bg-green-50 rounded-lg transition" title="Mark Done">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            </button>
                            <button @click="fillOrderCustomer()" class="ml-2 text-xs bg-indigo-50 text-indigo-600 hover:bg-indigo-100 font-bold px-3 py-1.5 rounded-lg transition hidden md:block">
                                Copy to Order Form
                            </button>
                        </div>
                    </div>

                    <!-- Messages -->
                    <div class="flex-1 overflow-y-auto p-6 space-y-4 bg-gray-50/50" id="chat-messages-container" @click="showEmojiPicker = false">
                        
                        <!-- Load More Messages -->
                        <div class="flex justify-center pb-2">
                            <template x-if="nextMessageCursor && !loadingMessages">
                                <button @click="loadMoreMessages()" class="text-xs bg-white border border-gray-200 text-gray-600 hover:text-blue-600 hover:border-blue-200 font-bold px-4 py-2 rounded-full shadow-sm transition flex items-center gap-2">
                                    <svg x-show="loadingMoreMessages" class="animate-spin h-3 w-3" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                    <span x-text="loadingMoreMessages ? 'Loading...' : 'Load older messages'"></span>
                                </button>
                            </template>
                        </div>

                        <template x-if="loadingMessages">
                            <div class="flex justify-center py-4">
                                <svg class="animate-spin h-5 w-5 text-blue-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                            </div>
                        </template>

                        <template x-for="msg in reversedMessages" :key="msg.id">
                            <div class="flex flex-col" :class="msg.from.id === selectedPageId ? 'items-end' : 'items-start'">
                                <div class="flex gap-2 max-w-[75%]" :class="msg.from.id === selectedPageId ? 'flex-row-reverse' : ''">
                                    
                                    <!-- Avatar -->
                                    <div class="w-6 h-6 rounded-full bg-gray-200 shrink-0 mt-1 flex items-center justify-center overflow-hidden">
                                        <template x-if="msg.from.id === selectedPageId">
                                            <span class="text-[10px] text-gray-500 font-bold" x-text="getSelectedPageName().charAt(0)"></span>
                                        </template>
                                        <template x-if="msg.from.id !== selectedPageId">
                                            <span class="text-[10px] text-gray-500 font-bold" x-text="getParticipantName(selectedConversation).charAt(0)"></span>
                                        </template>
                                    </div>

                                    <!-- Message Bubble -->
                                    <div class="flex flex-col">
                                        <div class="px-4 py-2.5 rounded-2xl text-sm"
                                             :class="msg.from.id === selectedPageId ? 'bg-blue-600 text-white rounded-tr-sm' : 'bg-white border border-gray-200 text-gray-900 rounded-tl-sm'">
                                            <template x-if="msg.message">
                                                <span x-text="msg.message" style="white-space: pre-wrap;"></span>
                                            </template>
                                            <!-- Attachments (Native feature) -->
                                            <template x-if="msg.attachments && msg.attachments.data && msg.attachments.data.length > 0">
                                                <div class="mt-2 space-y-2">
                                                    <template x-for="att in msg.attachments.data">
                                                        <div>
                                                            <template x-if="att.image_data">
                                                                <img :src="att.image_data.url" class="rounded-lg max-w-full h-auto max-h-48">
                                                            </template>
                                                            <template x-if="!att.image_data">
                                                                <a :href="att.file_url" target="_blank" class="flex items-center gap-2 bg-black/10 p-2 rounded-lg text-xs hover:bg-black/20">
                                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                                                                    Attachment
                                                                </a>
                                                            </template>
                                                        </div>
                                                    </template>
                                                </div>
                                            </template>
                                        </div>
                                        <div class="flex items-center gap-1 mt-1" :class="msg.from.id === selectedPageId ? 'justify-end' : 'justify-start'">
                                            <span class="text-[10px] text-gray-400 font-medium" x-text="formatDate(msg.created_time)"></span>
                                            <template x-if="msg.from.id === selectedPageId">
                                                <span class="text-[10px] text-gray-400 font-medium whitespace-nowrap">· Sent by <span x-text="getSelectedPageName()"></span></span>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Input Area -->
                    <div class="p-3 bg-white border-t border-gray-100 shrink-0 relative">
                        <!-- Uploaded File Preview -->
                        <div x-show="selectedFile" class="mb-2 p-2 bg-gray-50 rounded-lg flex items-center justify-between border border-gray-200">
                            <div class="flex items-center gap-2 truncate">
                                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                                <span class="text-xs text-gray-700 font-medium" x-text="selectedFile ? selectedFile.name : ''"></span>
                            </div>
                            <button @click="clearFile()" class="text-gray-400 hover:text-red-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>

                        <!-- Emoji Picker Popover -->
                        <div x-show="showEmojiPicker" @click.away="showEmojiPicker = false" class="absolute bottom-full mb-2 left-10 bg-white border border-gray-200 shadow-xl rounded-xl p-2 z-50 flex flex-wrap gap-1 w-64 max-h-48 overflow-y-auto">
                            <template x-for="emoji in emojis">
                                <button @click="insertEmoji(emoji)" class="w-8 h-8 flex items-center justify-center hover:bg-gray-100 rounded text-lg transition" x-text="emoji"></button>
                            </template>
                        </div>

                        <form @submit.prevent="sendMessage" class="flex flex-col gap-2">
                            <div class="flex items-center gap-2">
                                <!-- Action Icons -->
                                <button type="button" @click="$refs.fileInput.click()" class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-full transition" title="Attach a file">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                                </button>
                                <input type="file" x-ref="fileInput" @change="handleFileSelect" class="hidden">

                                <button type="button" @click="showSavedRepliesModal = true" class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-full transition" title="Saved Replies">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
                                </button>

                                <button type="button" @click="showEmojiPicker = !showEmojiPicker" class="p-2 text-gray-400 hover:text-yellow-500 hover:bg-yellow-50 rounded-full transition" title="Choose an emoji">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </button>

                                <!-- Text Input -->
                                <input type="text" x-model="newMessage" placeholder="Type a reply..." 
                                       class="flex-1 bg-gray-50 border border-gray-200 rounded-full px-4 py-2 text-sm focus:border-blue-500 focus:ring-blue-500/20"
                                       :disabled="sendingMessage">
                                       
                                <!-- Thumbs Up / Send -->
                                <template x-if="!newMessage.trim() && !selectedFile">
                                    <button type="button" @click="sendThumbsUp()" :disabled="sendingMessage" class="p-2 text-blue-600 hover:bg-blue-50 rounded-full transition disabled:opacity-50" title="Send a Like">
                                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M2 10h3v10H2V10zm20.46 3.14c.48-.68.74-1.48.74-2.31 0-2.21-1.79-4-4-4h-5.26c.21-.57.36-1.19.36-1.83 0-2.36-1.57-4-3.48-4-.56 0-1.12.16-1.61.45l-.41.25c-.24.15-.36.43-.28.7l.55 1.95c.24.87.1 1.8-.39 2.54l-.45.69C7.8 8.08 7.37 8.5 6.8 8.8V20h11.16c1.37 0 2.58-.93 2.91-2.26l1.59-6.6zm-11.45-8.4l.2.12c1.33.81 1.76 2.38 1.15 3.86l-1.01 2.44h8.31c1.1 0 2 .9 2 2 0 .5-.2 1-.57 1.35l-1.6 1.6 1.25 1.25c.34.34.54.8.54 1.28 0 .48-.2 1-.57 1.35l-1.6 1.6 1.25 1.25c.34.34.54.8.54 1.28 0 1.1-.9 2-2 2H6.8c-.83 0-1.56-.47-1.88-1.18-.08-.18-.12-.38-.12-.59V8.8c0-.6.28-1.15.75-1.5l2.4-1.8c1.07-.81 2.33-1.67 2.33-3.2 0-.25-.04-.49-.1-.73z"/></svg>
                                    </button>
                                </template>
                                
                                <template x-if="newMessage.trim() || selectedFile">
                                    <button type="submit" :disabled="sendingMessage"
                                            class="bg-blue-600 text-white p-2 rounded-full hover:bg-blue-700 transition disabled:opacity-50 flex items-center justify-center w-10 h-10">
                                        <svg x-show="sendingMessage" class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                        <svg x-show="!sendingMessage" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                                    </button>
                                </template>
                            </div>
                        </form>
                    </div>

                    <!-- Toast Notification -->
                    <div x-show="toastMessage" x-transition.opacity
                         class="absolute bottom-20 left-1/2 transform -translate-x-1/2 bg-gray-900 text-white text-xs font-bold px-4 py-2 rounded-full shadow-lg z-50">
                        <span x-text="toastMessage"></span>
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
                <form action="{{ route('orders.store') }}" method="POST" id="quick-order-form">
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
                            <input type="number" name="quantity" x-model="orderForm.quantity" required min="1" class="w-full border-gray-200 rounded-lg text-sm bg-gray-50 py-2 font-bold text-gray-900">
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

        <!-- Saved Replies Modal -->
        <div x-show="showSavedRepliesModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" style="display: none;">
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-md overflow-hidden flex flex-col max-h-[80vh]" @click.away="showSavedRepliesModal = false">
                <div class="p-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                    <h3 class="font-black text-gray-900">Saved Replies</h3>
                    <button @click="showSavedRepliesModal = false" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                
                <div class="flex-1 overflow-y-auto p-4 space-y-2">
                    <template x-if="savedReplies.length === 0 && !loadingReplies">
                        <p class="text-sm text-gray-500 text-center py-4">No saved replies yet.</p>
                    </template>
                    <template x-for="reply in savedReplies" :key="reply.id">
                        <div class="border border-gray-200 rounded-lg p-3 hover:border-blue-300 hover:bg-blue-50 cursor-pointer transition relative group" @click="useSavedReply(reply)">
                            <h4 class="font-bold text-sm text-gray-900 mb-1" x-text="reply.title"></h4>
                            <p class="text-xs text-gray-500 line-clamp-2" x-text="reply.content"></p>
                            <button @click.stop="deleteSavedReply(reply.id)" class="absolute top-2 right-2 p-1 text-gray-300 hover:text-red-500 opacity-0 group-hover:opacity-100 transition bg-white rounded-full">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </div>
                    </template>
                </div>

                <div class="p-4 border-t border-gray-100 bg-gray-50">
                    <h4 class="text-xs font-bold text-gray-700 mb-2">Create New</h4>
                    <input type="text" x-model="newReplyTitle" placeholder="Title (e.g. Greeting)" class="w-full mb-2 border-gray-200 rounded-lg text-sm p-2">
                    <textarea x-model="newReplyContent" placeholder="Message content..." class="w-full mb-2 border-gray-200 rounded-lg text-sm p-2" rows="2"></textarea>
                    <button @click="saveNewReply()" :disabled="!newReplyTitle || !newReplyContent || savingReply" class="w-full bg-gray-900 text-white font-bold py-2 rounded-lg hover:bg-black transition disabled:opacity-50">
                        <span x-text="savingReply ? 'Saving...' : 'Save Reply'"></span>
                    </button>
                </div>
            </div>
        </div>

    </div>

    <script>
        function facebookInbox() {
            return {
                selectedPageId: '',
                conversations: [],
                loadingConversations: false,
                loadingMoreConversations: false,
                nextConversationCursor: null,
                selectedConversation: null,
                messages: [],
                loadingMessages: false,
                loadingMoreMessages: false,
                nextMessageCursor: null,
                newMessage: '',
                sendingMessage: false,
                searchQuery: '',
                
                // Native Features State
                isStarred: false,
                toastMessage: '',
                showEmojiPicker: false,
                selectedFile: null,
                
                // Saved Replies State
                showSavedRepliesModal: false,
                savedReplies: [],
                loadingReplies: false,
                savingReply: false,
                newReplyTitle: '',
                newReplyContent: '',

                emojis: ['😀','😂','🥰','😎','🤔','👍','❤️','🔥','🎉','✨','✅','👋','🙏','🛒','📦'],
                
                orderForm: {
                    name: '',
                    phone: '',
                    address: '',
                    product_id: '',
                    quantity: 1,
                    remarks: ''
                },

                get reversedMessages() {
                    return [...this.messages].reverse();
                },

                get filteredConversations() {
                    if (!this.searchQuery.trim()) return this.conversations;
                    const q = this.searchQuery.toLowerCase();
                    return this.conversations.filter(conv => {
                        const name = this.getParticipantName(conv).toLowerCase();
                        const lastMsg = this.getLastMessageText(conv).toLowerCase();
                        return name.includes(q) || lastMsg.includes(q);
                    });
                },

                init() {
                    const select = document.querySelector('select[x-model="selectedPageId"]');
                    if(select && select.options.length > 1) {
                        this.selectedPageId = select.options[1].value;
                        this.fetchConversations();
                    }
                    this.fetchSavedReplies();
                },

                async fetchConversations() {
                    if (!this.selectedPageId) return;
                    this.loadingConversations = true;
                    this.conversations = [];
                    this.nextConversationCursor = null;
                    this.selectedConversation = null;
                    try {
                        const res = await fetch(`/api/facebook/pages/${this.selectedPageId}/conversations`);
                        const data = await res.json();
                        if(data.data) {
                            this.conversations = data.data;
                            if (data.paging && data.paging.cursors && data.paging.cursors.after) {
                                this.nextConversationCursor = data.paging.cursors.after;
                            } else {
                                this.nextConversationCursor = null;
                            }
                        }
                    } catch (err) {
                        console.error("Failed to fetch conversations", err);
                    } finally {
                        this.loadingConversations = false;
                    }
                },

                async loadMoreConversations() {
                    if (!this.selectedPageId || !this.nextConversationCursor || this.loadingMoreConversations) return;
                    this.loadingMoreConversations = true;
                    try {
                        const res = await fetch(`/api/facebook/pages/${this.selectedPageId}/conversations?cursor=${this.nextConversationCursor}`);
                        const data = await res.json();
                        if(data.data) {
                            this.conversations = [...this.conversations, ...data.data];
                            // Check if there is next page. Facebook returns paging.next if there is more.
                            if (data.paging && data.paging.cursors && data.paging.cursors.after && data.data.length > 0) {
                                this.nextConversationCursor = data.paging.cursors.after;
                            } else {
                                this.nextConversationCursor = null;
                            }
                        }
                    } catch (err) {
                        console.error("Failed to load more conversations", err);
                    } finally {
                        this.loadingMoreConversations = false;
                    }
                },

                async selectConversation(conv) {
                    this.selectedConversation = conv;
                    this.messages = [];
                    this.loadingMessages = true;
                    this.nextMessageCursor = null;
                    this.isStarred = false; // Reset state per convo
                    
                    try {
                        // Mark as read immediately on UI side
                        if (conv.unread_count > 0) {
                            conv.unread_count = 0;
                            // Make background API call to FB to mark as seen
                            fetch(`/api/facebook/pages/${this.selectedPageId}/conversations/${conv.id}/mark-read`, {
                                method: 'POST',
                                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') }
                            }).catch(e => console.error(e));
                        }

                        const res = await fetch(`/api/facebook/pages/${this.selectedPageId}/conversations/${conv.id}/messages`);
                        const data = await res.json();
                        if(data.data) {
                            this.messages = data.data;
                            if (data.paging && data.paging.cursors && data.paging.cursors.after) {
                                this.nextMessageCursor = data.paging.cursors.after;
                            } else {
                                this.nextMessageCursor = null;
                            }
                            this.scrollToBottom();
                        }
                    } catch (err) {
                        console.error("Failed to fetch messages", err);
                    } finally {
                        this.loadingMessages = false;
                    }
                },

                async loadMoreMessages() {
                    if (!this.selectedConversation || !this.nextMessageCursor || this.loadingMoreMessages) return;
                    this.loadingMoreMessages = true;
                    
                    try {
                        // Store current scroll height
                        const container = document.getElementById('chat-messages-container');
                        const oldScrollHeight = container ? container.scrollHeight : 0;

                        const res = await fetch(`/api/facebook/pages/${this.selectedPageId}/conversations/${this.selectedConversation.id}/messages?cursor=${this.nextMessageCursor}`);
                        const data = await res.json();
                        if(data.data) {
                            this.messages = [...this.messages, ...data.data];
                            
                            if (data.paging && data.paging.cursors && data.paging.cursors.after && data.data.length > 0) {
                                this.nextMessageCursor = data.paging.cursors.after;
                            } else {
                                this.nextMessageCursor = null;
                            }

                            // Restore scroll position so it doesn't jump
                            setTimeout(() => {
                                if (container) {
                                    container.scrollTop = container.scrollHeight - oldScrollHeight;
                                }
                            }, 50);
                        }
                    } catch (err) {
                        console.error("Failed to load more messages", err);
                    } finally {
                        this.loadingMoreMessages = false;
                    }
                },

                handleFileSelect(e) {
                    const file = e.target.files[0];
                    if(file) {
                        this.selectedFile = file;
                    }
                },

                clearFile() {
                    this.selectedFile = null;
                    this.$refs.fileInput.value = '';
                },

                insertEmoji(emoji) {
                    this.newMessage += emoji;
                    this.showEmojiPicker = false;
                    // Focus input
                    const input = document.querySelector('input[x-model="newMessage"]');
                    if(input) input.focus();
                },

                sendThumbsUp() {
                    this.newMessage = '👍';
                    this.sendMessage();
                },

                async sendMessage() {
                    if((!this.newMessage.trim() && !this.selectedFile) || !this.selectedConversation) return;
                    
                    this.sendingMessage = true;
                    
                    const formData = new FormData();
                    formData.append('message', this.newMessage.trim());
                    if(this.selectedFile) {
                        formData.append('file', this.selectedFile);
                    }

                    // Save state for optimistic UI
                    const msgText = this.newMessage.trim();
                    const hasFile = !!this.selectedFile;
                    
                    // Clear input immediately for UX
                    this.newMessage = '';
                    this.clearFile();

                    try {
                        const res = await fetch(`/api/facebook/pages/${this.selectedPageId}/conversations/${this.selectedConversation.id}/messages`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: formData
                        });
                        const data = await res.json();
                        
                        if(data.success) {
                            // Optimistically add message
                            this.messages.unshift({
                                id: Date.now(),
                                message: msgText + (hasFile ? '\n[File Attached]' : ''),
                                created_time: new Date().toISOString(),
                                from: { id: this.selectedPageId }
                            });
                            this.scrollToBottom();
                        } else {
                            alert("Failed to send message: " + (data.error || 'Unknown error'));
                            this.newMessage = msgText; // restore
                        }
                    } catch (err) {
                        console.error("Failed to send message", err);
                        alert("Error sending message.");
                        this.newMessage = msgText; // restore
                    } finally {
                        this.sendingMessage = false;
                    }
                },

                // --- Saved Replies Methods ---
                async fetchSavedReplies() {
                    try {
                        const res = await fetch('/api/facebook/saved-replies');
                        const data = await res.json();
                        if(data.data) {
                            this.savedReplies = data.data;
                        }
                    } catch (e) { console.error(e); }
                },

                async saveNewReply() {
                    this.savingReply = true;
                    try {
                        const res = await fetch('/api/facebook/saved-replies', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({
                                title: this.newReplyTitle,
                                content: this.newReplyContent
                            })
                        });
                        const data = await res.json();
                        if(data.success) {
                            this.savedReplies.push(data.data);
                            this.newReplyTitle = '';
                            this.newReplyContent = '';
                        }
                    } catch (e) { console.error(e); }
                    this.savingReply = false;
                },

                async deleteSavedReply(id) {
                    if(!confirm('Delete this saved reply?')) return;
                    try {
                        await fetch(`/api/facebook/saved-replies/${id}`, {
                            method: 'DELETE',
                            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') }
                        });
                        this.savedReplies = this.savedReplies.filter(r => r.id !== id);
                    } catch (e) { console.error(e); }
                },

                useSavedReply(reply) {
                    this.newMessage = reply.content;
                    this.showSavedRepliesModal = false;
                    const input = document.querySelector('input[x-model="newMessage"]');
                    if(input) input.focus();
                },

                // --- UI Helpers ---
                toggleStar() {
                    this.isStarred = !this.isStarred;
                    if(this.isStarred) this.showToast('Conversation starred');
                },

                showToast(msg) {
                    this.toastMessage = msg;
                    setTimeout(() => { this.toastMessage = ''; }, 3000);
                },

                getParticipantName(conv) {
                    if(!conv || !conv.participants || !conv.participants.data) return 'Unknown';
                    const other = conv.participants.data.find(p => p.id !== this.selectedPageId);
                    return (other && other.name) ? other.name : 'Unknown';
                },

                getParticipantAvatar(conv) {
                    // Graph API doesn't return avatars by default for privacy, but we can generate an initial avatar.
                    return null; // Fallback to initial
                },
                
                getSelectedPageName() {
                    const select = document.querySelector('select[x-model="selectedPageId"]');
                    if(select && this.selectedPageId) {
                        const option = select.querySelector(`option[value="${this.selectedPageId}"]`);
                        return option ? option.innerText : 'Page';
                    }
                    return 'Page';
                },

                getLastMessageText(conv) {
                    if(conv && conv.messages && conv.messages.data && conv.messages.data.length > 0) {
                        return conv.messages.data[0].message || '[Attachment]';
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
                }
            }
        }
    </script>
</x-app-layout>
