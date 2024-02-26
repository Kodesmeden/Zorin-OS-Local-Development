<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="color-scheme" content="light dark">
        <title>Laravel</title>

        {{-- Load Feather Icons --}}
        <script src="https://cdnjs.cloudflare.com/ajax/libs/feather-icons/4.29.1/feather.min.js" integrity="sha512-4lykFR6C2W55I60sYddEGjieC2fU79R7GUtaqr3DzmNbo0vSaO1MfUjMoTFYYuedjfEix6uV9jVTtRCSBU/Xiw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
        
        {{-- Load Modal --}}
        <script src="http://dev.test/custom-assets/modal.js"></script>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body>
        <div class="grid sidebar-content">
            <aside class="sidebar">
                <nav>
                    <ul>
                        <li><a href="#"><i data-feather="monitor"></i> {{ __( 'Dashboard' ) }}</a></li>
                    </ul>
                </nav>
                <hr>
                <nav>
                    <ul>
                        <li><a href="{{ route('phpinfo') }}" target="_blank"><i data-feather="info"></i> {{ __( 'PHP Info' ) }}</a></li>
                        <li><a href="{{ route('xdebug') }}" target="_blank"><i data-feather="shuffle"></i> {{ __( 'Xdebug Info' ) }}</a></li>
                        <li><a href="http://pma.test/"><i data-feather="database" target="_blank"></i> {{ __( 'phpMyAdmin' ) }}</a></li>
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
                <button class="contrast" data-target="modal-example" onclick="toggleModal(event)">Launch demo modal</button>
            </main>
        </div>

        <dialog id="modal-example">
            <article>
                <header>
                    <button aria-label="Close" rel="prev" data-target="modal-example" onclick="toggleModal(event)"></button>
                    <h3>Confirm your action!</h3>
                </header>
                <p>
                    Cras sit amet maximus risus. Pellentesque sodales odio sit amet augue finibus
                    pellentesque. Nullam finibus risus non semper euismod.
                </p>
                <footer>
                    <button role="button" class="secondary" data-target="modal-example" onclick="toggleModal(event)">Cancel</button>
                    <button autofocus="" data-target="modal-example" onclick="toggleModal(event)">Confirm</button>
                </footer>
            </article>
        </dialog>

        <script>
            feather.replace();
        </script>
    </body>
</html>
