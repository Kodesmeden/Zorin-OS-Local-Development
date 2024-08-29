@extends('layouts.app')

@section('page_title', 'Dashboard - Websites')

@section('content')
    <button data-target="create-application" data-toggle="modal"><i data-feather="plus"></i> Create App</button>
    <hr>
    <section>
        <table>
            <tr>
                <th style="width: 50px"></th>
                <th>Name</th>
                <th style="width: 400px">PHP Version</th>
                <th style="width: 200px">Actions</th>
            </tr>
            @foreach( $applications as $application )
                <tr>
                    <td><i data-feather="monitor"></i></td>
                    <td><a href="http://{{ $application->domain }}" target="_blank">{{ $application->name }}</a></td>
                    <td>
                        <form action="{{ route('change-php-version') }}" method="post">
                            @csrf
                            <input type="hidden" name="website_id" value="{{ $application->id }}">
                            <input type="hidden" name="old_php_version" value="{{ $application->php }}">
                            <select name="new_php_version" onchange="this.form.submit()">
                                @foreach($phpVersions as $version)
                                <option value="{{ $version }}"{{ $version === $application->php ? ' selected' : '' }}>{{ $version }}</option>
                                @endforeach
                            </select>
                        </form>
                    </td>
                    <td>
                        <form action="" method="post">
                            @csrf
                            @method('DELETE')
                            <button type="submit" onclick="return confirm('Are you sure you want to delete this application?')"><i data-feather="trash"></i> Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </table>
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