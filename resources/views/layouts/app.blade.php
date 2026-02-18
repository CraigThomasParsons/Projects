<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>ChatProjects</title>
    <link rel="icon" href="/favicons/favicon.ico" sizes="any">
    <link rel="icon" type="image/svg+xml" href="/favicons/favicon.svg">
    <link rel="apple-touch-icon" sizes="180x180" href="/favicons/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicons/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicons/favicon-16x16.png">
    <link rel="manifest" href="/favicons/site.webmanifest">
    <link rel="mask-icon" href="/favicons/safari-pinned-tab.svg" color="#2563eb">
    <meta name="msapplication-TileColor" content="#2563eb">
    <meta name="msapplication-config" content="/favicons/browserconfig.xml">
    <meta name="theme-color" content="#2563eb">

    <!-- Theme Styles -->
    <link id="theme-foundation" rel="stylesheet" href="https://cdn.jsdelivr.net/npm/foundation-sites@6.7.5/dist/css/foundation.min.css" disabled>
    <link id="theme-foundation-tailwind" rel="stylesheet" href="{{ asset('css/foundation-tailwind.css') }}" disabled>
    <link id="theme-materialize" rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css" disabled>
    <link id="theme-materialize-tailwind" rel="stylesheet" href="{{ asset('css/materialize-tailwind.css') }}" disabled>
    <link id="theme-materialize-dark" rel="stylesheet" href="{{ asset('css/materialize-dark.css') }}" disabled>
    <link id="theme-lcars" rel="stylesheet" href="{{ asset('css/lcars-tailwind.css') }}" disabled>
    <link id="theme-cyberpunk-overrides" rel="stylesheet" href="{{ asset('css/cyberpunk-tailwind.css') }}" disabled>
    <link id="theme-ccdf" rel="stylesheet" href="{{ asset('css/ccdf.css') }}" disabled>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @livewireStyles
    <style>
        /* Base transition for smooth theme switching */
        body {
            transition: background-color 0.3s ease, color 0.3s ease;
        }
    </style>
</head>
<body class="antialiased">
    {{ $slot }}

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const body = document.body;
            
            // Function to apply theme
            const applyTheme = () => {
                const theme = localStorage.getItem('theme') || 'cyberpunk';
                const mode = localStorage.getItem('mode') || 'dark';

                // Reset Body Classes involved in theming
                body.classList.remove('lcars', 'cyberpunk', 'foundation', 'materialize', 'light', 'dark');

                // Enable/Disable CSS Files
                const lcarsCss = document.getElementById('theme-lcars');
                const foundationCss = document.getElementById('theme-foundation');
                const foundationTailwindCss = document.getElementById('theme-foundation-tailwind');
                const materializeCss = document.getElementById('theme-materialize');
                const materializeTailwindCss = document.getElementById('theme-materialize-tailwind');
                const materializeDarkCss = document.getElementById('theme-materialize-dark');
                const cyberpunkOverridesCss = document.getElementById('theme-cyberpunk-overrides');
                const ccdfCss = document.getElementById('theme-ccdf');
                
                // Helper to disable all first
                [lcarsCss, foundationCss, foundationTailwindCss, materializeCss, materializeTailwindCss, materializeDarkCss, cyberpunkOverridesCss, ccdfCss].forEach(link => {
                    if(link) link.disabled = true;
                });

                // Apply Logic
                // Tailwind Dark Mode Logic
                if (mode === 'dark') {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }

                body.classList.add(theme);

                if (theme === 'lcars') {
                    lcarsCss.disabled = false;
                } else if (theme === 'foundation') {
                    foundationCss.disabled = false;
                    foundationTailwindCss.disabled = false;
                } else if (theme === 'materialize') {
                    materializeCss.disabled = false;
                    materializeTailwindCss.disabled = false;
                    body.classList.add(mode);
                    if (mode === 'dark') {
                        materializeDarkCss.disabled = false;
                    }
                } else if (theme === 'ccdf') {
                    ccdfCss.disabled = false;
                } else {
                    // Cyberpunk is default/base styles in app.css
                    // Enable overrides for fixes
                    cyberpunkOverridesCss.disabled = false;
                }
            };
            
            // Apply on load
            applyTheme();

            // Listen for changes from Preferences page
            window.addEventListener('theme-changed', (e) => {
                applyTheme();
            });
        });
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>

    @livewireScripts
</body>
</html>
