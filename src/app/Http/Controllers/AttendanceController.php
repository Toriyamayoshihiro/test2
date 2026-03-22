<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Attendance;
use App\Models\RestTime;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    public function index(){
        return view('attendance');
    }
    public function attendance_list(){
        $user = Auth::user();
        $attendances = Attendance::with('rests')->where('user_id',$user->id)->get();
        return view('attendance_list',compact('user','attendances'));
    }
}
