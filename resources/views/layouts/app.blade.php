<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Google Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
        rel="stylesheet">

    <!-- QR Code Library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

    <title>{{ config('app.name') }}</title>

    <style>
        body {
            font-family: Poppins, sans-serif;
        }

        @media print {
            body * {
                visibility: hidden; /* Hide everything by default */
            }

            .print-only,
            .print-only * {
                visibility: visible; /* Show only the print-only section */
            }

            .print-only {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                height: 100%;
                display: flex !important;
                align-items: center;
                justify-content: center;
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
</head>

<body class="bg-gray-100 transition-colors duration-200 dark:bg-gray-900 dark:text-white" id="body">

    <!-- Dark mode toggle -->
    <div class="fixed top-4 right-4 flex space-x-2 no-print z-50">
        <button id="dark-mode-toggle" class="bg-gray-200 dark:bg-gray-700 p-2 rounded-lg shadow-md">
            <svg id="sun-icon" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
            </svg>
            <svg id="moon-icon" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 hidden" fill="none"
                viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
        </button>
    </div>

    <!-- QR Code Modal -->
    <div id="qr-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center no-print hidden z-[99]">
        <div class="bg-white dark:bg-gray-800 p-8 rounded-lg shadow-xl max-w-md w-full z-[100]">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold dark:text-white">QR Code</h3>
                <button id="close-qr-modal"
                    class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-white z-[101]">
                    ✕
                </button>
            </div>
            <div class="flex justify-center mb-4">
                <div id="qrcode" class="bg-white p-2 rounded"></div>
            </div>
            <div class="text-center">
                <button id="print-qr" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 z-[101]">
                    Print QR Code
                </button>
            </div>
        </div>
    </div>

    <!-- Print only QR code -->
    <div class="print-only fixed inset-0 flex items-center justify-center">
        <div class="text-center">
            <div id="qrcode-print" class="mx-auto"></div>
            <p class="mt-4" id="print-url"></p>
        </div>
    </div>

    {{ $slot }}

    <script>
        // Dark Mode Toggle
        const darkModeToggle = document.getElementById('dark-mode-toggle');
        const body = document.getElementById('body');
        const sunIcon = document.getElementById('sun-icon');
        const moonIcon = document.getElementById('moon-icon');

        // Set dark mode from localStorage if available
        if (localStorage.getItem('darkMode') === 'true') {
            body.classList.add('dark');
            moonIcon.classList.remove('hidden');
            sunIcon.classList.add('hidden');
        }

        darkModeToggle.addEventListener('click', function () {
            body.classList.toggle('dark');
            sunIcon.classList.toggle('hidden');
            moonIcon.classList.toggle('hidden');
            localStorage.setItem('darkMode', body.classList.contains('dark'));
        });

        // QR Code Functions
        window.generateQR = function (url) {
            console.log('Generating QR for:', url);
            
            const qrModal = document.getElementById('qr-modal');
            const qrContainer = document.getElementById('qrcode');
            
            if (!qrModal || !qrContainer) {
                console.error('QR modal or container not found!');
                return;
            }
            
            // Clear previous QR code
            qrContainer.innerHTML = '';
            
            // Generate new QR code
            try {
                new QRCode(qrContainer, {
                    text: url || window.location.href,
                    width: 256,
                    height: 256,
                    correctLevel: QRCode.CorrectLevel.H
                });
                console.log('QR code generated successfully');
            } catch (error) {
                console.error('Error generating QR code:', error);
            }

            qrModal.classList.remove('hidden');
        };

        // Close modal handler
        document.getElementById('close-qr-modal').addEventListener('click', function() {
            document.getElementById('qr-modal').classList.add('hidden');
        });

        // Print handler
        document.getElementById('print-qr').addEventListener('click', function() {
            console.log('Printing QR for:', window.location.href);
            const printContainer = document.getElementById('qrcode-print');
            const printUrl = document.getElementById('print-url');
            
            // Clear previous QR code
            printContainer.innerHTML = '';
            
            // Generate new QR code
            new QRCode(printContainer, {
                text: window.location.href,
                width: 256,
                height: 256,
                correctLevel: QRCode.CorrectLevel.H
            });
            
            // Set the URL text
            printUrl.textContent = window.location.href;
            
            // Trigger print
            window.print();
        });
    </script>
</body>
</html>