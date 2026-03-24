/**
 * ChatProjects Theme Engine
 *
 * Wraps the existing 5-theme CSS system with a unified API,
 * adds preset metadata for the customizer UI, and supports
 * per-theme custom CSS variable overrides stored in localStorage.
 *
 * Themes are applied by writing CSS custom properties directly onto
 * <html> via style.setProperty(). CSS file toggling (enabling/disabling
 * <link> tags) is handled separately by app.blade.php, which listens
 * for the "theme-changed" CustomEvent fired here.
 *
 * @namespace ThemeEngine
 */
(function () {
    'use strict';

    /* ─── Theme Presets ─────────────────────────────────────────── */

    /**
     * Static registry of all available themes.
     *
     * Each entry describes the theme's display metadata (name, icon,
     * description), which color modes it supports, a set of swatch colors
     * shown in the customizer picker cards, and a map of CSS custom
     * properties that are written to <html> when the theme is active.
     *
     * @type {Object.<string, {
     *   name: string,
     *   description: string,
     *   icon: string,
     *   defaultMode: 'dark'|'light',
     *   supportsModes: boolean,
     *   swatches: string[],
     *   defaultOverrides: Object.<string, string>
     * }>}
     */
    const PRESETS = {
        cyberpunk: {
            name: 'Cyberpunk',
            description: 'Satellite Reign–inspired neon HUD with scan-line grid',
            icon: '🌃',
            defaultMode: 'dark',
            supportsModes: false,
            swatches: ['#050a10', '#00ffff', '#00aaff', '#ffaa00', '#ff3333', '#e0f0ff'],
            defaultOverrides: {
                '--sr-bg-dark': '#050a10',
                '--sr-neon-cyan': '#00ffff',
                '--sr-neon-blue': '#00aaff',
                '--sr-neon-orange': '#ffaa00',
                '--sr-neon-red': '#ff3333',
                '--sr-text-main': '#e0f0ff',
                '--sr-text-muted': '#607080',
            },
        },
        lcars: {
            name: 'LCARS',
            description: 'Star Trek–style panel interface with bold stripes',
            icon: '🖖',
            defaultMode: 'dark',
            supportsModes: false,
            swatches: ['#000000', '#ff9900', '#cc0000', '#9933cc', '#3366cc', '#ff9900'],
            defaultOverrides: {
                '--lcars-bg': '#000000',
                '--lcars-orange': '#ff9900',
                '--lcars-red': '#cc0000',
                '--lcars-purple': '#9933cc',
                '--lcars-blue': '#3366cc',
                '--lcars-text': '#ff9900',
            },
        },
        foundation: {
            name: 'Foundation',
            description: 'Clean Zurb Foundation layout with dark slate palette',
            icon: '🏗️',
            defaultMode: 'dark',
            supportsModes: true,
            swatches: ['#0f172a', '#60a5fa', '#e2e8f0', '#334155', '#1e293b', '#f1f5f9'],
            defaultOverrides: {},
        },
        'writers-room': {
            name: 'Writers Room',
            description: 'Clean indigo-accented design from stories.elasticgun.com',
            icon: '✍️',
            defaultMode: 'dark',
            supportsModes: true,
            swatches: ['#111827', '#818cf8', '#1f2937', '#9ca3af', '#374151', '#f3f4f6'],
            defaultOverrides: {
                '--wr-accent': '#818cf8',
                '--wr-accent-dark': '#6366f1',
            },
        },
    };

    /* ─── State ─────────────────────────────────────────────────── */
    let currentTheme = localStorage.getItem('theme') || 'cyberpunk';
    let currentMode = localStorage.getItem('mode') || 'dark';

    /**
     * Loads the user's saved CSS variable overrides for a given theme from
     * localStorage. Overrides are stored as a JSON object keyed by CSS
     * custom property name (e.g. `{ "--sr-neon-cyan": "#ff00ff" }`).
     *
     * Returns an empty object if nothing is saved or if the stored value
     * cannot be parsed (e.g. corrupted localStorage entry).
     *
     * @param {string} themeKey - The theme identifier (e.g. "cyberpunk").
     * @returns {Object.<string, string>} Map of CSS property → color value.
     */
    function loadOverrides(themeKey) {
        try {
            const raw = localStorage.getItem('theme-overrides-' + themeKey);
            return raw ? JSON.parse(raw) : {};
        } catch { return {}; }
    }

    /**
     * Persists a map of CSS variable overrides for a given theme to
     * localStorage so they survive page reloads.
     *
     * The object is serialised as JSON under the key
     * `theme-overrides-<themeKey>`.
     *
     * @param {string} themeKey - The theme identifier (e.g. "cyberpunk").
     * @param {Object.<string, string>} overrides - Map of CSS property → color value.
     */
    function saveOverrides(themeKey, overrides) {
        localStorage.setItem('theme-overrides-' + themeKey, JSON.stringify(overrides));
    }

    /* ─── Core Apply ─────────────────────────── */

    /**
     * Writes all effective CSS variable overrides for a theme onto the
     * document root (`<html>`), making them immediately visible in the
     * browser.
     *
     * The effective set is the theme's `defaultOverrides` merged with any
     * user-saved overrides from localStorage — user values win on conflict.
     * If the theme key is unknown the function is a no-op.
     *
     * @param {string} themeKey - The theme identifier (e.g. "cyberpunk").
     */
    function applyOverrides(themeKey) {
        const preset = PRESETS[themeKey];
        if (!preset) return;
        const merged = { ...preset.defaultOverrides, ...loadOverrides(themeKey) };
        const root = document.documentElement;
        Object.entries(merged).forEach(([prop, val]) => {
            root.style.setProperty(prop, val);
        });
    }

    /**
     * Removes every CSS custom property that ThemeEngine has ever written to
     * `<html>`, covering both the built-in defaults across all presets and
     * any user-saved overrides from localStorage.
     *
     * Called before switching themes to ensure stale variables from the
     * previous theme do not bleed into the new one.
     */
    function clearOverrideStyles() {
        const root = document.documentElement;
        // Remove all custom properties previously set by us
        Object.values(PRESETS).forEach(p => {
            Object.keys(p.defaultOverrides).forEach(prop => {
                root.style.removeProperty(prop);
            });
        });
        // Also clear any user-set ones
        const all = Object.keys(PRESETS).reduce((acc, k) => {
            return { ...acc, ...loadOverrides(k) };
        }, {});
        Object.keys(all).forEach(prop => root.style.removeProperty(prop));
    }

    /* ─── Public API ─────────────────────────── */
    const ThemeEngine = {

        /** The full PRESETS registry, exposed for external inspection. */
        PRESETS,

        /**
         * Returns the key of the currently active theme.
         *
         * @returns {string} Theme key (e.g. "cyberpunk").
         */
        getTheme() { return currentTheme; },

        /**
         * Returns the currently active color mode.
         *
         * @returns {'dark'|'light'} The current mode.
         */
        getMode() { return currentMode; },

        /**
         * Returns the preset metadata object for a given theme key,
         * or null if the key is not registered.
         *
         * @param {string} key - Theme identifier.
         * @returns {Object|null} The preset object, or null.
         */
        getPreset(key) { return PRESETS[key] || null; },

        /**
         * Returns an array of all registered theme keys in definition order.
         *
         * @returns {string[]} Array of theme identifiers.
         */
        getPresetKeys() { return Object.keys(PRESETS); },

        /**
         * Returns the effective CSS variable overrides for a theme — the
         * theme's built-in defaults merged with any user customisations saved
         * in localStorage. User values take precedence on conflict.
         *
         * Falls back to the active theme if no key is provided.
         *
         * @param {string} [themeKey] - Theme identifier; defaults to the active theme.
         * @returns {Object.<string, string>} Merged map of CSS property → color value.
         */
        getOverrides(themeKey) {
            const preset = PRESETS[themeKey || currentTheme];
            if (!preset) return {};
            return { ...preset.defaultOverrides, ...loadOverrides(themeKey || currentTheme) };
        },

        /**
         * Saves a single CSS variable override for a theme and, if the theme
         * is currently active, applies the change to the document immediately
         * so the user sees the result in real time.
         *
         * @param {string} prop     - CSS custom property name (e.g. "--sr-neon-cyan").
         * @param {string} value    - CSS color value (e.g. "#ff00ff").
         * @param {string} [themeKey] - Theme to update; defaults to the active theme.
         */
        setOverride(prop, value, themeKey) {
            const key = themeKey || currentTheme;
            const overrides = loadOverrides(key);
            overrides[prop] = value;
            saveOverrides(key, overrides);
            if (key === currentTheme) {
                document.documentElement.style.setProperty(prop, value);
            }
        },

        /**
         * Deletes all user-saved overrides for a theme from localStorage,
         * reverting it to its built-in defaults. If the theme is currently
         * active, the defaults are re-applied to the document immediately.
         *
         * @param {string} [themeKey] - Theme to reset; defaults to the active theme.
         */
        resetOverrides(themeKey) {
            const key = themeKey || currentTheme;
            localStorage.removeItem('theme-overrides-' + key);
            if (key === currentTheme) {
                clearOverrideStyles();
                applyOverrides(key);
            }
        },

        /**
         * Switches the active theme. This updates module state, persists the
         * choice to localStorage, clears stale CSS variables, applies the new
         * theme's overrides to the document, and fires a "theme-changed"
         * CustomEvent so app.blade.php can enable/disable the correct CSS
         * stylesheet <link> tags.
         *
         * For themes that don't support multiple modes (e.g. Cyberpunk, LCARS)
         * the mode is forced to the preset's `defaultMode`.
         *
         * Is a no-op if `newTheme` is not a registered preset key.
         *
         * @param {string} newTheme - The theme key to activate (e.g. "lcars").
         */
        setTheme(newTheme) {
            if (!PRESETS[newTheme]) return;
            clearOverrideStyles();
            currentTheme = newTheme;
            localStorage.setItem('theme', newTheme);

            // If the preset only supports one mode, force it
            if (!PRESETS[newTheme].supportsModes) {
                currentMode = PRESETS[newTheme].defaultMode;
                localStorage.setItem('mode', currentMode);
            }

            // Apply the new overrides
            applyOverrides(newTheme);

            // Fire event so the existing app.blade.php script handles CSS file toggling
            window.dispatchEvent(new CustomEvent('theme-changed', {
                detail: { theme: newTheme, mode: currentMode }
            }));
        },

        /**
         * Switches the active color mode (dark/light), persists it to
         * localStorage, toggles the `dark` class on `<html>` for Tailwind's
         * dark-mode utilities, and fires a "theme-changed" CustomEvent.
         *
         * @param {'dark'|'light'} newMode - The mode to activate.
         */
        setMode(newMode) {
            currentMode = newMode;
            localStorage.setItem('mode', newMode);

            if (newMode === 'dark') {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }

            window.dispatchEvent(new CustomEvent('theme-changed', {
                detail: { theme: currentTheme, mode: newMode }
            }));
        },

        /**
         * Serialises the current theme configuration — active theme, active
         * mode, and all user-saved overrides for every preset — into a
         * pretty-printed JSON string suitable for clipboard copy or file
         * download.
         *
         * Only themes that have at least one user override are included in
         * the `overrides` object to keep the export compact.
         *
         * @returns {string} JSON configuration string.
         */
        exportJSON() {
            const data = {
                theme: currentTheme,
                mode: currentMode,
                overrides: {},
            };
            Object.keys(PRESETS).forEach(k => {
                const o = loadOverrides(k);
                if (Object.keys(o).length) data.overrides[k] = o;
            });
            return JSON.stringify(data, null, 2);
        },

        /**
         * Parses a JSON configuration string (as produced by `exportJSON`)
         * and applies it: overrides are written to localStorage, mode is
         * switched, and then the theme is activated (which also fires
         * "theme-changed" and re-applies CSS variables).
         *
         * Returns false and logs an error if the string is not valid JSON or
         * the theme key it references is not registered.
         *
         * @param {string} json - JSON configuration string to import.
         * @returns {boolean} True on success, false on failure.
         */
        importJSON(json) {
            try {
                const data = JSON.parse(json);
                if (data.overrides) {
                    Object.entries(data.overrides).forEach(([k, v]) => {
                        saveOverrides(k, v);
                    });
                }
                if (data.mode) {
                    ThemeEngine.setMode(data.mode);
                }
                if (data.theme && PRESETS[data.theme]) {
                    ThemeEngine.setTheme(data.theme);
                }
                return true;
            } catch (e) {
                console.error('Theme import failed:', e);
                return false;
            }
        },
    };

    // Apply overrides on load so user customizations take effect immediately
    applyOverrides(currentTheme);

    // Expose globally
    window.ThemeEngine = ThemeEngine;
})();
