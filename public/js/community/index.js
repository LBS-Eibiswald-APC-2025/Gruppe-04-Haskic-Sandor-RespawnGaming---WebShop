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

        // Zusätzlich: Status zurücksetzen wenn Textarea verlassen wird
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
                            ? `${users[0]} schreibt...`
                            : `${users.join(', ')} schreiben...`;
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
            body: `room_id=${roomId}&message=${encodeURIComponent(messageText)}`
        })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    lastSentMessageId = data.messageId;
                    const now = new Date(); // Erzeuge neues Datum

                    const newMessageElement = createMessageElement(
                        data.username,
                        data.avatar || '/avatars/default.jpg',
                        messageText,
                        now.toISOString(), // ISO-String für konsistente Formatierung
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

    function createMessageElement(username, avatarUrl, messageText, timestamp, isOwnMessage, messageId) {
        const messageDiv = document.createElement('div');
        messageDiv.classList.add('message');
        messageDiv.dataset.messageId = messageId;
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

        messageDiv.innerHTML = `
        <img src="${avatarUrl}" alt="Avatar" class="user-avatar">
        <div class="message-content">
            <strong>${username}</strong>
            <span class="timestamp">${formattedTimestamp}</span>
            <p>${messageText}</p>
        </div>
    `;

        return messageDiv;
    }

    function fetchNewMessages() {
        if (!roomId) return;

        // Initialisiere lastMessageId falls noch nicht gesetzt
        if (lastMessageId === 0) {
            initializeLastMessageId();
        }

        fetch(`/community/getNewMessages?room_id=${roomId}&last_message_id=${lastMessageId}`)
            .then(response => response.json())
            .then(data => {
                if (data.messages && data.messages.length > 0) {
                    data.messages.forEach(message => {
                        // Prüfen ob die Nachricht bereits existiert
                        if (!document.querySelector(`.message[data-message-id="${message.id}"]`)) {
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
        const messages = chatMessages.querySelectorAll('.message');
        if (messages.length > 0) {
            const lastMessage = messages[messages.length - 1];
            const messageId = parseInt(lastMessage.dataset.messageId || '0');
            lastMessageId = messageId;
            lastSentMessageId = messageId; // Verhindert doppeltes Laden der letzten Nachricht
        }
    }

    initializeLastMessageId();
    scrollToLatestMessage();

    if (roomId) {
        setInterval(fetchNewMessages, 3000);
    }

    window.addEventListener('beforeunload', function() {
        updateTypingStatus(false);
    });
});