<div class="h-screen flex bg-[#0d0d0d] text-slate-100 overflow-hidden font-sans selection:bg-indigo-500/30"
    x-data="{
        ws: null,
        streamingResponse: '',
        isStreaming: false,
        init() {
            this.connect();
        },
        connect() {
            this.ws = new WebSocket('ws://localhost:3001');
            this.ws.onmessage = (event) => {
                const data = JSON.parse(event.data);
                if (data.type === 'delta') {
                    this.isStreaming = true;
                    this.streamingResponse += data.content;
                    // Auto-scroll
                    const el = document.getElementById('chat-scroll-container');
                    if (el) el.scrollTop = el.scrollHeight;
                } else if (data.type === 'done') {
                    this.isStreaming = false;
                    $wire.saveAiResponse(this.streamingResponse);
                    this.streamingResponse = '';
                }
            };
            this.ws.onclose = () => {
                console.log('[Bridge] WebSocket closed. Retrying in 3s...');
                setTimeout(() => this.connect(), 3000);
            };
        },
        sendMessageToBridge(content) {
            if (this.ws && this.ws.readyState === WebSocket.OPEN) {
                this.ws.send(JSON.stringify({ type: 'send_message', content }));
            } else {
                console.error('[Bridge] WebSocket not connected');
            }
        }
    }"
    @message-sent.window="sendMessageToBridge($event.detail.content)"
