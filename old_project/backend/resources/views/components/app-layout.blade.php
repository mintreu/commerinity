<!doctype html>
<html>
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    @vite(['resources/css/app.css'])
    @stack('styles')
    @livewireStyles
</head>
<body>
{{$slot}}
@livewireScripts
@vite('resources/js/app.js')
@stack('scripts')
</body>
</html>
