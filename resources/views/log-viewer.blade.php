<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laravel Log Viewer</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .sidebar {
            transition: all 0.3s ease;
        }
        .folder-item, .file-item {
            transition: all 0.2s ease;
        }
        .folder-item:hover, .file-item:hover {
            background-color: #f3f4f6;
        }
        .folder-children {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
        }
        .folder-children.open {
            max-height: 1000px;
        }
        .rotate-0 {
            transform: rotate(0deg);
        }
        .rotate-90 {
            transform: rotate(90deg);
        }

        /* Dark mode styles */
        .dark-mode {
            background-color: #1f2937;
            color: #f9fafb;
        }
        .dark-mode .sidebar {
            background-color: #374151;
            border-color: #4b5563;
        }
        .dark-mode .folder-item:hover,
        .dark-mode .file-item:hover {
            background-color: #4b5563;
        }
        .dark-mode .bg-white {
            background-color: #374151;
        }
        .dark-mode .text-gray-800 {
            color: #f9fafb;
        }
        .dark-mode .text-gray-700 {
            color: #e5e7eb;
        }
        .dark-mode .text-gray-600 {
            color: #d1d5db;
        }
        .dark-mode .text-gray-500 {
            color: #9ca3af;
        }
        .dark-mode .border-gray-200 {
            border-color: #4b5563;
        }
        .dark-mode .bg-gray-100 {
            background-color: #4b5563;
        }
        .dark-mode .bg-gray-50 {
            background-color: #374151;
        }
    </style>
</head>
<body class="bg-gray-100">

<div class="flex h-screen">
    <!-- Sidebar -->
    <div class="sidebar w-64 bg-white border-r border-gray-200 flex-shrink-0 overflow-y-auto">
        <div class="p-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800">Log Files</h2>
            <p class="text-sm text-gray-500 mt-1">Browse laravel log files</p>
        </div>

        <div class="p-4">
            <!-- Add New Project Button -->
            <div class="mb-4">
                <button type="button" onclick="openProjectModal()"
                        class="w-full px-4 py-2 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold rounded-lg shadow-md hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200 transform hover:scale-105">
                    <i class="fas fa-plus-circle mr-2"></i>
                    Add New Project
                </button>
            </div>

            <!-- Folder Tree -->
            <div class="space-y-1">
                <!-- Projects Section -->
                @if(isset($projects) && count($projects) > 0)
                    <div class="folder-item">
                        <div class="flex items-center justify-between py-2 px-3 rounded-md cursor-pointer hover:bg-gray-100"
                             onclick="toggleFolder('projects-folder')">
                            <div class="flex items-center">
                                <i id="projects-folder-icon" class="fas fa-chevron-right text-gray-400 text-xs mr-2 rotate-0 transition-transform"></i>
                                <i class="fas fa-project-diagram text-purple-500 mr-2"></i>
                                <span class="text-sm font-medium text-gray-700">Remote Logs</span>
                            </div>
                            <span class="text-xs text-gray-400 bg-gray-200 px-2 py-1 rounded-full">{{ count($projects) }}</span>
                        </div>
                        <div id="projects-folder" class="folder-children ml-4">
                            @foreach($projects as $project)
                                <div class="file-item py-2 px-3 rounded-md cursor-pointer hover:bg-gray-100 flex items-center justify-between group">
                                    <a href="{{ route('log.fetch', $project->project_id) }}" class="flex-1">
                                        <div class="flex items-center">
                                            <i class="fas fa-file-alt text-blue-400 mr-2"></i>
                                            <span class="text-sm text-gray-600">{{ $project->project_name }}</span>
                                        </div>
                                    </a>
                                    <button onclick="event.stopPropagation(); deleteProject({{ $project->id }})"
                                            class="opacity-0 group-hover:opacity-100 text-red-400 hover:text-red-600 transition-opacity">
                                        <i class="fas fa-trash text-xs"></i>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Refresh Section -->
        <div class="p-4 border-t border-gray-200 bg-white">
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-500">
                    Total: {{ isset($projects) ? count($projects) : 0 }} files
                </span>
                <button id="clear-session-btn" class="text-xs text-blue-600 hover:text-blue-800">
                    <i class="fas fa-sync-alt mr-1"></i> Refresh
                </button>
            </div>
        </div>

        <!-- Settings Section -->
        <div class="p-4 border-t border-gray-200 bg-white">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Settings</h3>

            <!-- Dark Mode Toggle -->
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas fa-moon text-gray-400 mr-3"></i>
                    <span class="text-sm text-gray-600">Dark Mode</span>
                </div>
                <button id="dark-mode-toggle"
                        class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent bg-gray-200 transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                        role="switch"
                        aria-checked="false">
                    <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out translate-x-0"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="flex-1 overflow-y-auto">
        <div class="container mx-auto px-4 py-8">
            <div class="bg-white rounded-lg shadow-md p-6 mb-8">
                <h1 class="text-2xl font-bold text-gray-800 mb-6">Laravel Log Viewer</h1>

                <!-- Upload Log Form -->
                <form action="{{ route('log.upload') }}" method="POST" enctype="multipart/form-data" class="mb-6">
                    @csrf
                    <div class="flex items-center space-x-4">
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Upload Log File</label>
                            <div class="flex items-center">
                                <input type="file" name="log_file" id="log_file" accept=".log,.txt"
                                       class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4
                               file:rounded-md file:border-0 file:text-sm file:font-semibold
                               file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                <button type="submit"
                                        class="ml-4 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700
                                focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                    Upload
                                </button>
                            </div>
                        </div>
                    </div>
                </form>

                <!-- Currently viewed file info -->
                @if(isset($filename))
                    <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mt-6">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-info-circle text-blue-400"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-blue-700">
                                    Viewing log file: <span class="font-medium">{{ $filename }}</span>
                                </p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Log Entries Table -->
            <div id="tbl_log_entries">
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
                                                {{ in_array(strtolower($log['level']), ['error', 'emergency', 'critical', 'alert']) ? 'bg-red-100 text-red-800' :
                                                   (in_array(strtolower($log['level']), ['warning', 'notice']) ? 'bg-yellow-100 text-yellow-800' :
                                                   (in_array(strtolower($log['level']), ['info', 'debug']) ? 'bg-blue-100 text-blue-800' :
                                                   'bg-gray-100 text-gray-800')) }}">
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
                                                    $showPages = 7;

                                                    $start = max(1, $current - floor($showPages / 2));
                                                    $end = min($last, $start + $showPages - 1);
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
            </div>
        </div>
    </div>
