<x-app-layout>
    {{-- このビュー独自の<head>要素（CSS/JS）を定義するスロット --}}
    <x-slot name="header_scripts">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/github-dark.min.css">
        <link rel="stylesheet" href="{{ asset('css/chat.css') }}">
    </x-slot>

    <div class="chat-main-container" id="main-container" data-current-user-id="{{ Auth::id() }}">
        {{-- Sidebar --}}
        <div class="sidebar p-2 d-flex flex-column">
            <form action="{{ route('chat.new') }}" method="post" class="mb-3">
                @csrf
                <button type="submit" class="btn btn-outline-light w-100"><i class="bi bi-plus-lg"></i> 新しいチャット</button>
            </form>
            <div class="flex-grow-1" style="overflow-y: auto;">
                @foreach($conversations as $conv)
                    <a href="{{ route('chat.show', $conv) }}" class="mb-2 {{ optional($currentConversation)->id === $conv->id ? 'active' : '' }}">
                        <i class="bi bi-chat-left-text"></i> {{ $conv->title }}
                    </a>
                @endforeach
            </div>
            <div class="user-menu mt-auto">
                 <div class="dropdown">
                    <button class="btn btn-dark w-100 d-flex align-items-center justify-content-between dropdown-toggle" type="button" id="userMenuButton" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Toggle user menu">
                        <span><i class="bi bi-person-circle me-2"></i>{{ Auth::user()->name }}</span>
                        <i class="bi bi-three-dots-vertical"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-dark w-100" aria-labelledby="userMenuButton">
                        <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="bi bi-gear me-2"></i>プロフィール</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault(); this.closest('form').submit();">
                                    <i class="bi bi-box-arrow-right me-2"></i>ログアウト
                                </a>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Chat Area --}}
        <div class="chat-area">
            <div class="chat-header">
                <button class="hamburger-btn" id="hamburger-btn" aria-label="Toggle sidebar"><i class="bi bi-list"></i></button>
                {{-- ▼▼▼ Bot名を表示するように修正 ▼▼▼ --}}
                <span id="header-title">{{ $settings->bot_name ?? 'AI Chat' }}</span>
            </div>
            <div class="chat-history" id="chat-history">
                @if($currentConversation)
                    @php $lastDate = null; @endphp
                    @foreach($currentConversation->messages()->with('files')->get() as $msg)
                        {{-- 日付区切り --}}
                        @php $currentDate = $msg->created_at->format('Y-m-d'); @endphp
                        @if ($currentDate !== $lastDate)
                            <div class="date-divider"><span>{{ $msg->created_at->isToday() ? '今日' : $msg->created_at->format('Y年n月j日') }}</span></div>
                            @php $lastDate = $currentDate; @endphp
                        @endif
                        
                        {{-- ▼▼▼ メッセージ表示エリア全体を修正 ▼▼▼ --}}
                        <div class="d-flex mb-3 {{ $msg->role === 'user' ? 'justify-content-end user-message' : 'justify-content-start assistant-message' }}">
                            
                            {{-- アイコン表示 --}}
                            @if ($msg->role !== 'user')
                                @php
                                    $assistantIcon = !empty($settings->bot_icon_path) 
                                        ? Storage::url($settings->bot_icon_path) 
                                        : asset('images/assistant-icon.png');
                                @endphp
                                <img src="{{ $assistantIcon }}" alt="assistant icon" class="chat-icon">
                            @else
                                <img src="{{ Auth::user()->icon ? Storage::url(Auth::user()->icon) : asset('images/default-icon.png') }}" alt="user icon" class="chat-icon">
                            @endif
                            
                            {{-- 名前と吹き出しをまとめるコンテナ --}}
                            <div class="message-content-container">
                                {{-- 送信者名 --}}
                                <div class="chat-sender-name">
                                    @if ($msg->role === 'user')
                                        {{ Auth::user()->name }}
                                    @else
                                        {{ $settings->bot_name ?? 'AI Assistant' }}
                                    @endif
                                </div>

                                {{-- 既存の吹き出しとタイムスタンプ --}}
                                <div class="message-container">
                                    <div class="chat-bubble">
                                        <div class="sent-files">
                                            @foreach($msg->files as $file)
                                                @if(str_starts_with($file->mime_type, 'image/'))
                                                    <div class="sent-files d-flex flex-wrap">
                                                        <img src="{{ Storage::url($file->file_path) }}" class="img-fluid rounded" alt="{{ $file->original_name }}">
                                                    </div>
                                                @else
                                                    <a href="{{ Storage::url($file->file_path) }}" target="_blank" class="file-attachment">
                                                        <i class="bi {{ \App\Http\Controllers\ChatController::getFileIconClass($file->mime_type) }}"></i>
                                                        <span>{{ $file->original_name }}</span>
                                                    </a>
                                                @endif
                                            @endforeach
                                        </div>
                                        @if($msg->content)
                                            <div class="message-content">{!! Str::markdown($msg->content, ['html_input' => 'strip']) !!}</div>
                                        @endif
                                    </div>
                                    <div class="chat-timestamp">
                                        {{ $msg->created_at->format('H:i') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        {{-- ▲▲▲ メッセージ表示エリアの修正ここまで ▲▲▲ --}}
                    @endforeach
                @else
                    <p class="text-center text-muted mt-5">サイドバーの「新しいチャット」から会話を開始してください。</p>
                @endif
            </div>
            <div class="chat-form-container">
                @if($currentConversation)
                <form id="chatForm">
                    <div id="file-preview-container" class="image-preview-container"></div>
                    <div class="input-group">
                        <label class="btn btn-secondary" for="file-upload-input"><i class="bi bi-paperclip"></i></label>
                        <input type="file" id="file-upload-input" multiple class="d-none">
                        <textarea id="question" class="form-control" rows="1" placeholder="質問内容を入力してください"></textarea>
                        <button id="submitBtn" class="btn btn-primary" type="submit"><i class="bi bi-send"></i></button>
                    </div>
                </form>
                @endif
            </div>
        </div>
    </div>
    
    <div id="imageModal" class="image-modal" style="display: none;">
        <span class="image-modal-close">&times;</span>
        <img class="image-modal-content" id="modalImage">
    </div>

    {{-- JavaScriptのロジックを外部ファイルに分離 --}}
    <x-slot name="footer_scripts">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/dompurify@3.0.6/dist/purify.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>
        
        @if($currentConversation)
        <script>
            window.chatConfig = {
                postUrl: "{{ route('chat.post', $currentConversation) }}",
                csrfToken: "{{ csrf_token() }}"
            };
        </script>
        @endif

        <script src="{{ asset('js/chat.js') }}"></script>
    </x-slot>
</x-app-layout>