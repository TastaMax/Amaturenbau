<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<meta http-equiv="x-ua-compatible" content="ie=edge" />

<!-- CSRF Token -->
<meta name="csrf-token" content="{{ csrf_token() }}">

<!-- SEO -->
<title>{{ isset($title) ? $title : env('APP_NAME') }}</title>
<meta name="description" content="{{ isset($description) ? $description : 'Integrationssystem' }}" />

<!-- icon -->
<link rel="apple-touch-icon" sizes="192x192" href="{{ Vite::asset('resources/images/logo/favicon.png') }}">
<link rel="icon" type="image/png" sizes="192x192" href="{{ Vite::asset('resources/images/logo/favicon.ico') }}">
