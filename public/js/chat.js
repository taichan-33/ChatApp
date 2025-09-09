document.addEventListener('DOMContentLoaded', () => {
    // Check if we are on a page with a chat interface
    if (!document.getElementById('main-container') || typeof window.chatConfig === 'undefined') {
        return;
    }

    // --- Start of Chat Logic ---
    marked.setOptions({
        sanitizer: DOMPurify.sanitize
    });

    const mainContainer = document.getElementById('main-container');
    const hamburgerBtn = document.getElementById('hamburger-btn');
    if (hamburgerBtn) {
        hamburgerBtn.addEventListener('click', () => {
            mainContainer.classList.toggle('sidebar-hidden');
        });
    }
    if (window.innerWidth <= 768) {
        mainContainer.classList.add('sidebar-hidden');
    }

    const chatHistory = document.getElementById('chat-history');
    const chatForm = document.getElementById('chatForm');
    const questionInput = document.getElementById('question');
    const submitBtn = document.getElementById('submitBtn');
    const fileUploadInput = document.getElementById('file-upload-input');
    const filePreviewContainer = document.getElementById('file-preview-container');
    let selectedFiles = [];
    let previewObjectUrls = [];

    const imageModal = document.getElementById('imageModal');
    const modalImage = document.getElementById('modalImage');
    const closeModalBtn = document.querySelector('.image-modal-close');
    
    let lastMessageDate = null;
    const today = new Date().toISOString().split('T')[0];

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
            const thinkingBubbleWrapper = appendMessage('assistant', thinkingContent, [], true);
            scrollToBottom();

            try {
                const response = await fetch(window.chatConfig.postUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': window.chatConfig.csrfToken,
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                if (!response.ok) {
                    let errorText = `サーバーエラーが発生しました (Status: ${response.status})。`;
                    if (response.status === 419) {
                        errorText = 'ページの有効期限が切れました。ページをリロードして再度お試しください。';
                    } else {
                        try {
                            const errorData = await response.json();
                            errorText = errorData.message || errorText;
                        } catch (e) { /* ignore */ }
                    }
                    throw new Error(errorText);
                }

                const data = await response.json();

                if (data.success) {
                    thinkingBubbleWrapper.querySelector('.chat-bubble').innerHTML = marked.parse(data.message || '');
                    thinkingBubbleWrapper.querySelector('.chat-timestamp').style.display = 'block';
                    applySyntaxHighlightingToElement(thinkingBubbleWrapper);
                } else {
                    throw new Error(data.message || '不明なAPIエラーが発生しました。');
                }
            } catch (error) {
                console.error('送信エラー:', error);
                thinkingBubbleWrapper.querySelector('.chat-bubble').innerHTML = `<p class="text-danger">エラー: ${escapeHtml(error.message)}</p>`;
                thinkingBubbleWrapper.querySelector('.chat-timestamp').style.display = 'block';
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="bi bi-send"></i>';
                scrollToBottom();
            }
        });
    }

    function appendMessage(role, textContent, files = [], isThinking = false) {
        const now = new Date();
        const currentDate = now.toISOString().split('T')[0];

        if (currentDate !== lastMessageDate) {
            const dateDivider = document.createElement('div');
            dateDivider.className = 'date-divider';
            const dateLabel = (currentDate === today) ? '今日' : `${now.getFullYear()}年${now.getMonth() + 1}月${now.getDate()}日`;
            dateDivider.innerHTML = `<span>${dateLabel}</span>`;
            chatHistory.appendChild(dateDivider);
            lastMessageDate = currentDate;
        }

        const messageWrapper = document.createElement('div');
        messageWrapper.className = `d-flex mb-3 ${role === 'user' ? 'justify-content-end user-message' : 'justify-content-start assistant-message'}`;

        const messageContainer = document.createElement('div');
        messageContainer.className = 'message-container';

        const bubble = document.createElement('div');
        bubble.className = 'chat-bubble';

        let filesHtml = '';
        if (role === 'user' && files.length > 0) {
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
            if (isThinking) {
                textHtml = textContent;
            } else {
                const content = role === 'user' ? escapeHtml(textContent).replace(/\n/g, '<br>') : marked.parse(textContent);
                textHtml = `<div class="mt-2">${content}</div>`;
            }
        }
        bubble.innerHTML = filesHtml + textHtml;

        const timestampDiv = document.createElement('div');
        timestampDiv.className = 'chat-timestamp';
        timestampDiv.textContent = `${now.getHours().toString().padStart(2, '0')}:${now.getMinutes().toString().padStart(2, '0')}`;

        if (isThinking) {
            timestampDiv.style.display = 'none';
        }

        messageContainer.appendChild(bubble);
        messageContainer.appendChild(timestampDiv);
        messageWrapper.appendChild(messageContainer);
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
        copyButton.innerHTML = '<i class="bi bi-clipboard"></i> Copy';
        pre.appendChild(copyButton);
        copyButton.addEventListener('click', () => {
            navigator.clipboard.writeText(code.innerText).then(() => {
                copyButton.innerHTML = '<i class="bi bi-check-lg"></i> Copied';
                setTimeout(() => {
                    copyButton.innerHTML = '<i class="bi bi-clipboard"></i> Copy';
                }, 2000);
            });
        });
    }

    function scrollToBottom() {
        if (chatHistory) {
            chatHistory.scrollTop = chatHistory.scrollHeight;
        }
    }

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
            if (e.target.tagName === 'IMG' && e.target.closest('.sent-files')) {
                openModal(e.target.src);
            }
        });

        // Initialize last message date from existing messages
        const lastMessageElement = chatHistory.querySelector('.d-flex.mb-3:last-child');
        if(lastMessageElement) {
            // This is a simplification; for full accuracy, you'd parse the timestamp.
            // But for just checking if a new day has started, this is sufficient.
            lastMessageDate = today; 
        }
    }

    if (closeModalBtn) closeModalBtn.addEventListener('click', closeModal);
    if (imageModal) imageModal.addEventListener('click', (e) => {
        if (e.target === imageModal) {
            closeModal();
        }
    });

    setTimeout(() => {
        document.querySelectorAll('.assistant-message .chat-bubble').forEach(applySyntaxHighlightingToElement);
        scrollToBottom();
    }, 100);
});

