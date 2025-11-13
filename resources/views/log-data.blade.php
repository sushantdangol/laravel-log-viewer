@if(isset($logs) && count($logs) > 0)
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="bg-gray-50 px-6 py-3 border-b border-gray-200">
            <p class="text-sm text-gray-700">
                Showing
                <span class="font-medium">{{ ($paginator->currentPage() - 1) * $paginator->perPage() + 1 }}</span>
                to
                <span class="font-medium">{{ min($paginator->currentPage() * $paginator->perPage(), $paginator->total()) }}</span>
                of
                <span class="font-medium">{{ $paginator->total() }}</span>
                results
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date/Time</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Level</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Environment</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Message</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                @foreach($logs as $index => $log)
                    @php
                        $globalIndex = (($paginator->currentPage() - 1) * $paginator->perPage()) + $index;
                    @endphp
                    <tr class="{{ $log['level'] === 'error' ? 'bg-red-50' : ($log['level'] === 'warning' ? 'bg-yellow-50' : 'bg-white') }}">
                        <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">{{ $log['datetime'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs font-semibold rounded-full
                                            {{ $log['level'] === 'error' ? 'bg-red-100 text-red-800' :
                                               ($log['level'] === 'warning' ? 'bg-yellow-100 text-yellow-800' :
                                               ($log['level'] === 'info' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800')) }}">
                                            {{ strtoupper($log['level']) }}
                                        </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">{{ $log['environment'] }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            <div class="max-w-2xl">
                                <div class="break-words font-sans text-sm leading-relaxed">
                                    {{ trim($log['message']) }}
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-right text-sm whitespace-nowrap">
                            @if(!empty(array_filter($log['trace'])))
                                <button onclick="toggleTrace({{ $globalIndex }})"
                                        class="text-blue-600 hover:text-blue-900 mr-3 focus:outline-none">
                                    <i class="fas fa-eye mr-1"></i> Trace
                                </button>
                            @endif
                        </td>
                    </tr>

                    <!-- Trace Details Row (hidden by default) -->
                    @if(!empty(array_filter($log['trace'])))
                        <tr id="trace-{{ $globalIndex }}" class="hidden bg-gray-50">
                            <td colspan="5" class="px-6 py-4">
                                <div class="space-y-4">
                                    <!-- Stack Trace Section -->
                                    <div>
                                        <div class="flex justify-between items-center mb-3">
                                            <span class="text-sm font-medium text-gray-700">Stack Trace</span>
                                            <button onclick="toggleTrace({{ $globalIndex }})"
                                                    class="text-gray-500 hover:text-gray-700 focus:outline-none">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                        <div class="bg-gray-100 border border-gray-300 rounded-lg p-4 font-mono text-sm overflow-x-auto max-h-96 overflow-y-auto">
                                            @foreach($log['trace'] as $traceLine)
                                                @if(trim($traceLine))
                                                    <div class="py-1 text-gray-700 hover:bg-gray-200 px-2 rounded">
                                                        {{ trim($traceLine) }}
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endif
                @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($paginator->hasPages())
            <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                <div class="flex items-center justify-between">
                    <div class="flex-1 flex justify-between sm:hidden">
                        @if($paginator->onFirstPage())
                            <span class="px-4 py-2 border text-sm text-gray-300 bg-white cursor-not-allowed">Previous</span>
                        @else
                            <a href="{{ $paginator->previousPageUrl() }}" class="px-4 py-2 border text-sm text-gray-700 bg-white hover:bg-gray-50">Previous</a>
                        @endif

                        @if($paginator->hasMorePages())
                            <a href="{{ $paginator->nextPageUrl() }}" class="ml-3 px-4 py-2 border text-sm text-gray-700 bg-white hover:bg-gray-50">Next</a>
                        @else
                            <span class="ml-3 px-4 py-2 border text-sm text-gray-300 bg-white cursor-not-allowed">Next</span>
                        @endif
                    </div>

                    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm text-gray-700">
                                Showing
                                <span class="font-medium">{{ ($paginator->currentPage() - 1) * $paginator->perPage() + 1 }}</span>
                                to
                                <span class="font-medium">{{ min($paginator->currentPage() * $paginator->perPage(), $paginator->total()) }}</span>
                                of
                                <span class="font-medium">{{ $paginator->total() }}</span>
                                results
                            </p>
                        </div>
                        <div>
                            <nav class="inline-flex rounded-md shadow-sm -space-x-px">
                                <!-- Previous Page Link -->
                                @if($paginator->onFirstPage())
                                    <span class="px-2 py-2 border border-gray-300 rounded-l-md text-gray-300 bg-white cursor-not-allowed">
                                <i class="fas fa-chevron-left w-5 h-5"></i>
                            </span>
                                @else
                                    <a href="{{ $paginator->previousPageUrl() }}" class="px-2 py-2 border border-gray-300 rounded-l-md text-gray-500 bg-white hover:bg-gray-50">
                                        <i class="fas fa-chevron-left w-5 h-5"></i>
                                    </a>
                                @endif

                                <!-- Page Number Links with Ellipsis -->
                                @php
                                    $current = $paginator->currentPage();
                                    $last = $paginator->lastPage();
                                    $showPages = 7; // Number of page links to show (including ellipsis)

                                    $start = max(1, $current - floor($showPages / 2));
                                    $end = min($last, $start + $showPages - 1);

                                    // Adjust start if we're near the end
                                    $start = max(1, $end - $showPages + 1);

                                    $showStartEllipsis = $start > 1;
                                    $showEndEllipsis = $end < $last;
                                @endphp

                                    <!-- First Page -->
                                @if($showStartEllipsis)
                                    <a href="{{ $paginator->url(1) }}" class="px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">1</a>
                                    @if($start > 2)
                                        <span class="px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-500">...</span>
                                    @endif
                                @endif

                                <!-- Page Numbers -->
                                @foreach(range($start, $end) as $i)
                                    @if($i == $paginator->currentPage())
                                        <span class="px-4 py-2 border border-gray-300 bg-blue-50 text-sm font-medium text-blue-600 border-blue-500 z-10">{{ $i }}</span>
                                    @else
                                        <a href="{{ $paginator->url($i) }}" class="px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">{{ $i }}</a>
                                    @endif
                                @endforeach

                                <!-- Last Page -->
                                @if($showEndEllipsis)
                                    @if($end < $last - 1)
                                        <span class="px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-500">...</span>
                                    @endif
                                    <a href="{{ $paginator->url($last) }}" class="px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">{{ $last }}</a>
                                @endif

                                <!-- Next Page Link -->
                                @if($paginator->hasMorePages())
                                    <a href="{{ $paginator->nextPageUrl() }}" class="px-2 py-2 border border-gray-300 rounded-r-md text-gray-500 bg-white hover:bg-gray-50">
                                        <i class="fas fa-chevron-right w-5 h-5"></i>
                                    </a>
                                @else
                                    <span class="px-2 py-2 border border-gray-300 rounded-r-md text-gray-300 bg-white cursor-not-allowed">
                                <i class="fas fa-chevron-right w-5 h-5"></i>
                            </span>
                                @endif
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
@elseif(request()->isMethod('post'))
    <div class="bg-white rounded-lg shadow-md p-6 text-center">
        <i class="fas fa-file-alt text-gray-400 text-5xl mb-4"></i>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No log entries found</h3>
        <p class="text-gray-500">The uploaded log file doesn't contain any parsable entries or is empty.</p>
    </div>
@else
    <div class="bg-white rounded-lg shadow-md p-6 text-center">
        <i class="fas fa-upload text-gray-400 text-5xl mb-4"></i>
        <h3 class="text-lg font-medium text-gray-900 mb-2">Upload or Select a Log File</h3>
        <p class="text-gray-500">Choose a .log file from your Laravel app or upload a new one to view its content.</p>
    </div>
@endif
