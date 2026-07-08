<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\VisitorSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        // Issue #3: Access control — only users with 'settings' permission
        if (!auth()->user()->hasPermission('settings')) {
            abort(403);
        }

        $tab = $request->query('tab', 'admin');
        $allowedTabs = ['admin', 'system', 'customer'];
        if (!in_array($tab, $allowedTabs)) {
            $tab = 'admin';
        }

        $data = [];

        if ($tab === 'admin') {
            $data = $this->getAdminActivityData($request);
        } elseif ($tab === 'system') {
            $data = $this->getSystemLogData($request);
        } elseif ($tab === 'customer') {
            $data = $this->getCustomerActivityData($request);
        }

        return view('activity-log.index', compact('tab', 'data'));
    }

    /**
     * Tab 1: Admin Activity — query activity_logs with filters.
     */
    private function getAdminActivityData(Request $request): array
    {
        $query = ActivityLog::with('user')->latest();

        // Filter by action
        if ($action = $request->query('action')) {
            $query->where('action', $action);
        }

        // Filter by model type
        if ($model = $request->query('model')) {
            $modelClass = 'App\\Models\\' . $model;
            $query->where('model_type', $modelClass);
        }

        // Filter by user — PHASE-4.5: "system" shows automated actions
        // (webhooks, customer approvals, scheduled jobs run with no user)
        if ($userId = $request->query('user_id')) {
            if ($userId === 'system') {
                $query->whereNull('user_id');
            } else {
                $query->where('user_id', $userId);
            }
        }

        // Filter by date range
        if ($startDate = $request->query('start_date')) {
            $query->where('created_at', '>=', $startDate . ' 00:00:00');
        }
        if ($endDate = $request->query('end_date')) {
            $query->where('created_at', '<=', $endDate . ' 23:59:59');
        }

        // Search by model ID
        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('model_id', $search)
                  ->orWhere('action', 'like', "%{$search}%");
            });
        }

        $logs = $query->paginate(50)->appends($request->query());

        // Get unique values for filter dropdowns
        $actions = ActivityLog::select('action')->distinct()->orderBy('action')->pluck('action');
        $modelTypes = ActivityLog::select('model_type')->whereNotNull('model_type')->distinct()->pluck('model_type')
            ->map(fn($m) => class_basename($m))->unique()->sort()->values();
        $users = \App\Models\User::select('id', 'name')->orderBy('name')->get();

        return compact('logs', 'actions', 'modelTypes', 'users');
    }

    /**
     * Tab 2: System Logs — parse Laravel log files.
     */
    private function getSystemLogData(Request $request): array
    {
        $logPath = storage_path('logs');
        $logFiles = collect(File::files($logPath))
            ->filter(fn($f) => $f->getExtension() === 'log')
            ->sortByDesc(fn($f) => $f->getMTime())
            ->values();

        $selectedFile = $request->query('log_file', $logFiles->first()?->getFilename() ?? 'laravel.log');

        // Security: prevent path traversal
        $selectedFile = basename($selectedFile);
        $fullPath = $logPath . '/' . $selectedFile;

        $entries = [];
        $fileSize = 0;
        $totalLines = 0;

        // Issue #6: Level filter
        $levelFilter = strtoupper($request->query('level', ''));

        if (File::exists($fullPath)) {
            $fileSize = File::size($fullPath);

            // Read last portion of file (max 500KB to prevent memory issues)
            $maxBytes = 512 * 1024;
            $content = '';

            if ($fileSize > $maxBytes) {
                $handle = fopen($fullPath, 'r');
                fseek($handle, -$maxBytes, SEEK_END);
                fgets($handle); // skip partial line
                $content = fread($handle, $maxBytes);
                fclose($handle);
            } else {
                $content = File::get($fullPath);
            }

            $totalLines = substr_count($content, "\n");

            // Parse log entries (Laravel format: [YYYY-MM-DD HH:MM:SS] environment.LEVEL: message)
            $pattern = '/\[(\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}:\d{2})\]\s+\w+\.(\w+):\s+(.*?)(?=\n\[\d{4}|\z)/s';
            preg_match_all($pattern, $content, $matches, PREG_SET_ORDER);

            $entries = collect($matches)->reverse()->map(function ($match) {
                $stackTrace = null;
                $message = trim($match[3]);

                // Separate stack trace from message
                if (str_contains($message, '#0 ') || str_contains($message, 'Stack trace:')) {
                    $parts = preg_split('/(Stack trace:|#0\s)/', $message, 2);
                    $message = trim($parts[0]);
                    $stackTrace = isset($parts[1]) ? trim($parts[1]) : null;
                    if ($stackTrace && !str_starts_with($stackTrace, '#0')) {
                        $stackTrace = '#0 ' . $stackTrace;
                    }
                }

                return [
                    'timestamp' => $match[1],
                    'level' => strtoupper($match[2]),
                    'message' => $message,
                    'stack_trace' => $stackTrace,
                ];
            });

            // Issue #6: Apply level filter after parsing
            if ($levelFilter) {
                $entries = $entries->filter(fn($e) => $e['level'] === $levelFilter);
            }

            $entries = $entries->take(500)->values()->all();
        }

        $logFileList = $logFiles->map(fn($f) => [
            'name' => $f->getFilename(),
            'size' => $this->formatFileSize($f->getSize()),
            'modified' => Carbon::createFromTimestamp($f->getMTime())->format('M d, Y h:i A'),
        ])->all();

        // Issue #11: Pre-format file size in the controller
        $fileSizeFormatted = $this->formatFileSize($fileSize);

        return compact('entries', 'logFileList', 'selectedFile', 'fileSize', 'fileSizeFormatted', 'totalLines', 'levelFilter');
    }

    /**
     * Tab 3: Customer Activity — visitor sessions with events.
     */
    private function getCustomerActivityData(Request $request): array
    {
        // Issue #1: Eager-load events.product to prevent N+1 queries
        $query = VisitorSession::with(['events.product', 'orders'])->latest();

        // Filter: only converted sessions
        if ($request->query('converted') === '1') {
            $query->has('orders');
        }

        // Filter by date range
        if ($startDate = $request->query('start_date')) {
            $query->where('created_at', '>=', $startDate . ' 00:00:00');
        }
        if ($endDate = $request->query('end_date')) {
            $query->where('created_at', '<=', $endDate . ' 23:59:59');
        }

        // Filter by UTM source
        if ($utmSource = $request->query('utm_source')) {
            $query->where('utm_source', $utmSource);
        }

        $sessions = $query->paginate(30)->appends($request->query());

        // Issue #4: Apply same date filters to KPI stats so they match the filtered results
        $statsQuery = VisitorSession::query();
        if ($startDate = $request->query('start_date')) {
            $statsQuery->where('created_at', '>=', $startDate . ' 00:00:00');
        }
        if ($endDate = $request->query('end_date')) {
            $statsQuery->where('created_at', '<=', $endDate . ' 23:59:59');
        }
        if ($utmSource = $request->query('utm_source')) {
            $statsQuery->where('utm_source', $utmSource);
        }

        $totalSessions = (clone $statsQuery)->count();
        $convertedSessions = (clone $statsQuery)->has('orders')->count();
        $conversionRate = $totalSessions > 0 ? round(($convertedSessions / $totalSessions) * 100, 2) : 0;

        // UTM sources for filter
        $utmSources = VisitorSession::select('utm_source')
            ->whereNotNull('utm_source')
            ->distinct()
            ->pluck('utm_source');

        return compact('sessions', 'totalSessions', 'convertedSessions', 'conversionRate', 'utmSources');
    }

    /**
     * Issue #11: Helper to format bytes — used by controller instead of Blade view.
     */
    private function formatFileSize(int $bytes, int $precision = 1): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
