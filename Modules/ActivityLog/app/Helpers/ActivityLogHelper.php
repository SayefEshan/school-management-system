<?php

namespace Modules\ActivityLog\Helpers;

class ActivityLogHelper
{
    /**
     * Convert a string to title case, preserving already uppercase letters
     *
     * @param string $string
     * @return string
     */
    public static function titleCase(string $string): string
    {
        $string = str_replace('_', ' ', $string);
        return ucwords($string);
    }

    /**
     * Get a human-readable name from a fully qualified class name
     *
     * @param string $class
     * @return string
     */
    public static function getModelName(string $class): string
    {
        $parts = explode('\\', $class);
        $name = end($parts);

        // Add spaces before capital letters (camelCase to Title Case)
        $name = preg_replace('/([a-z])([A-Z])/', '$1 $2', $name);

        return $name;
    }

    /**
     * Get a list of all available auditable event types
     *
     * @return array
     */
    public static function getEventTypes(): array
    {
        return ['created', 'updated', 'deleted', 'restored', 'pivot_attached', 'pivot_detached', 'pivot_updated'];
    }

    /**
     * Format changed values for display
     *
     * @param array $modifiedData
     * @return string
     */
    public static function formatChanges(array $modifiedData): string
    {
        $result = [];

        foreach ($modifiedData as $key => $value) {
            if (is_array($value)) {
                if (isset($value['old']) && isset($value['new'])) {
                    $oldValue = is_array($value['old']) ? json_encode($value['old']) : $value['old'];
                    $newValue = is_array($value['new']) ? json_encode($value['new']) : $value['new'];
                    $result[] = self::titleCase($key) . ': ' . $oldValue . ' → ' . $newValue;
                } else {
                    $result[] = self::titleCase($key) . ': ' . json_encode($value);
                }
            } else {
                $result[] = self::titleCase($key) . ': ' . $value;
            }
        }

        return implode('<br>', $result);
    }

    /**
     * Get IP address information using IPInfo API
     *
     * @param string $ip
     * @return array
     */
    public static function getIpInfo(string $ip): array
    {
        // For local IPs, return basic info
        if (
            in_array($ip, ['127.0.0.1', 'localhost', '::1']) ||
            substr($ip, 0, 3) === '10.' ||
            substr($ip, 0, 8) === '192.168.'
        ) {
            return [
                'ip' => $ip,
                'location' => 'Local Network',
                'country' => 'Local',
                'region' => 'Local',
                'city' => 'Local',
                'latitude' => null,
                'longitude' => null,
                'isp' => 'Local ISP',
                'timezone' => config('app.timezone'),
            ];
        }

        try {
            // Using ipinfo.io API (free tier allows 50,000 requests per month)
            $response = file_get_contents("https://ipinfo.io/{$ip}/json");
            $data = json_decode($response, true);

            if (!empty($data) && !isset($data['error'])) {
                $location = $data['loc'] ?? '';
                list($latitude, $longitude) = !empty($location) ? explode(',', $location) : [null, null];

                return [
                    'ip' => $ip,
                    'location' => $location,
                    'country' => $data['country'] ?? 'Unknown',
                    'region' => $data['region'] ?? 'Unknown',
                    'city' => $data['city'] ?? 'Unknown',
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'isp' => $data['org'] ?? 'Unknown',
                    'timezone' => $data['timezone'] ?? 'Unknown',
                ];
            }
        } catch (\Exception $e) {
            // Silent fail
        }

        return [
            'ip' => $ip,
            'location' => null,
            'country' => 'Unknown',
            'region' => 'Unknown',
            'city' => 'Unknown',
            'latitude' => null,
            'longitude' => null,
            'isp' => 'Unknown',
            'timezone' => 'Unknown',
        ];
    }
}
