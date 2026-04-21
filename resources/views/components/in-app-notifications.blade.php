{{-- Unread database notifications (e.g. Paxos transfer updates). Works in Bootstrap (borrower) and Tailwind (lender) shells. --}}
@auth
    @php
        $inAppNotifications = auth()->user()->unreadNotifications()->latest()->take(8)->get();
        $initialNotificationIds = $inAppNotifications->pluck('id')->values()->all();
    @endphp
    <div id="paxos-toast-host" style="position:fixed;top:1rem;right:1rem;z-index:1085;max-width:22rem;width:calc(100% - 2rem);pointer-events:none;padding:0.25rem;display:flex;flex-direction:column;gap:0.5rem;" aria-live="polite" aria-atomic="true"></div>
    @if ($inAppNotifications->isNotEmpty())
        <div class="mb-4" id="in-app-notifications" role="region" aria-label="In-app notifications">
            @foreach ($inAppNotifications as $n)
                @php($data = $n->data ?? [])
                <div class="mb-2 p-3 rounded-3 border shadow-sm d-flex flex-wrap justify-content-between gap-2 align-items-start bg-white">
                    <div class="min-w-0">
                        <p class="fw-semibold mb-1 text-body">{{ $data['title'] ?? __('Update') }}</p>
                        <p class="mb-0 small text-muted">{{ $data['message'] ?? '' }}</p>
                        @if (! empty($data['action_url']))
                            <a href="{{ $data['action_url'] }}" class="btn btn-sm btn-outline-primary rounded-3 mt-2">{{ $data['action_label'] ?? __('View') }}</a>
                        @endif
                    </div>
                    <form method="POST" action="{{ route('notifications.read', $n->id) }}" class="flex-shrink-0">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-secondary rounded-3">{{ __('Dismiss') }}</button>
                    </form>
                </div>
            @endforeach
        </div>
    @endif
    <script>
        (function () {
            const pollUrl = @json(route('notifications.unread-summary'));
            const knownIds = new Set(@json($initialNotificationIds));

            function showToast(n) {
                const host = document.getElementById('paxos-toast-host');
                if (! host) {
                    return;
                }
                const card = document.createElement('div');
                card.style.pointerEvents = 'auto';
                card.style.border = '1px solid rgba(0,0,0,0.08)';
                card.style.borderRadius = '12px';
                card.style.boxShadow = '0 8px 24px rgba(0,0,0,0.12)';
                card.style.background = '#fff';
                card.style.padding = '12px 14px';
                card.style.fontSize = '0.9rem';

                const title = document.createElement('p');
                title.style.fontWeight = '600';
                title.style.margin = '0 0 6px 0';
                title.textContent = n.title || 'Update';

                const msg = document.createElement('p');
                msg.style.margin = '0 0 8px 0';
                msg.style.color = '#555';
                msg.textContent = n.message || '';

                card.appendChild(title);
                card.appendChild(msg);

                if (n.action_url) {
                    const link = document.createElement('a');
                    link.href = n.action_url;
                    link.textContent = n.action_label || 'View';
                    link.style.display = 'inline-block';
                    link.style.fontWeight = '500';
                    link.style.marginTop = '4px';
                    card.appendChild(link);
                }

                host.appendChild(card);
                window.setTimeout(function () {
                    card.remove();
                }, 14000);
            }

            async function poll() {
                if (document.visibilityState !== 'visible') {
                    return;
                }
                try {
                    const res = await fetch(pollUrl, {
                        headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                        credentials: 'same-origin',
                    });
                    if (! res.ok) {
                        return;
                    }
                    const body = await res.json();
                    const list = Array.isArray(body.notifications) ? body.notifications : [];
                    for (let i = 0; i < list.length; i++) {
                        const n = list[i];
                        if (! n || ! n.id || knownIds.has(n.id)) {
                            continue;
                        }
                        knownIds.add(n.id);
                        showToast(n);
                    }
                } catch (e) {
                    console.warn('notification poll failed', e);
                }
            }

            window.setInterval(poll, 12000);
            window.setTimeout(poll, 2500);
        })();
    </script>
@endauth
