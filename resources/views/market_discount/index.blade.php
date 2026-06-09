<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ $tabTitle }}
        </h2>
    </x-slot>

    <div class="py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-0">
                    <iframe id="marketDiscountIframe" src="{{ $iframeUrl }}#pagemode=thumbs&toolbar=1"
                            title="{{ $tabTitle }}"
                            class="w-full border-0 block"
                            loading="lazy"
                            referrerpolicy="strict-origin-when-cross-origin"
                            allow="fullscreen">
                    </iframe>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    (function() {
        function adjustIframeHeight() {
            var iframe = document.getElementById('marketDiscountIframe');
            if (!iframe) return;

            // Distance from top of viewport to iframe top
            var rect = iframe.getBoundingClientRect();

            // Optional footer height if a footer element exists
            var footer = document.querySelector('footer');
            var footerH = footer ? footer.offsetHeight : 0;

            // Account for bottom padding of the immediate container (py-4)
            var container = iframe.closest('.py-4') || iframe.parentElement;
            var bottomPadding = 0;
            if (container) {
                var cs = window.getComputedStyle(container);
                bottomPadding = parseFloat(cs.paddingBottom) || 0;
            }

            var available = Math.max(0, window.innerHeight - rect.top - footerH - bottomPadding);
            iframe.style.height = available + 'px';
        }

        var rafId;
        function requestAdjust() {
            if (rafId) cancelAnimationFrame(rafId);
            rafId = requestAnimationFrame(adjustIframeHeight);
        }

        // Initial and reactive adjustments
        document.addEventListener('DOMContentLoaded', requestAdjust);
        window.addEventListener('resize', requestAdjust, { passive: true });
        window.addEventListener('orientationchange', requestAdjust, { passive: true });
        requestAdjust();

        // Recompute after sidebar animation completes (layout shifts width)
        var toggle = document.getElementById('sidebar-toggle');
        if (toggle) {
            toggle.addEventListener('click', function() {
                setTimeout(requestAdjust, 300);
            });
        }
    })();
    </script>
    @endpush
</x-app-layout>