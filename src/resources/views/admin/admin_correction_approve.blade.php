@extends('layouts.default')

@section('css')
<link rel="stylesheet" href="{{ asset('css/login.css') }}">
@endsection

@section('content')
@include('components.header')

    <h2 class="content__header--item">勤怠詳細</h2>
</div>

<div class="attendance-detail">
<form action="/admin/stamp_correction_request/approve/{{ $stamp->id }}" method="post">
@csrf

    <table class="attendance-detail__table">
        <tr>
            <th>名前</th>
            <td colspan="3">{{ $stamp->attendance->user->name }}</td>
        </tr>

        <tr>
            <th>日付</th>
            <td>{{ $stamp->attendance->date->format('Y年') }}</td>
            <td colspan="2">{{ $stamp->attendance->date->format('n月j日') }}</td>
        </tr>

        <tr>
            <th>出勤・退勤</th>
            <td>{{ $stamp->request_start_time->format('H:i') }}</td>
            <td>〜</td>
            <td>{{ $stamp->request_end_time->format('H:i') }}</td>
        </tr>

        @foreach($stamp->attendance->rests_stamp as $rest)
            <tr>
                <th>
                    休憩@if($loop->iteration > 1){{ $loop->iteration }}@endif
                </th>
                <td>{{ $rest->request_rest_start->format('H:i') }}</td>
                <td>〜</td>
                <td>{{ $rest->request_rest_end->format('H:i') }}</td>
            </tr>
        @endforeach

        <tr>
            <th>備考</th>
            <td colspan="3">{{ $stamp->memo }}</td>
        </tr>
    </table>

    @if($stamp->status->value === 0)
        <button type="submit">承認</button>
    @else
        <div>
            {{$stamp->statusLabel}}
        </div>
        
    @endif
</form>

</div>


@endsection