<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8" />
    <title>ONU</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="{{ asset('css/styles.css') }}" rel="stylesheet"/>
</head>
<body>
<h1>ONU Data & ONU Stats</h1>
<button id="loadBtn">Загрузить информацию</button>
<div id="loading">Загрузка...</div>
<div id="result"></div>

<script src="{{ asset('https://code.jquery.com/jquery-3.6.0.min.js') }}"></script>
<script src="{{ asset('js/scripts.js') }}"></script>
</body>
</html>
