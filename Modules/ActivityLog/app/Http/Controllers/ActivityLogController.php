<?php

namespace Modules\ActivityLog\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use OwenIt\Auditing\Models\Audit;
use Illuminate\Support\Facades\Log;
use Modules\ActivityLog\Helpers\ActivityLogHelper;
use Modules\ImportDownloadManager\Service\DownloadImportService;

class ActivityLogController extends Controller
{
    public function __construct()
    {
        $this->middleware(['can:View Activity Log'])->only(['index', 'show']);
        $this->middleware(['can:Delete Activity Log'])->only(['destroy']);
        $this->middleware(['can:Export Activity Log'])->only(['export']);
    }

    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(Request $request)
    {
        $query = Audit::query();

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->whereRaw('old_values LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('new_values LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('user_agent LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('ip_address LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('url LIKE ?', ["%{$search}%"]);
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->get('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->get('date_to'));
        }

        if ($request->filled('event')) {
            $query->where('event', $request->get('event'));
        }

        if ($request->filled('auditable_type')) {
            $query->where('auditable_type', $request->get('auditable_type'));
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->get('user_id'));
        }

        // Get event types for filter dropdown
        $eventTypes = ActivityLogHelper::getEventTypes();

        // Get all unique auditable types for filter dropdown
        $auditableTypes = Audit::select('auditable_type')
            ->distinct()
            ->whereNotNull('auditable_type')
            ->orderBy('auditable_type')
            ->pluck('auditable_type')
            ->toArray();

        // Order by latest first and paginate
        $perPage = $request->get('per_page', 15);
        $audits = $query->with('user')->orderBy('id', 'desc')->paginate($perPage)->withQueryString();

        return view('activitylog::index', compact('audits', 'eventTypes', 'auditableTypes'));
    }

    public function show($id)
    {
        $audit = Audit::findOrFail($id);
        $audits = Audit::where('auditable_id', $audit->auditable_id)
            ->where('auditable_type', $audit->auditable_type)
            ->latest()
            ->get();
        return view('activitylog::show', compact('audits'));
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        try {
            $audit = Audit::findOrFail($id);
            $audit->delete();

            return redirect()->route('activity-logs.index')
                ->with('success', 'Activity log deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to delete activity log: ' . $e->getMessage());
            return redirect()->route('activity-logs.index')
                ->with('error', 'Failed to delete activity log.');
        }
    }

    /**
     * Track IP information
     * @param Request $request
     * @return mixed
     */
    public function trackIpInfo(Request $request)
    {
        $ip = $request->get('ip');

        if (empty($ip)) {
            return '<div class="alert alert-danger">No IP address provided.</div>';
        }

        // For local IPs, return basic info
        if (
            in_array($ip, ['127.0.0.1', 'localhost', '::1']) ||
            substr($ip, 0, 3) === '10.' ||
            substr($ip, 0, 8) === '192.168.'
        ) {

            return view('activitylog::partials.ip_info', [
                'ip' => $ip,
                'location' => 'Local Network',
                'country' => 'Local',
                'region' => 'Local',
                'city' => 'Local',
                'latitude' => null,
                'longitude' => null,
                'isp' => 'Local ISP',
                'timezone' => config('app.timezone'),
            ]);
        }

        try {
            // Using ipinfo.io API (free tier allows 50,000 requests per month)
            $response = file_get_contents("https://ipinfo.io/{$ip}/json");
            $data = json_decode($response, true);

            if (!empty($data) && !isset($data['error'])) {
                $location = $data['loc'] ?? '';
                list($latitude, $longitude) = !empty($location) ? explode(',', $location) : [null, null];

                return view('activitylog::partials.ip_info', [
                    'ip' => $ip,
                    'location' => $location,
                    'country' => $data['country'] ?? 'Unknown',
                    'region' => $data['region'] ?? 'Unknown',
                    'city' => $data['city'] ?? 'Unknown',
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'isp' => $data['org'] ?? 'Unknown',
                    'timezone' => $data['timezone'] ?? 'Unknown',
                ]);
            }
        } catch (\Exception $e) {
            // Log the error but don't expose details to user
            Log::error('IP tracking error: ' . $e->getMessage());
        }

        return '<div class="alert alert-warning">Could not retrieve information for IP: ' . e($ip) . '</div>';
    }


    /**
     * Export activity logs
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|null
     */
    public function export(Request $request): ?\Illuminate\Http\RedirectResponse
    {
        try {
            $importManagerId = DownloadImportService::create(request()->user(), 'Activity Logs Export', 'Download');
            \Modules\ActivityLog\Jobs\ActivityLogExportJob::dispatch($importManagerId, $request->all(), request()->user());
            return redirect()->route("download.import.manager.index")->with('success', "Report sent to process");
        } catch (\Exception $e) {
            Log::error($e);
            return redirect()->back()->withErrors("Couldn't send to process");
        }
    }
}
