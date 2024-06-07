<!DOCTYPE html>
<html lang="da" data-theme="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="color-scheme" content="dark">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="robots" content="noindex, nofollow">

        <title>@hasSection('page_title') @yield('page_title') - @endif {{ config('app.name', 'Kodesmedens Development Environment') }}</title>

        @vite(['resources/css/scss/app.scss', 'resources/js/app.js', 'resources/js/modal.js'])
    </head>
    <body>
        <div class="grid sidebar-content">
            <aside class="sidebar">
                <nav>
                    <ul>
                        <li><a href="{{ route('dashboard') }}"><i data-feather="monitor"></i> {{ __( 'Dashboard' ) }}</a></li>
                        <li><a href="{{ route('databases') }}"><i data-feather="database"></i> {{ __( 'Databases' ) }}</a></li>
                    </ul>
                </nav>
                <hr>
                <nav>
                    <ul>
                        <li><a href="{{ route('phpinfo') }}" target="_blank"><i data-feather="info"></i> {{ __( 'PHP Info' ) }}</a></li>
                        @if (Route::has('xdebug'))
                        <li><a href="{{ route('xdebug') }}" target="_blank"><i data-feather="shuffle"></i> {{ __( 'Xdebug Info' ) }}</a></li>
                        @endif
                        <li><a href="http://pma.test/" target="_blank"><i data-feather="database"></i> {{ __( 'phpMyAdmin' ) }}</a></li>
                    </ul>
                </nav>
                <hr>
                <nav>
                    <ul>
                        <li><a href="#"><i data-feather="clipboard"></i> {{ __( 'Logs' ) }}</a></li>
                    </ul>
                </nav>
            </aside>
            <main class="content">
                <header>
                    <button data-target="create-application" data-toggle="modal"><i data-feather="plus"></i> Create App</button>
                </header>

                @yield('content')
            </main>
        </div>

        @yield('footer')
    </body>
</html>
</html>