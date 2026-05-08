@extends('layouts.default')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendnce_list.css') }}">
@endsection
@section('title','勤怠一覧')
@section('content')

@include('components.header')
<div class="content__header">
        <h2 class="content__header--item">勤怠一覧</h2>
 </div>
<a href="/attendance/list?month={{$prevMonth }}" class="month-nav__link">
    ←前月
</a>
<form action="attendance/list" method="get" class="month-nav__center">
    <label for="monthPicker" style="cursor:pointer;">
        📅
    </label>
    <input type="month" id="monthPicker" name="month" value="{{$selectedMonth}}" 
            class="month-nav__input" style="display:none" onchange="this.form.submit()"> 
</form>

<p>{{$currentTime}}</p>
<a href="/attendance/list?month={{$nextMonth }}" class="month-nav__link">
    ←翌月
</a>


<table class="attendance_table">
    <tr class="attendance_row">
        <th class="attendance_label">日付</th>
        <th class="attendance_label">出勤</th>
        <th class="attendance_label">退勤</th>
        <th class="attendance_label">休憩</th>
        <th class="attendance_label">合計</th>
        <th class="attendance_label">詳細</th>
    </tr>
    @foreach($dates as $day)
        @php
            $attendance = $attendances[$day] ?? null;
        @endphp
    <tr class="attendance_row">
        <td class="attendance_label">
            {{\Carbon\Carbon::parse($day)->locale('ja')->isoFormat('MM/DD(ddd)')}}
        </td>

        @if($attendance)

        
        <td class="attendance_label">{{$attendance->start_time->format('H:i')}}</td>
        <td class="attendance_label">{{$attendance->end_time->format('H:i')}}</td>
        <td class="attendance_label">{{floor($attendance->total_rests / 3600)}}:{{floor($attendance->total_rests % 3600) / 60}}</td>
        <td class="attendance_label">{{floor($attendance->total_attendances /3600)}}:{{floor($attendance->total_attendances % 3600) / 60}}</td>
        <td class="attendance_label"><a href="/attendance/detail/{{$attendance->id}}">詳細</a></td> 
        @else
        <td class="attendance_label"></td>
        <td class="attendance_label"></td>
        <td class="attendance_label"></td>
        <td class="attendance_label"></td>
        <td class="attendance_label"></td>
        @endif
    </tr>
    @endforeach
</table>
@if(Session::has('flashError'))
    toastr.error("{{session('flashError')}}")
@endif
    

@endsection