>
    <!-- Left Panel: Projects -->
    <aside class="w-64 flex-shrink-0 bg-[#171717] border-r border-[#262626] flex flex-col">
        <header class="p-4 flex items-center justify-between border-b border-[#262626]">
            <h2 class="text-xs font-bold tracking-[0.2em] text-slate-500 uppercase">Projects</h2>
            <button wire:click="$toggle('showNewProjectForm')" class="p-1.5 text-slate-500 hover:text-white hover:bg-[#262626] rounded-md transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3.5 h-3.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
            </button>
        </header>

        <div class="flex-1 overflow-y-auto py-3 px-2 space-y-1 custom-scrollbar">
            @if($showNewProjectForm)
                <form wire:submit.prevent="createProject" class="mb-4 px-2">
                    <input
                        type="text"
                        wire:model.defer="newProjectName"
                        placeholder="Name..."
                        class="w-full bg-[#0d0d0d] border border-indigo-500/50 rounded-md px-3 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500"
                        autoFocus
                    />
                </form>
            @endif

            @foreach ($projectList as $project)
                <button
                    wire:click="selectProject({{ $project['id'] }})"
                    @class([
                        'w-full flex items-center justify-between px-3 py-2.5 rounded-lg transition-all group',
                        $selectedProjectId === $project['id'] ? 'bg-[#262626] text-white shadow-sm' : 'text-slate-400 hover:bg-[#1f1f1f] hover:text-slate-200',
                    ])
                >
                    <div class="flex items-center gap-2.5 min-w-0">
                        <div @class([
                            'w-2 h-2 rounded-full flex-shrink-0',
                            'bg-emerald-500' => strtolower($project['status']) === 'active',
                            'bg-amber-500' => strtolower($project['status']) === 'paused',
                            'bg-blue-500' => strtolower($project['status']) === 'incubating',
                            'bg-rose-500' => strtolower($project['status']) === 'blocked',
                            'bg-slate-500' => !in_array(strtolower($project['status']), ['active', 'paused', 'incubating', 'blocked']),
                        ])></div>
                        <span class="text-sm font-medium truncate">{{ $project['name'] }}</span>
                    </div>
                    @php
                        $badgeStyles = [
                            'active' => 'bg-emerald-500/10 text-emerald-500 border-emerald-500/20',
                            'paused' => 'bg-amber-500/10 text-amber-500 border-amber-500/20',
                            'incubating' => 'bg-blue-500/10 text-blue-500 border-blue-500/20',
                            'blocked' => 'bg-rose-500/10 text-rose-500 border-rose-500/20',
                        ];
                        $badgeStyle = $badgeStyles[strtolower($project['status'])] ?? 'bg-slate-500/10 text-slate-500 border-slate-500/20';
                    @endphp
                    <span class="px-1.5 py-0.5 rounded text-[9px] font-bold uppercase tracking-wider border {{ $badgeStyle }} opacity-80 group-hover:opacity-100 transition-opacity">
                        {{ $project['status'] ?? 'Active' }}
                    </span>
                </button>
            @endforeach

            <button wire:click="$toggle('showNewProjectForm')" class="w-full flex items-center gap-2 px-3 py-2.5 text-sm text-slate-500 hover:text-slate-300 transition rounded-lg hover:bg-[#1f1f1f]">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                New project
            </button>
        </div>
    </aside>

    <!-- Middle Panel: Conversations -->
    <aside class="w-80 flex-shrink-0 bg-[#0d0d0d] border-r border-[#262626] flex flex-col">
        <header class="p-4 border-b border-[#262626] bg-[#0d0d0d]/80 backdrop-blur-md">
            @php
                $currentProject = collect($projectList)->firstWhere('id', $selectedProjectId);
            @endphp
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-slate-100 truncate pr-2">
                    {{ $currentProject['name'] ?? 'Inbox' }}
                </h3>
                <div class="flex items-center gap-2">
                    <button class="p-1.5 text-slate-500 hover:text-white transition">
                         <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 12a.75.75 0 11-1.5 0 .75.75 0 011.5 0zM12.75 12a.75.75 0 11-1.5 0 .75.75 0 011.5 0zM18.75 12a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
                        </svg>
                    </button>
                </div>
            </div>
            
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2 text-slate-500">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                    </svg>
                    <span class="text-xs font-bold uppercase tracking-widest">Conversations</span>
                </div>
                <button wire:click="$toggle('showNewConversationForm')" class="p-1.5 text-slate-500 hover:text-white hover:bg-[#171717] rounded-md transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3.5 h-3.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                </button>
            </div>
        </header>

        <div class="flex-1 overflow-y-auto py-3 px-2 space-y-1 custom-scrollbar">
            @if($showNewConversationForm)
                <form wire:submit.prevent="createConversation" class="mb-4 px-2">
                    <input
                        type="text"
                        wire:model.defer="newConversationTitle"
                        placeholder="Title..."
                        class="w-full bg-[#171717] border border-indigo-500/50 rounded-md px-3 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500"
                        autoFocus
                    />
                </form>
            @endif

            @foreach ($conversationList as $conversation)
                <button
                    wire:click="selectConversation({{ $conversation['id'] }})"
                    @class([
                        'w-full text-left px-3 py-3 rounded-xl transition-all group relative',
                        $selectedConversationId === $conversation['id'] ? 'bg-[#171717] ring-1 ring-[#262626] text-white' : 'text-slate-400 hover:bg-[#171717]/50 hover:text-slate-200',
                    ])
                >
                    <div class="flex items-center gap-2.5">
                        <div @class([
                            'w-2 h-2 rounded-full',
                            'bg-emerald-500' => strtolower($conversation['status']) === 'active',
                            'bg-amber-500' => strtolower($conversation['status']) === 'paused',
                            'bg-slate-700' => strtolower($conversation['status']) !== 'active' && strtolower($conversation['status']) !== 'paused',
                        ])></div>
                        <div class="text-sm font-medium truncate">{{ $conversation['title'] }}</div>
                    </div>
                    @if($selectedConversationId === $conversation['id'])
                        <div class="absolute right-3 top-1/2 -translate-y-1/2">
                             <span class="px-1.5 py-0.5 rounded text-[8px] font-black uppercase tracking-widest bg-emerald-500/10 text-emerald-500 border border-emerald-500/20">
                                {{ strtoupper($conversation['status'] ?? 'Active') }}
                            </span>
                        </div>
                    @endif
                </button>
            @endforeach

            <button wire:click="$toggle('showNewConversationForm')" class="w-full flex items-center gap-2 px-3 py-3 text-sm text-slate-500 hover:text-slate-300 transition rounded-xl hover:bg-[#171717]/50">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3.5 h-3.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                New conversation
            </button>
        </div>
    </aside>

    <!-- Main Panel: Chat -->
    <main class="flex-1 flex flex-col bg-[#0d0d0d] relative shadow-2xl">
        <!-- Top Bar -->
        <header class="h-14 flex-shrink-0 border-b border-[#262626] flex items-center justify-between px-6 bg-[#0d0d0d]/50 backdrop-blur-xl z-10">
            <div class="flex items-center gap-3">
                 @php
                    $currentConv = collect($conversationList)->firstWhere('id', $selectedConversationId);
                @endphp
                <span class="text-sm font-bold text-slate-200">{{ $currentConv['title'] ?? 'General' }}</span>
            </div>
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-2 bg-[#171717] px-3 py-1.5 rounded-full border border-[#262626] shadow-sm">
                    <div class="w-4 h-4 rounded-full bg-emerald-500 animate-pulse"></div>
                    <span class="text-[11px] font-bold text-slate-200">ChatGPT 5.2</span>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-3.5 h-3.5 text-slate-500">
                        <path fill-rule="evenodd" d="M5.22 8.22a.75.75 0 011.06 0L10 11.94l3.72-3.72a.75.75 0 111.06 1.06l-4.25 4.25a.75.75 0 01-1.06 0L5.22 9.28a.75.75 0 010-1.06z" clip-rule="evenodd" />
                    </svg>
                </div>
                <button class="text-slate-500 hover:text-white transition">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.75a.75.75 0 110-1.5.75.75 0 010 1.5zM12 12.75a.75.75 0 110-1.5.75.75 0 010 1.5zM12 18.75a.75.75 0 110-1.5.75.75 0 010 1.5z" />
                    </svg>
                </button>
            </div>
        </header>

        <!-- Conversation References -->
        @if($selectedConversationId)
            <div class="border-b border-[#262626] bg-[#171717]/30 px-6 py-6 space-y-6">
                
                @if (session()->has('success'))
                    <div class="bg-emerald-500/10 text-emerald-500 px-4 py-3 rounded-lg border border-emerald-500/20 text-sm font-medium flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                        </svg>
                        {{ session('success') }}
                    </div>
                @endif
                
                <div class="grid grid-cols-1 gap-4 max-w-3xl mx-auto">
                    <!-- Original URL -->
                    <div class="space-y-2">
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider">Original Conversation URL</label>
                        <input
                            type="text"
                            wire:model.defer="originalUrl"
                            placeholder="https://chatgpt.com/c/..."
                            class="w-full bg-[#0d0d0d] border border-[#262626] rounded-md px-3 py-2 text-sm text-slate-300 focus:outline-none focus:border-indigo-500 transition-colors placeholder-slate-600 font-mono"
                        />
                         @error('originalUrl') <span class="text-xs text-red-500 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Shared URL -->
                    <div class="space-y-2">
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider">Shared Conversation URL</label>
                        <input
                            type="text"
                            wire:model.defer="shareUrl"
                            placeholder="https://chatgpt.com/share/..."
                            class="w-full bg-[#0d0d0d] border border-[#262626] rounded-md px-3 py-2 text-sm text-slate-300 focus:outline-none focus:border-indigo-500 transition-colors placeholder-slate-600 font-mono"
                        />
                         @error('shareUrl') <span class="text-xs text-red-500 block">{{ $message }}</span> @enderror
                    </div>
                    
                    <div class="pt-2">
                        <button
                            wire:click="saveShareUrl"
                            class="w-full px-4 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-bold uppercase tracking-wider rounded-md shadow-lg shadow-indigo-600/20 hover:shadow-indigo-600/30 transition-all active:scale-[0.98]"
                        >
                            Save & Connect to Piper
                        </button>
                    </div>
                </div>
            </div>
        @endif

        <!-- Chat History -->
        <div class="flex-1 overflow-y-auto p-8 space-y-12 scroll-smooth custom-scrollbar" id="message-container">
            @forelse ($messageList as $message)
                <div class="group mx-auto max-w-3xl flex gap-6">
                    <div class="flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center text-xs font-black shadow-lg {{ $message['author_role'] === 'user' ? 'bg-indigo-600 text-white' : 'bg-[#262626] text-emerald-500' }}">
                        @if($message['author_role'] === 'user')
                            U
                        @else
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L8.5 14.5c.28.28.67.5 1.5.5v2.5c0 .28.22.5.5.5h2v-2.5c0-.83-.67-1.5-1.5-1.5h-1v-2h3c.83 0 1.5-.67 1.5-1.5V9h-5V7c0-.55.45-1 1-1h2.5c.28 0 .5-.22.5-.5V4.07c3.06.74 5.39 3.29 5.8 6.43h-2.3c-.28 0-.5.22-.5.5v2.5c0 .83.67 1.5 1.5 1.5h2.3c-.41 3.14-2.74 5.69-5.8 6.43v-2.43h2c.28 0 .5-.22.5-.5v-1c0-.28-.22-.5-.5-.5h-2v1h-2v-1h-2v2.43z" />
                            </svg>
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <header class="flex items-center gap-3 mb-2">
                            <span class="text-sm font-black text-slate-100 uppercase tracking-wider">{{ $message['author_role'] === 'user' ? 'You' : 'ChatGPT 5.2' }}</span>
                            <span class="text-[10px] font-bold text-slate-600 uppercase tracking-[0.2em]">{{ \Carbon\Carbon::parse($message['created_at'])->format('h:i A') }}</span>
                        </header>
                        <div class="prose prose-invert prose-slate max-w-none text-[16px] leading-relaxed text-slate-300">
                            {!! $message['rendered_html'] !!}
                        </div>

                        @if (!empty($message['checkboxes']))
                            <div class="mt-6 bg-[#171717]/50 border border-[#262626] rounded-2xl p-5 shadow-sm hover:border-[#404040] transition-colors group/checklist">
                                <ul class="space-y-3.5">
                                    @foreach ($message['checkboxes'] as $checkbox)
                                        <li class="flex items-center gap-4 group/item">
                                            <div class="relative flex items-center">
                                                <input
                                                    type="checkbox"
                                                    wire:click="toggleCheckbox({{ $checkbox['id'] }})"
                                                    @checked($checkbox['is_checked'])
                                                    class="h-5 w-5 rounded-md border-[#404040] text-indigo-600 bg-[#0d0d0d] transition focus:ring-offset-[#0d0d0d] cursor-pointer appearance-none checked:bg-indigo-600 checked:border-transparent"
                                                />
                                                @if($checkbox['is_checked'])
                                                    <svg class="absolute w-3.5 h-3.5 text-white left-0.5 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="4">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                @endif
                                            </div>
                                            <span @class(['text-sm transition-all duration-300 font-medium', $checkbox['is_checked'] ? 'text-slate-600 line-through' : 'text-slate-200'])>
                                                {{ $checkbox['label'] }}
                                            </span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="h-full flex flex-col items-center justify-center text-slate-700">
                    <div class="w-16 h-16 rounded-3xl bg-[#171717] border border-[#262626] flex items-center justify-center mb-6 shadow-2xl">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 01.865-.501 48.172 48.172 0 003.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z" />
                        </svg>
                    </div>
                    <p class="text-sm font-bold uppercase tracking-[0.3em]">Ready to Think</p>
                    <p class="text-xs mt-2 opacity-50 font-mono italic">Start typing in the terminal below...</p>
                </div>
            @endforelse

            <!-- Streaming Response Placeholder -->
            <template x-if="isStreaming">
                <div class="group flex flex-col gap-6 px-4 py-8 rounded-[32px] transition-all hover:bg-[#111111]/50">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-2xl bg-indigo-600 flex items-center justify-center shadow-lg shadow-indigo-600/20">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-white">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 18L12 20M12 4L12 6M21 12L23 12M1 12L3 12M18.364 18.364L19.778 19.778M4.222 4.222L5.636 5.636M18.364 5.636L19.778 4.222M4.222 19.778L5.636 18.364" />
                            </svg>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-sm font-black text-slate-100 uppercase tracking-wider">ChatGPT 5.2</span>
                            <span class="text-[10px] font-bold text-indigo-400 uppercase tracking-widest animate-pulse">Thinking...</span>
                        </div>
                    </div>
                    <div class="pl-14">
                        <div class="prose prose-invert max-w-none text-slate-300 leading-[1.8] font-mono text-[16px]">
                            <p x-text="streamingResponse" class="whitespace-pre-wrap terminal-cursor"></p>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <!-- Chat Input Area -->
        <div class="px-8 pb-8 pt-4 bg-gradient-to-t from-[#0d0d0d] via-[#0d0d0d] to-transparent">
            <div class="mx-auto max-w-3xl">
                <form wire:submit.prevent="sendMessage" class="relative" x-data="{
                    resize() {
                        const el = this.$refs.textarea;
                        el.style.height = 'auto';
                        el.style.height = Math.min(el.scrollHeight, 500) + 'px';
                    }
                }" x-init="resize()">
                    <div class="bg-[#171717] border border-[#262626] rounded-[24px] shadow-2xl overflow-hidden focus-within:border-indigo-500/50 focus-within:ring-4 focus-within:ring-indigo-500/10 transition-all duration-500 relative group/input">
                        <textarea
                            x-ref="textarea"
                            wire:model.defer="newMessageContent"
                            @input="resize()"
                            placeholder="Type a long-form prompt..."
                            class="w-full bg-transparent border-none px-6 py-6 text-slate-100 text-[17px] leading-[1.6] focus:ring-0 resize-none font-mono placeholder-slate-700 terminal-cursor min-h-[180px] custom-scrollbar selection:bg-indigo-500/40"
                            @keydown.enter.prevent="if (!$event.shiftKey) $wire.sendMessage().then(() => { $nextTick(() => resize()) })"
                        ></textarea>

                        <div class="flex items-center justify-between px-4 py-3 bg-[#1f1f1f]/80 backdrop-blur-md border-t border-[#262626]">
                            <div class="flex items-center gap-1.5">
                                <button type="button" class="p-2.5 text-slate-500 hover:text-white transition-all rounded-xl hover:bg-[#262626] active:scale-90" title="Manage Files">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor" class="w-5 h-5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 014.5 9.75h15A2.25 2.25 0 0121.75 12v.75m-8.625-12.123L21.75 9.75H2.25l8.125-9.123A2.25 2.25 0 008.625.627zM2.25 12.75L12 21l9.75-8.25H2.25z" />
                                    </svg>
                                </button>
                                <button type="button" class="p-2.5 text-slate-500 hover:text-white transition-all rounded-xl hover:bg-[#262626] active:scale-90" title="Attach file">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor" class="w-5 h-5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 01-6.364-6.364l10.94-10.94A3 3 0 1119.5 7.5l-10.744 10.744a1.5 1.5 0 11-2.121-2.121L17.364 5.385" />
                                    </svg>
                                </button>
                                <button type="button" class="p-2.5 text-slate-500 hover:text-white transition-all rounded-xl hover:bg-[#262626] active:scale-90" title="Attach screenshot">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor" class="w-5 h-5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM18.75 10.5h.008v.008h-.008V10.5z" />
                                    </svg>
                                </button>
                            </div>

                            <div class="flex items-center gap-3">
                                <div class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-600 hidden sm:block">
                                    {{ $currentProject['name'] ?? 'Inbox' }}
                                </div>
                                <button
                                    type="submit"
                                    @class([
                                        'group flex items-center gap-2.5 px-6 py-2 rounded-2xl transition-all font-black text-sm active:scale-95 shadow-lg',
                                        'bg-indigo-600 text-white hover:bg-indigo-500 hover:shadow-indigo-500/20' => !empty($newMessageContent),
                                        'bg-[#262626] text-slate-500 opacity-50 pointer-events-none' => empty($newMessageContent),
                                    ])
                                >
                                    <span>Send</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4 transition-transform group-hover:translate-x-0.5 group-hover:-translate-y-0.5">
                                        <path d="M3.478 2.405a.75.75 0 00-.926.94l2.432 7.155a.75.75 0 00.614.498H21a.75.75 0 01.621 1.17l-1.06 1.541a.75.75 0 01-.62.336H5.598a.75.75 0 00-.614.498l-2.432 7.155a.75.75 0 00.926.94 45.13 45.13 0 0021.31-10.377.75.75 0 000-1.206 45.13 45.13 0 00-21.31-10.377z" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <style>
        .terminal-cursor:focus {
            caret-shape: block;
            caret-color: #6366f1;
        }
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #262626;
            border-radius: 10px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #404040;
        }
    </style>
</div>
