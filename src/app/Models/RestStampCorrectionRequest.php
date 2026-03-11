<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RestStampCorrectionRequest extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'attendance_id',
        'request_rest_start',
        'request_rest_end',
    ];
    public function attendance(){ 
        return $this->belongsTo(Attendance::class);
    }
}
