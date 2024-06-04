<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SRJ | {{ $title }}</title>
    <link rel="icon" href="frontend/img/icon/srj-icon.png" type="image/png">

    <!-- google font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Caveat:wght@400;500;600;700&family=Inter:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <!-- google font -->
    <!-- Bootstrap -->
    <link rel="stylesheet" href="frontend/libraries/bootstrap/css/bootstrap.min.css">
    <!-- Bootstrap -->
    <!-- fontawesome -->
    <link rel="stylesheet" href="frontend/libraries/fontawesome/css/all.min.css">
    <!-- fontawesome -->
    <!-- my css -->
    <link rel="stylesheet" href="{{ asset('frontend/style/main/main.css') }}">
    <link rel="stylesheet" href="frontend/style/navbar/navbar.css">
    <link rel="stylesheet" href="frontend/style/footer/footer.css">
    <link rel="stylesheet" href="frontend/style/responsive/responsive.css">
    <!-- my css -->
</head>
<body>
    @include('partials.navbar')
    <main>
        @yield('container')
    </main>
    @include('partials.footer')
    <!-- myscript -->
    <script src="{{ asset('script/main.js') }}"></script>
    <!-- myscript -->
    <!-- jquery -->
    <script src="{{ asset('libraries/jquery/jquery-3.7.0.min.js') }}"></script>
    <!-- jquery -->
</body>
</html>