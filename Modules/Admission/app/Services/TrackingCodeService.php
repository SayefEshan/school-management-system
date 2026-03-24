<?php

namespace Modules\Admission\Services;

use Modules\Admission\Models\AdmissionApplication;

class TrackingCodeService
{
    /**
     * Generate a unique tracking code in format: ADM-YYYY-NNNNNN
     */
    public static function generate(): string
    {
        $year = date('Y');
        $prefix = "ADM-{$year}-";

        $lastApplication = AdmissionApplication::withTrashed()
            ->where('tracking_code', 'like', $prefix . '%')
            ->orderBy('tracking_code', 'desc')
            ->first();

        if ($lastApplication) {
            $lastNumber = (int) str_replace($prefix, '', $lastApplication->tracking_code);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return $prefix . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }
}
