/**
 * Alpine.js component for the Theme Customizer page.
 *
 * Registered as the named component "themeCustomizer" via Alpine.data() so
 * the blade template can reference it with a clean `x-data="themeCustomizer"`
 * attribute rather than inlining all logic in the HTML.
 *
 * All theme read/write operations delegate to the ThemeEngine global (loaded
 * by theme-engine.js), keeping this file focused on UI state and Alpine
 * reactivity. This script must load after theme-engine.js and before Alpine
 * boots — it hooks into the "alpine:init" event to guarantee that timing.
 *
 * @see public/js/theme-engine.js
 */
document.addEventListener('alpine:init', () => {

    /**
     * Registers the "themeCustomizer" Alpine component.
     *
     * The factory function returns the component's reactive data object and
     * all methods used by the preferences.blade.php template. Alpine calls
     * this factory once per `x-data="themeCustomizer"` element it encounters.
     */
    Alpine.data('themeCustomizer', () => ({

        /** @type {string} Key of the currently active theme (e.g. "cyberpunk"). */
        theme: ThemeEngine.getTheme(),

        /** @type {'dark'|'light'} Currently active color mode. */
        mode: ThemeEngine.getMode(),

        /**
         * Live map of CSS custom property → color value for the active theme.
         * Populated by `init()` and updated whenever the theme changes or an
         * override is edited, driving the color picker grid in the template.
         *
         * @type {Object.<string, string>}
         */
        overrides: {},

        /** @type {string} Text of the currently visible toast notification, or empty string. */
        toast: '',

        /** @type {number|null} setTimeout handle used to auto-dismiss the toast. */
        toastTimer: null,

        /**
         * Alpine lifecycle hook — runs once after the component is mounted.
         * Loads the effective overrides for the initial theme so the color
         * picker grid is populated on first render.
         */
        init() {
            this.overrides = ThemeEngine.getOverrides(this.theme);
        },

        /**
         * Activates a theme preset selected from the picker cards.
         *
         * Delegates to ThemeEngine.setTheme() (which fires "theme-changed"
         * so CSS files are swapped by app.blade.php), then syncs local
         * Alpine state — theme key, mode, and the overrides grid — so the
         * UI reflects the new selection immediately.
         *
         * @param {string} key - Theme identifier to activate (e.g. "lcars").
         */
        selectPreset(key) {
            ThemeEngine.setTheme(key);
            this.theme = key;
            this.mode = ThemeEngine.getMode();
            this.overrides = ThemeEngine.getOverrides(key);
        },

        /**
         * Switches the active color mode (dark/light) for themes that support
         * both. Delegates to ThemeEngine.setMode() (which toggles the `dark`
         * class on `<html>` for Tailwind) and syncs Alpine state.
         *
         * @param {'dark'|'light'} m - The mode to activate.
         */
        setMode(m) {
            ThemeEngine.setMode(m);
            this.mode = m;
        },

        /**
         * Handles a change from one of the color `<input type="color">`
         * pickers. Persists the new value via ThemeEngine (localStorage +
         * immediate CSS variable update) and syncs the local `overrides` map
         * so Alpine re-renders the label style reactively.
         *
         * @param {string} prop  - CSS custom property name (e.g. "--sr-neon-cyan").
         * @param {string} value - New hex color value (e.g. "#ff00ff").
         */
        updateOverride(prop, value) {
            ThemeEngine.setOverride(prop, value, this.theme);
            this.overrides[prop] = value;
        },

        /**
         * Resets all user-saved color overrides for the active theme back to
         * the built-in defaults. Delegates to ThemeEngine.resetOverrides()
         * (which removes the localStorage entry and re-applies defaults to
         * the document), then refreshes the local `overrides` map and shows
         * a confirmation toast.
         */
        resetOverrides() {
            ThemeEngine.resetOverrides(this.theme);
            this.overrides = ThemeEngine.getOverrides(this.theme);
            this.showToast('Overrides reset to defaults');
        },

        /**
         * Exports the full theme configuration (active theme, mode, and all
         * user overrides across every preset) as a JSON string and copies it
         * to the clipboard. Falls back to triggering a file download if the
         * Clipboard API is unavailable or denied.
         *
         * Shows a toast confirming the action either way.
         */
        exportConfig() {
            const json = ThemeEngine.exportJSON();
            navigator.clipboard.writeText(json).then(() => {
                this.showToast('Config copied to clipboard');
            }).catch(() => {
                const blob = new Blob([json], { type: 'application/json' });
                const a = document.createElement('a');
                a.href = URL.createObjectURL(blob);
                a.download = 'chatprojects-theme.json';
                a.click();
                this.showToast('Config downloaded');
            });
        },

        /**
         * Opens a hidden `<input type="file">` dialog so the user can select
         * a previously exported JSON config file. On selection the file is
         * read as text and passed to ThemeEngine.importJSON(), which applies
         * the stored theme, mode, and overrides. Alpine state is synced from
         * ThemeEngine after a successful import.
         *
         * Shows a toast indicating success or failure.
         */
        importConfig() {
            const input = document.createElement('input');
            input.type = 'file';
            input.accept = '.json';
            input.onchange = (e) => {
                const file = e.target.files[0];
                if (!file) return;
                const reader = new FileReader();
                reader.onload = (ev) => {
                    if (ThemeEngine.importJSON(ev.target.result)) {
                        this.theme = ThemeEngine.getTheme();
                        this.mode = ThemeEngine.getMode();
                        this.overrides = ThemeEngine.getOverrides(this.theme);
                        this.showToast('Config imported successfully');
                    } else {
                        this.showToast('Import failed — invalid JSON');
                    }
                };
                reader.readAsText(file);
            };
            input.click();
        },

        /**
         * Displays a brief toast notification at the bottom-right of the
         * screen with the given message. Any existing toast is replaced and
         * the auto-dismiss timer is reset to 2.5 seconds.
         *
         * @param {string} msg - Message text to display in the toast.
         */
        showToast(msg) {
            this.toast = msg;
            clearTimeout(this.toastTimer);
            this.toastTimer = setTimeout(() => { this.toast = ''; }, 2500);
        },

        /**
         * Returns an array of preset descriptor objects for all registered
         * themes, each augmented with its key for use in the `x-for` template
         * loop that renders the preset picker cards.
         *
         * @returns {{ key: string, name: string, icon: string, description: string, swatches: string[], supportsModes: boolean }[]}
         */
        presets() {
            return ThemeEngine.getPresetKeys().map(k => ({
                key: k,
                ...ThemeEngine.getPreset(k),
            }));
        },

        /**
         * Returns the `overrides` map as an array of `[prop, value]` pairs,
         * suitable for use in an Alpine `x-for` loop over the color picker
         * grid.
         *
         * @returns {[string, string][]} Array of [cssProperty, colorValue] tuples.
         */
        overrideEntries() {
            return Object.entries(this.overrides);
        },

        /**
         * Returns the preset metadata object for the currently active theme.
         * Used by the template to check `supportsModes` for showing/hiding
         * the light/dark toggle, among other conditional rendering.
         *
         * @returns {Object} The active theme's preset descriptor, or an empty object.
         */
        currentPreset() {
            return ThemeEngine.getPreset(this.theme) || {};
        },

        /**
         * Converts a CSS hex color string to an [R, G, B] integer array.
         * Accepts both 3-digit shorthand (#abc) and 6-digit full (#aabbcc)
         * notation. Returns [128, 128, 128] (mid-grey) for any input that
         * cannot be parsed, ensuring callers always receive a valid tuple.
         *
         * Used internally by `contrastBg()`.
         *
         * @param {string} hex - Hex color string (e.g. "#00ffff" or "#0ff").
         * @returns {[number, number, number]} RGB components in the range 0–255.
         */
        hexToRgb(hex) {
            if (!hex || typeof hex !== 'string') return [128, 128, 128];
            hex = hex.replace(/^#/, '');
            if (hex.length === 3) hex = hex.split('').map(c => c + c).join('');
            if (hex.length !== 6) return [128, 128, 128];
            return [
                parseInt(hex.slice(0, 2), 16),
                parseInt(hex.slice(2, 4), 16),
                parseInt(hex.slice(4, 6), 16),
            ];
        },

        /**
         * Computes a high-contrast background color for a given foreground
         * hex color using the WCAG relative luminance formula. Returns a
         * near-black background for bright/light colors and a near-white
         * background for dark colors, ensuring the foreground color always
         * reads clearly against it.
         *
         * Used to style the Cyberpunk color override labels so each label
         * is rendered in its own override color with a guaranteed readable
         * background.
         *
         * @see https://stackoverflow.com/questions/21290669/auto-contrast-font-color-to-background
         *
         * @param {string} hex - Hex color string of the foreground/label text.
         * @returns {string} CSS rgba() value for the contrasting background.
         */
        contrastBg(hex) {
            if (!hex || !hex.startsWith('#')) return 'transparent';
            const [r, g, b] = this.hexToRgb(hex);
            const [rs, gs, bs] = [r, g, b].map(c => {
                c /= 255;
                return c <= 0.03928 ? c / 12.92 : Math.pow((c + 0.055) / 1.055, 2.4);
            });
            const lum = 0.2126 * rs + 0.7152 * gs + 0.0722 * bs;
            return lum > 0.179 ? 'rgba(0,0,0,0.82)' : 'rgba(255,255,255,0.88)';
        },
    }));
});
