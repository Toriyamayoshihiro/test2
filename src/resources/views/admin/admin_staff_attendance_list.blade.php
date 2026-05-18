@extends('layouts.default')

@section('css')
<link rel="stylesheet" href="{{ asset('css/login.css') }}">
@endsection

@section('content')
@include('components.header')

<div class="content__header">
<h2 class="content__header--item">{{ $user->name }}さんの勤怠</h2>
</div>

<div class="month-nav">
<a href="/admin/attendance/staff/{{ $user->id }}?month={{ $prevMonth }}">
← 前月
</a>
<div>
    📅 {{ $currentMonth }}
</div>

<a href="/admin/attendance/staff/{{ $user->id }}?month={{ $nextMonth }}">
    翌月 →
</a>
</div>

<table class="attendance_table">
<tr>
<th>日付</th>
<th>出勤</th>
<th>退勤</th>
<th>休憩</th>
<th>合計</th>
<th>詳細</th>
</tr>

@foreach($dates as $day)
    @php
        $attendance = $attendances[$day] ?? null;
    @endphp

    <tr>
        <td>{{ \Carbon\Carbon::parse($day)->locale('ja')->isoFormat('MM/DD(ddd)') }}</td>

        @if($attendance)
            <td>{{ $attendance->start_time ? $attendance->start_time->format('H:i') : '' }}</td>
            <td>{{ $attendance->end_time ? $attendance->end_time->format('H:i') : '' }}</td>
            <td>
                {{ sprintf('%d:%02d',
                    floor($attendance->total_rests / 3600),
                    floor(($attendance->total_rests % 3600) / 60)
                ) }}
            </td>
            <td>
                {{ sprintf('%d:%02d',
                    floor($attendance->total_attendances / 3600),
                    floor(($attendance->total_attendances % 3600) / 60)
                ) }}
            </td>
            <td>
                <a href="/admin/attendance/detail/{{ $attendance->id }}">詳細</a>
            </td>
        @else
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td>詳細</td>
        @endif
    </tr>
@endforeach
</table>

<div class="csv-button">
<a href="/admin/attendance/staff/{{ $user->id }}/csv?month={{ $csvMonth }}">
CSV出力
</a>
</div>

@endsection