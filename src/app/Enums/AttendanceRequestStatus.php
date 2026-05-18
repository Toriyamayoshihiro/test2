<?php

namespace App\Enums;

enum AttendanceRequestStatus: int
{
    case Pending = 0;
    case Approved = 1;
    case Rejected = 2;

    public function label(): string
    {
        return match ($this) {
            AttendanceRequestStatus::Pending  => '承認待ち',
            AttendanceRequestStatus::Approved => '承認済み',
            AttendanceRequestStatus::Rejected => '却下'
        };
    }
}
