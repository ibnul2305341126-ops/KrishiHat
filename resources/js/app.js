
import './bootstrap';
import { Modal } from 'bootstrap';
import 'bootstrap';
import './address-selector';

document.querySelectorAll('[data-market-price-range]').forEach((range) => {
    const output = document.querySelector(`[data-market-price-output][for="${range.id}"]`);
    if (!output) return;

    const updatePriceOutput = () => {
        const value = Number(range.value);
        output.value = `৳${value.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
        output.textContent = output.value;
    };

    range.addEventListener('input', updatePriceOutput);
    updatePriceOutput();
});

document.querySelectorAll('[data-product-photo-input]').forEach((input) => {
    const preview = input.parentElement.querySelector('[data-product-photo-previews]');
    input.addEventListener('change', () => {
        preview.replaceChildren();
        const files = [...input.files];
        files.forEach((file, index) => {
            const wrapper = document.createElement('div');
            wrapper.className = 'position-relative';
            const image = document.createElement('img');
            image.alt = file.name;
            image.width = 100;
            image.height = 75;
            image.style.objectFit = 'cover';
            image.className = 'rounded border';
            image.src = URL.createObjectURL(file);
            const remove = document.createElement('button');
            remove.type = 'button';
            remove.className = 'btn btn-sm btn-danger position-absolute top-0 end-0';
            remove.textContent = '×';
            remove.setAttribute('aria-label', `Remove ${file.name}`);
            remove.addEventListener('click', () => {
                const transfer = new DataTransfer();
                files.filter((_, fileIndex) => fileIndex !== index).forEach((selected) => transfer.items.add(selected));
                input.files = transfer.files;
                input.dispatchEvent(new Event('change'));
            });
            wrapper.append(image, remove);
            preview.append(wrapper);
        });
    });
});

document.querySelectorAll('[data-fallback-image]').forEach((image) => {
    const placeholder = image.nextElementSibling;

    if (!placeholder?.matches('[data-image-placeholder]')) {
        return;
    }

    const showPlaceholder = () => {
        image.hidden = true;
        placeholder.hidden = false;
    };

    image.addEventListener('error', showPlaceholder, { once: true });

    if (image.complete && image.naturalWidth === 0) {
        showPlaceholder();
    }
});

const chatPanel = document.querySelector('[data-kh-chat-panel]');

if (chatPanel) {
    const chatToggles = document.querySelectorAll('[data-kh-chat-toggle]');
    const chatClose = chatPanel.querySelector('[data-kh-chat-close]');
    const chatForm = chatPanel.querySelector('[data-kh-chat-form]');
    const chatInput = chatPanel.querySelector('[data-kh-chat-input]');
    const chatSubmit = chatPanel.querySelector('[data-kh-chat-submit]');
    const chatMessages = chatPanel.querySelector('[data-kh-chat-messages]');
    const loadingIndicator = chatPanel.querySelector('[data-kh-chat-loading]');

    const addMessage = (message, kind = 'assistant') => {
        const item = document.createElement('div');
        item.className = `kh-chat-message kh-chat-message-${kind}`;
        item.textContent = message;
        chatMessages.append(item);
        chatMessages.scrollTop = chatMessages.scrollHeight;
        return item;
    };

    const setChatOpen = (isOpen) => {
        chatPanel.hidden = !isOpen;
        chatToggles.forEach((toggle) => toggle.setAttribute('aria-expanded', String(isOpen)));

        if (isOpen) {
            chatInput.focus();
        } else {
            chatToggles[0]?.focus();
        }
    };

    chatToggles.forEach((toggle) => toggle.addEventListener('click', () => setChatOpen(chatPanel.hidden)));
    chatClose.addEventListener('click', () => setChatOpen(false));

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !chatPanel.hidden) {
            setChatOpen(false);
        }
    });

    chatForm.addEventListener('submit', (event) => {
        event.preventDefault();

        const message = chatInput.value.trim();

        if (!message) {
            return;
        }

        addMessage(message, 'user');
        chatInput.value = '';
        loadingIndicator.hidden = true;

        addMessage(
            'The AI service is not connected yet. Your message was not sent.',
            'assistant kh-chat-message-error',
        );
    });

    chatSubmit.disabled = false;
}

const conversationMessages = document.querySelector('[data-chat-message-list]');

if (conversationMessages) {
    const endpoint = conversationMessages.dataset.messagesUrl;
    const currentUserId = Number(conversationMessages.dataset.currentUserId);
    let lastMessageId = Number(conversationMessages.dataset.lastMessageId || 0);
    let polling = false;
    const messageIds = new Set(
        [...conversationMessages.querySelectorAll('[data-message-id]')].map((item) => Number(item.dataset.messageId)),
    );
    const connectionStatus = document.querySelector('[data-chat-connection]');
    const imageModalElement = document.querySelector('#chatImageModal');
    const imageModal = imageModalElement ? Modal.getOrCreateInstance(imageModalElement) : null;
    const modalImage = imageModalElement?.querySelector('[data-chat-image-modal]');

    conversationMessages.addEventListener('click', (event) => {
        const target = event.target instanceof Element ? event.target : null;
        const link = target?.closest('[data-chat-image-open]');
        if (!link || !imageModal || !modalImage) return;

        event.preventDefault();
        modalImage.src = link.href;
        modalImage.alt = link.querySelector('img')?.alt || 'Chat image';
        imageModal.show();
    });

    imageModalElement?.addEventListener('hidden.bs.modal', () => {
        if (modalImage) modalImage.removeAttribute('src');
    });

    const appendMessage = (message) => {
        const messageId = Number(message.id);
        if (messageIds.has(messageId)) return;

        const wasAtBottom = conversationMessages.scrollHeight - conversationMessages.scrollTop - conversationMessages.clientHeight < 48;
        const emptyState = conversationMessages.querySelector('[data-chat-empty]');
        emptyState?.remove();

        const article = document.createElement('article');
        article.className = `mb-3 ${Number(message.sender_id) === currentUserId ? 'text-end' : ''}`;
        article.dataset.messageId = String(messageId);

        const timestamp = document.createElement('span');
        timestamp.className = 'small text-secondary';
        timestamp.textContent = `${message.sender_name} · ${message.sent_at}`;

        const bubble = document.createElement('div');
        bubble.className = 'd-inline-block text-start p-3 rounded bg-light';
        bubble.style.cssText = 'max-width:100%;overflow-wrap:anywhere';

        if (message.body) {
            const body = document.createElement('p');
            body.className = 'mb-0';
            body.textContent = message.body;
            bubble.append(body);
        }

        if (message.attachment_url) {
            let attachmentUrl;
            try {
                attachmentUrl = new URL(message.attachment_url, window.location.href);
            } catch {
                return;
            }

            if (attachmentUrl.origin !== window.location.origin) return;

            const link = document.createElement('a');
            link.href = attachmentUrl.href;
            link.dataset.chatImageOpen = '';
            link.setAttribute('aria-label', 'Open full image attachment');

            const image = document.createElement('img');
            image.src = attachmentUrl.href;
            image.alt = `Image sent by ${message.sender_name}`;
            image.loading = 'lazy';
            image.style.cssText = 'display:block;max-width:min(100%, 420px);max-height:420px;width:auto;height:auto;border-radius:.5rem;margin-top:.5rem';
            link.append(image);
            bubble.append(link);
        }

        article.append(timestamp, document.createElement('br'), bubble);
        conversationMessages.append(article);
        messageIds.add(messageId);
        lastMessageId = Math.max(lastMessageId, messageId);

        if (wasAtBottom) conversationMessages.scrollTop = conversationMessages.scrollHeight;
    };

    const pollMessages = async () => {
        if (polling || document.hidden) return;
        polling = true;

        try {
            const response = await fetch(`${endpoint}?after=${lastMessageId}`, {
                method: 'GET',
                credentials: 'same-origin',
                cache: 'no-store',
                headers: { Accept: 'application/json' },
            });

            if (!response.ok) throw new Error(`Message refresh failed (${response.status})`);
            const messages = await response.json();
            messages.forEach(appendMessage);
            if (connectionStatus) connectionStatus.textContent = 'Connected · checking for messages every 3 seconds';
        } catch {
            if (connectionStatus) connectionStatus.textContent = 'Connection issue · retrying shortly';
        } finally {
            polling = false;
            window.setTimeout(pollMessages, document.hidden ? 5000 : 3000);
        }
    };

    pollMessages();
}
