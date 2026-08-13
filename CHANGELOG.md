# Changelog

All notable changes to the LeadCapture Form Plugin will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.1.1] - 2026-08-12

### Added

- **Tests**: Add a PHPUnit test suite (none existed before), following the `WP_UnitTestCase`-based
  convention used across the rest of the SilverAssist WordPress plugin portfolio — real WordPress test
  environment via `scripts/install-wp-tests.sh`, not mocks. Covers `Plugin`, `ShortcodeHandler`,
  `LeadCaptureFormBlock`, `LeadCaptureFormAdmin`, and `WidgetsLoader`: singleton identity, deprecated
  `get_instance()` forwarding, `get_priority()`/`should_load()` gating, hook registration, and both
  embed/popup-mode shortcode rendering. Wired into CI (`quality-checks.yml`) with a MySQL service and
  WordPress Test Suite installation step, reusing the existing `wordpress-version` matrix

## [1.1.0] - 2026-08-12

### Changed

- **Plugin bootstrap**: the monolithic `LeadCapture_Form` class (previously
  living directly in the main plugin file, handling hooks, shortcode
  rendering, admin/updater init, and Elementor detection all in one place)
  is replaced by `Plugin`, extending `silverassist/wp-plugin-kernel`'s
  `AbstractPlugin`. Its responsibilities are decomposed into focused
  `LoadableInterface` components:
  - `ShortcodeHandler` (new) — `[leadcapture_form]` shortcode registration
    and conditional asset enqueuing, extracted verbatim from the old class.
  - `LeadCaptureFormBlock` — Gutenberg block, unchanged behavior.
  - `Elementor\WidgetsLoader` — Elementor widget registration, unchanged
    behavior. `should_load()` now reflects the same
    `did_action('elementor/loaded')` check the old constructor used
    internally, instead of a manual `require_once` guard in the main file.
  - `LeadCaptureFormAdmin` — admin settings/updater UI. No longer
    constructor-injected an `Updater` instance; reads it from
    `Plugin::instance()->get_updater()` instead, same as the other
    plugins in this rollout.
- **Bootstrap hook**: the plugin now initializes on `init` instead of
  `plugins_loaded`. `WidgetsLoader::should_load()` depends on
  `did_action('elementor/loaded')`, and Elementor typically fires that
  from its own `plugins_loaded` callback — evaluating the check during
  this plugin's `plugins_loaded` callback would risk running before
  Elementor announces itself, depending on plugin load order across a
  site. By `init`, every plugin's `plugins_loaded` has already run. None
  of this plugin's components need `plugins_loaded`-specific timing.
- `includes/elementor/widgets/LeadCaptureFormWidget.php` now calls
  `ShortcodeHandler::instance()->render_shortcode()` instead of the
  removed `LeadCapture_Form::get_instance()->render_shortcode()`.

### Deprecated

- `LeadCaptureFormBlock::get_instance()` / `WidgetsLoader::get_instance()`
  — renamed to `instance()` to match the `LoadableInterface` convention
  used across this rollout; kept as forwarding aliases since this project
  follows Semantic Versioning and they're public API.

Note: the removed `LeadCapture_Form` class itself (previously reachable
via `LeadCapture_Form::get_instance()`) has no equivalent forwarding
shim — its responsibilities are split across the components above with
no single coherent replacement to forward to, and it lived in the main
plugin file rather than a documented public API surface. Same rationale
as `leadgen-app-form`'s identical migration, its near-identical twin.

## [1.0.1] - 2026-03-09

### Changed

- **Release Pipeline**: Add Node.js setup and asset build steps to release workflow for minified assets
- **Release Pipeline**: Make Node.js steps conditional on `package.json` existence to prevent CI failures

## [1.0.0] - 2025-07-14

### Added - Core Plugin Features

- **WordPress Shortcode System**: `[leadcapture_form]` with form-token, mode, trigger-class, and height parameters
- **Gutenberg Block Integration**: Native block editor support with visual interface and server-side rendering
- **Elementor Widget Integration**: Custom Elementor widget with drag-and-drop functionality and styling options
- **Embed Mode**: Inline form embedding via LeadCapture.io pixel script
- **Popup Mode**: Click-triggered popup forms using LeadCapture.io trigger classes
- **Lazy Loading**: Forms load only on user interaction (focus, mousemove, scroll, touch) for performance
- **CSS Pulse Animation**: Smooth placeholder animation during form loading
- **Vanilla JavaScript**: No jQuery dependency for lightweight frontend integration

### Added - Integration Architecture

- **LeadCapture.io Pixel Script**: Dynamic loading of `https://api.useleadbot.com/lead-bots/get-pixel-script.js`
- **Form Token System**: Single `form-token` attribute (LeadCapture.io handles responsiveness internally)
- **Singleton Script Loading**: Pixel script loaded once with callback queue for multiple form instances
- **Public JavaScript API**: `window.LeadCaptureForm.loadForm()`, `.isLoaded()`, `.getContainerCount()`

### Added - Elementor Integration

- **LeadCapture Form Widget**: Custom Elementor widget with native interface
- **Widget Category**: Organized under "LeadCapture Forms" category
- **Content Controls**: Form token, mode selection (embed/popup), conditional trigger class and height
- **Style Controls**: Alignment and responsive width settings
- **Consistent Rendering**: Uses same `render_shortcode()` logic as shortcode

### Added - Admin & Update System

- **Settings Hub Integration**: Admin page via silverassist/wp-settings-hub with fallback to native WP options page
- **GitHub Auto-Updates**: Built-in update system via silverassist/wp-github-updater package
- **Usage Documentation**: Admin page shows shortcode, block, and widget usage instructions

### Added - Development Infrastructure

- **GitHub Actions Workflows**: Release automation and quality checks (PHP 8.2-8.4 matrix)
- **SHA-Pinned Actions**: All GitHub Actions pinned to specific commit SHAs for supply chain security
- **Build Scripts**: Unified build-release.sh, update-version-simple.sh, check-versions.sh
- **Polyform Noncommercial License**: Licensed under Polyform Noncommercial 1.0.0

### Technical Requirements

- **PHP**: 8.2 or higher
- **WordPress**: 5.0 or higher
- **Dependencies**: silverassist/wp-github-updater ^1.3, silverassist/wp-settings-hub ^1.2
