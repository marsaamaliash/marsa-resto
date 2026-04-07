<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>SCCR – Login</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    {{ $slot }}
    @livewireScripts
</body>

</html>
