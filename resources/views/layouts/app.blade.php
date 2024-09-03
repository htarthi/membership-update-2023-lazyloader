<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Accessibility') }}</title>

</head>

@if (isset($current_user))
    <script>
        window.initialData = @json($id); // $data is your Laravel data
    </script>
@endif
{{-- <script src="https://cdn.jsdelivr.net/npm/react@18/umd/react.production.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/react-dom@18/umd/react-dom.production.min.js"></script> --}}
{{-- {{Auth::user()}} --}}

@if (Auth::user())

<script>
    window.user = @json(Auth::user());
</script>

@endif

<body>

    <div id="app"></div>
    @viteReactRefresh
     @vite('resources/js/app.js')

</body>

</html>
{{-- <script src="{{ asset('js/app.js') }}"></script> --}}

