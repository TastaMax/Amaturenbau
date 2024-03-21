<!doctype html>
<html lang="de">
<head>
    @include('layouts.head')

    @vite(['resources/js/app.js'])
</head>
<body>

@auth('web')
    @include('layouts.header')
@endauth

<main>
@yield('content')
</main>

@include('layouts.footer')

@include('partials.alert')

@stack('scripts')
</body>
</html>
