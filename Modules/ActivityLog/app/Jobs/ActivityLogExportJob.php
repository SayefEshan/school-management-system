<?php

namespace Modules\ActivityLog\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Modules\ActivityLog\Helpers\ActivityLogHelper;
use Modules\ImportDownloadManager\Service\DownloadImportService;
use Modules\Notification\Services\NotificationService;
use OwenIt\Auditing\Models\Audit;
use Rap2hpoutre\FastExcel\FastExcel;

class ActivityLogExportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $importDownloadManagerId;
    protected $request;
    protected $authUser;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($importDownloadManagerId, $request, $authUser)
    {
        $this->importDownloadManagerId = $importDownloadManagerId;
        $this->request = $request;
        $this->authUser = $authUser;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', 0);
        set_time_limit(0);

        try {
            DownloadImportService::update($this->importDownloadManagerId, 'Processing');

            $request = new Request();
            $request = $request->merge($this->request);

            $query = Audit::query();

            // Apply filters
            if ($request->has('search')) {
                $search = $request->get('search');
                $query->where(function ($q) use ($search) {
                    $q->whereRaw('old_values LIKE ?', ["%{$search}%"])
                        ->orWhereRaw('new_values LIKE ?', ["%{$search}%"])
                        ->orWhereRaw('user_agent LIKE ?', ["%{$search}%"])
                        ->orWhereRaw('ip_address LIKE ?', ["%{$search}%"])
                        ->orWhereRaw('url LIKE ?', ["%{$search}%"]);
                });
            }

            if ($request->has('date_from')) {
                $query->whereDate('created_at', '>=', $request->get('date_from'));
            }

            if ($request->has('date_to')) {
                $query->whereDate('created_at', '<=', $request->get('date_to'));
            }

            if ($request->has('event') && !empty($request->get('event'))) {
                $query->where('event', $request->get('event'));
            }

            if ($request->has('auditable_type') && !empty($request->get('auditable_type'))) {
                $query->where('auditable_type', $request->get('auditable_type'));
            }

            if ($request->has('user_id') && !empty($request->get('user_id'))) {
                $query->where('user_id', $request->get('user_id'));
            }

            // Order by latest first
            $query->orderBy('created_at', 'desc');
            $audits = $query->get();

            $data = collect($audits);
            $exportData = [];
            $sl = 1;

            $data->map(function ($audit) use (&$exportData, &$sl) {
                $metaData = $audit->getMetadata();
                $modifiedData = $audit->getModified();
                $model = ActivityLogHelper::getModelName($audit->auditable_type);

                // Format changes for better readability
                $changes = [];
                foreach ($modifiedData as $key => $value) {
                    if (is_array($value)) {
                        if (isset($value['old']) && isset($value['new'])) {
                            $oldValue = is_array($value['old']) ? json_encode($value['old']) : $value['old'];
                            $newValue = is_array($value['new']) ? json_encode($value['new']) : $value['new'];
                            $changes[] = ActivityLogHelper::titleCase($key) . ': ' . $oldValue . ' → ' . $newValue;
                        } else {
                            $changes[] = ActivityLogHelper::titleCase($key) . ': ' . json_encode($value);
                        }
                    } else {
                        $changes[] = ActivityLogHelper::titleCase($key) . ': ' . $value;
                    }
                }

                $exportData[] = [
                    'SL' => $sl++,
                    'ID' => $audit->id,
                    'Event' => ucfirst($audit->event),
                    'Entity Type' => $model,
                    'Entity ID' => $audit->auditable_id,
                    'Changes' => implode(", ", $changes),
                    'IP Address' => $metaData['audit_ip_address'] ?? 'N/A',
                    'URL' => $metaData['audit_url'] ?? 'N/A',
                    'User Agent' => $metaData['audit_user_agent'] ?? 'N/A',
                    'User' => $metaData['user_id'] ?
                        "{$metaData['user_first_name']} {$metaData['user_last_name']} (ID: {$metaData['user_id']})" :
                        'System',
                    'Date' => $audit->created_at->format('Y-m-d H:i:s'),
                ];
            });

            if ($exportData) {
                Storage::makeDirectory('public/exports');
                $filePath = "exports/activity_logs_" . time() . ".xlsx";
                $fullPath = storage_path('app/public/' . $filePath);
                (new FastExcel($exportData))->export($fullPath);
                DownloadImportService::update($this->importDownloadManagerId, 'Completed', "completed", $filePath);

                // Send notification to the user who requested the export
                if ($this->authUser) {
                    NotificationService::send(
                        $this->authUser,
                        'Activity Log Export Complete',
                        'Your activity log export is ready for download.',
                        [
                            'download_id' => $this->importDownloadManagerId,
                            'file_path' => $filePath,
                            'type' => 'export_complete'
                        ],
                        'export'
                    );
                }
            } else {
                DownloadImportService::update($this->importDownloadManagerId, 'Failed', "No Data Found For Export");

                // Notify user of empty export
                if ($this->authUser) {
                    NotificationService::send(
                        $this->authUser,
                        'Activity Log Export Failed',
                        'No data found for export with your selected filters.',
                        [
                            'download_id' => $this->importDownloadManagerId,
                            'type' => 'export_failed'
                        ],
                        'error'
                    );
                }
            }
        } catch (Exception $e) {
            DownloadImportService::update($this->importDownloadManagerId, 'Failed', $e->getMessage());
            Log::error('Activity log export error: ' . $e->getMessage());

            // Notify user of export error
            if ($this->authUser) {
                NotificationService::send(
                    $this->authUser,
                    'Activity Log Export Failed',
                    'There was an error processing your export: ' . $e->getMessage(),
                    [
                        'download_id' => $this->importDownloadManagerId,
                        'type' => 'export_error'
                    ],
                    'error'
                );
            }
        }
    }

    /**
     * Handle job failure.
     *
     * @param Exception $exception
     * @return void
     */
    public function failed(Exception $exception)
    {
        DownloadImportService::update($this->importDownloadManagerId, 'Failed', $exception->getMessage());
        Log::error('Activity log export failed: ' . $exception->getMessage());

        // Notify user of export failure
        if ($this->authUser) {
            NotificationService::send(
                $this->authUser,
                'Activity Log Export Failed',
                'Your export job failed: ' . $exception->getMessage(),
                [
                    'download_id' => $this->importDownloadManagerId,
                    'type' => 'export_failed'
                ],
                'error'
            );
        }
    }
}
