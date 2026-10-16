@extends('admin.layout')

@section('page-title', $title ?? 'Report')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <h2 class="text-2xl font-bold text-gray-900">{{ $title ?? 'Report' }}</h2>
                @if(!empty($badge))
                    <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">
                        {{ $badge }}
                    </span>
                @endif
            </div>
            @if(!empty($subtitle))
                <p class="text-sm text-gray-500 mt-1">{{ $subtitle }}</p>
            @endif
        </div>
        <div class="flex flex-wrap items-center gap-2">
            @if(!empty($exportPdfUrl))
                <a href="{{ $exportPdfUrl }}" target="_blank"
                   class="inline-flex items-center px-3.5 py-2 border border-gray-300 shadow-xs text-sm font-medium rounded-xl text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                    <svg class="w-4 h-4 mr-2 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                    Export PDF
                </a>
            @endif
            @if(!empty($exportExcelUrl))
                <a href="{{ $exportExcelUrl }}"
                   class="inline-flex items-center px-3.5 py-2 border border-gray-300 shadow-xs text-sm font-medium rounded-xl text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                    <svg class="w-4 h-4 mr-2 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Export Excel / CSV
                </a>
            @endif
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="bg-white rounded-2xl shadow-xs border border-gray-100 p-4">
        <form method="GET" action="{{ $formAction ?? url()->current() }}" class="flex flex-wrap items-center gap-3">
            @if(isset($showDateFilter) && $showDateFilter)
                <div class="flex items-center gap-2">
                    <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">From:</label>
                    <input type="date" name="from" value="{{ $from ?? '' }}" class="px-3 py-1.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 bg-gray-50">
                </div>
                <div class="flex items-center gap-2">
                    <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">To:</label>
                    <input type="date" name="to" value="{{ $to ?? '' }}" class="px-3 py-1.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 bg-gray-50">
                </div>
            @endif

            {{-- Custom extra filters if passed --}}
            @if(isset($extraFilters) && is_array($extraFilters))
                @foreach($extraFilters as $filter)
                    <div class="flex items-center gap-2">
                        @if(!empty($filter['label']))
                            <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ $filter['label'] }}:</label>
                        @endif
                        @if($filter['type'] === 'select')
                            <select name="{{ $filter['name'] }}" class="px-3 py-1.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 bg-gray-50">
                                @foreach($filter['options'] as $val => $text)
                                    <option value="{{ $val }}" {{ (string)($filter['value'] ?? '') === (string)$val ? 'selected' : '' }}>{{ $text }}</option>
                                @endforeach
                            </select>
                        @elseif($filter['type'] === 'text')
                            <input type="text" name="{{ $filter['name'] }}" value="{{ $filter['value'] ?? '' }}" placeholder="{{ $filter['placeholder'] ?? '' }}" class="px-3 py-1.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 bg-gray-50">
                        @endif
                    </div>
                @endforeach
            @endif

            <button type="submit" class="px-4 py-2 bg-emerald-600 text-white font-medium text-sm rounded-xl hover:bg-emerald-700 transition shadow-xs">
                Filter
            </button>
            <a href="{{ $resetUrl ?? url()->current() }}" class="px-3 py-2 text-sm text-gray-500 hover:text-gray-700 font-medium">Reset</a>
        </form>
    </div>

    {{-- Summary KPI Cards --}}
    @if(!empty($summaryCards))
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-{{ min(6, count($summaryCards)) }} gap-4">
            @foreach($summaryCards as $card)
                <div class="bg-white rounded-2xl shadow-xs border border-gray-100 p-5">
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">{{ $card['label'] }}</p>
                    <p class="text-2xl font-bold mt-1.5 {{ $card['class'] ?? 'text-gray-900' }}">{{ $card['value'] }}</p>
                    @if(!empty($card['helptext']))
                        <p class="text-xs text-gray-400 mt-1">{{ $card['helptext'] }}</p>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    {{-- Main Data Table --}}
    <div class="bg-white rounded-2xl shadow-xs border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-gray-50/80">
                    <tr>
                        @foreach($columns as $col)
                            <th class="px-6 py-3.5 text-xs font-semibold uppercase tracking-wider text-gray-500 {{ $col['align'] ?? 'text-left' }} {{ $col['class'] ?? '' }}">
                                {{ $col['label'] }}
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse($rows as $row)
                        <tr class="hover:bg-gray-50/70 transition">
                            @foreach($columns as $col)
                                @php
                                    $key = $col['key'];
                                    $val = $row[$key] ?? '—';
                                    $align = $col['align'] ?? 'text-left';
                                    $class = $col['cellClass'] ?? 'text-gray-700';
                                @endphp
                                <td class="px-6 py-4 whitespace-nowrap {{ $align }} {{ $class }}">
                                    @if(!empty($col['isHtml']))
                                        {!! $val !!}
                                    @else
                                        {{ $val }}
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($columns) }}" class="px-6 py-12 text-center text-gray-400">
                                <svg class="w-10 h-10 mx-auto text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                No records found for the selected filter criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if(!empty($totals) && is_array($totals))
                    <tfoot class="bg-gray-50/90 font-semibold border-t-2 border-gray-200">
                        <tr>
                            @foreach($columns as $col)
                                @php
                                    $key = $col['key'];
                                    $val = $totals[$key] ?? '';
                                    $align = $col['align'] ?? 'text-left';
                                @endphp
                                <td class="px-6 py-3.5 whitespace-nowrap {{ $align }} text-gray-900 font-bold">
                                    {{ $val }}
                                </td>
                            @endforeach
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>

        @if(isset($paginator) && method_exists($paginator, 'hasPages') && $paginator->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $paginator->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
