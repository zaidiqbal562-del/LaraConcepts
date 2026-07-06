<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'App')</title>
    <style>
        /* minimal layout styling */
        body { font-family: Arial, sans-serif; padding: 20px; }
    </style>
    @stack('head')
</head>
<body>
    @include('components.navbar')


    <div style="display:flex; gap:20px; align-items:flex-start;">
        @include('components.menu')

        <main style="flex:1; padding:6px 12px;">
            @yield('content')
        </main>
    </div>

    @stack('scripts')
    @auth
    
    @endauth
</body>
</html>