</div>


<!-- Project Modal -->
<div id="projectModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <!-- Modal Header -->
            <div class="flex items-center justify-between pb-3 border-b">
                <h3 class="text-xl font-semibold text-gray-800">Add New Project</h3>
                <button onclick="closeProjectModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <!-- Modal Form -->
            <form id="projectForm" action="{{ route('projects.store') }}" method="POST" class="mt-4 space-y-4">
                @csrf

                <!-- Project Name Field -->
                <div>
                    <label for="project_name" class="block text-sm font-medium text-gray-700 mb-1">
                        Project Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           name="project_name"
                           id="project_name"
                           required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150"
                           placeholder="Enter project name">
                    <p class="text-xs text-gray-500 mt-1">A descriptive name for your project</p>
                </div>

                <!-- Project File Path Field -->
                <div>
                    <label for="project_file_path" class="block text-sm font-medium text-gray-700 mb-1">
                        Project URL <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           name="project_file_path"
                           id="project_file_path"
                           required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150"
                           placeholder="https://example.com/logs/laravel.log">
                    <p class="text-xs text-gray-500 mt-1">The URL where your Laravel log file is hosted</p>
                </div>

                <!-- Modal Actions -->
                <div class="flex justify-end space-x-3 pt-4 border-t">
                    <button type="button"
                            onclick="closeProjectModal()"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 transition duration-150">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-4 py-2 text-sm font-medium text-white bg-gradient-to-r from-blue-600 to-blue-700 rounded-md hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-150">
                        <i class="fas fa-save mr-1"></i>
                        Save Project
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Dark Mode Toggle
    const darkModeToggle = document.getElementById('dark-mode-toggle');
    const isDarkMode = localStorage.getItem('darkMode') === 'true';

    // Apply dark mode on page load
    if (isDarkMode) {
        document.body.classList.add('dark-mode');
        darkModeToggle.setAttribute('aria-checked', 'true');
        darkModeToggle.classList.remove('bg-gray-200');
        darkModeToggle.classList.add('bg-blue-600');
        darkModeToggle.querySelector('span').classList.remove('translate-x-0');
        darkModeToggle.querySelector('span').classList.add('translate-x-5');
    }

    darkModeToggle.addEventListener('click', function() {
        const isDark = document.body.classList.toggle('dark-mode');

        // Update toggle state
        this.setAttribute('aria-checked', isDark.toString());
        this.classList.toggle('bg-gray-200', !isDark);
        this.classList.toggle('bg-blue-600', isDark);
        this.querySelector('span').classList.toggle('translate-x-0', !isDark);
        this.querySelector('span').classList.toggle('translate-x-5', isDark);

        // Save preference to localStorage
        localStorage.setItem('darkMode', isDark.toString());
    });

    function toggleTrace(index) {
        const traceElement = document.getElementById(`trace-${index}`);
        traceElement.classList.toggle('hidden');
    }

    function toggleFolder(folderId) {
        const folder = document.getElementById(folderId);
        const icon = document.getElementById(folderId + '-icon');
        folder.classList.toggle('open');
        icon.classList.toggle('rotate-0');
        icon.classList.toggle('rotate-90');
    }

    function openProjectModal() {
        document.getElementById('projectModal').classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }

    function closeProjectModal() {
        document.getElementById('projectModal').classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
        document.getElementById('projectForm').reset();
    }

    function deleteProject(projectId) {
        if (confirm('Are you sure you want to delete this project?')) {
            fetch(`/projects/${projectId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                },
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error deleting project: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error deleting project');
                });
        }
    }

    // Close modal when clicking outside
    document.getElementById('projectModal').addEventListener('click', function(e) {
        if (e.target.id === 'projectModal') {
            closeProjectModal();
        }
    });

    // Close modal with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeProjectModal();
        }
    });

    // Initialize with projects folder open
    document.addEventListener('DOMContentLoaded', function() {
        @if(isset($projects) && count($projects) > 0)
        toggleFolder('projects-folder');
        @endif
    });

    // Refresh button
    document.getElementById('clear-session-btn').addEventListener('click', async function() {
        try {
            const response = await fetch('{{ route('session.clear') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
            });
            if (response.ok) {
                location.reload();
            }
        } catch (error) {
            console.error('Failed to clear session:', error);
        }
    });
</script>

</body>
</html>
