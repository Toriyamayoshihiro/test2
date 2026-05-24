@extends('layouts.default')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin_staff_list.css') }}">
<link rel="stylesheet" href="{{ asset('css/header.css') }}">
@endsection

@section('content')
@include('components.header')

<div class="content__header">

    <h2 class="content__header--item">スタッフ一覧</h2>

</div>
<div class="staff-list">

    <table class="staff-list__table">

        <tr>

            <th>名前</th>

            <th>メールアドレス</th>

            <th>月次勤怠</th>

        </tr>
        @foreach($users as $user)

        <tr>

            <td>                {{ $user->name }}            </td>
            <td>                {{ $user->email }}            </td>
            <td>

                <a href="/admin/attendance/staff/{{ $user->id }}">

                    詳細

                </a>

            </td>

        </tr>

        @endforeach
    </table>

</div>

@endsection