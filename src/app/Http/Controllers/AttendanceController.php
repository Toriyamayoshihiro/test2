<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Attendance;
use App\Models\RestTime;
use Illuminate\Support\Facades\Auth;
use App\Models\StampCorrectionRequest;
use App\Models\RestStampCorrectionRequest;
use Carbon\Carbon;
use App\Enums\AttendanceStatus;

class AttendanceController extends Controller
{
    public function index(){
        $currentDateTime = Carbon::now('Asia/Tokyo');
        $user = Auth::user();
        $attendance = Attendance::where('user_id',$user->id)
                                    ->latest()->with('rests')->first();
        $statusEnum = AttendanceStatus::from($user->attendance_status);
        $statusLabel = $statusEnum->label();
        $buttons = $statusEnum->button();
        $message = null;
        $showButtons = true;
        if($attendance && $attendance->date->toDateString() === $currentDateTime->toDateString() && $attendance->end_time ){
            $message = 'お疲れさまでした。';
            $statusLabel = '退勤済';
            $showButtons = false;
        }
        //switch($user->attendance_status){
            //case AttendanceStatus::Off :
                //$status = '出勤';
                //break;
            //case AttendanceStatus::Working:
                //$status = '退勤';
                //$rest_button = '休憩';
                //break;
            //case AttendanceStatus::Resting:
                //$status = '休憩戻';
                //break;
        //}
        return view('attendance',compact('currentDateTime','user','attendance','buttons','message','statusLabel','showButtons'));
    }
    public function store(Request $request){
        $user = Auth::user();
        $now = Carbon::now('Asia/Tokyo');
        $date = $now->toDateString();
        $currentTime=  $now->format('H:i:s');
        $action = $request->action;
        switch($action){
            case 'in_attendance':
                $attendance = new Attendance();
                $attendance->date = $date;
                $attendance->user_id = $user->id;
                $attendance->start_time = $currentTime;
                $attendance->save();
                $user->update([
                    'attendance_status' => AttendanceStatus::Working->value
                ]);
                break;
            case 'out_attendance':
                $attendance = Attendance::where('user_id',$user->id)->latest()->with('rests')->first();
                                                
                
                if(!$attendance->rests()->exists()){
                    return redirect('/attendance')->with('flashError','休憩してください');
                }
                if($attendance->rests()->whereNull('rest_end')->exists()){
                    return redirect('/attendance')->with('flashError','休憩終了してください');
                }
                $attendance->end_time = $currentTime;
                $attendance->save();
                $user->update([
                    'attendance_status' => AttendanceStatus::Off->value
                ]);
                break;
            case 'in_rest':
                $attendance = Attendance::where('user_id',$user->id)>latest()->first();
                $rest = new RestTime();
                $rest->attendance_id = $attendance->id;
                $rest->rest_start = $now;
                $rest->save();
                $user->update([
                    'attendance_status' => AttendanceStatus::Resting->value
                ]);
                break;
            case 'out_rest':
                $attendance = Attendance::where('user_id',$user->id)>latest()->first(); 
                $rest = $attendance->rests()->whereNull('rest_end')->latest()->first();
                $rest->rest_end = $now;
                $rest->save();
                $user->update([
                    'attendance_status' => AttendanceStatus::Working->value
                ]);
                break;
        }
                return redirect('/attendance');
    }
    public function attendance_list(){
        $user = Auth::user();
        $attendances = Attendance::where('user_id',$user->id)
                                    ->whereNotNull('end_time')->with('rests')->get();
        $now = Carbon::now();
        $currentTime = $now->format('Y/m');
        foreach($attendances as $attendance){
            $total_rests = 0;
            $work_time = $attendance->start_time->diffInSeconds($attendance->end_time);
            foreach($attendance->rests as $rest){
                if($rest->rest_start && $rest->rest_end){
                    $second = $rest->rest_start->diffInSeconds($rest->rest_end); 
                    $total_rests += $second;
                }
            }
            $attendance->total_rests = $total_rests;
            $attendance->total_attendances = $work_time - $total_rests;
        }
        
        
        return view('attendance_list',compact('user','attendances','currentTime'));
    }
    public function attendance_detail($attendance_id){
        $attendance = Attendance::with(['user','rests','stamp','rests_stamp'])->findOrFail($attendance_id); 
        $stamp = StampCorrectionRequest::where('attendance_id',$attendance_id)->first();
        $message = $stamp ? '承認待ちのため修正はできません。' : '';

        return view('attendance_detail',compact('attendance','stamp','message'));
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
