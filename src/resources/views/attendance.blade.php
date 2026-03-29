@extends('layouts.default')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendnce.css') }}">
@endsection

@section('content')

@include('components.header')
<div class="currentDate">
    {{$currentDate->locale('ja')->isoFormat('YYYY年M月D日(ddd)')}}
</div>
<div>
    {{$currentTime->format('H:i')}}
</div>
<div>
    <form action="/attendance" method="post">
        @csrf
        <input type="text" name="Date" value="{{$currentDate}}">
        <input type="text" name="start_time" value="{{$currentTime}}">
        <button>{{$status}}</button>
        <button>{{$status2}}</button>
    </form>
</div>
@endsection