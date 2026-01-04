<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class FileManagerService
{
    private static function isUrl($url): bool
    {
        return filter_var($url, FILTER_VALIDATE_URL);
    }

    private static function getStoragePath($url, $disk = 'public'): string
    {
        return str_replace(Storage::disk($disk)->url(''), '', $url);
    }

    /**
     * Upload an image to local storage and return the file path.
     *
     * @param $file
     * @param string|null $existing_file
     * @param string $directory
     * @param string $disk
     * @param bool $isBase64
     * @return string|null
     */
    public static function uploadFile($file, ?string $existing_file = null, string $directory = 'files', string $disk = 'public', bool $isBase64 = false): ?string
    {
        if (!$file) {
            return null;
        }
        if (self::isUrl($file)) {
            return self::getStoragePath($file, $disk);
        }
        if ($existing_file) {
            self::deleteFile($existing_file, $disk);
        }
        if ($isBase64) {
            $randomImageString = md5(uniqid(mt_rand(), true));
            $directory .= '/' . $randomImageString . '.png';
            $file = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $file));
            Storage::disk($disk)->put($directory, $file);
            return $directory;
        }
        return Storage::disk($disk)->putFile($directory, $file);
    }

    /**
     * Get the file path from local storage.
     *
     * @param ?string $filePath
     * @param string $disk
     * @param string $default
     * @return string|null
     */
    public static function getImage(?string $filePath, string $disk = 'public', ?string $default = 'images/default.png'): ?string
    {
        if (!$default && !$filePath) {
            return null;
        }
        return $filePath ? Storage::disk($disk)->url($filePath) : url($default);
    }

    /**
     * Get the file path from local storage.
     *
     * @param ?string $filePath
     * @param string $disk
     * @param bool $getPath
     * @return string|null
     */
    public static function getFile(?string $filePath, string $disk = 'public', bool $getPath = false): ?string
    {
        if ($getPath) {
            return $filePath ? Storage::disk($disk)->path($filePath) : null;
        }
        return $filePath ? Storage::disk($disk)->url($filePath) : null;
    }


    /**
     * Delete an image from local storage.
     *
     * @param string|null $filePath
     * @param string $disk
     * @return bool
     */
    public static function deleteFile(?string $filePath, string $disk = 'public'): bool
    {
        if (!$filePath) {
            return true;
        }
        return Storage::disk($disk)->delete($filePath);
    }

    /**
     * List all files in a directory for a given disk.
     *
     * @param string $directory
     * @param string $disk
     * @return array
     */
    public static function listFiles(string $directory, string $disk = 'public'): array
    {
        return \Storage::disk($disk)->files($directory);
    }

    /**
     * Check if a file exists in a given disk.
     *
     * @param string $filePath
     * @param string $disk
     * @return bool
     */
    public static function fileExists(string $filePath, string $disk = 'public'): bool
    {
        return \Storage::disk($disk)->exists($filePath);
    }
}
