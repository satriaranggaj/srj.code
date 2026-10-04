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

        {{-- Admin dependencies. jQuery + SweetAlert2 power the delete-confirmation flow in
             script/admin.js; Toastr renders flash messages. Everything else in the
             dashboard is Tailwind + Alpine, so Bootstrap JS, Swiper and the old
             public main.js are no longer loaded here. --}}
        <script src="{{ asset('/libraries/jquery/jquery-3.7.0.min.js') }}"></script>
        <script src="{{ asset('/libraries/toastr/toastr.min.js') }}"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script src="{{ asset('/script/admin.js') }}"></script>

        <script>
            /*
             * Flash messages and validation errors are rendered through
             * json_encode + JSON.parse instead of raw string interpolation, so
             * admin-editable text can never break out of the JS string literal.
             * Types are restricted to the four Toastr methods we actually use.
             */
            (function () {
                var allowed = ['success', 'error', 'warning', 'info'];

                function notify(type, text) {
                    if (allowed.indexOf(type) === -1) {
                        type = 'info';
                    }

                    if (typeof window.toastr === 'undefined') {
                        return;
                    }

                    window.toastr[type](String(text));
                }

                var flash = @json(Session::get('message', []));

                if (Array.isArray(flash)) {
                    flash.forEach(function (entry) {
                        if (Array.isArray(entry) && entry.length >= 2) {
                            notify(entry[0], entry[1]);
                        }
                    });
                }

                var errors = @json($errors->all());

                if (Array.isArray(errors)) {
                    errors.forEach(function (error) {
                        notify('error', error);
                    });
                }
            })();
        </script>
        @stack('scripts')
    </body>
</html>
