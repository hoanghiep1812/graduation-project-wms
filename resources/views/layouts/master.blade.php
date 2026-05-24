<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="utf-8" />
    <title>@yield('title', 'WMS - Hệ thống quản lý kho')</title>
    <link rel="shortcut icon" href="{{ asset('assets/media/logos/logo.svg') }}" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/js/app.js'])

    <link href="{{ asset('assets/plugins/global/plugins.bundle.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/style.bundle.css') }}" rel="stylesheet" type="text/css" />

    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>

    @stack('styles')
    <style>
        :root {
            --chat-window-bg: #ffffff;
            --chat-header-bg: #1e293b;
            --chat-header-text: #ffffff;
            --chat-body-bg: #f8fafc;
            --chat-border: #e2e8f0;
            --chat-bubble-ai-bg: #ffffff;
            --chat-bubble-ai-text: #0f172a;
            --chat-bubble-ai-border: #cbd5e1;
            --chat-bubble-user-bg: #2563eb;
            --chat-bubble-user-text: #ffffff;
            --chat-input-bg: #ffffff;
            --chat-input-text: #0f172a;
            --chat-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
            --chat-btn-bg: #2563eb;
            --chat-btn-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }

        [data-bs-theme="dark"],
        body.dark-mode,
        [data-theme="dark"] {
            --chat-window-bg: #1e1e2d;
            --chat-header-bg: #151521;
            --chat-header-text: #e2e8f0;
            --chat-body-bg: #151521;
            --chat-border: #2b2b40;
            --chat-bubble-ai-bg: #1e1e2d;
            --chat-bubble-ai-text: #e2e8f0;
            --chat-bubble-ai-border: #3f3f5a;
            --chat-bubble-user-bg: #2563eb;
            --chat-bubble-user-text: #ffffff;
            --chat-input-bg: #151521;
            --chat-input-text: #e2e8f0;
            --chat-shadow: 0 8px 30px rgba(0, 0, 0, 0.5);
            --chat-btn-bg: #2563eb;
            --chat-btn-shadow: 0 4px 12px rgba(0, 0, 0, 0.4);
        }

        @media (min-width: 992px) {
            #kt_wrapper {
                padding-left: 225px;
                transition: padding-left 0.3s ease;
            }

            body[data-kt-app-sidebar-minimize="on"] #kt_wrapper {
                padding-left: 70px;
            }
        }

        #kt_app_sidebar_menu_wrapper .menu-title,
        #kt_app_sidebar_menu_wrapper .menu-heading {
            white-space: nowrap !important;
        }

        .chat-toggle-btn {
            position: fixed;
            bottom: 24px;
            right: 24px;
            width: 54px;
            height: 54px;
            background: var(--chat-btn-bg);
            color: #ffffff;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            box-shadow: var(--chat-btn-shadow);
            z-index: 10000;
            transition: transform 0.2s ease, background-color 0.2s ease;
        }

        .chat-toggle-btn:hover {
            transform: translateY(-2px);
            background-color: #1d4ed8;
        }

        .chatbot-window {
            position: fixed;
            bottom: 90px;
            right: 24px;
            width: 400px;
            height: 600px;
            max-height: calc(100vh - 120px);
            background: var(--chat-window-bg);
            border-radius: 12px;
            box-shadow: var(--chat-shadow);
            border: 1px solid var(--chat-border);
            display: flex;
            flex-direction: column;
            font-family: inherit;
            z-index: 9999;
            overflow: hidden;
        }

        .chatbot-header {
            background: var(--chat-header-bg);
            color: var(--chat-header-text);
            padding: 14px 18px;
            font-weight: 600;
            font-size: 14px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid var(--chat-border);
        }

        .chatbot-header .close-btn {
            cursor: pointer;
            font-size: 22px;
            line-height: 1;
            opacity: 0.7;
            transition: opacity 0.2s;
        }

        .chatbot-header .close-btn:hover {
            opacity: 1;
        }

        .chatbot-body {
            flex: 1;
            padding: 20px 16px;
            overflow-y: auto;
            background: var(--chat-body-bg);
            font-size: 13px;
            scroll-behavior: smooth;
        }

        .chatbot-footer {
            padding: 12px 16px;
            background: var(--chat-window-bg);
            border-top: 1px solid var(--chat-border);
            display: flex;
            gap: 8px;
        }

        .chatbot-input {
            flex: 1;
            padding: 10px 14px;
            background: var(--chat-input-bg);
            color: var(--chat-input-text);
            border: 1px solid var(--chat-border);
            border-radius: 6px;
            outline: none;
            font-size: 13px;
            transition: border-color 0.2s;
        }

        .chatbot-input:focus {
            border-color: var(--chat-btn-bg);
        }

        .chatbot-send-btn {
            padding: 0 16px;
            background: var(--chat-btn-bg);
            color: #ffffff;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            font-size: 13px;
            transition: background-color 0.2s;
        }

        .chatbot-send-btn:hover {
            background-color: #1d4ed8;
        }

        .chat-bubble {
            padding: 10px 14px;
            border-radius: 8px;
            margin-bottom: 12px;
            width: fit-content;
            max-width: 85%;
            line-height: 1.5;
            word-wrap: break-word;
        }

        .bubble-ai {
            background: var(--chat-bubble-ai-bg);
            color: var(--chat-bubble-ai-text);
            border: 1px solid var(--chat-bubble-ai-border);
            border-bottom-left-radius: 2px;
        }

        .bubble-user {
            background: var(--chat-bubble-user-bg);
            color: var(--chat-bubble-user-text);
            border-bottom-right-radius: 2px;
            margin-left: auto;
        }

        @media (max-width: 576px) {
            .chatbot-window {
                width: calc(100vw - 32px);
                height: calc(100vh - 110px);
                bottom: 85px;
                right: 16px;
            }

            .chat-toggle-btn {
                bottom: 16px;
                right: 16px;
            }
        }

        .markdown-body table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
            font-size: 13px;
        }

        .markdown-body th,
        .markdown-body td {
            border: 1px solid var(--chat-border);
            padding: 8px;
            text-align: left;
        }

        .markdown-body th {
            background-color: rgba(0, 0, 0, 0.03);
            font-weight: 600;
        }

        .markdown-body p {
            margin-top: 0;
            margin-bottom: 8px;
        }

        .markdown-body ul,
        .markdown-body ol {
            margin: 0 0 10px 20px;
            padding: 0;
        }

        .markdown-body strong {
            font-weight: 600;
        }

        .typing-indicator span {
            display: inline-block;
            width: 5px;
            height: 5px;
            background-color: #94a3b8;
            border-radius: 50%;
            margin: 0 2px;
            animation: bounce 1.4s infinite ease-in-out both;
        }

        .typing-indicator span:nth-child(1) {
            animation-delay: -0.32s;
        }

        .typing-indicator span:nth-child(2) {
            animation-delay: -0.16s;
        }

        @keyframes bounce {

            0%,
            80%,
            100% {
                transform: scale(0);
            }

            40% {
                transform: scale(1);
            }
        }
    </style>
