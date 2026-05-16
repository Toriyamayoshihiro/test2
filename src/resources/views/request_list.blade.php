@extends('layouts.default')

@section('css')
<link rel="stylesheet" href="{{ asset('css/request_list.css') }}">
@endsection

@section('content')
@section('title','申請一覧')
@include('components.header')

<div class="content__header">
        <h2 class="content__header--item">申請一覧</h2>
 </div>

    <form class="tab" action="/stamp_correction_request/list" method="get" >
                <button class="tab__item" type="submit" name="tab" value="">承認待ち</button>

                <button class="tab__item" type="submit" name="tab" value="approved">承認済み</button>
    </form>

    <table class="stamp_table">
        <tr class="stamp_row">
            <th class="stamp_label">状態</th>
            <th class="stamp_label">名前</th>
            <th class="stamp_label">対象日時</th>
            <th class="stamp_label">申請理由</th>
            <th class="stamp_label">申請日時</th>
            <th class="stamp_label">詳細</th>
        </tr>
        
        @foreach($stamps as $stamp)
        <tr class="stamp_talbe">
            <td class="stamp_label">{{$stamp->status->label()}}</td>
            <td class="stamp_label">{{$user->name}}</td>
            <td class="stamp_label">{{$stamp->attendance->date->format('Y/m/d')}}</td>
            <td class="stamp_label">{{$stamp->memo}}</td>
            <td class="stamp_label">{{$stamp->created_at->format('Y/m/d')}}</td>
            <td class="stamp_label">
                <a href="/attendance/detail/{{$stamp->attendance->id}}">詳細</a>
            </td>
        </tr>
        @endforeach
    </table>
 @endsection