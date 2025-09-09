<x-app-layout>
    {{-- このビュー独自の<head>要素（CSSなど）を定義するスロット --}}
    <x-slot name="header_scripts">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/github-dark.min.css">
        <style>
            .chat-main-container { display: flex; height: 100vh; }
            body { font-family: "Noto Sans JP", sans-serif; overflow: hidden; }
            .sidebar { width: 260px; background-color: #202123; color: white; flex-shrink: 0; transition: margin-left 0.3s ease; }
            .sidebar a { color: #ececf1; text-decoration: none; display: block; padding: 12px 15px; border-radius: 5px; }
            .sidebar a:hover, .sidebar a.active { background-color: #343541; }
            .chat-area { flex-grow: 1; display: flex; flex-direction: column; background-color: #a4c5d2; overflow: hidden; }
            .chat-header { background-color: #3d424b; color: white; padding: 10px 15px; font-weight: bold; display: flex; align-items: center; }
            .chat-history { flex-grow: 1; padding: 20px; overflow-y: auto; }
            .chat-bubble { max-width: 75%; padding: 12px 18px; border-radius: 20px; margin-bottom: 12px; word-wrap: break-word; line-height: 1.6; }
            .user-message .chat-bubble { background-color: #8de041; margin-left: auto; }
            .assistant-message .chat-bubble { background-color: #ffffff; }
            .chat-form-container { padding: 20px; background-color: #f0f2f5; flex-shrink: 0; }
            .chat-bubble p:last-child { margin-bottom: 0; }
            .hamburger-btn { font-size: 1.5rem; background: none; border: none; color: white; margin-right: 15px; }
            .chat-main-container.sidebar-hidden .sidebar { margin-left: -260px; }
            @media (max-width: 768px) { .chat-main-container:not(.sidebar-initial) .sidebar { position: fixed; height: 100%; z-index: 1000; } }
            .user-menu { border-top: 1px solid #444; padding-top: 0.5rem; }
            .user-menu .dropdown-toggle::after { display: none; }
            .user-menu .dropdown-menu { background-color: #343541; border: 1px solid #444; }
            .user-menu .dropdown-item { color: #ececf1; }
            .user-menu .dropdown-item:hover { background-color: #4a4a4a; }
            .chat-bubble pre { background-color: #2d2d2d; color: #f8f8f2; padding: 1rem; border-radius: 8px; position: relative; white-space: pre-wrap; word-wrap: break-word; overflow-x: auto; }
            .chat-bubble pre code.hljs { padding: 0; background: none; }
            .copy-btn { position: absolute; top: 10px; right: 10px; background-color: #444; color: white; border: none; padding: 5px 10px; border-radius: 5px; cursor: pointer; opacity: 0; transition: opacity 0.2s; }
            .chat-bubble pre:hover .copy-btn { opacity: 1; }
            .chat-bubble table { width: 100%; border-collapse: collapse; margin: 1rem 0; }
            .chat-bubble th, .chat-bubble td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            .chat-bubble th { background-color: #f2f2f2; font-weight: bold; }
            .chat-bubble tr:nth-child(even) { background-color: #f9f9f9; }
            .thinking { display: inline-flex; align-items: baseline; }
            .thinking span { animation: blink 1.4s infinite both; font-weight: bold; font-size: 1.2rem; }
            .thinking span:nth-child(2) { animation-delay: 0.2s; }
            .thinking span:nth-child(3) { animation-delay: 0.4s; }
            @keyframes blink { 0%, 80%, 100% { opacity: 0; } 40% { opacity: 1; } }
            .image-preview-container { display: flex; gap: 10px; margin-bottom: 10px; overflow-x: auto; }
            .image-preview-item { position: relative; flex-shrink: 0; }
            .image-preview-item img { width: 80px; height: 80px; object-fit: cover; border-radius: 8px; }
            .remove-image-btn { position: absolute; top: -5px; right: -5px; background-color: rgba(0,0,0,0.7); color: white; border: none; border-radius: 50%; width: 20px; height: 20px; font-size: 12px; line-height: 20px; text-align: center; cursor: pointer; }
            /* ▼▼▼ 修正: sent-filesクラス内のimgタグに直接スタイルを適用 ▼▼▼ */
            .sent-files img { max-width: 200px; max-height: 200px; border-radius: 8px; margin: 5px; cursor: pointer; }
            .file-attachment { display: flex; align-items: center; background-color: #e9ecef; border-radius: 8px; padding: 8px 12px; margin-top: 5px; text-decoration: none; color: #212529; }
            .file-attachment:hover { background-color: #ced4da; }
            .file-attachment i { font-size: 1.5rem; margin-right: 10px; }
            .file-icon-preview { padding: 10px; background: #eee; border-radius: 5px; font-size: 12px; display: flex; align-items: center; gap: 5px; width: 80px; height: 80px; justify-content: center; text-align: center; }
            #question { resize: none; overflow-y: hidden; }

            /* ▼▼▼ 追加: 画像拡大モーダルのためのCSS ▼▼▼ */
            .image-modal {
                position: fixed;
                z-index: 2000;
                left: 0;
                top: 0;
                width: 100%;
                height: 100%;
                overflow: auto;
                background-color: rgba(0,0,0,0.9);
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .image-modal-content {
                margin: auto;
                display: block;
                max-width: 80%;
                max-height: 80vh;
            }
            .image-modal-close {
                position: absolute;
                top: 15px;
                right: 35px;
                color: #f1f1f1;
                font-size: 40px;
                font-weight: bold;
                transition: 0.3s;
                cursor: pointer;
            }
            /* ▲▲▲ 追加ここまで ▲▲▲ */
        </style>
    </x-slot>

    <div class="chat-main-container" id="main-container">
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
                        @if(Auth::user()->is_admin)
                            <li><a class="dropdown-item" href="{{ route('admin.index') }}"><i class="bi bi-shield-lock me-2"></i>管理画面</a></li>
                        @endif
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
                <span id="header-title">{{ optional($currentConversation)->title ?? 'AI Chat' }}</span>
            </div>
            <div class="chat-history" id="chat-history">
                @if($currentConversation)
                    @foreach($currentConversation->messages()->with('files')->get() as $msg)
                        <div class="d-flex flex-column {{ $msg->role === 'user' ? 'align-items-end user-message' : 'align-items-start assistant-message' }}">
                            <div class="chat-bubble">
                                <div class="sent-files">
                                    @foreach($msg->files as $file)
                                        @if(str_starts_with($file->mime_type, 'image/'))
                                            {{-- ▼▼▼ 修正: sent-filesクラスをdivに追加 ▼▼▼ --}}
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
                                    <div class="mt-2">{!! Str::markdown($msg->content, ['html_input' => 'strip']) !!}</div>
                                @endif
                            </div>
                        </div>
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
    
    {{-- ▼▼▼ 追加: 画像拡大モーダルのためのHTML ▼▼▼ --}}
    <div id="imageModal" class="image-modal" style="display: none;">
        <span class="image-modal-close">&times;</span>
        <img class="image-modal-content" id="modalImage">
    </div>

    <x-slot name="footer_scripts">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/dompurify@3.0.6/dist/purify.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                marked.setOptions({ sanitizer: DOMPurify.sanitize });

                const mainContainer = document.getElementById('main-container');
                const hamburgerBtn = document.getElementById('hamburger-btn');
                if(hamburgerBtn) {
                    hamburgerBtn.addEventListener('click', () => { mainContainer.classList.toggle('sidebar-hidden'); });
                }
                if (window.innerWidth <= 768) { mainContainer.classList.add('sidebar-hidden'); }

                @if($currentConversation)
                    const chatHistory = document.getElementById('chat-history');
                    const chatForm = document.getElementById('chatForm');
                    const questionInput = document.getElementById('question');
                    const submitBtn = document.getElementById('submitBtn');
                    const fileUploadInput = document.getElementById('file-upload-input');
                    const filePreviewContainer = document.getElementById('file-preview-container');
                    let selectedFiles = [];
                    let previewObjectUrls = [];
                    
                    // ▼▼▼ 追加: 画像拡大モーダルのための要素を取得 ▼▼▼
                    const imageModal = document.getElementById('imageModal');
                    const modalImage = document.getElementById('modalImage');
                    const closeModalBtn = document.querySelector('.image-modal-close');

                    const adjustTextareaHeight = () => {
                        if (!questionInput) return;
                        questionInput.style.height = 'auto';
                        questionInput.style.height = (questionInput.scrollHeight) + 'px';
                    };

                    if (questionInput) {
                        questionInput.addEventListener('input', adjustTextareaHeight);
                        adjustTextareaHeight();
                    }
                    
                    if (fileUploadInput) {
                        fileUploadInput.addEventListener('change', () => {
                            if (fileUploadInput.files.length + selectedFiles.length > 3) {
                                alert('ファイルは3つまで選択できます。');
                                fileUploadInput.value = '';
                                return;
                            }
                            Array.from(fileUploadInput.files).forEach(file => selectedFiles.push(file));
                            updateFilePreview();
                            fileUploadInput.value = '';
                        });
                    }

                    function updateFilePreview() {
                        previewObjectUrls.forEach(url => URL.revokeObjectURL(url));
                        previewObjectUrls = [];
                        filePreviewContainer.innerHTML = '';
                        
                        selectedFiles.forEach((file, index) => {
                            const item = document.createElement('div');
                            item.className = 'image-preview-item';
                            if (file.type.startsWith('image/')) {
                                const url = URL.createObjectURL(file);
                                previewObjectUrls.push(url);
                                item.innerHTML = `<img src="${url}" alt="${escapeHtml(file.name)}"><button type="button" class="remove-image-btn" data-index="${index}">&times;</button>`;
                            } else {
                                item.innerHTML = `<div class="file-icon-preview">${getFileIconHtml(file.type)} <small>${escapeHtml(file.name.substring(0,10))}...</small></div><button type="button" class="remove-image-btn" data-index="${index}">&times;</button>`;
                            }
                            filePreviewContainer.appendChild(item);
                        });
                    }
                    
                    if (filePreviewContainer) {
                        filePreviewContainer.addEventListener('click', e => {
                            if (e.target.classList.contains('remove-image-btn')) {
                                selectedFiles.splice(parseInt(e.target.dataset.index, 10), 1);
                                updateFilePreview();
                            }
                        });
                    }

                    if (chatForm) {
                        chatForm.addEventListener('submit', async e => {
                            e.preventDefault();
                            const question = questionInput.value.trim();
                            if (!question && selectedFiles.length === 0) return;
                            
                            submitBtn.disabled = true;
                            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';

                            const tempFiles = [...selectedFiles];
                            appendMessage('user', question, tempFiles);
                            scrollToBottom();
                            
                            const formData = new FormData();
                            formData.append('question', question);
                            selectedFiles.forEach(file => formData.append('files[]', file));

                            questionInput.value = '';
                            selectedFiles = [];
                            updateFilePreview();
                            adjustTextareaHeight();

                            const thinkingContent = `<div class="d-flex align-items-center"><span>返信中</span><div class="thinking ms-1"><span>.</span><span>.</span><span>.</span></div></div>`;
                            const thinkingBubble = appendMessage('assistant', thinkingContent, [], true);
                            scrollToBottom();
                            
                            try {
                                const response = await fetch("{{ route('chat.post', $currentConversation) }}", {
                                    method: 'POST',
                                    headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}", 'Accept': 'application/json' },
                                    body: formData
                                });

                                if (!response.ok) {
                                    let errorText = `サーバーエラーが発生しました (Status: ${response.status})。`;
                                    if (response.status === 419) {
                                        errorText = 'ページの有効期限が切れました。ページをリロードして再度お試しください。';
                                    } else {
                                        try { const errorData = await response.json(); errorText = errorData.message || errorText; } catch (e) { /* ignore */ }
                                    }
                                    throw new Error(errorText);
                                }
                                
                                const data = await response.json();

                                if (data.success) {
                                    thinkingBubble.querySelector('.chat-bubble').innerHTML = marked.parse(data.message || '');
                                    applySyntaxHighlightingToElement(thinkingBubble);
                                } else {
                                    throw new Error(data.message || '不明なAPIエラーが発生しました。');
                                }
                            } catch (error) {
                                console.error('送信エラー:', error);
                                thinkingBubble.querySelector('.chat-bubble').innerHTML = `<p class="text-danger">エラー: ${escapeHtml(error.message)}</p>`;
                            } finally {
                                submitBtn.disabled = false;
                                submitBtn.innerHTML = '<i class="bi bi-send"></i>';
                                scrollToBottom();
                            }
                        });
                    }

                    function appendMessage(role, textContent, files = [], isThinking = false) {
                        const messageWrapper = document.createElement('div');
                        messageWrapper.className = `d-flex flex-column ${role === 'user' ? 'align-items-end user-message' : 'align-items-start assistant-message'}`;
                        const bubble = document.createElement('div');
                        bubble.className = 'chat-bubble';

                        let filesHtml = '';
                        if (role === 'user' && files.length > 0) {
                            // ▼▼▼ 修正: sent-filesクラスをdivに追加 ▼▼▼
                            filesHtml += '<div class="sent-files d-flex flex-wrap">';
                            files.forEach(file => {
                                const url = URL.createObjectURL(file);
                                if (file.type.startsWith('image/')) {
                                    filesHtml += `<img src="${url}" class="img-fluid rounded" alt="${escapeHtml(file.name)}">`;
                                } else {
                                    filesHtml += `<div class="file-attachment">${getFileIconHtml(file.type)} <span>${escapeHtml(file.name)}</span></div>`;
                                }
                            });
                            filesHtml += '</div>';
                        }
                        
                        let textHtml = '';
                        if (textContent) {
                            if(isThinking) {
                                textHtml = textContent;
                            } else {
                                const content = role === 'user' ? escapeHtml(textContent).replace(/\n/g, '<br>') : marked.parse(textContent);
                                textHtml = `<div class="mt-2">${content}</div>`;
                            }
                        }
                        
                        bubble.innerHTML = filesHtml + textHtml;
                        messageWrapper.appendChild(bubble);
                        chatHistory.appendChild(messageWrapper);
                        
                        return messageWrapper;
                    }
                    
                    function getFileIconHtml(mimeType) {
                        if (mimeType.includes('pdf')) return '<i class="bi bi-file-earmark-pdf"></i>';
                        if (mimeType.includes('word')) return '<i class="bi bi-file-earmark-word"></i>';
                        if (mimeType.includes('excel') || mimeType.includes('spreadsheet')) return '<i class="bi bi-file-earmark-excel"></i>';
                        return '<i class="bi bi-file-earmark"></i>';
                    }

                    function escapeHtml(str) {
                        const div = document.createElement('div');
                        div.appendChild(document.createTextNode(str));
                        return div.innerHTML;
                    }

                    function applySyntaxHighlightingToElement(element) {
                        element.querySelectorAll('pre code').forEach(el => {
                           hljs.highlightElement(el);
                        });
                        element.querySelectorAll('pre').forEach(addCopyButton);
                    }

                    function addCopyButton(pre) {
                        if (pre.querySelector('.copy-btn')) return;
                        const code = pre.querySelector('code');
                        if (!code) return;
                        const copyButton = document.createElement('button');
                        copyButton.className = 'copy-btn';
                        copyButton.innerHTML = '<i class="bi bi-clipboard"></i> コピー';
                        pre.appendChild(copyButton);
                        copyButton.addEventListener('click', () => {
                            navigator.clipboard.writeText(code.innerText).then(() => {
                                copyButton.innerHTML = '<i class="bi bi-check-lg"></i> コピーしました';
                                setTimeout(() => { copyButton.innerHTML = '<i class="bi bi-clipboard"></i> コピー'; }, 2000);
                            });
                        });
                    }

                    function scrollToBottom() {
                        if(chatHistory) { chatHistory.scrollTop = chatHistory.scrollHeight; }
                    }
                    
                    // ▼▼▼ 追加: 画像拡大モーダルのためのロジック ▼▼▼
                    function openModal(src) {
                        if (imageModal && modalImage) {
                            modalImage.src = src;
                            imageModal.style.display = 'flex';
                        }
                    }

                    function closeModal() {
                        if (imageModal) {
                            imageModal.style.display = 'none';
                        }
    
                    }

                    if (chatHistory) {
                        chatHistory.addEventListener('click', (e) => {
                            // クリックされたのがsent-files内の画像であるかを確認
                            if (e.target.tagName === 'IMG' && e.target.closest('.sent-files')) {
                                openModal(e.target.src);
                            }
                        });
                    }

                    if(closeModalBtn) closeModalBtn.addEventListener('click', closeModal);
                    if(imageModal) imageModal.addEventListener('click', (e) => {
                        // 背景をクリックした場合のみ閉じる
                        if (e.target === imageModal) {
                            closeModal();
                        }
                    });
                     // ▲▲▲ 追加ここまで ▲▲▲


                    setTimeout(() => {
                        document.querySelectorAll('.assistant-message .chat-bubble').forEach(applySyntaxHighlightingToElement);
                        scrollToBottom();
                    }, 100);
                @endif
            });
        </script>
    </x-slot>
</x-app-layout>

