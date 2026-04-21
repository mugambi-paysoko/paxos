{{-- Lightweight JSON polling: visibility-aware; transfer mode stops at terminal status; digest mode reloads the page when the server digest changes. --}}
@props([
    'url',
    'snapshot' => [],
    'interval' => 15000,
    'mode' => 'transfer',
    'reloadOnChange' => false,
])

@php
    $pollId = 'status-poll-' . substr(sha1($url . json_encode($snapshot) . $mode), 0, 12);
@endphp

@push('scripts')
    <script>
        (function () {
            const pollId = @json($pollId);
            const url = @json($url);
            const intervalMs = {{ (int) $interval }};
            const mode = @json($mode);
            const reloadOnChange = @json((bool) $reloadOnChange);
            let lastSnapshot = @json($snapshot);
            let timer = null;

            function snapshotFromPayload(data) {
                if (mode === 'identity') {
                    return {
                        id: data.id_verification_status,
                        s: data.sanctions_verification_status,
                    };
                }
                if (mode === 'digest') {
                    return { d: data.digest || '' };
                }
                return { st: data.status || 'PENDING' };
            }

            function snapshotsEqual(a, b) {
                return JSON.stringify(a) === JSON.stringify(b);
            }

            function transferTerminal(status) {
                const s = (status || 'PENDING').toString().toUpperCase();
                return ['COMPLETED', 'FAILED', 'CANCELLED', 'REJECTED', 'SETTLED', 'RETURNED'].includes(s);
            }

            function identityTerminal(snap) {
                const id = (snap.id || '').toString().toUpperCase();
                const s = (snap.s || '').toString().toUpperCase();
                return id !== 'PENDING' && s !== 'PENDING';
            }

            function applyTransferStatus(status) {
                const el = document.querySelector('[data-status-poll-target="transfer-status"]');
                if (!el) {
                    return;
                }
                el.textContent = status || 'PENDING';
            }

            function stop() {
                if (timer) {
                    clearInterval(timer);
                    timer = null;
                }
            }

            async function tick() {
                if (document.visibilityState !== 'visible') {
                    return;
                }
                try {
                    const res = await fetch(url, {
                        headers: {
                            Accept: 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        credentials: 'same-origin',
                    });
                    if (!res.ok) {
                        return;
                    }
                    const data = await res.json();
                    const next = snapshotFromPayload(data);
                    if (mode === 'digest') {
                        if (snapshotsEqual(next, lastSnapshot)) {
                            return;
                        }
                        window.location.reload();
                        return;
                    }
                    if (snapshotsEqual(next, lastSnapshot)) {
                        if (mode === 'transfer' && transferTerminal(data.status)) {
                            stop();
                        }
                        if (mode === 'identity' && identityTerminal(next)) {
                            stop();
                        }
                        return;
                    }
                    lastSnapshot = next;
                    if (reloadOnChange) {
                        window.location.reload();
                        return;
                    }
                    if (mode === 'transfer') {
                        applyTransferStatus(data.status);
                    }
                    if (mode === 'transfer' && transferTerminal(data.status)) {
                        stop();
                    }
                } catch (e) {
                    console.warn('[' + pollId + '] status poll failed', e);
                }
            }

            document.addEventListener('visibilitychange', function () {
                if (document.visibilityState === 'visible') {
                    tick();
                }
            });

            timer = setInterval(tick, intervalMs);
            setTimeout(tick, 2000);
        })();
    </script>
@endpush
