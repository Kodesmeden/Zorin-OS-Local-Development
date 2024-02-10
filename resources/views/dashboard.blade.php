<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="color-scheme" content="light dark">
        <title>Laravel</title>

        @vite(['resources/css/app.css'])
    </head>
    <body>
        <div class="grid sidebar-content">
            <aside class="sidebar">
                Here is the sidebar
            </aside>
            <main class="content">
                Here is the content
            </main>
        </div>
    </body>
</html>
