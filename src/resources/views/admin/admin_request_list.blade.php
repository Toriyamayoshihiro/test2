@extends('layouts.default')

@section('css')
<link rel="stylesheet" href="{{ asset('css/login.css') }}">
@endsection

@section('content')
@include('components.header')

<div class="content__header">
<h2 class="content__header--item">申請一覧</h2>
</div>

<form class="tab" action="/admin/stamp_correction_request/list" method="get">
<button type="submit" name="tab" value="">
承認待ち
</button>
<button type="submit" name="tab" value="approved">
    承認済み
</button>

</form>

<table class="stamp_table">
<tr>
<th>状態</th>
<th>名前</th>
<th>対象日時</th>
<th>申請理由</th>
<th>申請日時</th>
<th>詳細</th>
</tr>

@foreach($stamps as $stamp)
    <tr>
        <td>{{ $stamp->statusLabel }}</td>

        <td>{{ $stamp->attendance->user->name }}</td>

        <td>{{ $stamp->attendance->date->format('Y/m/d') }}</td>

        <td>{{ $stamp->memo }}</td>

        <td>{{ $stamp->created_at->format('Y/m/d') }}</td>

        <td>
            <a href="/admin/stamp_correction_request/approve/{{ $stamp->id }}">
                詳細
            </a>
        </td>
    </tr>
@endforeach

</table>


@endsection