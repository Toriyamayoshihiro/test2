@extends('layouts.default')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendnce.css') }}">
@endsection

@section('content')

@include('components.header')
<p class="attendance_status">{{$statusLabel}}</p>
<div class="currentDate">
    {{$currentDateTime->locale('ja')->isoFormat('YYYY年M月D日(ddd)')}}
</div>
<div>
    {{$currentDateTime->format('H:i')}}
</div>
@if($showButtons)
<div>
    <form action="/attendance" method="post">
        @csrf
        @foreach($buttons as $button)
            <button type="submit" name="action" value="{{$button['value']}}" class="attendance_btn">{{$button['label']}}</button>
        @endforeach
    </form>
</div>
@else
    <div class="out_message">
        {{$message}}
    </div>
@endif


@endsection