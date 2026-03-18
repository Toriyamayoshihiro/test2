@extends('layout.auth')

@section('css')
<link rel="stylesheet" href="{{ asset('css/verify-email.css') }}">
@endsection

@section('content')
<div class="verify">
    <p class="verigy_message">登録していただいたメールアドレスに認証メールを送付しました。
        メール認証を完了してください。
    </p>
    <div class="mail-verify__link">
        <a class="link" href="http://localhost:8025" target="brank" rel="nopener">認証はこちらから</a>
    </div>
    <form action="{{route('verification.send')}}"  method="post">
        @csrf
        <button type="submit" class="btn">
        認証メールを再送する
        </button>
    </form>
</div>
@endsection