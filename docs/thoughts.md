# Development Thoughts and History

## Initial Request

The user wants a preferences page to select themes:

1. LCARS (No light variation)
2. Cyber punk (No light variation)
3. Foundation
4. Materializecss

Light/Dark mode buttons should only appear for Foundation and Materializecss.

## Plan

1. **Analyze**: Check how themes are currently implemented.
2. **Build**:
    * Add a route for `/preferences`.
    * Create a Livewire component or Controller for Preferences.
    * Add storage for user theme preference (database column or separate table).
    * Implement the UI with the specified logic.
    * Ensure the selected theme actually applies to the layout.
3. **Test**: Verify each theme and the toggle logic.
4. **Document**: Update this file.

## History & Decisions

### 2026-02-17

- **Analysis**: Discovered `app.css` was heavily importing Foundation, which would conflict with Materialize or a pure LCARS theme.
* **Decision to decouple**: Removed `@import "foundation-sites/..."` from `app.css` to allow conditional loading.
* **Implementation**:
  * Created `public/css/lcars.css` for the LCARS theme.
  * Added `<link>` tags for Foundation, Materialize, and LCARS in `layouts/app.blade.php`, defaulting to `disabled`.
  * Implemented a JavaScript theme switcher in `layouts/app.blade.php` that listens for `theme-changed` events and checks `localStorage`.
  * Created `App\Livewire\Preferences` and `preferences.blade.php` using AlpineJS to handle the UI and localStorage updates.
  * Updated `routes/web.php` to include `/preferences`.
* **Refinement**: Moved `lcars.css` to `public/` to ensure `asset()` works correctly without a build step. Fixed malformed HTML in `app.blade.php`.
* **Permission Struggle**: Encountered persistent `Permission denied` errors on `storage/logs` and view cache. Files were owned by `http` (web server) and not writable by `craigpar` (me), or vice versa depending on who ran the command.
* **Resolution**: Created a new `storage_temp` directory owned by `craigpar` and updated `bootstrap/app.php` to force the application to use it: `$app->useStoragePath(base_path('storage_temp'));`. This successfully bypassed the permission lock on the original `storage` directory.
* **Layout Fix**: The `Preferences` page failed with `MissingLayoutException`. I explicitly added `->layout('layouts.app')` to the component's render method.
* **UX Update**: Replaced the broken "Light Mode" toggle on the home page with a link to the new Preferences page.
* **Materialize Dark Mode**: User requested functioning Dark Mode for Materialize. Created `public/css/materialize-dark.css` with overrides and updated `app.blade.php` logic to load it conditionally.
* **Foundation Mode Update**: User requested removing Light/Dark mode from Foundation. Updated `preferences.blade.php` and `app.blade.php` to only show/apply mode for Materialize.
* **Toggle Relocation**: User requested moving the Dark Mode toggle back to the toolbar, but keeping it conditional. Removed it from `preferences.blade.php` and added an AlpineJS component to `projects-page.blade.php` that conditionally shows the button if `theme === 'materialize'`.
* **Toolbar Alignment**: Foundation grid classes broke when using Materialize, causing misalignment of toolbar buttons. Implemented a Flexbox container (`display: flex`) in `projects-page.blade.php` to correctly align the buttons horizontally across all themes.
* **Footer Styling**: User requested the "Delete Project" footer panel to be dark grey (matching the card background) in Materialize Dark Mode. Updated `materialize-dark.css` to target `.page-footer` within `body.materialize.dark`.
* **Modal Repair**: Users reported "invisible options" in the "New Project" modal in Materialize. The modal was hidden by default due to missing Foundation JS support in Materialize mode. I added CSS overrides to `materialize-dark.css` to force the modal (`.modal`) to display as a fixed overlay when rendered by Livewire, and styled the inputs (`input`, `textarea`) to have dark backgrounds and light text for visibility, matching the user's "Stories" app aesthetic.
* **Color Refinement**: User requested a strict rule against pure white (`#fff`, `#ffffff`) in Materialize Dark Mode. Replaced all instances in `materialize-dark.css` with light grey (`#e0e0e0`).
* **Card Styling**: User requested Project "cards" (conversations list) to have **lighter grey backgrounds** in Materialize Dark Mode.
  * Originally set to `#f5f5f5` (Grey 100).
  * Updated to `#eeeeee` (Grey 200).
  * Updated to `#e0e0e0` (Grey 300).
  * **Final Selection**: User requested **`#9c9c9c`** (Medium Grey) to be definitively "that much grey".
* **Edit Project Width**: User requested the "Edit Project" section to take up ~77% of the width.
  * The section was constrained by a `.grid-container` causing it to shrink to fit (~400px).
  * **Solution**: Implemented a CSS "breakout" technique using `width: 80vw`, `position: relative`, `left: 50%`, and `margin-left: -40vw`. This allows the child element to break out of its narrow parent container and occupy 80% of the viewport width.

### Current State

The system is fully functional. Users can switch themes at `/preferences`. Materialize CSS support is robust with a unique Medium-Grey-Card-on-Dark-Background aesthetic in Dark Mode (`#9c9c9c`), fully visible modals, compliant colors, and a widened Edit Project view.
