@extends('layouts.app')

@section('title', 'System Health Check')

@section('content')
<div class="space-y-6">

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900"><i class="fas fa-heart-pulse text-red-500 mr-2"></i>System Health Check</h1>
            <p class="text-sm text-gray-500">Checks your accounting ledger and inventory records for anything pending, missing, or out of sync.</p>
        </div>
        <a href="{{ route('system-health.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-medium px-4 py-2 rounded-lg transition">
            <i class="fas fa-rotate mr-2"></i> Re-run Check
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border-l-4 border-green-400 p-4 rounded-lg text-sm text-green-800">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        </div>
    @endif

    <!-- Summary Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <div class="bg-white rounded-lg shadow p-3 text-center">
            <p class="text-xs text-gray-500">Total Issues</p>
            <p class="text-2xl font-bold {{ $summary['total'] > 0 ? 'text-gray-900' : 'text-green-600' }}">{{ $summary['total'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-3 text-center border-l-4 border-red-500">
            <p class="text-xs text-gray-500">Critical</p>
            <p class="text-2xl font-bold text-red-600">{{ $summary['critical'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-3 text-center border-l-4 border-yellow-500">
            <p class="text-xs text-gray-500">Warnings</p>
            <p class="text-2xl font-bold text-yellow-600">{{ $summary['warning'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-3 text-center border-l-4 border-blue-500">
            <p class="text-xs text-gray-500">Auto-fixable</p>
            <p class="text-2xl font-bold text-blue-600">{{ $summary['fixable'] }}</p>
        </div>
    </div>

    @if($summary['total'] === 0)
        <!-- All Clear -->
        <div class="bg-white rounded-lg shadow p-10 text-center">
            <i class="fas fa-heart-circle-check text-6xl text-green-500 mb-4"></i>
            <h2 class="text-xl font-bold text-gray-900">All systems healthy</h2>
            <p class="text-sm text-gray-500 mt-1">Every accounting entry balances, every sale/purchase has posted to the ledger, and cylinder/gas stock all checks out.</p>
        </div>
    @else
        @if($summary['fixable'] > 0)
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="text-sm text-blue-800">
                <i class="fas fa-wrench mr-2"></i>
                <strong>{{ $summary['fixable'] }}</strong> issue(s) below can be reconciled automatically — each fix recomputes a number from its own source records (e.g. a cached balance from the ledger, an issued count from the actual per-customer records) or posts a ledger entry only after confirming it's genuinely still missing. Nothing is guessed.
            </div>
            <form action="{{ route('system-health.reconcile') }}" method="POST"
                  onsubmit="return confirm('Reconcile all auto-fixable issues now? This will recompute cached balances/counts and post any missing ledger entries. This cannot be automatically undone.');">
                @csrf
                <button type="submit" class="whitespace-nowrap bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 rounded-lg transition">
                    <i class="fas fa-check-double mr-2"></i> Reconcile Safe Issues
                </button>
            </form>
        </div>
        @endif

        @foreach(['accounting' => ['title' => '⚖️ Accounting', 'empty' => null], 'inventory' => ['title' => '🛢️ Inventory', 'empty' => null]] as $category => $meta)
            @php $categoryIssues = $issues->where('category', $category); @endphp
            @if($categoryIssues->count() > 0)
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">{{ $meta['title'] }} <span class="text-sm font-normal text-gray-400">({{ $categoryIssues->count() }})</span></h2>
                </div>
                <div class="divide-y divide-gray-100">
                    @foreach($categoryIssues as $issue)
                    <div class="px-6 py-4 flex items-start gap-3">
                        <div class="mt-0.5">
                            @if($issue['severity'] === 'critical')
                                <i class="fas fa-circle-exclamation text-red-500"></i>
                            @else
                                <i class="fas fa-triangle-exclamation text-yellow-500"></i>
                            @endif
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-gray-900">{{ $issue['title'] }}</p>
                            <p class="text-sm text-gray-600 mt-0.5">{{ $issue['description'] }}</p>
                        </div>
                        <div>
                            @if($issue['fixable'])
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    <i class="fas fa-wrench mr-1"></i> Auto-fixable
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                    Needs manual review
                                </span>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        @endforeach
    @endif

</div>
@endsection
