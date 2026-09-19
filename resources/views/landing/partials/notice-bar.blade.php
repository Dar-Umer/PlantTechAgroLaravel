@php
    $noticeBar = config('frontend.notice_bar', []);
    $noticeItems = (!empty($noticeBar['enabled']) && !empty($noticeBar['items']) && is_array($noticeBar['items']))
        ? array_values($noticeBar['items'])
        : [];
    $noticeSpeed = max(10, min(120, (int) ($noticeBar['speed'] ?? 40)));
@endphp
@if($noticeItems !== [])
    <div class="bg-white dark:bg-gray-900 border-b border-gray-100 dark:border-gray-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center gap-3 py-1.5">
            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md bg-red-600 text-white text-[11px] font-bold tracking-wide flex-shrink-0">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                NOTICE
            </span>
            <div class="overflow-hidden flex-1" aria-live="polite">
                <div class="animate-marquee flex w-max items-center gap-12 whitespace-nowrap text-[13px] text-gray-600 dark:text-gray-300" style="animation-duration: {{ $noticeSpeed }}s">
                    @foreach($noticeItems as $noticeItem)
                        <span>{{ $noticeItem }}</span>
                    @endforeach
                    @foreach($noticeItems as $noticeItem)
                        <span aria-hidden="true">{{ $noticeItem }}</span>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endif
