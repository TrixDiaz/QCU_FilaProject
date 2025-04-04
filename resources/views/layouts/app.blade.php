<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">

    <!-- Scripts -->
    {{-- @vite(['resources/css/app.css', 'resources/js/app.js']) --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- QR Code Library -->
    <script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.0/build/qrcode.min.js"></script>

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body class="bg-gray-100 dark:bg-gray-900 dark:text-white" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }" x-init="$watch('darkMode', val => { localStorage.setItem('darkMode', val); if (val) { document.documentElement.classList.add('dark') } else { document.documentElement.classList.remove('dark') } })"
    :class="{ 'dark': darkMode }">


    <!-- QR Code Modal -->
    <div id="qr-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center hidden">
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg max-w-md w-full">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold text-gray-800 dark:text-white">Scan QR Code</h3>
                <button onclick="closeQRModal()"
                    class="text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div id="qrcode-container" class="flex justify-center mb-4"></div>
            <p class="text-sm text-center text-gray-600 dark:text-gray-400">Scan this code to access this page on
                another device</p>
        </div>
    </div>

    <main class="py-4">
        {{ $slot }}
    </main>

    <script>
        function generateQR(url) {
            const modal = document.getElementById('qr-modal');
            const container = document.getElementById('qrcode-container');

            // Clear previous QR code
            container.innerHTML = '';

            // Generate new QR code
            QRCode.toCanvas(
                document.createElement('canvas'),
                url, {
                    width: 200,
                    margin: 1
                },
                function(error, canvas) {
                    if (error) console.error(error);
                    container.appendChild(canvas);
                    modal.classList.remove('hidden');
                }
            );
        }

        function closeQRModal() {
            document.getElementById('qr-modal').classList.add('hidden');
        }
    </script>
</body>

</html>
