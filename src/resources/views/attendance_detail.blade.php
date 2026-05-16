@extends('layouts.default')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendnce_detail.css') }}">
@endsection

@section('title','勤怠詳細')

@section('content')
@include('components.header')

<div class="attendance_datail_table">
     <div class="detail__header">
            <h2 class="content__header--item">勤怠詳細</h2>
    </div>

    <form action="{{ $attendance ? '/attendance/detail/' . $attendance->id : '/attendance/detail/date/' . $date }}" method="post">
    
        @csrf
        <table>
            
                <tr>
                    <th>名前</th>
                    <td>{{$attendance ? $attendance->user->name : $user->name}}</td>
                </tr>
                <tr>
                    <th>日付</th>
                    <td>{{$attendance ? $attendance->date->locale('ja')->isoFormat('YYYY M月D日')
                            : \Carbon\Carbon::parse($date)->locale('ja')->isoFormat('YYYY年M月D日')}}</td>
                </tr>
                <tr>
                    <th>出勤・退勤</th>
                    <td>
                        <input type="text" @if($stamp) readonly @endif
                        name="start_time" 
                        value="{{$stamp
                                    ? $stamp->request_start_time->format('H:i')
                                    : ($attendance && $attendance->start_time
                                    ? $attendance->start_time->format('H:i') : '') }}"
                        <span>～</span>
                        <input type="text" @if($stamp) readonly @endif
                        name="end_time" 
                        value="{{$stamp
                                    ? $stamp->request_end_time->format('H:i')
                                    : ($attendance && $attendance->end_time
                                    ? $attendance->end_time->format('H:i') : '') }}"
                    </td>
                </tr>
                @php
                    $displayRests = $attendance ? ($stamp ? $attendance->rests_stamp : $attendance->rests) : collect();
                @endphp

                @foreach($displayRests as $rest)
                <tr>
                    <th>
                        休憩@if($loop->iteration > 1){{$loop->iteration}}@endif
                    </th>
                    
                    <td>
                        <input type="text" @if($stamp) readonly @endif
                        name="rests[{{$loop->index}}][rest_start]"
                        value="{{ $stamp
                                ? $rest->request_rest_start->format('H:i')
                                : optional($rest->rest_start)->format('H:i') }}">
                        <span>～</span>
                        <input type="text" @if($stamp) readonly @endif
                        name="rests[{{$loop->index}}][rest_end]"
                        value="{{ $stamp
                                ? $rest->request_rest_end->format('H:i')
                                : optional($rest->rest_end)->format('H:i') }}">
                    </td>
                </tr>
                @endforeach
                @if(!$stamp)
                <tr>
                    <th>休憩{{$attendance ? $attendance->rests->count() +1 : 1}}</th>
                    <td>
                        <input type="text" 
                        name="rests[{{ $attendance ? $attendance->rests->count() : 0 }}][rest_start]"
                        value="">
                        <span>～</span>
                        <input
                        type="text" 
                        name="rests[{{ $attendance ? $attendance->rests->count() : 0 }}][rest_end]"
                        value="">
                    </td>
                </tr>
                @endif
                <tr>
                    <th>備考</th>
                    <td>
                        <textarea name="note" @if($stamp) readonly @endif>{{ old('note', $stamp ? $stamp->memo : '') }}</textarea>
                    </td>
                </tr>
        </table>
        @if($stamp)
            <div class="stamp_message">
                {{$message}}
            </div>
        @else
        <button type="submit">修正</button>
        @endif
    </form>
</div>
@endsection