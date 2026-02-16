<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>ChatProjects</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @livewireStyles
</head>
<body class="cyberpunk">
    {{ $slot }}

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const body = document.body;
            
            const updateToggleButtons = (theme) => {
                const buttons = document.querySelectorAll('.theme-toggle');
                buttons.forEach(btn => {
                    if (theme === 'cyberpunk') {
                        btn.textContent = 'Light Mode ☀️';
                    } else {
                        btn.textContent = 'Dark Mode 🌙';
                    }
                });
            };

            // Check local storage
            const currentTheme = localStorage.getItem('theme');
            if (currentTheme === 'light') {
                body.classList.remove('cyberpunk');
                updateToggleButtons('light');
            } else if (currentTheme === 'cyberpunk') {
                body.classList.add('cyberpunk');
                updateToggleButtons('cyberpunk');
            } else {
                // Default is cyberpunk
                updateToggleButtons('cyberpunk');
            }

            // Event Delegation for Theme Toggle
            document.addEventListener('click', (e) => {
                if (e.target && e.target.closest('.theme-toggle')) {
                    if (body.classList.contains('cyberpunk')) {
                        body.classList.remove('cyberpunk');
                        localStorage.setItem('theme', 'light');
                        updateToggleButtons('light');
                    } else {
                        body.classList.add('cyberpunk');
                        localStorage.setItem('theme', 'cyberpunk');
                        updateToggleButtons('cyberpunk');
                    }
                }
            });
        });
    </script>

    @livewireScripts
</body>
</html>
