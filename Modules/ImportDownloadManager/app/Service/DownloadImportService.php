<?php

namespace Modules\ImportDownloadManager\Service;

use Exception;
use Modules\ImportDownloadManager\Models\DownloadImportManager;
use RuntimeException;

class DownloadImportService
{
    public static function create($authUser, $title, $type, $url = null)
    {
        $downloadImportManager = new DownloadImportManager();
        $downloadImportManager->user_id = $authUser->id;
        $downloadImportManager->title = $title;
        $downloadImportManager->type = $type;
        $downloadImportManager->url = $url;
        $downloadImportManager->save();
        return $downloadImportManager->id;
    }


    /**
     * @throws Exception
     */
    public static function update($downloadImportId, $status, $remarks = null, $url = null): void
    {
        $downloadImportManager = DownloadImportManager::find($downloadImportId);
        if ($downloadImportManager) {
            $downloadImportManager->remarks = $remarks;
            $downloadImportManager->status = $status;
            if ($url) {
                $downloadImportManager->url = $url;
            }
            $downloadImportManager->save();
        } else {
            throw new RuntimeException('Download Import Manager ID not found');
        }
    }

    public static function deleteFile($importManagerId): void
    {
        $downloadImportManager = DownloadImportManager::find($importManagerId);
        $filepath = public_path($downloadImportManager->url);
        if ($downloadImportManager->type === "Import") {
            $filepath = public_path('storage/' . $downloadImportManager->url);
        }
        if (file_exists($filepath) && is_readable($filepath)) {
            @unlink($filepath);
        }
    }
}
