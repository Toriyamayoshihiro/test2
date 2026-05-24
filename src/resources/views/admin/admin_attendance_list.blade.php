@extends('layouts.default')

@section('title','勤怠一覧画面')
@section('css')
<link rel="stylesheet" href="{{ asset('css/admin_attendance_list.css') }}">
<link rel="stylesheet" href="{{ asset('css/header.css') }}">
@endsection

@section('content')
@include('components.header')

<div class="admin-attendance-content">
    <div class="content__header">
            <h2 class="content__header--item">{{$currentTime}}の勤怠</h2>
    </div>

    <a href="/admin/attendance/list?date={{$prevDate }}" class="date-nav__link">
        ←前日
    </a>
    <p>📅{{$date->format('Y/m/d')}}</p>
    <a href="/admin/attendance/list?date={{$nextDate }}" class="date-nav__link">
        ←翌日
    </a>

    <table class="attendance-table">
    <tr>
    <th>名前</th>
    <th>出勤</th>
    <th>退勤</th>
    <th>休憩</th>
    <th>合計</th>
    <th>詳細</th>
    </tr>
    @foreach($users as $user)
        @php
            $attendance = $user->attendances->first();
        @endphp

        <tr>
            <td>{{ $user->name }}</td>
            <td>
                {{ $attendance && $attendance->start_time
                    ? $attendance->start_time->format('H:i')
                    : '' }}
            </td>
            <td>
                {{ $attendance && $attendance->end_time
                    ? $attendance->end_time->format('H:i')
                    : '' }}
            </td>
            <td>
                {{ $attendance && isset($attendance->total_rests)
                    ? gmdate('G:i', $attendance->total_rests)
                    : '' }}
            </td>
            <td>
                {{ $attendance && isset($attendance->total_attendances)
                    ? gmdate('G:i', $attendance->total_attendances)
                    : '' }}
            </td>
            <td>
                    <a href="/admin/attendance/detail/{{$attendance->id}}">詳細</a>
            
            </td>
        </tr>
    @endforeach
    </table>
</div>
@endsection