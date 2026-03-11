<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RestTime extends Model
{
    use HasFactory;
    protected $fillable = [
        'rest_start',
        'rest_end',
        'attendance_id',
    ];
    public function attendance(){ 
        return $this->belongsTo(Attendance::class);
    }
}
