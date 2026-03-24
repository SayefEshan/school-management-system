<?php

namespace Modules\Admission\Enums;

enum AdmissionStatus: string
{
    case PENDING = 'pending';
    case UNDER_REVIEW = 'under_review';
    case ACCEPTED = 'accepted';
    case REJECTED = 'rejected';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::UNDER_REVIEW => 'Under Review',
            self::ACCEPTED => 'Accepted',
            self::REJECTED => 'Rejected',
            self::CANCELLED => 'Cancelled',
        };
    }

    public function labelBn(): string
    {
        return match ($this) {
            self::PENDING => 'অপেক্ষমাণ',
            self::UNDER_REVIEW => 'পর্যালোচনাধীন',
            self::ACCEPTED => 'গৃহীত',
            self::REJECTED => 'প্রত্যাখ্যাত',
            self::CANCELLED => 'বাতিল',
        };
    }
}
