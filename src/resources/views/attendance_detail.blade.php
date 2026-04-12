@extends('layouts.default')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendnce_detail.css') }}">
@endsection

@section('title','勤怠詳細')

@section('content')
@include('components.header')

<div class="attendance_datail_table">
    <form action="attendance/detail/{$attendance_id}" method="post">
        @csrf
        <table>
            
                <tr>
                    <th>名前</th>
                    <td>{{$attendance->user->name}}</td>
                </tr>
                <tr>
                    <th>日付</th>
                    <td>{{$attendance->date->locale('ja')->isoFormat('YYYY M月D日(ddd)')}}</td>
                </tr>
                <tr>
                    <th>出勤・退勤</th>
                    <td>
                        <input type="time" name=start_time value="{{$attendance->start_time->format('H:i')}}">
                        ～
                        <input type="time" name="end_time" value="{{$attendance->end_time->format('H:i')}}">
                    </td>
                </tr>
                @foreach($attendance->rests as $rest)
                <tr>
                    <th>
                        休憩@if($loop->iteration > 1){{$loop->iteration}}@endif
                    </th>
                    
                    <td>
                        <input type="time" name="rests[{{$loop->index}}][rest_start]"
                                value="{{ old('rests.' . $loop->index . '.rest_start', optional($rest->rest_start)->format('H:i')) }}">
                        ～
                        <input type="time" name="rests[{{$loop->index}}][rest_end]"
                       value="{{ old('rests.' . $loop->index . '.rest_end', optional($rest->rest_end)->format('H:i')) }}">
                    </td>
                </tr>
                @endforeach
                <tr>
                    <th>休憩{{$attendance->rests->count() +1}}</th>
                    <td>
                        <input type="time" name="rests[{{$attendance->rests->count() }}][rest_start]"
                        value="{{old('rests.' . $attendance->rests->count() .'rest_end.')}}">
                    </td>
                </tr>
                <tr>
                    <th>備考</th>
                    <td>
                        <textarea   textarea name="note" @if($stamp) readonly @endif>{{ old('note') }}</textarea>
                    </td>
                </tr>
        </table>
        <button type="submit">修正</button>
    </form>
</div>
@endsection