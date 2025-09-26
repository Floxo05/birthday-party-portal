import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static values = {
        partyId: String,
        ownerId: String,
        fetchUrl: String,
        postUrl: String,
    };

    connect() {
        this.messagesEl = this.element.querySelector('#chat-messages');
        this.formEl = this.element.querySelector('#chat-form');
        this.lastId = null;
        this.isFetching = false;
        this.pollIntervalMs = 2500;
        this._poll = this._poll.bind(this);
        this._poll();
        this.timer = setInterval(this._poll, this.pollIntervalMs);
    }

    disconnect() {
        if (this.timer) {
            clearInterval(this.timer);
        }
    }

    async _poll() {
        if (this.isFetching) return;
        this.isFetching = true;
        try {
            const url = new URL(this.fetchUrlValue, window.location.origin);
            if (this.lastId) {
                url.searchParams.set('sinceId', this.lastId);
            }
            const res = await fetch(url.toString(), { headers: { 'Accept': 'application/json' } });
            if (!res.ok) return;
            const data = await res.json();
            const items = data.items || [];
            for (const msg of items) {
                this._appendMessage(msg);
                this.lastId = msg.id;
            }
            if (items.length > 0) {
                this.messagesEl.scrollTop = this.messagesEl.scrollHeight;
            }
        } catch (_) {
            // ignore transient errors
        } finally {
            this.isFetching = false;
        }
    }

    async send(event) {
        event.preventDefault();
        const input = this.formEl.querySelector('input[name="content"]');
        const content = (input.value || '').trim();
        if (!content) return;

        try {
            const res = await fetch(this.postUrlValue, {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'Content-Type': 'application/json' },
                body: JSON.stringify({ content })
            });
            if (!res.ok) return;
            const msg = await res.json();
            this._appendMessage(msg);
            this.lastId = msg.id;
            input.value = '';
            this.messagesEl.scrollTop = this.messagesEl.scrollHeight;
        } catch (_) {
            // ignore
        }
    }

    _appendMessage(msg) {
        const wrapper = document.createElement('div');
        const isOwn = !!msg.isOwn;
        wrapper.className = `d-flex mb-2 ${isOwn ? 'justify-content-end' : 'justify-content-start'}`;

        const bubble = document.createElement('div');
        bubble.className = `p-2 rounded-3 shadow-sm ${isOwn ? 'bg-primary text-white' : 'bg-light border'}`;
        bubble.style.maxWidth = '85%';

        const header = document.createElement('div');
        header.className = 'small text-muted mb-1';
        const time = msg.createdAt ? new Date(msg.createdAt) : null;
        const shortTime = time ? time.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) : '';
        const sender = msg.senderName || 'Unbekannt';
        header.textContent = isOwn ? `Du · ${shortTime}` : `${sender} · ${shortTime}`;

        const body = document.createElement('div');
        body.textContent = msg.content;

        bubble.appendChild(header);
        bubble.appendChild(body);
        wrapper.appendChild(bubble);
        this.messagesEl.appendChild(wrapper);
    }
}


