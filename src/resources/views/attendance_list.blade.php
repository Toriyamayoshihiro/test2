@extends('layouts.default')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendnce_list.css') }}">
@endsection

@section('content')
@include('components.header')
{{$currentTime}}

<table class="attendance_table">
    <tr class="attendance_row">
        <th class="attendance_label">日付</th>
        <th class="attendance_label">出勤</th>
        <th class="attendance_label">退勤</th>
        <th class="attendance_label">休憩</th>
        <th class="attendance_label">合計</th>
        <th class="attendance_label">詳細</th>
    </tr>
    @foreach($attendances as $attendance)
    <tr class="attendance_row">
        <td class="attendance_label">{{$attendance->date->locale('ja')->isoFormat('MM/DD(ddd)')}}</td>
        <td class="attendance_label">{{$attendance->start_time->format('H:i')}}</td>
        <td class="attendance_label">{{$attendance->end_time->format('H:i')}}</td>
        <td class="attendance_label">{{floor($attendance->total_rests / 3600)}}:{{floor($attendance->total_rests % 3600) / 60}}</td>
        <td class="attendance_label">{{$attendance->total_attendances}}</td>
        <td class="attendance_label"><a href="/attendance/detail/{{$attendance->id}}">詳細</a></td> 
    </tr>
    @endforeach
</table>
@if(Session::has('flashError'))
    toastr.error("{{session('flashError')}}")
@endif
    

@endsection