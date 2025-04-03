document.addEventListener('DOMContentLoaded', function () {
    const chatForm = document.querySelector('.message-form');
    const chatMessages = document.getElementById('chatMessages');
    const roomId = document.querySelector('input[name="room_id"]')?.value;
    const messageTextarea = document.querySelector('textarea[name="message"]');
    let lastMessageId = 0;
    let lastSentMessageId = 0;
    let typingTimer;
    const TYPING_TIMER_LENGTH = 1000;

    if (messageTextarea) {
        messageTextarea.addEventListener('input', function() {
            clearTimeout(typingTimer);
            const message = messageTextarea.value.trim();

            // Nur Status ändern wenn tatsächlich Text vorhanden
            updateTypingStatus(message.length > 0);

            // Timer zum Zurücksetzen
            typingTimer = setTimeout(() => {
                updateTypingStatus(false);
            }, TYPING_TIMER_LENGTH);
        });

        // Zusätzlich: Status zurücksetzen, wenn Textarea verlassen wird
        messageTextarea.addEventListener('blur', function() {
            clearTimeout(typingTimer);
            updateTypingStatus(false);
        });
    }

    function updateTypingStatus(isTyping) {
        fetch('/community/updateTypingStatus', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                room_id: roomId,
                is_typing: isTyping
            })
        });
    }

    function checkTypingUsers() {
        if (!roomId) return;

        fetch(`/community/getTypingUsers?room_id=${roomId}`)
            .then(response => response.json())
            .then(data => {
                const typingStatus = document.getElementById('typingStatus');
                if (data.typing_users && data.typing_users.length > 0) {
                    // Username aus $_SESSION im PHP laden
                    const currentUser = window.currentUsername || '';
                    // Alle User außer dem aktuellen anzeigen
                    const users = data.typing_users.filter(user => user !== currentUser);

                    if (users.length > 0) {
                        typingStatus.textContent = users.length === 1
                            ? `${escapeHTML(users[0])} schreibt...`
                            : `${users.map(escapeHTML).join(', ')} schreiben...`;
                    } else {
                        typingStatus.textContent = '';
                    }
                } else {
                    typingStatus.textContent = '';
                }
            });
    }

    // Öfter prüfen für flüssigere Anzeige
    if (roomId) {
        setInterval(checkTypingUsers, 500);
    }

    // Event-Listener für Enter-Taste
    if (messageTextarea) {
        messageTextarea.addEventListener('keydown', function (e) {
            // Senden bei Enter ohne Shift
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                const messageText = this.value.trim();

                if (messageText !== '') {
                    sendMessage(messageText);
                }
            }
        });
    }

    function sendMessage(messageText) {
        fetch('/community/sendMessage', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `room_id=${encodeURIComponent(roomId)}&message=${encodeURIComponent(messageText)}`
        })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    lastSentMessageId = data.messageId;
                    const now = new Date();

                    const newMessageElement = createMessageElement(
                        data.username,
                        data.avatar || '/avatars/default.jpg',
                        messageText,
                        now.toISOString(),
                        true,
                        data.messageId
                    );

                    chatMessages.appendChild(newMessageElement);
                    messageTextarea.value = '';
                    scrollToLatestMessage();
                } else {
                    swal({
                        title: "Fehler",
                        text: data.message,
                        icon: "error",
                        button: "OK",
                    });
                }
            })
    }

    // Vorhandene Chat-Form Submit-Handler
    if (chatForm) {
        chatForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const messageText = messageTextarea.value.trim();

            if (messageText === '') {
                alert('Bitte geben Sie eine Nachricht ein.');
                return;
            }

            sendMessage(messageText);
        });
    }

    // Hilfsfunktion zum Escapen von HTML
    function escapeHTML(str) {
        if (!str) return '';
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    function createMessageElement(username, avatarUrl, messageText, timestamp, isOwnMessage, messageId) {
        const messageDiv = document.createElement('div');
        messageDiv.classList.add('message');
        messageDiv.dataset.id = messageId;
        if (isOwnMessage) {
            messageDiv.classList.add('own-message');
        }

        // Formatierung des Timestamps
        const date = new Date(timestamp);
        const formattedTimestamp = date.toLocaleString('de-DE', {
            day: '2-digit',
            month: '2-digit',
            year: '2-digit',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        }).replace(/,/g, ' - ');

        // HTML escapen aber fast wie im Original bleiben
        messageDiv.innerHTML = `
        <img src="${escapeHTML(avatarUrl)}" alt="Avatar" class="user-avatar">
        <div class="message-content">
            <strong>${escapeHTML(username)}</strong>
            <span class="timestamp">${escapeHTML(formattedTimestamp)}</span>
            <p class="message-text">${escapeHTML(messageText)}</p>
        </div>
    `;

        return messageDiv;
    }

    function fetchNewMessages() {
        if (!roomId) return;

        fetch(`/community/getNewMessages?room_id=${encodeURIComponent(roomId)}&last_message_id=${encodeURIComponent(lastMessageId)}`)
            .then(response => response.json())
            .then(data => {
                if (data.messages && data.messages.length > 0) {
                    const existingMessages = new Set(
                        Array.from(document.querySelectorAll('.message'))
                            .map(el => el.dataset.id)
                    );

                    data.messages.forEach(message => {
                        if (!existingMessages.has(String(message.id))) {
                            const messageElement = createMessageElement(
                                message.user_name,
                                message.avatar || '/avatars/default.jpg',
                                message.message_text,
                                message.created_at,
                                message.is_own_message,
                                message.id
                            );
                            chatMessages.appendChild(messageElement);
                            lastMessageId = Math.max(lastMessageId, message.id);
                        }
                    });
                    scrollToLatestMessage();
                }
            })
            .catch(error => console.error('Fehler beim Abrufen neuer Nachrichten:', error));
    }

    function scrollToLatestMessage() {
        if (chatMessages) {
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }
    }

    function initializeLastMessageId() {
        const messages = document.querySelectorAll('.message');
        if (messages.length > 0) {
            const ids = Array.from(messages).map(msg => parseInt(msg.dataset.id || '0'));
            lastMessageId = Math.max(...ids);
        } else {
            lastMessageId = 0;
        }
    }

    // Verbesserte Funktion zum Schutz vor CSS- und JS-Angriffen
    function sanitizePage() {
        // Entferne verdächtige style-Tags
        document.querySelectorAll('style').forEach(style => {
            if (style.innerHTML.includes('rainbow') ||
                style.innerHTML.includes('animation') ||
                !style.hasAttribute('data-safe')) {
                style.remove();
            }
        });

        // Entferne iframes
        document.querySelectorAll('iframe').forEach(iframe => {
            iframe.remove();
        });

        // Entferne objects und embeds
        document.querySelectorAll('object, embed').forEach(obj => {
            obj.remove();
        });

        // Entferne alle Event-Handler von Elementen
        const dangerousAttrs = ['onclick', 'onload', 'onerror', 'onmouseover', 'onmouseout'];
        dangerousAttrs.forEach(attr => {
            document.querySelectorAll(`[${attr}]`).forEach(el => {
                el.removeAttribute(attr);
            });
        });

        // Prüfe alle Bilder auf potenziell gefährliche Attribute
        document.querySelectorAll('img').forEach(img => {
            if (img.hasAttribute('onload') ||
                img.hasAttribute('onerror') ||
                img.hasAttribute('onclick') ||
                (img.src && img.src.startsWith('javascript:'))) {
                // Bild erhalten aber gefährliche Attribute entfernen
                const newImg = document.createElement('img');
                newImg.src = img.src.startsWith('javascript:') ? '/avatars/default.jpg' : img.src;
                newImg.alt = img.alt;
                newImg.className = img.className;
                img.parentNode.replaceChild(newImg, img);
            }
        });

        // Entferne JavaScript URLs aus Links
        document.querySelectorAll('a').forEach(link => {
            if (link.href && link.href.toLowerCase().startsWith('javascript:')) {
                link.href = '#';
                link.removeAttribute('onclick');
            }
        });
    }

    // Optimierter MutationObserver - nur für den Chat-Bereich
    const chatContainer = document.getElementById('chatMessages');

    if (chatContainer) {
        const observer = new MutationObserver(function(mutations) {
            // Prüfe, ob die Mutationen im Chat-Bereich aufgetreten sind
            let needsCleaning = false;

            for (const mutation of mutations) {
                if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
                    needsCleaning = true;
                    break;
                }
            }

            if (needsCleaning) {
                sanitizePage();
            }
        });

        // Beobachte nur den Chat-Bereich statt dem ganzen Body
        observer.observe(chatContainer, {
            childList: true,
            subtree: true
        });
    }

    // Initial einmal ausführen
    sanitizePage();
});