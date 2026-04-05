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
                                    ->where('date',$currentDateTime->toDateString())->with('rests')->first();
        $statusEnum = AttendanceStatus::from($user->attendance_status);
        $buttons = $statusEnum->button();
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
        return view('attendance',compact('currentDateTime','user','attendance','buttons'));
    }
    public function store(Request $request){
        $user = Auth::user();
        $date = Carbon::now('Asia/Tokyo')->format('Y-m-d');
        $currentTime=  Carbon::now('Asia/Tokyo')->format('H:i:s');
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
                $attendance = Attendance::where('date',$date)
                                                ->where('user_id',$user->id)->with('rests')->first();
                
                if(!$attendance->rests()->exists()){
                    return redirect('/attendance')->with('flashError','休憩してください');
                }
                if(!$attendance->rests()->whereNull('rest_end')->exists()){
                    return redirect('/attendance')->with('flashError','休憩終了してください');
                }
                $attendance->end_time = $currentTime;
                $attendance->save();
                $user->update([
                    'attendance_status' => AttendanceStatus::Off->value
                ]);
                break;
            case 'in_rest':
                $attendance = Attendance::where('date',$date)
                                        ->where('user_id',$user->id)->first();
                $rest = new Rest();
                $rest->attendance_id = $attendance->id;
                $rest->rest_start = $currentTime;
                $rest->save();
                $user->update([
                    'attendance_status' => AttendanceStatus::Resting->value
                ]);
                break;
            case 'out_rest':
                $attendance = Attendance::where('date',$date)
                                        ->where('user_id',$user->id)->with('rests')->first();
                $rest = $attendance->rests()->whereNull('rest_end')->latest()->first();
                $rest->rest_end = $currentTime;
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
