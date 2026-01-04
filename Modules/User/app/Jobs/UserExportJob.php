<?php

namespace Modules\User\Jobs;

use App\Models\User;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Modules\ImportDownloadManager\Service\DownloadImportService;
use Rap2hpoutre\FastExcel\FastExcel;
use Illuminate\Http\Request;

class UserExportJob implements ShouldQueue
{

    use Dispatchable,
        InteractsWithQueue,
        Queueable,
        SerializesModels;

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

            $users = User::with('roles');
            $users->filter($request->all());
            $users->orderBy('id', 'desc');
            $users = $users->get();

            $data = collect($users);
            $exportData = [];
            $sl = 1;
            $data->map(function ($item) use (&$exportData, &$sl) {
                $exportData[] = [
                    'SL' => $sl++,
                    'Name' => ucfirst($item->name),
                    'Email' => $item->email ?: '',
                    'Phone' => $item->phone ?: '',
                    'Gender' => $item->gender ? ucfirst($item->gender) : '',
                    'Role' => $item->roles->pluck('name')->implode(', '),
                    'Account Status' => $item->is_active === false ? 'Inactive' : 'Active',
                    'Registration Date' => date('d-M-Y h:i:s A', strtotime($item->created_at)),
                ];
            });

            if ($exportData) {
                Storage::makeDirectory('public/exports');
                $filePath = "exports/user_list" . '_' . time() . ".xlsx";
                $fullPath = storage_path('app/public/' . $filePath);
                (new FastExcel($exportData))->export($fullPath);
                DownloadImportService::update($this->importDownloadManagerId, 'Completed', "completed", $filePath);
            } else {
                DownloadImportService::update($this->importDownloadManagerId, 'Failed', "No Data Found For Export");
            }
        } catch (Exception $e) {
            DownloadImportService::update($this->importDownloadManagerId, 'Failed', $e->getMessage());
            Log::error($e);
        }
    }

    public function failed(Exception $exception)
    {
        DownloadImportService::update($this->importDownloadManagerId, 'Failed', $exception->getMessage());
        Log::error($exception);
    }
}
