<?php

namespace App\Enums;

enum AttendanceStatus: int
{
    case Off = 0;
    case Working = 1;
    case Resting = 2;

    public function label(): string
    {
        return match ($this) {
            self::Off  => '勤務外',
            self::Working => '出勤中',
            self::Resting => '休憩中',
        };
    }
    public function button(): array
    {
        return match ($this) {
            self::Off  => [
                ['label' => '出勤', 'value' => 'in_attendance'],
                ],
            self::Working => [
                ['label' => '退勤' , 'value' => 'out_attendance'],
                ['label' => '休憩入' , 'value' => 'in_rest']
                ],
            self::Resting => [
                ['label' => '休憩戻' , 'value' => 'out_rest'],
                ],
        };
    }
    
}
