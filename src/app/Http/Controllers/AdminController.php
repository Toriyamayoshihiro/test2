<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\RestTime;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\AdminRequest;
use App\Models\User;
use App\Models\StampCorrectionRequest;
use App\Models\RestStampCorrectionRequest;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Enums\AttendanceStatus;
use App\Enums\AttendanceRequestStatus;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\StampCorrectionRequest as StampCorrectionRequestForm;


class AdminController extends Controller
{
    public function showLoginForm()
    {
        return view('admin.admin_login');
    }
    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');
        $credentials['is_admin'] = true;

        if (Auth::attempt($credentials)) {
            return redirect('/admin/attendance/list');
        }
        return back()->withErrors(['email' => 'ログイン情報が登録されていません']);
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

        $users =  User::where('is_admin', false)
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
    public function admin_attendance_detail_modify(StampCorrectionRequestForm $request, $attendance_id){
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
    $users = User::where('is_admin', 'false')->get();
    return view('admin.admin_staff_list',compact('users'));
   }
   public function admin_staff_attendance(Request $request, $user_id)
    {
        $user = User::findOrFail($user_id);
        $date = $request->input('month')
            ? Carbon::parse($request->input('month'))
            : Carbon::now();

        $startOfMonth = $date->copy()->startOfMonth();
        $endOfMonth = $date->copy()->endOfMonth();

        $period = CarbonPeriod::create($startOfMonth, $endOfMonth);

        $dates = [];
        foreach ($period as $day) {
            $dates[] = $day->toDateString();
        }

        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('date', [
                $startOfMonth->toDateString(),
                $endOfMonth->toDateString()
            ])
            ->with('rests')
            ->get()
            ->keyBy(function ($item) {
                return $item->date->toDateString();
            });

        foreach ($attendances as $attendance) {
            $totalRests = 0;

            foreach ($attendance->rests as $rest) {
                if ($rest->rest_start && $rest->rest_end) {
                    $totalRests += $rest->rest_start->diffInSeconds($rest->rest_end);
                }
            }

            $workTime = 0;
            if ($attendance->start_time && $attendance->end_time) {
                $workTime = $attendance->start_time->diffInSeconds($attendance->end_time);
            }

            $attendance->total_rests = $totalRests;
            $attendance->total_attendances = $workTime - $totalRests;
        }

        $currentMonth = $date->format('Y/m');
        $prevMonth = $date->copy()->subMonth()->format('Y-m');
        $nextMonth = $date->copy()->addMonth()->format('Y-m');
        $csvMonth = $date->format('Y-m');

        return view('admin.admin_staff_attendance_list', compact(
            'user',
            'dates',
            'attendances',
            'currentMonth',
            'prevMonth',
            'nextMonth',
            'csvMonth'
        ));
    }
    public function admin_staff_attendance_csv(Request $request, $user_id)
    {
        $user = User::findOrFail($user_id);

        $month = $request->input('month')
            ? Carbon::parse($request->input('month'))
            : Carbon::now();

        $startOfMonth = $month->copy()->startOfMonth();
        $endOfMonth = $month->copy()->endOfMonth();

        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('date', [
                $startOfMonth->toDateString(),
                $endOfMonth->toDateString()
            ])
            ->with('rests')
            ->get();

        $csvData = [];

        $csvData[] = [
            '日付',
            '出勤',
            '退勤',
            '休憩時間',
            '勤務時間'
        ];

        foreach ($attendances as $attendance) {

            $totalRests = 0;

            foreach ($attendance->rests as $rest) {
                if ($rest->rest_start && $rest->rest_end) {
                    $totalRests += $rest->rest_start->diffInSeconds($rest->rest_end);
                }
            }

            $workTime = 0;

            if ($attendance->start_time && $attendance->end_time) {
                $workTime =
                    $attendance->start_time->diffInSeconds($attendance->end_time)
                    - $totalRests;
            }

            $csvData[] = [
                $attendance->date->format('Y/m/d'),
                optional($attendance->start_time)->format('H:i'),
                optional($attendance->end_time)->format('H:i'),
                sprintf(
                    '%d:%02d',
                    floor($totalRests / 3600),
                    floor(($totalRests % 3600) / 60)
                ),
                sprintf(
                    '%d:%02d',
                    floor($workTime / 3600),
                    floor(($workTime % 3600) / 60)
                ),
            ];
        }

        $filename =
            $user->name . '_' .
            $month->format('Y_m') .
            '_attendance.csv';

        $handle = fopen('php://temp', 'r+');

        foreach ($csvData as $row) {
            fputcsv($handle, $row);
        }

        rewind($handle);

        $csv = stream_get_contents($handle);

        fclose($handle);

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header(
                'Content-Disposition',
                'attachment; filename="' . $filename . '"'
            );
    }

   public function admin_request_list(Request $request){
    $type = $request->tab ?? '';

    $query = StampCorrectionRequest::with('attendance.user');

    if($type === 'approved' ){
        $query->where('status',AttendanceRequestStatus::Approved->value);
    }else {
        $query->where('status',AttendanceRequestStatus::Pending->value);
    }
    
    $stamps = $query->latest()->get();

    foreach($stamps as $stamp){
        
        $stamp->statusLabel = $stamp->status->label();
    }

    return view('admin.admin_request_list',compact('stamps','type',));
   }
   public function admin_request_approve($stamp_id){
        $stamp = StampCorrectionRequest::with('attendance.user','attendance.rests_stamp')->findOrFail($stamp_id);

        $stamp->statusLabel = $stamp->status->label();

        
        return view('admin.admin_correction_approve',compact('stamp'));
   }
   public function admin_request_approved($stamp_id)
    {
        $stamp = StampCorrectionRequest::with([
        'attendance.rests',
        'attendance.rests_stamp',
        ])->findOrFail($stamp_id);

        $attendance = $stamp->attendance;

        $attendance->start_time = $stamp->request_start_time;
        $attendance->end_time = $stamp->request_end_time;
        $attendance->save();

        $attendance->rests()->delete();

        foreach ($attendance->rests_stamp as $restStamp) {
            RestTime::create([
                'attendance_id' => $attendance->id,
                'rest_start' => $restStamp->request_rest_start,
                'rest_end' => $restStamp->request_rest_end,
            ]);
        }

        $stamp->status = AttendanceRequestStatus::Approved;
        $stamp->save();
        

        return redirect('/admin/stamp_correction_request/approve/' . $stamp->id);
}
}