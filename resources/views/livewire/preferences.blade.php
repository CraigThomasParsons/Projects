<div class="tc-page" x-data="themeCustomizer">
    {{-- Back --}}
    <a href="{{ route('projects.index') }}" class="tc-back">&larr; Back to Projects</a>

    <h1 class="h2">Theme Customizer</h1>

    <div class="tc-columns">
        {{-- ═══ Left: Controls ═══ --}}
        <div>
            {{-- Preset Cards --}}
            <div class="tc-section">
                <h3>Theme Presets</h3>
                <div class="tc-presets">
                    <template x-for="p in presets()" :key="p.key">
                        <div class="tc-preset-card"
                             :class="{ 'active': theme === p.key }"
                             @click="selectPreset(p.key)">
                            <div class="tc-card-header">
                                <span class="tc-card-icon" x-text="p.icon"></span>
                                <span class="tc-card-name" x-text="p.name"></span>
                            </div>
                            <div class="tc-card-desc" x-text="p.description"></div>
                            <div class="tc-swatch-row">
                                <template x-for="(c, i) in p.swatches" :key="i">
                                    <div class="tc-swatch" :style="'background:' + c"></div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Mode Toggle --}}
            <div class="tc-section" x-show="currentPreset().supportsModes" x-transition>
                <h3>Color Mode</h3>
                <div class="tc-mode-toggle">
                    <button :class="{ 'active': mode === 'light' }" @click="setMode('light')">
                        ☀️ Light
                    </button>
                    <button :class="{ 'active': mode === 'dark' }" @click="setMode('dark')">
                        🌙 Dark
                    </button>
                </div>
            </div>

            {{-- Color Pickers (for themes with CSS variable overrides) --}}
            <div class="tc-section" x-show="overrideEntries().length > 0" x-transition>
                <h3>Color Overrides</h3>
                <div class="tc-color-grid">
                    <template x-for="[prop, val] in overrideEntries()" :key="prop">
                        <div class="tc-color-item" :class="{ 'tc-cp-item': theme === 'cyberpunk' }">
                            <input type="color"
                                   :value="val"
                                   @input="updateOverride(prop, $event.target.value)">
                            <label x-text="prop.replace(/^--(?:sr-|lcars-|wr-)?/, '')"
                                   :style="theme === 'cyberpunk'
                                       ? 'color:' + val + ';background:' + contrastBg(val) + ';padding:1px 7px;border-radius:4px;transition:background 0.2s,color 0.2s'
                                       : ''"></label>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Actions --}}
            <div class="tc-actions">
                <button class="tc-btn" @click="exportConfig()">
                    📋 Export Config
                </button>
                <button class="tc-btn" @click="importConfig()">
                    📂 Import Config
                </button>
                <button class="tc-btn danger"
                        @click="resetOverrides()"
                        x-show="overrideEntries().length > 0">
                    🔄 Reset Overrides
                </button>
            </div>

            <livewire:project-aliases-manager />
        </div>

        {{-- ═══ Right: Live Preview ═══ --}}
        <div class="tc-preview">
            <h3>Live Preview</h3>
            <div class="tc-preview-frame">
                {{-- Simulated header --}}
                <div class="tc-preview-header">
                    <strong style="font-size: 0.85rem;">📬 ChatProjects</strong>
                    <div class="nav-links">
                        <span>Projects</span>
                        <span>Preferences</span>
                    </div>
                </div>

                {{-- Simulated body --}}
                <div class="tc-preview-body">
                    <div class="tc-preview-card">
                        <div class="card-title">My Laravel App</div>
                        <div class="card-sub">3 conversations · Updated 2h ago</div>
                    </div>
                    <div class="tc-preview-card">
                        <div class="card-title">RTS Colony Generator</div>
                        <div class="card-sub">7 conversations · Updated 5d ago</div>
                    </div>
                    <div class="tc-preview-btns">
                        <span class="btn-mock">+ New Project</span>
                        <span class="btn-mock">Sync All</span>
                    </div>
                    <input class="tc-preview-input" type="text" placeholder="Search projects..." disabled>
                </div>
            </div>
        </div>
    </div>

    {{-- Toast --}}
    <div class="tc-toast" x-show="toast" x-transition x-text="toast"></div>
</div>
