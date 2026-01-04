<?php

use App\Services\FileManagerService;
use Illuminate\Http\JsonResponse;

if (!function_exists('tableDataInfo')) {

    function tableDataInfo($table)
    {
        $table->softDeletes();
        $table->foreignId('created_by')->nullable()->comment('0 for system');
        $table->foreignId('updated_by')->nullable()->comment('0 for system');
    }

}

if (!function_exists('apiResponse')) {

    function apiResponse(bool $result, string $message = "", $data = null, $errors = null, int $code = 200): JsonResponse
    {
        return response()->json(
            [
                'error' => !$result,
                'code' => $code ?? ($result ? 200 : 400),
                'message' => $message,
                'data' => $data,
                'errors' => $errors,
            ],
            $code ?? ($result ? 200 : 400)
        );
    }

}

if (!function_exists('ajaxResponse')) {

    function ajaxResponse($code, $message = "The given data was invalid.", $errors = null, $data = null): JsonResponse
    {

        if (!is_null($errors)) {
            if (!is_object($errors)) {
                $errors = (object)$errors;
            }
        }
        return response()->json(
            [
                'message' => $message,
                'errors' => $errors,
                'data' => $data,
                'code' => $code,
            ],
            $code);
    }

}

if (!function_exists('getUrlFromPath')) {

    function getUrlFromPath($path): string
    {
        return FileManagerService::getImage($path);
    }

}

if (!function_exists('status')) {

    function status()
    {
        return [
            'Active' => 'Active',
            'Deactivate' => 'Deactivate',
        ];
    }

}
if (!function_exists('integerStatus')) {

    function integerStatus()
    {
        return [
            '1' => 'Active',
            '0' => 'Inactive',
        ];
    }

}

if (!function_exists('getCommonStatus')) {

    function getCommonStatus()
    {

        return [
            'Active' => 'Active',
            'Inactive' => 'Inactive',
        ];
    }

}

if (!function_exists('getParPagePaginate')) {

    function getParPagePaginate(): array
    {
        return ['10' => '10', '25' => '25', '50' => '50', '100' => '100'];
    }

}

if (!function_exists('getIntegerMonth')) {

    function getIntegerMonth()
    {
        return [
            '01' => 'January',
            '02' => 'February',
            '03' => 'March',
            '04' => 'April',
            '05' => 'May',
            '06' => 'June',
            '07' => 'July',
            '08' => 'August',
            '09' => 'September',
            '10' => 'October',
            '11' => 'November',
            '12' => 'December',
        ];
    }

}

if (!function_exists('getLast11Digit')) {

    function getLast11Digit($number)
    {
        $phoneNumber = trim($number);
        // Remove any non-numeric characters from the phone number
        $numericOnly = preg_replace('/[^0-9]/', '', $phoneNumber);

        // If the phone number starts with '880', remove that prefix
        if (Str::startsWith($numericOnly, '880')) {
            $numericOnly = substr($numericOnly, 3);
        }

// Ensure the phone number starts with '0'
        if (!Str::startsWith($numericOnly, '0')) {
            $numericOnly = '0' . $numericOnly;
        }

// Now you have the formatted phone number
        return $numericOnly;
    }

}

if (!function_exists('engToBangla')) {

    function engToBangla($number)
    {
        $search_array = array("1", "2", "3", "4", "5", "6", "7", "8", "9", "0");
        $replace_array = array("১", "২", "৩", "৪", "৫", "৬", "৭", "৮", "৯", "০");
        return str_replace($search_array, $replace_array, $number);
    }
}

if (!function_exists('currency_number')) {
    function currency_number($number)
    {
        return number_format($number, 2, '.', ',');
    }
}

if (!function_exists('snakeCase')) {
    function snakeCase($string)
    {
        $string = preg_replace('/[^A-Za-z0-9]/', '', $string);
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $string));
    }
}

if (!function_exists('isImage')) {
    function isImage($url): bool
    {
        if (!is_string($url)) {
            return false;
        }
        if (!isUrl($url)) {
            return false;
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $headers = curl_exec($ch);
        curl_close($ch);

        if ($headers === false) {
            return false;
        }

        return str_contains($headers, 'Content-Type: image/');
    }
}

if (!function_exists('isUrl')) {
    function isUrl($url): bool
    {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }
}