</head>

<body id="kt_app_body" data-kt-app-layout="dark-sidebar" data-kt-app-header-fixed="true"
    data-kt-app-sidebar-enabled="true" data-kt-app-sidebar-fixed="true" data-kt-app-sidebar-hoverable="true"
    data-kt-app-sidebar-push-header="true" data-kt-app-sidebar-push-toolbar="true"
    data-kt-app-sidebar-push-footer="true" class="app-default">

    <div class="d-flex flex-column flex-root app-root" id="kt_app_root">
        <div class="app-page flex-column flex-column-fluid" id="kt_app_page">
            @include('layouts.partials.header')
            <div class="app-wrapper flex-column flex-row-fluid" id="kt_app_wrapper">
                @include('layouts.partials.sidebar')
                <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
                    <div class="d-flex flex-column flex-column-fluid">
                        <div id="kt_app_content" class="app-content flex-column-fluid pt-8">
                            <div id="kt_app_content_container" class="app-container container-fluid">
                                @yield('content')
                            </div>
                        </div>
                    </div>
                    @include('layouts.partials.footer')
                </div>
            </div>
        </div>
    </div>

    <div id="chatToggleBtn" class="chat-toggle-btn" onclick="toggleChat()">
        <svg width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
            <path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2z"></path>
        </svg>
    </div>

    <div id="ai-chatbot" class="chatbot-window" style="display: none;">
        <div class="chatbot-header">
            <span>WMS Assistant</span>
            <span class="close-btn" onclick="toggleChat()">&times;</span>
        </div>
        <div id="chatBox" class="chatbot-body"></div>
        <div class="chatbot-footer">
            <input type="text" id="userInput" class="chatbot-input" autocomplete="off"
                placeholder="Nhập câu lệnh tra cứu..." onkeypress="if(event.key === 'Enter') sendAiMessage()">
            <button id="sendBtn" class="chatbot-send-btn" onclick="sendAiMessage()">Gửi</button>
        </div>
    </div>

    <script src="{{ asset('assets/plugins/global/plugins.bundle.js') }}"></script>
    <script src="{{ asset('assets/js/scripts.bundle.js') }}"></script>

    <script>
        const chatBox = document.getElementById('chatBox');
        const chatbot = document.getElementById('ai-chatbot');
        const inputField = document.getElementById('userInput');
        const sendBtn = document.getElementById('sendBtn');

        document.addEventListener("DOMContentLoaded", function () {
            const isChatOpen = sessionStorage.getItem('wms_chat_open');
            if (isChatOpen === 'true') {
                chatbot.style.display = 'flex';
                setTimeout(() => chatBox.scrollTop = chatBox.scrollHeight, 100);
            }

            const savedChat = sessionStorage.getItem('wms_chat_history');
            if (savedChat) {
                chatBox.innerHTML = savedChat;
                setTimeout(() => chatBox.scrollTop = chatBox.scrollHeight, 100);
            } else {
                chatBox.innerHTML = `
                <div class="chat-bubble bubble-ai">
                    Xin chào. Tôi là Trợ lý AI Hệ thống. Tôi có thể giúp bạn tra cứu thông tin gì?
                </div>`;
            }
        });

        document.addEventListener("DOMContentLoaded", function () {
            let currentUnread = parseInt("{{ isset($unreadCount) ? $unreadCount : 0 }}") || 0;
            toastr.options = { "closeButton": true, "progressBar": true, "positionClass": "toast-top-right", "timeOut": "8000" };
            let lastKnownUnread = localStorage.getItem('last_known_unread_count');

            if (currentUnread > 0) {
                if (lastKnownUnread === null || currentUnread > parseInt(lastKnownUnread)) {
                    toastr.clear();
                    toastr.info("Hệ thống có " + currentUnread + " thông báo mới.", "THÔNG BÁO");
                    localStorage.setItem('last_known_unread_count', currentUnread);
                }
            } else {
                localStorage.setItem('last_known_unread_count', 0);
            }

            let isSubmitting = false;
            document.querySelectorAll('form').forEach(form => {
                form.addEventListener('submit', () => { isSubmitting = true; });
            });

            if (typeof window.Echo !== 'undefined') {
                if (!window._echoSubscribed) {
                    window._echoSubscribed = true;
                    window.Echo.channel('de-xuat-channel').listen('.ai.completed', (e) => {
                        if (isSubmitting) return;
                        let userRole = "{{ auth()->check() ? auth()->user()->role : 'guest' }}";

                        if (userRole === 'admin') {
                            if (typeof toastr !== 'undefined') toastr.success(e.message, "HỆ THỐNG");
                            let bellIcon = document.querySelector('.ki-notification-status');
                            if (bellIcon && !document.querySelector('.badge-danger')) {
                                bellIcon.parentElement.insertAdjacentHTML('beforeend', '<span class="bullet badge-danger position-absolute translate-middle top-0 start-100 animation-blink"></span>');
                            }

                            let unreadTextHeader = document.getElementById('unread_text_header');
                            if (unreadTextHeader) {
                                let current = parseInt(unreadTextHeader.innerText) || 0;
                                unreadTextHeader.style.display = 'inline';
                                unreadTextHeader.innerText = (current + 1) + " chưa đọc";
                            }

                            let container = document.querySelector('#notifications_list_container');
                            if (container) {
                                container.insertAdjacentHTML('afterbegin', `
                                    <div class="d-flex flex-column mb-2">
                                        <span class="fw-bold text-gray-800">AI Task</span>
                                        <span class="text-gray-600">${e.message}</span>
                                    </div>
                                `);
                            }
                        }

                        fetch(window.location.href, { headers: { "X-Requested-With": "XMLHttpRequest" } })
                            .then(res => res.text())
                            .then(html => {
                                let parser = new DOMParser();
                                let doc = parser.parseFromString(html, "text/html");

                                let newTableBody = doc.querySelector('.table-responsive tbody');
                                let currentTableBody = document.querySelector('.table-responsive tbody');
                                if (currentTableBody && newTableBody) currentTableBody.innerHTML = newTableBody.innerHTML;

                                let newPagination = doc.querySelector('.mt-4');
                                let currentPagination = document.querySelector('.mt-4');
                                if (currentPagination && newPagination) currentPagination.innerHTML = newPagination.innerHTML;

                                if (currentTableBody) {
                                    currentTableBody.style.opacity = '0.5';
                                    setTimeout(() => currentTableBody.style.opacity = '1', 200);
                                }
                            }).catch(err => console.error(err));
                    });
                }
            }

            let bellContainer = document.querySelector('.ki-notification-status')?.parentElement;
            if (bellContainer) {
                bellContainer.addEventListener('click', function () {
                    let badge = this.querySelector('.badge-danger');
                    if (badge) {
                        badge.remove();
                        let unreadText = document.querySelector('.opacity-75.ps-3');
                        if (unreadText) unreadText.style.display = 'none';
                        currentUnread = 0;
                        localStorage.setItem('last_known_unread_count', 0);

                        let tokenElement = document.querySelector('meta[name="csrf-token"]');
                        if (tokenElement) {
                            fetch("{{ route('notifications.markRead') }}", {
                                method: "POST",
                                headers: {
                                    "Content-Type": "application/json",
                                    "Accept": "application/json",
                                    "X-CSRF-TOKEN": tokenElement.getAttribute('content')
                                }
                            });
                        }
                    }
                });
            }
        });

        function toggleChat() {
            const isHidden = chatbot.style.display === 'none' || chatbot.style.display === '';
            chatbot.style.display = isHidden ? 'flex' : 'none';
            sessionStorage.setItem('wms_chat_open', isHidden ? 'true' : 'false');

            if (isHidden) {
                inputField.focus();
                setTimeout(() => chatBox.scrollTop = chatBox.scrollHeight, 50);
            }
        }

        function saveChatHistory() {
            sessionStorage.setItem('wms_chat_history', chatBox.innerHTML);
        }

        async function sendAiMessage() {
            const text = inputField.value.trim();
            if (!text) return;

            chatBox.innerHTML += `<div class="chat-bubble bubble-user">${text}</div>`;
            inputField.value = '';
            inputField.disabled = true;
            sendBtn.disabled = true;

            setTimeout(() => chatBox.scrollTop = chatBox.scrollHeight, 50);
            saveChatHistory();

            const botBubbleId = 'ai-msg-' + Date.now();
            chatBox.innerHTML += `
            <div id="${botBubbleId}" class="chat-bubble bubble-ai markdown-body">
                <div class="typing-indicator"><span></span><span></span><span></span></div>
            </div>`;

            setTimeout(() => chatBox.scrollTop = chatBox.scrollHeight, 50);
            const botBubble = document.getElementById(botBubbleId);

            try {
                const response = await fetch('/ai-api/chat', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        text: text,
                        session_id: "user_wms_" + Date.now()
                    })
                });

                if (!response.ok) throw new Error(response.status);

                const reader = response.body.getReader();
                const decoder = new TextDecoder('utf-8');
                let rawMarkdown = "";

                while (true) {
                    const { done, value } = await reader.read();
                    if (done) break;

                    if (rawMarkdown === "") botBubble.innerHTML = "";

                    const chunk = decoder.decode(value, { stream: true });
                    rawMarkdown += chunk;

                    botBubble.innerHTML = marked.parse(rawMarkdown);
                    chatBox.scrollTop = chatBox.scrollHeight;
                }
            } catch (error) {
                botBubble.innerHTML = `<span style='color: #dc2626;'>Lỗi kết nối máy chủ AI.</span>`;
            }

            inputField.disabled = false;
            sendBtn.disabled = false;
            inputField.focus();
            saveChatHistory();
        }
    </script>
    @stack('scripts')
</body>

</html>