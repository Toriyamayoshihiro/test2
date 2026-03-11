<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;
    protected $fillable = [
        'start_time',
        'end_time',
        'date',
        'user_id',
    ];
    public function user(){
        return $this->belongsTo(User::class);
    }
    public function rests(){
       return $this->hasMany(RestTime::class);
    }
    public function stamps(){
       return $this->hasOne(StampCorrectionRequest::class);
    }
    public function rest_stamps(){
       return $this->hasOne(RestStampCorrectionRequest::class);
    }
}
