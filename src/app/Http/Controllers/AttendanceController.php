<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Attendance;
use App\Models\RestTime;
use Illuminate\Support\Facades\Auth;
use App\Models\StampCorrectionRequest;
use App\Models\RestStampCorrectionRequest;

class AttendanceController extends Controller
{
    public function index(){
        return view('attendance');
    }
    public function attendance_list(){
        $user = Auth::user();
        $attendances = Attendance::where('user_id',$user->id)->with('rests')->get();
        return view('attendance_list',compact('user','attendances'));
    }
    public function attendance_detail($attendance_id){
        $attendance = Attendance::with(['user','rests','stamp','rests_stamp'])->findOrFail($attendance_id);
        $stamp = StampCorrectionRequest::where('attendance_id',$attendance_id)->first();
        $message = $stamp ? '承認待ちのため修正はできません。' : '';

        return view('attendance.detail',compact('attendance','stamp'));
    }
    public function request_list(Request $request){
        $user = Auth::user();
        $type = $request->tab ?? '';
        $query = StampCorrectionRequest::with('attendance')
        ->whereHas('attendance',function ($q) use ($user){
            $q->where('user_id',$user->id);
            });
        if($type==='approved'){
            $query->where('status',1);
                
                                
        }else{
             $query->where('status',0);
        }   
        $stamps = $query->get();
        return view('request_list',compact('user','stamps'));
    }
}
