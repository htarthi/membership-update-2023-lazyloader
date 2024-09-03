<html>
<head>
    <!-- jQuery and bootstrap css -->
    <script src="js/bootstrap.bundle.min.js"></script>
    <!-- Scripts -->
    <script src="{{ asset('js/bootstrap.bundle.min.js') }}" defer></script>
    <!-- font-awesome CSS -->
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <script src="https://kit.fontawesome.com/7945acc650.js" crossorigin="anonymous"></script>
    <style>{!! $portal_style  !!}</style>
</head>
<body>
    <div id="portal">
        <div class="container">
            <div class="simplee-portal__wrapper">
                <div class="simplee-portal__verify">
                    <span class="throbber-loader"></span>
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript">

    </script>
    <!-- jQuery and bootstrap css -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

    <script type="text/javascript">
      {!! $portal_js !!}
    </script>
</body>
</html>
