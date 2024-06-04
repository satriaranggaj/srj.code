<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <link rel="stylesheet" href="{{ asset('frontend/style/navigation/navigation.css') }}">
        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-primary-900">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @if (isset($header))
                <header class="bg-primary-700 shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>

        <form action="" method="post" id="form-delete">
            @csrf
            @method('delete')
        </form>

        <script src="{{ asset('/libraries/jquery/jquery-3.7.0.min.js') }}"></script>
        <script src="{{ asset('/libraries/toastr/toastr.min.js') }}"></script>

        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script src="{{ asset('/script/admin.js') }}"></script>

        <script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
        <script src="{{ asset('script/bootstrap.bundle.min.js') }}"></script>
        <script src="{{ asset('script/main.js') }}"></script>

        <script>
            @if ($messages = Session::get('message'))
                @foreach ($messages as $message)
                    toastr['{{ $message[0] }}']('{{ $message[1] }}');
                @endforeach
            @endif

            @if ($errors->any())
                @foreach ($errors->all() as $error)
                    toastr["error"]("{{ $error }}");
                @endforeach
            @endif
        </script>
        @stack('scripts')
    </body>
</html>
