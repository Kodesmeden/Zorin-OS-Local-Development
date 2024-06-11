@extends('layouts.app')

@section('content')
    <header>
        <h1>{{ __( 'Dashboard - Websites' ) }}</h1>
    </header>

    <section>

        <ul>
            {{-- @foreach( $applications as $application )
                <li>
                    <a href="{{ route('applications.show', $application->id) }}">
                        <h3>{{ $application->name }}</h3>
                        <p>{{ $application->type }}</p>
                    </a>
                </li>
            @endforeach --}}
        </ul>
    </section>
@endsection

@section('footer')
    <dialog id="create-application" class="pico-modal">
        <article>
            <form action="{{ route('create-website') }}" method="post">
                @csrf

                <header>
                    <h3>Create Application</h3>
                    <button aria-label="Close" rel="prev" data-target="create-application" data-toggle="modal"></button>
                </header>

                <label for="app-name">Application Name</label>
                <input type="text" name="name" id="app-name" required autofocus>
                
                <label for="app-type">Application Type</label>
                <select name="type" id="app-type">
                    <option value="laravel">Laravel</option>
                    <option value="wordpress">WordPress</option>
                    <option value="git">Git</option>
                    <option value="php">PHP</option>
                </select>

                {{-- Show if Git chosen --}}
                <div class="show-if-git">
                    <label for="app-repo">Repository URL</label>
                    <input type="url" name="repo" id="app-repo">
                </div>
                
                <label for="app-type">PHP Version</label>
                <select name="php_version" id="app-php-version">
                    @foreach($phpVersions as $version)
                    <option value="{{ $version }}"{{ $version === env('DEFAULT_PHP_VERSION') ? ' selected' : '' }}>{{ $version }}</option>
                    @endforeach
                </select>

                <footer>
                    <button role="button" type="submit">Create</button>
                </footer>
            </form>
        </article>
    </dialog>
@endsection