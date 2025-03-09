<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{
    darkMode: localStorage.getItem('darkMode') === 'true',
    showQr: false,
    qrUrl: '',
    toggleDarkMode() {
        this.darkMode = !this.darkMode;
        localStorage.setItem('darkMode', this.darkMode);
    },
    generateQR(url) {
        this.qrUrl = url || window.location.href;
        this.showQr = true;
        setTimeout(() => {
            QRCode.toCanvas(document.getElementById('qrcode'), this.qrUrl, function(error) {
                if (error) console.error(error);
            });
        }, 100);
    },
    printQr() {
        window.print();
    }
}" x-bind:class="{ 'dark': darkMode }">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- google font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
        rel="stylesheet">

    <!-- QR Code Library -->
    <script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.1/build/qrcode.min.js"></script>

    <title>{{ config('app.name') }}</title>

    <style>
        body {
            font-family: Poppins, sans-serif;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            body {
                background-color: white !important;
            }

            .print-only {
                display: block !important;
            }
        }

        .print-only {
            display: none;
        }
    </style>

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        // Add your custom colors here if needed
                    }
                }
            }
        }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>

<body class="bg-gray-100 transition-colors duration-200 dark:bg-gray-900 dark:text-white" x-data="qrCodeModal()">
    <!-- Dark mode toggle -->
    <div class="fixed top-4 right-4 flex space-x-2 no-print z-50">
        <button class="bg-gray-200 dark:bg-gray-700 p-2 rounded-lg shadow-md" @click="toggleDarkMode()">
            <svg x-show="!darkMode" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
            </svg>
            <svg x-show="darkMode" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
        </button>
    </div>

    <!-- QR Code Modal -->
    <div x-show="showQr" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center no-print"
        x-transition>
        <div class="bg-white dark:bg-gray-800 p-8 rounded-lg shadow-xl max-w-md w-full">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold dark:text-white">QR Code</h3>
                <button @click="showQr = false"
                    class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="flex justify-center mb-4">
                <canvas id="qrcode" class="bg-white p-2 rounded"></canvas>
            </div>
            <div class="text-center">
                <button @click="printQr()" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                    Print QR Code
                </button>
            </div>
        </div>
    </div>

    <!-- Print only QR code -->
    <div class="print-only fixed inset-0 flex items-center justify-center z-50">
        <div class="text-center">
            <canvas id="qrcode-print" class="mx-auto"></canvas>
            <p class="mt-4" id="print-url"></p>
        </div>
    </div>

    {{ $slot }}
</body>

</html>
