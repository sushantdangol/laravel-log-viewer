<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Pagination\LengthAwarePaginator;

class LogViewerController extends Controller
{
    /**
     * The main log viewer page
     */
    public function show(Request $request)
    {
        if ($request->has('file') && session('remote_log_url') !== $request->get('file')) {
            session()->forget(['remote_log_url', 'remote_log_temp_path']);
        }

        $filePath = $request->get('file');
        $page = $request->get('page', 1);
        $perPage = 10;
        $logs = [];
        $paginator = null;
        $filename = null;

        $projects = Project::get();

        if (!$filePath) {
            return view('log-viewer', compact('logs', 'paginator', 'filename', 'projects'));
        }

        if (Storage::exists($filePath)) {
            $result = $this->parseLogStream(Storage::path($filePath), $page, $perPage);
            $filename = basename($filePath);
        }

        elseif (filter_var($filePath, FILTER_VALIDATE_URL)) {
            $cachedPath = session('remote_log_temp_path');

            if ($cachedPath && session('remote_log_url') === $filePath && file_exists($cachedPath)) {
                $tempFile = $cachedPath;
            } else {
                $tempFile = $this->getRemoteLogFile($filePath);

                if (!$tempFile) {
                    return back()->with('error', 'Failed to fetch remote log file.');
                }

                session([
                    'remote_log_url' => $filePath,
                    'remote_log_temp_path' => $tempFile
                ]);
            }

            $result = $this->parseLogStream($tempFile, $page, $perPage);
            $filename = 'Remote: ' . $filePath;
        } else {
            return back()->with('error', 'Invalid log file path.');
        }

        $paginator = new LengthAwarePaginator(
            $result['logs'],
            $result['total'],
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        $logs = $result['logs'];
        return view('log-viewer', compact('logs', 'paginator', 'filename', 'projects'));
    }

    /**
     * Handle uploaded log file
     */
    public function upload(Request $request)
    {
        $request->validate([
            'log_file' => 'required|file|mimes:log,txt|max:51200', // 50MB
        ]);

        $file = $request->file('log_file');
        if (!$file->isValid()) {
            return back()->with('error', 'Invalid file upload.');
        }

        $filename = 'uploaded_' . now()->timestamp . '_' . Str::random(10) . '.log';
        $path = $file->storeAs('logs/uploads', $filename);

        return redirect()->route('log.viewer', ['file' => $path]);
    }

    /**
     * Fetch log file directly from project record
     */
    public function fetchRemote($projectId)
    {
        $project = Project::find($projectId);

        if (!$project || !$project->project_file_path) {
            return back()->with('error', 'Project log file not found.');
        }

        return redirect()->route('log.viewer', ['file' => $project->project_file_path]);
    }

    /**
     * Get remote log file and temporarily store it locally
     */
    private function getRemoteLogFile($url)
    {
        try {
            $tempDir = storage_path('app/logs/temp');
            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0755, true);
            }

            $tempName = 'temp_' . Str::random(10) . '.log';
            $tempFullPath = $tempDir . DIRECTORY_SEPARATOR . $tempName;

            if (!filter_var($url, FILTER_VALIDATE_URL) && file_exists($url)) {
                if (copy($url, $tempFullPath)) {
                    return $tempFullPath;
                }
                return null;
            }

            $context = stream_context_create([
                'http' => [
                    'timeout' => 30,
                    'follow_location' => 1,
                    'ignore_errors' => true,
                ],
                'https' => [
                    'timeout' => 30,
                    'follow_location' => 1,
                    'ignore_errors' => true,
                ],
            ]);

            $read = fopen($url, 'r', false, $context);
            if ($read === false) {
                return null;
            }

            $write = fopen($tempFullPath, 'w');
            if ($write === false) {
                fclose($read);
                return null;
            }

            $copied = stream_copy_to_stream($read, $write);
            fclose($read);
            fclose($write);

            if ($copied === false || $copied === 0) {
                unlink($tempFullPath);
                return null;
            }

            return $tempFullPath;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Parse large log file efficiently (streaming)
     */
    private function parseLogStream($path, $page = 1, $perPage = 10)
    {
        $handle = fopen($path, 'r');
        if (!$handle) {
            return ['logs' => [], 'total' => 0];
        }

        $entries = [];
        $current = null;
        $entryCount = 0;

        while (($line = fgets($handle)) !== false) {
            if (preg_match('/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] (\w+)\.(\w+): (.*)/', $line, $m)) {
                if ($current) {
                    $entries[] = $current;
                    $entryCount++;
                }

                $current = [
                    'datetime' => $m[1],
                    'environment' => $m[2],
                    'level' => $m[3],
                    'message' => $m[4],
                    'trace' => [],
                ];
            } elseif ($current && trim($line) !== '') {
                $current['trace'][] = trim($line);
            }
        }

        if ($current) {
            $entries[] = $current;
            $entryCount++;
        }

        fclose($handle);
        $entries = array_reverse($entries);
        $total = count($entries);

        $offset = ($page - 1) * $perPage;
        $pagedEntries = array_slice($entries, $offset, $perPage);

        return ['logs' => $pagedEntries, 'total' => $total];
    }
}
