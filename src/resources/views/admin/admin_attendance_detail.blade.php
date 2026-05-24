@extends('layouts.default')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin_attendance_detail.css') }}">
<link rel="stylesheet" href="{{ asset('css/header.css') }}">
@endsection

@section('content')
@include('components.header')

    <div class="content__header">
<h2 class="content__header--item">勤怠詳細</h2>
</div>
 <form action="/admin/attendance/detail/{{$attendance->id}}" method="post">
    @csrf

<div class="attendance-detail">
<table class="attendance-detail__table">
<tr>
<th>名前</th>
<td>{{ $attendance->user->name }}</td>
</tr>
    <tr>
        <th>日付</th>
        <td>{{ $attendance->date->format('Y年') }}</td>
        <td class="date-cell">{{ $attendance->date->format('n月j日') }}</td>
    </tr>

    <tr>
        <th>出勤・退勤</th>
        <td>
            <input type="text" @if($stamp) readonly @endif
            name="start_time"
            value="{{ $stamp 
                ? optional($stamp->request_start_time)->format('H:i')
                : optional($attendance->start_time)->format('H:i') }}">
            @error('start_time')
                <div class="form_error">
                    {{$message}}
                </div>
            @enderror
        </td>
        <td>〜</td>
        <td>
            <input type="text" @if($stamp) readonly @endif
            name="end_time"
            value="{{ $stamp 
                ? optional($stamp->request_end_time)->format('H:i')
                : optional($attendance->end_time)->format('H:i')}}">
            @error('end_time')
                <div class="form_error">
                    {{$message}}
                </div>
            @enderror
        </td>
    </tr>

    @php
        $displayRests = $stamp ? $attendance->rests_stamp : $attendance->rests;
    @endphp

    @foreach($displayRests as $rest)
        <tr>
            <th>
                休憩@if($loop->iteration > 1){{$loop->iteration}}@endif
            <td>
                <input type="text" @if($stamp) readonly @endif
                name="rests[{{ $loop->index }}][rest_start]"
                value="{{$stamp 
                ? optional($rest->request_rest_start)->format('H:i')
                : optional($rest->rest_start)->format('H:i') }}"
                >
            @error('rests.' . $loop->index . '.rest_start')
                <div class="form__error">
                    {{ $message }}
                </div>
            @enderror
            </td>
            <td>〜</td>
            <td>
                <input type="text" @if($stamp) readonly @endif
                name="rests[{{ $loop->index }}][rest_end]"
                value="{{$stamp 
                ? optional($rest->request_rest_end)->format('H:i')
                : optional($rest->rest_end)->format('H:i') }}"
                >
            @error('rests.' . $loop->index . '.rest_end')
                <div class="form__error">
                    {{ $message }}
                </div>
            @enderror
            </td>
        </tr>
    @endforeach

    @if(!$stamp)
    <tr>    

    <th>休憩{{ $attendance->rests->count() + 1 }}</th>

        <td>

            <input

                type="text"

                name="rests[{{ $attendance->rests->count() }}][rest_start]"

                value=""

            >
            @error('rests.' . ($attendance ? $attendance->rests->count() : 0) . '.rest_start')
                <div class="form__error">
                    {{ $message }}
                </div>
            @enderror
        </td>
        <td>〜</td>
        <td>

            <input            type="text"

                name="rests[{{ $attendance->rests->count() }}][rest_end]"

                value=""        >
            @error('rests.' . ($attendance ? $attendance->rests->count() : 0) . '.rest_end')
                <div class="form__error">
                    {{ $message }}
                </div>
            @enderror
        </td>
    </tr>
    @endif
    @if($attendance->rests->isEmpty())
        <tr>
            <th>休憩</th>
            <td></td>
            <td>〜</td>
            <td></td>
        </tr>
    @endif

    <tr>
        <th>備考</th>
        <td >
            <textarea name="note" @if($stamp) readonly @endif>{{ old('note', $stamp ? $stamp->memo : '') }}</textarea>
            @error('note')
                <div class="form_error">
                    {{$message}}
                </div>
            @enderror
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
</div>
</div>
@endsection
