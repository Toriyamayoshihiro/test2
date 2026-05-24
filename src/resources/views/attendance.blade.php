@extends('layouts.default')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
    <link rel="stylesheet" href="{{ asset('css/header.css') }}">
@endsection

@section('content')

@include('components.header')

<div class="attendance__content">
    <p class="attendance_status">{{ $statusLabel }}</p>

    <div class="currentDate">
        {{ $currentDateTime->locale('ja')->isoFormat('YYYY年M月D日(ddd)') }}
    </div>

    <div class="currentTime">
        {{ $currentDateTime->format('H:i') }}
    </div>

    @if($showButtons)
        <div class="attendance__button-area">
            <form action="/attendance" method="post">
                @csrf
                @foreach($buttons as $button)
                    <button type="submit" name="action" value="{{ $button['value'] }}" class="attendance_btn">
                        {{ $button['label'] }}
                    </button>
                @endforeach
            </form>
        </div>
    @else
        <div class="out_message">
            {{ $message }}
        </div>
    @endif

</div>


@endsection