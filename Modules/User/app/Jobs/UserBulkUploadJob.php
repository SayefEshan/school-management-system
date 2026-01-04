<?php

namespace Modules\User\Jobs;

use App\Models\User;
use App\Services\FileManagerService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\ImportDownloadManager\Service\DownloadImportService;

use Modules\User\Actions\CreateUserAction;
use Modules\User\Data\UserData;
use Rap2hpoutre\FastExcel\FastExcel;
use Spatie\Permission\Models\Role;

class UserBulkUploadJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $importDownloadManagerId;
    protected $file;
    protected $authUser;

    /**
     * Create a new job instance.
     */
    public function __construct($importDownloadManagerId, $file, $authUser)
    {
        $this->importDownloadManagerId = $importDownloadManagerId;
        $this->file = $file;
        $this->authUser = $authUser;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', 0);
        set_time_limit(0);
        try {
            DownloadImportService::update($this->importDownloadManagerId, 'Processing');
            $file = FileManagerService::getFile($this->file, getPath: true);
            $collection = (new FastExcel)->import($file)->toArray();

            $userPhones = User::pluck('phone')->toArray();
            $userEmails = User::pluck('email')->toArray();
            $errors = array();
            $i = 1;
            $rowNo = 1;
            if ($collection) {
                foreach ($collection as $key => $row) {
                    ++$rowNo;
                    $phone = $row['phone'] ?? null;
                    $email = $row['email'] ?? null;

                    if (empty($phone) && empty($email)) {
                        $errors[] = $i++ . ". Either Phone or Email is required at row: $rowNo";
                        continue;
                    }

                    // Validate/Format Phone
                    if (!empty($phone)) {
                        if (strlen($phone) === 10) {
                            $phone = "880" . $phone;
                        }
                        if (strlen($phone) === 11) {
                            $phone = "88" . $phone;
                        }
                        if (strlen($phone) !== 13) {
                            $errors[] = $i++ . ". Mobile No: $phone is invalid at row: $rowNo";
                            continue;
                        }
                        if (in_array($phone, $userPhones, true)) {
                            $errors[] = $i++ . ". Mobile No: $phone already exists at row: $rowNo";
                            continue;
                        }
                    }

                    // Validate Email
                    if (!empty($email)) {
                        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                             $errors[] = $i++ . ". Email: $email is invalid at row: $rowNo";
                             continue;
                        }
                        if (in_array($email, $userEmails, true)) {
                            $errors[] = $i++ . ". Email: $email already exists at row: $rowNo";
                            continue;
                        }
                    }

                    if (!$row['role']) {
                        $errors[] = $i++ . ". Role is required at row: $rowNo";
                        continue;
                    }
                    $item = [
                        'first_name' => $row['name'] ?? "User", // Mapping 'name' to 'first_name' (UserData expects first_name/last_name)
                        'last_name' => "",
                        'email' => $email,
                        'phone' => $phone,
                        'password' => $row['password'] ?? null,
                        'password_confirmation' => $row['password'] ?? null,
                        'is_active' => 1,
                        'gender' => $row['gender'] ?? 'male',
                        'role' => Role::whereIn('name', explode(',', $row['role']))->pluck('id')->toArray(),
                    ];

                    if (empty($item['role'])) {
                        $errors[] = $i++ . ". Role is invalid at row: $rowNo";
                        continue;
                    }

                    try {
                        $userData = UserData::from($item);
                        app(CreateUserAction::class)->execute($userData);
                    } catch (Exception $e) {
                        $errors[] = $i++ . ". " . $e->getMessage() . " at row: $rowNo";
                    }
                }

                $remarks = count($errors) > 0 ? implode("<br/>", $errors) : "Completed successfully";

                DownloadImportService::update($this->importDownloadManagerId, 'Completed', $remarks);
            } else {
                DownloadImportService::update($this->importDownloadManagerId, 'Failed', 'No data are imported.<br> ' . $this->file);
            }
        } catch (Exception $e) {
            DB::rollBack();
            Log::error($e);
            try {
                DownloadImportService::update($this->importDownloadManagerId, 'Failed', $e->getMessage());
            } catch (Exception $e) {
                Log::error($e);
            }
        }
    }
}
