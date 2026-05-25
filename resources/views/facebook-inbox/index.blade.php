<x-app-layout>
    <div x-data="facebookInbox('{{ $pages->count() > 0 ? $pages->first()->page_id : '' }}')" x-init="init()" class="flex h-[calc(100vh-72px)] bg-white dark:bg-gray-900 border-t border-gray-100 dark:border-gray-800">
        
        <!-- Sidebar: Threads & Posts -->
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
                    <select x-model="selectedPageId" @change="fetchConversations(); fetchPosts();" class="w-full bg-gray-50 border border-gray-200 rounded-xl text-sm font-bold text-gray-700 py-2.5 pl-3 pr-8 focus:border-blue-500 focus:ring-blue-500/20 mb-3">
                        <option value="">Select a Page...</option>
                        @foreach($pages as $page)
                            <option value="{{ $page->page_id }}">{{ $page->page_name }}</option>
                        @endforeach
                    </select>

                    <!-- Tabs Toggle -->
                    <div class="flex bg-gray-100 rounded-lg p-1">
                        <button @click="activeTab = 'messages'; searchQuery = ''" 
                                class="flex-1 text-xs font-bold py-1.5 rounded-md transition"
                                :class="activeTab === 'messages' ? 'bg-white text-blue-600 shadow-sm' : 'text-gray-500 hover:text-gray-700'">
                            Messages
                        </button>
                        <button @click="activeTab = 'comments'; searchQuery = ''" 
                                class="flex-1 text-xs font-bold py-1.5 rounded-md transition"
                                :class="activeTab === 'comments' ? 'bg-white text-blue-600 shadow-sm' : 'text-gray-500 hover:text-gray-700'">
                            Comments
                        </button>
                    </div>
                @else
                    <div class="text-xs text-gray-500 bg-yellow-50 p-3 rounded-lg border border-yellow-100">
                        No pages connected. Click "Connect Pages" to authenticate with Facebook.
                    </div>
                @endif

                <!-- Search Input -->
                <div class="mt-3 relative">
                    <input type="text" x-model="searchQuery" :placeholder="activeTab === 'messages' ? 'Search messages...' : 'Search posts...'" 
                           class="w-full bg-gray-100 border-transparent focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 rounded-xl text-sm py-2 pl-9 pr-3 transition">
                    <svg class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 transform -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    
                    <button x-show="searchQuery" @click="searchQuery = ''" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
            </div>

            <!-- List Sidebar -->
            <div class="flex-1 overflow-y-auto no-scrollbar relative p-2 space-y-1">
                
                <!-- MESSAGES TAB -->
                <div x-show="activeTab === 'messages'">
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

                    <template x-if="nextConversationCursor">
                        <button @click="loadMoreConversations()" class="w-full p-3 text-sm text-blue-600 font-bold hover:bg-blue-50 rounded-xl transition flex justify-center items-center gap-2">
                            <svg x-show="loadingMoreConversations" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                            <span x-text="loadingMoreConversations ? 'Loading...' : 'Load older conversations'"></span>
                        </button>
                    </template>
                </div>

                <!-- COMMENTS TAB -->
                <div x-show="activeTab === 'comments'" style="display: none;">
                    <template x-if="loadingPosts">
                        <div class="flex justify-center p-8">
                            <svg class="animate-spin h-6 w-6 text-blue-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                        </div>
                    </template>
                    
                    <template x-if="!loadingPosts && filteredPosts.length === 0 && selectedPageId">
                        <div class="text-center p-8 text-gray-400 text-sm font-bold">
                            <span x-show="!searchQuery">No posts found.</span>
                            <span x-show="searchQuery">No matches found for "<span x-text="searchQuery"></span>"</span>
                        </div>
                    </template>

                    <template x-for="post in filteredPosts" :key="post.id">
                        <button @click="selectPost(post)" 
                                class="w-full text-left p-3 rounded-xl transition-all duration-200 relative mb-2"
                                :class="selectedPost?.id === post.id ? 'bg-blue-50 border border-blue-100' : 'hover:bg-gray-100 border border-transparent'">
                            <div class="flex gap-3">
                                <template x-if="post.full_picture">
                                    <div class="w-12 h-12 rounded-lg bg-gray-200 shrink-0 overflow-hidden">
                                        <img :src="post.full_picture" class="w-full h-full object-cover">
                                    </div>
                                </template>
                                <template x-if="!post.full_picture">
                                    <div class="w-12 h-12 rounded-lg bg-gray-200 shrink-0 flex items-center justify-center text-gray-400">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"></path></svg>
                                    </div>
                                </template>
                                <div class="flex-1 min-w-0">
                                    <div class="flex justify-between items-start mb-1">
                                        <span class="font-bold text-xs text-gray-900 truncate">Page Post</span>
                                        <span class="text-[10px] text-gray-400 font-medium whitespace-nowrap ml-2" x-text="formatDate(post.created_time)"></span>
                                    </div>
                                    <p class="text-xs text-gray-500 line-clamp-2" x-text="post.message || 'No text content'"></p>
                                </div>
                            </div>
                        </button>
                    </template>

                    <template x-if="nextPostCursor">
                        <button @click="loadMorePosts()" class="w-full p-3 text-sm text-blue-600 font-bold hover:bg-blue-50 rounded-xl transition flex justify-center items-center gap-2">
                            <svg x-show="loadingMorePosts" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                            <span x-text="loadingMorePosts ? 'Loading...' : 'Load older posts'"></span>
                        </button>
                    </template>
                </div>

            </div>
        </div>

        <!-- Middle: Chat / Comments Area -->
        <div class="flex-1 flex flex-col relative bg-white dark:bg-gray-900 min-w-0">
            
            <!-- MESSAGES TAB MAIN -->
            <div x-show="activeTab === 'messages'" class="flex-1 flex flex-col h-full w-full">
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
                                    <span x-text="getParticipantName(selectedConversation).charAt(0)"></span>
                                </div>
                                <span x-text="getParticipantName(selectedConversation)"></span>
                            </div>
                            
                            <!-- Header Actions -->
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
                                <button @click="showToast('Marked as done')" class="p-2 text-gray-400 hover:text-green-600 hover:bg-green-50 rounded-lg transition" title="Mark Done">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                </button>
                                <button @click="fillOrderCustomer()" class="ml-2 text-xs bg-indigo-50 text-indigo-600 hover:bg-indigo-100 font-bold px-3 py-1.5 rounded-lg transition hidden md:block">
                                    Copy to Order Form
                                </button>
                            </div>
                        </div>

                        <!-- Messages Content -->
                        <div class="flex-1 overflow-y-auto p-6 space-y-4 bg-gray-50/50" id="chat-messages-container" @click="showEmojiPicker = false">
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
                                        <div class="w-6 h-6 rounded-full bg-gray-200 shrink-0 mt-1 flex items-center justify-center overflow-hidden">
                                            <template x-if="msg.from.id === selectedPageId">
                                                <span class="text-[10px] text-gray-500 font-bold" x-text="getSelectedPageName().charAt(0)"></span>
                                            </template>
                                            <template x-if="msg.from.id !== selectedPageId">
                                                <span class="text-[10px] text-gray-500 font-bold" x-text="getParticipantName(selectedConversation).charAt(0)"></span>
                                            </template>
                                        </div>

                                        <div class="flex flex-col">
                                            <div class="px-4 py-2.5 rounded-2xl text-sm"
                                                 :class="msg.from.id === selectedPageId ? 'bg-blue-600 text-white rounded-tr-sm' : 'bg-white border border-gray-200 text-gray-900 rounded-tl-sm'">
                                                <template x-if="msg.message">
                                                    <span x-text="msg.message" style="white-space: pre-wrap;"></span>
                                                </template>
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
                            <div x-show="selectedFile" class="mb-2 p-2 bg-gray-50 rounded-lg flex items-center justify-between border border-gray-200">
                                <div class="flex items-center gap-2 truncate">
                                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                                    <span class="text-xs text-gray-700 font-medium" x-text="selectedFile ? selectedFile.name : ''"></span>
                                </div>
                                <button @click="clearFile()" class="text-gray-400 hover:text-red-500">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </button>
                            </div>

                            <div x-show="showEmojiPicker" @click.away="showEmojiPicker = false" class="absolute bottom-full mb-2 left-10 bg-white border border-gray-200 shadow-xl rounded-xl p-2 z-50 flex flex-wrap gap-1 w-64 max-h-48 overflow-y-auto">
                                <template x-for="emoji in emojis">
                                    <button @click="insertEmoji(emoji)" class="w-8 h-8 flex items-center justify-center hover:bg-gray-100 rounded text-lg transition" x-text="emoji"></button>
                                </template>
                            </div>

                            <form @submit.prevent="sendMessage" class="flex flex-col gap-2">
                                <div class="flex items-center gap-2">
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

                                    <input type="text" x-model="newMessage" placeholder="Type a reply..." 
                                           class="flex-1 bg-gray-50 border border-gray-200 rounded-full px-4 py-2 text-sm focus:border-blue-500 focus:ring-blue-500/20"
                                           :disabled="sendingMessage">
                                           
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
                    </div>
                </template>
            </div>

            <!-- COMMENTS TAB MAIN -->
            <div x-show="activeTab === 'comments'" style="display: none;" class="flex-1 flex flex-col h-full w-full">
                <template x-if="!selectedPost">
                    <div class="flex-1 flex items-center justify-center flex-col text-gray-400">
                        <svg class="w-16 h-16 mb-4 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
                        <p class="font-bold">Select a post to view comments</p>
                    </div>
                </template>

                <template x-if="selectedPost">
                    <div class="flex-1 flex flex-col h-full relative">
                        <!-- Post Header -->
                        <div class="p-4 border-b border-gray-100 flex gap-4 bg-white shrink-0 shadow-sm z-10">
                            <template x-if="selectedPost.full_picture">
                                <img :src="selectedPost.full_picture" class="w-16 h-16 rounded-lg object-cover border border-gray-200">
                            </template>
                            <div class="flex-1">
                                <h3 class="font-black text-sm text-gray-900 mb-1">Page Post</h3>
                                <p class="text-sm text-gray-600 line-clamp-2" x-text="selectedPost.message"></p>
                                <span class="text-xs text-gray-400 mt-1 block" x-text="formatDate(selectedPost.created_time)"></span>
                            </div>
                        </div>

                        <!-- Comments Thread -->
                        <div class="flex-1 overflow-y-auto p-6 space-y-4 bg-gray-50/50">
                            <template x-if="loadingComments">
                                <div class="flex justify-center py-4">
                                    <svg class="animate-spin h-5 w-5 text-blue-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                </div>
                            </template>
                            
                            <template x-if="!loadingComments && comments.length === 0">
                                <div class="text-center py-8 text-gray-400 font-bold text-sm">
                                    No comments on this post yet.
                                </div>
                            </template>

                            <template x-for="comment in comments" :key="comment.id">
                                <div class="flex flex-col gap-2">
                                    <!-- Parent Comment -->
                                    <div class="flex gap-3">
                                        <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold text-xs shrink-0">
                                            <span x-text="comment.from?.name?.charAt(0) || 'U'"></span>
                                        </div>
                                        <div class="flex-1">
                                            <div class="bg-white border border-gray-200 rounded-2xl p-3 shadow-sm relative group" :class="comment.is_hidden ? 'opacity-50' : ''">
                                                <div class="flex justify-between items-start mb-1">
                                                    <span class="font-bold text-sm text-gray-900 cursor-pointer hover:underline" @click="fillOrderCustomerFromName(comment.from?.name)" x-text="comment.from?.name || 'Unknown User'"></span>
                                                    <span class="text-[10px] text-gray-400" x-text="formatDate(comment.created_time)"></span>
                                                </div>
                                                <p class="text-sm text-gray-700" x-text="comment.message"></p>
                                                
                                                <!-- Action Menu -->
                                                <div class="absolute right-2 top-2 opacity-0 group-hover:opacity-100 transition bg-white/90 backdrop-blur-sm rounded-lg shadow-sm border border-gray-100 flex p-1 gap-1">
                                                    <button @click="toggleLikeComment(comment)" class="p-1 rounded hover:bg-gray-100" :class="comment.user_likes ? 'text-blue-600' : 'text-gray-400'" title="Like">
                                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M2 10h3v10H2V10zm20.46 3.14c.48-.68.74-1.48.74-2.31 0-2.21-1.79-4-4-4h-5.26c.21-.57.36-1.19.36-1.83 0-2.36-1.57-4-3.48-4-.56 0-1.12.16-1.61.45l-.41.25c-.24.15-.36.43-.28.7l.55 1.95c.24.87.1 1.8-.39 2.54l-.45.69C7.8 8.08 7.37 8.5 6.8 8.8V20h11.16c1.37 0 2.58-.93 2.91-2.26l1.59-6.6zm-11.45-8.4l.2.12c1.33.81 1.76 2.38 1.15 3.86l-1.01 2.44h8.31c1.1 0 2 .9 2 2 0 .5-.2 1-.57 1.35l-1.6 1.6 1.25 1.25c.34.34.54.8.54 1.28 0 .48-.2 1-.57 1.35l-1.6 1.6 1.25 1.25c.34.34.54.8.54 1.28 0 1.1-.9 2-2 2H6.8c-.83 0-1.56-.47-1.88-1.18-.08-.18-.12-.38-.12-.59V8.8c0-.6.28-1.15.75-1.5l2.4-1.8c1.07-.81 2.33-1.67 2.33-3.2 0-.25-.04-.49-.1-.73z"/></svg>
                                                    </button>
                                                    <button @click="toggleHideComment(comment)" class="p-1 text-gray-400 hover:text-yellow-600 hover:bg-yellow-50 rounded" :title="comment.is_hidden ? 'Unhide' : 'Hide'">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path x-show="!comment.is_hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                                                            <path x-show="comment.is_hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                            <path x-show="comment.is_hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                        </svg>
                                                    </button>
                                                    <button @click="deleteComment(comment.id)" class="p-1 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded" title="Delete">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                    </button>
                                                </div>
                                            </div>

                                            <div class="flex items-center gap-3 mt-1 ml-2">
                                                <button @click="toggleReplyInput(comment.id)" class="text-xs font-bold text-gray-500 hover:text-blue-600">Reply</button>
                                                <span x-show="comment.is_hidden" class="text-[10px] font-bold text-red-500 uppercase tracking-wider">Hidden</span>
                                                <span x-show="comment.user_likes" class="text-[10px] font-bold text-blue-500 uppercase tracking-wider flex items-center gap-1"><svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24"><path d="M2 10h3v10H2V10zm20.46 3.14c.48-.68.74-1.48.74-2.31 0-2.21-1.79-4-4-4h-5.26c.21-.57.36-1.19.36-1.83 0-2.36-1.57-4-3.48-4-.56 0-1.12.16-1.61.45l-.41.25c-.24.15-.36.43-.28.7l.55 1.95c.24.87.1 1.8-.39 2.54l-.45.69C7.8 8.08 7.37 8.5 6.8 8.8V20h11.16c1.37 0 2.58-.93 2.91-2.26l1.59-6.6zm-11.45-8.4l.2.12c1.33.81 1.76 2.38 1.15 3.86l-1.01 2.44h8.31c1.1 0 2 .9 2 2 0 .5-.2 1-.57 1.35l-1.6 1.6 1.25 1.25c.34.34.54.8.54 1.28 0 .48-.2 1-.57 1.35l-1.6 1.6 1.25 1.25c.34.34.54.8.54 1.28 0 1.1-.9 2-2 2H6.8c-.83 0-1.56-.47-1.88-1.18-.08-.18-.12-.38-.12-.59V8.8c0-.6.28-1.15.75-1.5l2.4-1.8c1.07-.81 2.33-1.67 2.33-3.2 0-.25-.04-.49-.1-.73z"/></svg> Liked</span>
                                            </div>

                                            <!-- Reply Input Box -->
                                            <div x-show="activeReplyId === comment.id" class="mt-2 flex gap-2">
                                                <input type="text" x-model="replyText" placeholder="Write a reply..." class="flex-1 bg-white border border-gray-200 rounded-full px-3 py-1.5 text-xs focus:border-blue-500 focus:ring-blue-500/20" @keydown.enter="submitCommentReply(comment.id)">
                                                <button @click="submitCommentReply(comment.id)" class="bg-blue-600 text-white rounded-full px-3 py-1.5 text-xs font-bold hover:bg-blue-700 transition" :disabled="sendingCommentReply">Send</button>
                                            </div>

                                            <!-- Nested Replies -->
                                            <template x-if="comment.comments && comment.comments.data && comment.comments.data.length > 0">
                                                <div class="mt-3 space-y-3 border-l-2 border-gray-200 pl-4 ml-2">
                                                    <template x-for="reply in comment.comments.data" :key="reply.id">
                                                        <div class="flex gap-2">
                                                            <div class="w-6 h-6 rounded-full bg-blue-50 flex items-center justify-center text-blue-600 font-bold text-[10px] shrink-0">
                                                                <span x-text="reply.from?.name?.charAt(0) || 'U'"></span>
                                                            </div>
                                                            <div class="flex-1">
                                                                <div class="bg-gray-100 rounded-2xl p-2.5 shadow-sm inline-block group relative">
                                                                    <div class="flex justify-between items-center gap-4 mb-0.5">
                                                                        <span class="font-bold text-xs text-gray-900 cursor-pointer hover:underline" @click="fillOrderCustomerFromName(reply.from?.name)" x-text="reply.from?.name || 'Unknown User'"></span>
                                                                    </div>
                                                                    <p class="text-xs text-gray-700" x-text="reply.message"></p>
                                                                    
                                                                    <!-- Action Menu for Reply -->
                                                                    <div class="absolute -right-8 top-1/2 transform -translate-y-1/2 opacity-0 group-hover:opacity-100 transition flex gap-1">
                                                                        <button @click="deleteComment(reply.id, comment.id)" class="p-1 text-gray-400 hover:text-red-600 bg-white shadow-sm rounded-full" title="Delete">
                                                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                                <div class="flex items-center gap-2 mt-0.5 ml-1">
                                                                    <span class="text-[10px] text-gray-400" x-text="formatDate(reply.created_time)"></span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </template>
                                                </div>
                                            </template>

                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Toast Notification -->
            <div x-show="toastMessage" x-transition.opacity
                 class="absolute bottom-20 left-1/2 transform -translate-x-1/2 bg-gray-900 text-white text-xs font-bold px-4 py-2 rounded-full shadow-lg z-50">
                <span x-text="toastMessage"></span>
            </div>
        </div>

        <!-- Right Sidebar: Quick Order Form -->
        <div class="w-80 border-l border-gray-100 bg-white flex flex-col shrink-0 overflow-y-auto">
            <div class="p-4 border-b border-gray-100 bg-gray-50/50 sticky top-0 z-10 backdrop-blur-md">
                <h3 class="font-black text-gray-900">Quick Order</h3>
                <p class="text-[10px] text-gray-500">Create an order from this chat</p>
            </div>
            <div class="p-4">
                <form @submit.prevent="submitOrderForm" id="quick-order-form">
                    
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

                        <button type="submit" :disabled="submittingOrder" class="w-full bg-blue-600 text-white font-bold py-3 rounded-xl hover:bg-blue-700 transition shadow-[0_4px_14px_0_rgba(0,0,0,0.1)] active:scale-95 flex items-center justify-center gap-2 disabled:opacity-50">
                            <svg x-show="submittingOrder" class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                            <span x-text="submittingOrder ? 'Creating...' : 'Create Order'"></span>
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
        function facebookInbox(initialPageId) {
            return {
                selectedPageId: initialPageId || '',
                activeTab: 'messages', // 'messages' or 'comments'
                searchQuery: '',
                
                // Messages State
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
                
                // Posts & Comments State
                posts: [],
                loadingPosts: false,
                loadingMorePosts: false,
                nextPostCursor: null,
                selectedPost: null,
                comments: [],
                loadingComments: false,
                activeReplyId: null,
                replyText: '',
                sendingCommentReply: false,

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
                submittingOrder: false,

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

                get filteredPosts() {
                    if (!this.searchQuery.trim()) return this.posts;
                    const q = this.searchQuery.toLowerCase();
                    return this.posts.filter(post => {
                        const msg = (post.message || '').toLowerCase();
                        return msg.includes(q);
                    });
                },

                init() {
                    if (this.selectedPageId) {
                        this.fetchConversations();
                        this.fetchPosts();
                    }
                    this.fetchSavedReplies();
                },

                // --- Messages Methods ---
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
                    this.isStarred = false;
                    
                    try {
                        if (conv.unread_count > 0) {
                            conv.unread_count = 0;
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
                            setTimeout(() => {
                                if (container) container.scrollTop = container.scrollHeight - oldScrollHeight;
                            }, 50);
                        }
                    } catch (err) {
                        console.error("Failed to load more messages", err);
                    } finally {
                        this.loadingMoreMessages = false;
                    }
                },

                async sendMessage() {
                    if((!this.newMessage.trim() && !this.selectedFile) || !this.selectedConversation) return;
                    this.sendingMessage = true;
                    
                    const formData = new FormData();
                    formData.append('message', this.newMessage.trim());
                    if(this.selectedFile) formData.append('file', this.selectedFile);

                    const msgText = this.newMessage.trim();
                    const hasFile = !!this.selectedFile;
                    this.newMessage = '';
                    this.clearFile();

                    try {
                        const res = await fetch(`/api/facebook/pages/${this.selectedPageId}/conversations/${this.selectedConversation.id}/messages`, {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
                            body: formData
                        });
                        const data = await res.json();
                        
                        if(data.success) {
                            this.messages.unshift({
                                id: Date.now(),
                                message: msgText + (hasFile ? '\n[File Attached]' : ''),
                                created_time: new Date().toISOString(),
                                from: { id: this.selectedPageId }
                            });
                            this.scrollToBottom();
                        } else {
                            alert("Failed to send message.");
                            this.newMessage = msgText;
                        }
                    } catch (err) {
                        alert("Error sending message.");
                        this.newMessage = msgText;
                    } finally {
                        this.sendingMessage = false;
                    }
                },

                // --- Posts & Comments Methods ---
                async fetchPosts() {
                    if (!this.selectedPageId) return;
                    this.loadingPosts = true;
                    this.posts = [];
                    this.nextPostCursor = null;
                    this.selectedPost = null;
                    try {
                        const res = await fetch(`/api/facebook/pages/${this.selectedPageId}/posts`);
                        const data = await res.json();
                        if(data.data) {
                            this.posts = data.data;
                            if (data.paging && data.paging.cursors && data.paging.cursors.after) {
                                this.nextPostCursor = data.paging.cursors.after;
                            } else {
                                this.nextPostCursor = null;
                            }
                        }
                    } catch (err) {
                        console.error("Failed to fetch posts", err);
                    } finally {
                        this.loadingPosts = false;
                    }
                },

                async loadMorePosts() {
                    if (!this.selectedPageId || !this.nextPostCursor || this.loadingMorePosts) return;
                    this.loadingMorePosts = true;
                    try {
                        const res = await fetch(`/api/facebook/pages/${this.selectedPageId}/posts?cursor=${this.nextPostCursor}`);
                        const data = await res.json();
                        if(data.data) {
                            this.posts = [...this.posts, ...data.data];
                            if (data.paging && data.paging.cursors && data.paging.cursors.after && data.data.length > 0) {
                                this.nextPostCursor = data.paging.cursors.after;
                            } else {
                                this.nextPostCursor = null;
                            }
                        }
                    } catch (err) {
                        console.error("Failed to load more posts", err);
                    } finally {
                        this.loadingMorePosts = false;
                    }
                },

                async selectPost(post) {
                    this.selectedPost = post;
                    this.comments = [];
                    this.loadingComments = true;
                    try {
                        const res = await fetch(`/api/facebook/pages/${this.selectedPageId}/posts/${post.id}/comments`);
                        const data = await res.json();
                        if(data.data) {
                            this.comments = data.data;
                        }
                    } catch (err) {
                        console.error("Failed to fetch comments", err);
                    } finally {
                        this.loadingComments = false;
                    }
                },

                toggleReplyInput(commentId) {
                    if (this.activeReplyId === commentId) {
                        this.activeReplyId = null;
                        this.replyText = '';
                    } else {
                        this.activeReplyId = commentId;
                        this.replyText = '';
                        setTimeout(() => {
                            const input = document.querySelector(`input[x-model="replyText"]`);
                            if (input) input.focus();
                        }, 50);
                    }
                },

                async submitCommentReply(commentId) {
                    if(!this.replyText.trim() || this.sendingCommentReply) return;
                    this.sendingCommentReply = true;
                    const text = this.replyText.trim();
                    try {
                        const res = await fetch(`/api/facebook/pages/${this.selectedPageId}/comments/${commentId}/reply`, {
                            method: 'POST',
                            headers: { 
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') 
                            },
                            body: JSON.stringify({ message: text })
                        });
                        const data = await res.json();
                        if(data.success || data.id) {
                            this.showToast('Reply posted');
                            // Refresh comments to show new reply
                            this.selectPost(this.selectedPost);
                            this.activeReplyId = null;
                            this.replyText = '';
                        }
                    } catch (err) {
                        console.error(err);
                        alert("Error posting reply");
                    } finally {
                        this.sendingCommentReply = false;
                    }
                },

                async toggleHideComment(comment) {
                    const willHide = !comment.is_hidden;
                    try {
                        const res = await fetch(`/api/facebook/pages/${this.selectedPageId}/comments/${comment.id}/hide`, {
                            method: 'POST',
                            headers: { 
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') 
                            },
                            body: JSON.stringify({ is_hidden: willHide })
                        });
                        const data = await res.json();
                        if(data.success) {
                            comment.is_hidden = willHide;
                            this.showToast(willHide ? 'Comment hidden' : 'Comment visible');
                        }
                    } catch (err) {
                        console.error(err);
                    }
                },

                async deleteComment(commentId, parentCommentId = null) {
                    if (!confirm("Are you sure you want to delete this comment?")) return;
                    try {
                        await fetch(`/api/facebook/pages/${this.selectedPageId}/comments/${commentId}`, {
                            method: 'DELETE',
                            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') }
                        });
                        this.showToast('Comment deleted');
                        if (parentCommentId) {
                            // Find parent and remove nested reply
                            const parent = this.comments.find(c => c.id === parentCommentId);
                            if (parent && parent.comments && parent.comments.data) {
                                parent.comments.data = parent.comments.data.filter(r => r.id !== commentId);
                            }
                        } else {
                            // Remove top level
                            this.comments = this.comments.filter(c => c.id !== commentId);
                        }
                    } catch (err) {
                        console.error(err);
                    }
                },

                async toggleLikeComment(comment) {
                    try {
                        await fetch(`/api/facebook/pages/${this.selectedPageId}/comments/${comment.id}/like`, {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') }
                        });
                        comment.user_likes = !comment.user_likes;
                    } catch (err) {
                        console.error(err);
                    }
                },

                // --- UI Helpers ---
                handleFileSelect(e) { const f = e.target.files[0]; if(f) this.selectedFile = f; },
                clearFile() { this.selectedFile = null; this.$refs.fileInput.value = ''; },
                insertEmoji(emoji) { this.newMessage += emoji; this.showEmojiPicker = false; },
                sendThumbsUp() { this.newMessage = '👍'; this.sendMessage(); },
                toggleStar() { this.isStarred = !this.isStarred; if(this.isStarred) this.showToast('Conversation starred'); },
                showToast(msg) { this.toastMessage = msg; setTimeout(() => { this.toastMessage = ''; }, 3000); },
                getParticipantName(conv) {
                    const other = conv?.participants?.data?.find(p => p.id !== this.selectedPageId);
                    return other?.name || 'Unknown';
                },
                getParticipantAvatar(conv) { return null; },
                getSelectedPageName() {
                    const select = document.querySelector('select[x-model="selectedPageId"]');
                    if(select && this.selectedPageId) {
                        const option = select.querySelector(`option[value="${this.selectedPageId}"]`);
                        return option ? option.innerText : 'Page';
                    }
                    return 'Page';
                },
                getLastMessageText(conv) { return conv?.messages?.data?.[0]?.message || '[Attachment]'; },
                formatDate(d) { return new Date(d).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}); },
                scrollToBottom() {
                    setTimeout(() => {
                        const container = document.getElementById('chat-messages-container');
                        if(container) container.scrollTop = container.scrollHeight;
                    }, 50);
                },
                fillOrderCustomer() {
                    if(this.selectedConversation) this.orderForm.name = this.getParticipantName(this.selectedConversation);
                },
                fillOrderCustomerFromName(name) {
                    if(name) this.orderForm.name = name;
                },

                async submitOrderForm() {
                    if (this.submittingOrder) return;
                    this.submittingOrder = true;

                    try {
                        const res = await fetch('{{ route('orders.store') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({
                                customer_name: this.orderForm.name,
                                customer_phone: this.orderForm.phone,
                                address: this.orderForm.address,
                                product_id: this.orderForm.product_id,
                                quantity: this.orderForm.quantity,
                                remarks: this.orderForm.remarks,
                                source: 'facebook'
                            })
                        });

                        const data = await res.json();
                        
                        if (res.ok && data.success) {
                            this.showToast('Order created successfully!');
                            // Reset form
                            this.orderForm = {
                                name: '',
                                phone: '',
                                address: '',
                                product_id: '',
                                quantity: 1,
                                remarks: ''
                            };
                        } else {
                            alert(data.message || 'Failed to create order. Please check all required fields.');
                        }
                    } catch (err) {
                        console.error('Order submission error:', err);
                        alert('An error occurred while creating the order.');
                    } finally {
                        this.submittingOrder = false;
                    }
                },

                // --- Saved Replies Methods ---
                async fetchSavedReplies() {
                    try {
                        const res = await fetch('/api/facebook/saved-replies');
                        const data = await res.json();
                        if(data.data) this.savedReplies = data.data;
                    } catch (e) { console.error(e); }
                },
                async saveNewReply() {
                    this.savingReply = true;
                    try {
                        const res = await fetch('/api/facebook/saved-replies', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
                            body: JSON.stringify({ title: this.newReplyTitle, content: this.newReplyContent })
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
                        await fetch(`/api/facebook/saved-replies/${id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') } });
                        this.savedReplies = this.savedReplies.filter(r => r.id !== id);
                    } catch (e) { console.error(e); }
                },
                useSavedReply(reply) {
                    this.newMessage = reply.content;
                    this.showSavedRepliesModal = false;
                }
            }
        }
    </script>
</x-app-layout>
