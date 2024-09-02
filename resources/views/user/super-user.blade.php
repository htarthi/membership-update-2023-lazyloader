<!DOCTYPE html>
<html>
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Subscription</title>

    <!-- Scripts -->
    <script src="{{ asset('js/super-user.js') }}" defer></script>
    <!-- style custom css -->
    <!-- <link href="{{ asset('css/super-user.css') }}" rel="stylesheet"> -->
    <!-- Bootstrap CSS -->
    <!-- <link href="{{ asset('css/bootstrap.css') }}" rel="stylesheet">     -->
    <!-- font-awesome CSS -->
    <script src="https://kit.fontawesome.com/7945acc650.js" crossorigin="anonymous"></script>
</head>
<style>
    @font-face{
        src:url('{{ asset('font/SF Pro Display Regular.ttf') }}');
        font-family: sf-pro-regular;
    }
    @font-face{
        src:url('{{ asset('font/SF Pro Display Bold.ttf') }}');
        font-family: sf-pro-bold;
    }
    @font-face{
        src:url('{{ asset('font/SF Pro Display Light.ttf') }}');
        font-family: sf-pro-light;
    }
    @font-face{
        src:url('{{ asset('font/SF Pro Display Medium.ttf') }}');
        font-family: sf-pro-medium;
    }
    @font-face{
        src:url('{{ asset('font/SF Pro Text Regular.ttf') }}');
        font-family: sf-pro-text;
    }

</style>
<body>
<div id="superuser">
    <super-user></super-user>
</div>
</body>
</html>
