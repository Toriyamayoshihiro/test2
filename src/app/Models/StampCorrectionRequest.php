<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Enums\AttendanceRequestStatus;

class StampCorrectionRequest extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'status',
        'request_start_time',
        'request_end_time',
        'memo',
        'attendance_id',
    ];
    protected $casts = [
        'status' => AttendanceRequestStatus::class,
        'request_start_time' => 'datetime',
        'request_end_time' => 'datetime',
    ];
    
    public function attendance(){ 
        return $this->belongsTo(Attendance::class);
    }
}
