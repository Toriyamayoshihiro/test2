<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\AdminRequest;
use App\Models\User;
use App\Models\StampCorrectionRequest;
use App\Models\RestStampCorrectionRequest;

class AdminController extends Controller
{
    public function showLoginForm()
    {
        return view('admin.admin_login');
    }

    //public function login(Request $request)
    //{
    //    $credentials = $request->only('email', 'password');
    //    $credentials['is_admin'] = true;

    //    if (Auth::attempt($credentials)) {
    //        return redirect('/admin/attendance/list');
    //    }
    //    return back()->withErrors(['email' => 'ログインに失敗しました']);
    //}
    public function getLogout(){
        Auth::logout();
        return redirect()->route('admin.login');
    }
    public function index(){
        $users =  User::where('is_admin', '!=', 'true')->with('attendances')->get();
        return view('admin.admin_attendance_list',compact('users'));
    }
    public function admin_attendance_detail($attendance_id){
        
        $attendance = Attendance::with('user','resets')->find($attendance_id);
        return view('admin.admin_attendance_detail',compact('attendance'));
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