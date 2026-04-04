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
    <link id="theme-materialize" rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css" disabled>
    <link id="theme-foundation" rel="stylesheet" href="https://cdn.jsdelivr.net/npm/foundation-sites@6.7.5/dist/css/foundation.min.css" disabled>
    <link id="theme-foundation-tailwind" rel="stylesheet" href="{{ asset('css/foundation-tailwind.css') }}" disabled>
    <link id="theme-lcars" rel="stylesheet" href="{{ asset('css/lcars-tailwind.css') }}" disabled>
    <link id="theme-cyberpunk-overrides" rel="stylesheet" href="{{ asset('css/cyberpunk-tailwind.css') }}" disabled>
    <link id="theme-writers-room" rel="stylesheet" href="{{ asset('css/writers-room-tailwind.css') }}" disabled>
    <link rel="stylesheet" href="{{ asset('css/theme-customizer.css') }}">

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
    <script src="{{ asset('js/theme-engine.js') }}"></script>
    <script src="{{ asset('js/theme-customizer-component.js') }}" defer></script>

    <!-- Global LCARS/Materialize NavBar -->
    <div id="global-navbar" style="display: none;">
        <nav>
            <div class="nav-wrapper">
                <a href="{{ route('projects.index') }}" class="brand-logo" style="padding-left: 20px;">LCARS-OS</a>
                <ul id="nav-mobile" class="right hide-on-med-and-down" style="display: flex; height: 100%;">
                    <li><a href="{{ route('projects.index') }}">PROJECTS</a></li>
                    <li><a href="{{ route('registry') }}">REGISTRY</a></li>
                    <li><a href="{{ route('team.index') }}">TEAM</a></li>
                    <li><a href="{{ route('preferences') }}">PREFERENCES</a></li>
                </ul>
            </div>
        </nav>
    </div>

    @hasSection('content')
        @yield('content')
    @else
        {{ $slot }}
    @endif

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const body = document.body;
            
            // Function to apply theme
            const applyTheme = () => {
                const theme = localStorage.getItem('theme') || 'cyberpunk';
                const mode = localStorage.getItem('mode') || 'dark';

                // Reset Body Classes involved in theming
                body.classList.remove('lcars', 'cyberpunk', 'foundation', 'writers-room', 'light', 'dark');

                // Enable/Disable CSS Files
                const materializeCss = document.getElementById('theme-materialize');
                const lcarsCss = document.getElementById('theme-lcars');
                const foundationCss = document.getElementById('theme-foundation');
                const foundationTailwindCss = document.getElementById('theme-foundation-tailwind');
                const cyberpunkOverridesCss = document.getElementById('theme-cyberpunk-overrides');
                const writersRoomCss = document.getElementById('theme-writers-room');

                // Helper to disable all first
                [materializeCss, lcarsCss, foundationCss, foundationTailwindCss, cyberpunkOverridesCss, writersRoomCss].forEach(link => {
                    if(link) link.disabled = true;
                });

                // Apply Logic
                // Tailwind Dark Mode Logic
                if (mode === 'dark') {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }

                // Persist theme in a cookie so PHP/Livewire can read it server-side.
                document.cookie = 'theme=' + theme + '; path=/; max-age=31536000; SameSite=Lax';

                body.classList.add(theme);

                if (theme === 'lcars') {
                    lcarsCss.disabled = false;
                    if(materializeCss) materializeCss.disabled = false;
                    const nav = document.getElementById('global-navbar');
                    if(nav) nav.style.display = 'block';
                } else {
                    const nav = document.getElementById('global-navbar');
                    if(nav) nav.style.display = 'none';

                    if (theme === 'foundation') {
                        foundationCss.disabled = false;
                        foundationTailwindCss.disabled = false;
                    } else if (theme === 'writers-room') {
                        writersRoomCss.disabled = false;
                    } else {
                        // Cyberpunk is default/base styles in app.css
                        // Enable overrides for fixes
                        cyberpunkOverridesCss.disabled = false;
                    }
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

    @livewireScripts
</body>
</html>
