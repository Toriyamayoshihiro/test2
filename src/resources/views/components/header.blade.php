<header class="header">
    <div class="header__logo">
        <h1><a href="/attendance">COACHTECH</a></h1>
    </div>
    @if( !in_array(Route::currentRouteName(), ['auth.register', 'auth.login', 'auth.verify-email','admin.login']) )
        
        @auth
            @if(Auth::user()->is_admin)
                <a href="/admin/attendance/list" class="header__link">勤怠一覧</a>
                <a href="/admin/staff/list" class="header__link">スタッフ一覧</a>
                <a href="/admin/stamp_correction_request/list" class="header__link">申請一覧</a>
                <li><a href="{{ route('admin.logout') }}">ログアウト</a></li>
            @else
                <a href="/attendance" class="header__link">勤怠</a>
                <a href="/attendance/list" class="header__link">勤怠一覧</a>
                <a href="/stamp_correction_request/list" class="header__link">申請</a>
                <form action="/logout" method="post">
                    @csrf
                    <button class="header__logout">ログアウト</button>
                </form>
            @endif
        @endauth        
    @endif
</header>