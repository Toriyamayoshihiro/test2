<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\AdminRequest;
use App\Models\User;
use App\Models\StampCorrectionRequest;
use App\Models\RestStampCorrectionRequest;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Enums\AttendanceStatus;
use App\Enums\AttendanceRequestStatus;

class AdminController extends Controller
{
    public function showLoginForm()
    {
        return view('admin.admin_login');
    }
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');
        $credentials['is_admin'] = true;

        if (Auth::attempt($credentials)) {
            return redirect('/admin/attendance/list');
        }
        return back()->withErrors(['email' => 'ログインに失敗しました']);
    }
    
    public function getLogout(){
        Auth::logout();
        return redirect()->route('admin.login');
    }
    public function index(Request $request){
        $date = $request->input('date')
                ? Carbon::parse($request->input('date'))
                : Carbon::now('Asia/Tokyo');
        $currentTime=  $date->locale('ja')->isoFormat('YYYY年 M月D日'); 

        $prevDate = $date->copy()->subDay()->format('Y-m-d');
        $nextDate = $date->copy()->addDay()->format('Y-m-d');

        $users =  User::where('is_admin', 'false')
                        ->whereHas('attendances', function ($query) use ($date) {
                                $query->whereDate('date', $date->toDateString());
                                })
                        ->with(['attendances' => function ($query) use ($date) {
                                    $query->whereDate('date', $date->toDateString())
                                        ->with('rests');}])->get();
                        
        foreach ($users as $user) {
            $attendance = $user->attendances->first();

            if ($attendance && $attendance->start_time && $attendance->end_time) {
                $total_rests = 0;

                foreach ($attendance->rests as $rest) {
                    if ($rest->rest_start && $rest->rest_end) {
                        $total_rests += $rest->rest_start->diffInSeconds($rest->rest_end);
                    }
                }

                $work_time = $attendance->start_time->diffInSeconds($attendance->end_time);

                $attendance->total_rests = $total_rests;
                $attendance->total_attendances = $work_time - $total_rests;
            }
        }
        
        return view('admin.admin_attendance_list',compact('users','currentTime','date','prevDate','nextDate'));
    }
    public function admin_attendance_detail($attendance_id){
        
        $attendance = Attendance::with('user','rests','stamp','rests_stamp')->findOrFail($attendance_id);

        $stamp = StampCorrectionRequest::where('attendance_id',$attendance_id)->where('status',0)->first();
        $message = $stamp ? '承認待ちのため修正はできません。' : '';

        return view('admin.admin_attendance_detail',compact('attendance','stamp','message'));
    }
    public function admin_attendance_detail_modify(Request $request, $attendance_id){
        $attendance = Attendance::with('rests')->findOrFail($attendance_id);

        $date = $attendance->date->toDateString();

        $stamp = StampCorrectionRequest::create([
            'attendance_id' => $attendance_id,
            'request_start_time' => $request->start_time
                ? Carbon::parse($date . ' ' . $request->start_time)
                : null,
            'request_end_time' => $request->end_time
                ? Carbon::parse($date . ' ' . $request->end_time)
                : null,
            'memo' => $request->note,
            'status' => 0,   
        ]);

        foreach ($request->input('rests', []) as $rest) {
            if (empty($rest['rest_start']) && empty($rest['rest_end'])) {
                continue;
            }

            if (empty($rest['rest_start']) || empty($rest['rest_end'])) {
                return back()->withErrors([
                    'rests' => '休憩時間は開始・終了を両方入力してください。'
                ])->withInput();
            }

            RestStampCorrectionRequest::create([
                'attendance_id' => $attendance->id,
                'stamp_correction_request_id' => $stamp->id,
                'request_rest_start' => Carbon::parse($date . ' ' . $rest['rest_start']),
                'request_rest_end' => Carbon::parse($date . ' ' . $rest['rest_end']),
            ]);
        }

        return redirect('/admin/attendance/detail/' . $attendance_id);
        
        
    }
   public function admin_staff_list(){
    $users = User::where('is_admin','!','true')->get();
    return view('admin.admin_staff_list',compact('users'));
   }
   public function admin_staff_attendance($user_id){
    $attendances = Attendance::where('user_id',$user_id)->with('rests')->get();
    return view('admin.admin_staff_attendance_list',conpact('attendances'));
   }
   public function admin_request_list(){
    $stamps = StampCorrectionRequest::where('status','0')->with('attendance.user')->get();
    return view('admin.admin_request_list',compact('stamps'));
   }
   public function admin_request_approve($stamp_id){
        $stamp = StampCorrectionRequest::with('attendance.user','attendance.stamp_rest')->find($stamp_id);
        return view('admin.admin_correction_approve',compact('stamp'));
   }
}