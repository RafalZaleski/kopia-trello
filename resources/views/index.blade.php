<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <title>ToDo List</title>
        <meta name="description" content="Twoja lista zadań">
        <link rel="icon" href="/favicon.ico">
        <link rel="apple-touch-icon" href="/zarro_black.png" sizes="180x180">
        <link rel="mask-icon" href="/zarro_black.svg" color="#000000">
        <meta name="theme-color" content="#ffffff">
        <link rel="manifest" href="manifest.json" />
        
        @vite('resources/js/app.js')

        <!-- <script>
            if ('serviceWorker' in navigator) {
                window.addEventListener('load', () => {
                    navigator.serviceWorker.register('/sw.js').then((registration) => {
                        console.log('Service Worker zarejestrowany: ', registration);
                    }).catch((registrationError) => {
                        console.log('Błąd rejestracji Service Workera: ', registrationError);
                    });
                });
            }
        </script> -->
    </head>
    <body style="font-family: 'Roboto', sans-serif;">
        <div id="app"></div>
    </body>
</html>
