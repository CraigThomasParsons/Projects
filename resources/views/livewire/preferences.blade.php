<div class="preferences-page" x-data="{
    theme: localStorage.getItem('theme') || 'cyberpunk',
    mode: localStorage.getItem('mode') || 'dark',

    setTheme(newTheme) {
        this.theme = newTheme;
        localStorage.setItem('theme', newTheme);
        this.applyTheme();
    },

    setMode(newMode) {
        this.mode = newMode;
        localStorage.setItem('mode', newMode);
        this.applyTheme();
    },

    applyTheme() {
        // Remove all theme classes from body
        document.body.classList.remove('lcars', 'cyberpunk', 'foundation', 'materialize', 'light', 'dark');

        // Add selected theme class
        document.body.classList.add(this.theme);

        // Handle Mode (Light/Dark)
        if (['materialize', 'foundation', 'ccdf'].includes(this.theme)) {
            if (this.mode === 'dark') {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
            // Materialize specific handling (keep on body for materialize source css compabibility if needed, but standardizing on html is safer for tailwind)
            if (this.theme === 'materialize') {
                 // Materialize usually uses body classes? leaving strictly for materialize css compat if it relies on it.
                 // But wait, the previous code put it on body.
                 document.body.classList.add(this.mode);
            }
        }

        // Trigger a custom event for other components if needed
        window.dispatchEvent(new CustomEvent('theme-changed', { detail: { theme: this.theme, mode: this.mode } }));
        
        // Force reload css if needed by dispatching to layout listener
        // (Layout listener implementation in app.blade.php will handle CSS swapping)
    }
}" x-init="applyTheme()">

    <div class="container mx-auto p-4">
        <h1 class="text-3xl font-bold mb-6 ml-4 md:ml-0">User Preferences</h1>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="hidden md:block col-span-1"></div> <!-- Spacer to move content right -->
            <div class="col-span-1 md:col-span-2">
            
            <!-- Theme Selection -->
            <div class="card p-6 border rounded-lg shadow-lg bg-opacity-20 bg-gray-800">
                <h2 class="text-xl font-semibold mb-4">Select Theme</h2>

                <div class="space-y-4">
                    <!-- LCARS -->
                    <label class="flex items-center space-x-3 cursor-pointer p-3 rounded hover:bg-white hover:bg-opacity-10 transition">
                        <input type="radio" name="theme" value="lcars" x-model="theme" @change="setTheme('lcars')" class="form-radio h-5 w-5 text-orange-500">
                        <span>LCARS Inspired</span>
                    </label>

                    <!-- Cyberpunk -->
                    <label class="flex items-center space-x-3 cursor-pointer p-3 rounded hover:bg-white hover:bg-opacity-10 transition">
                        <input type="radio" name="theme" value="cyberpunk" x-model="theme" @change="setTheme('cyberpunk')" class="form-radio h-5 w-5 text-blue-500">
                        <span>Cyberpunk (Default)</span>
                    </label>

                    <!-- Foundation -->
                    <label class="flex items-center space-x-3 cursor-pointer p-3 rounded hover:bg-white hover:bg-opacity-10 transition">
                        <input type="radio" name="theme" value="foundation" x-model="theme" @change="setTheme('foundation')" class="form-radio h-5 w-5 text-gray-500">
                        <span>Foundation</span>
                    </label>

                    <!-- Materialize -->
                    <label class="flex items-center space-x-3 cursor-pointer p-3 rounded hover:bg-white hover:bg-opacity-10 transition">
                        <input type="radio" name="theme" value="materialize" x-model="theme" @change="setTheme('materialize')" class="form-radio h-5 w-5 text-teal-500">
                        <span>Materialize CSS</span>
                    </label>

                    <!-- CCDF (Standard) -->
                    <label class="flex items-center space-x-3 cursor-pointer p-3 rounded hover:bg-white hover:bg-opacity-10 transition">
                        <input type="radio" name="theme" value="ccdf" x-model="theme" @change="setTheme('ccdf')" class="form-radio h-5 w-5 text-indigo-500">
                        <span>CCDF (Standard)</span>
                    </label>
                </div>
            </div>

            <!-- Mode Selection (Light/Dark) -->
            <div class="card p-6 border rounded-lg shadow-lg bg-opacity-20 bg-gray-800" x-show="['materialize', 'foundation', 'ccdf'].includes(theme)" x-transition>
                <h2 class="text-xl font-semibold mb-4">Select Mode</h2>
                <div class="space-y-4">
                    <label class="flex items-center space-x-3 cursor-pointer p-3 rounded hover:bg-white hover:bg-opacity-10 transition">
                        <input type="radio" name="mode" value="light" x-model="mode" @change="setMode('light')" class="form-radio h-5 w-5 text-yellow-500">
                        <span>Light Mode</span>
                    </label>

                    <label class="flex items-center space-x-3 cursor-pointer p-3 rounded hover:bg-white hover:bg-opacity-10 transition">
                        <input type="radio" name="mode" value="dark" x-model="mode" @change="setMode('dark')" class="form-radio h-5 w-5 text-gray-800">
                        <span>Dark Mode</span>
                    </label>
                </div>
            </div>
        </div>



        <div class="mt-8 text-center">
            <a href="{{ route('projects.index') }}" class="button secondary">Back to Projects</a>
        </div>
        </div>
    </div>
</div>
