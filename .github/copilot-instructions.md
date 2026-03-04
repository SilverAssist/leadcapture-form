# LeadCapture Form — Project Context

WordPress plugin for embedding LeadCapture.io forms via shortcode, Gutenberg block, or Elementor widget with embed and popup mode support.

## Plugin Info

| Key              | Value                          |
|------------------|--------------------------------|
| Namespace        | `LeadCaptureForm`              |
| Text Domain      | `leadcapture-form`             |
| Version          | 1.0.0                         |
| Requires PHP     | 8.2                           |
| License          | Polyform Noncommercial 1.0.0  |
| GitHub Repo      | `SilverAssist/leadcapture-form`|

## Differences from Global Standards

- **Double quotes** everywhere (PHP and JS) — not single quotes
- **Singleton pattern** (`get_instance()`) — not LoadableInterface
- **No activation/deactivation hooks** — plugin doesn't modify WP internals
- PSR-4 autoloading via `require_once` in `load_dependencies()`, not a DI container
- **Vanilla JavaScript** — no jQuery dependency

## Architecture

```
leadcapture-form.php              # Entry point (Singleton)
includes/
├── LeadCaptureFormBlock.php      # Gutenberg block handler
├── LeadCaptureFormUpdater.php    # GitHub updater (extends silverassist/wp-github-updater)
├── LeadCaptureFormAdmin.php      # Admin interface (Settings → LeadCapture)
└── elementor/
    ├── WidgetsLoader.php         # Conditional loader (only when Elementor active)
    └── widgets/
        └── LeadCaptureFormWidget.php # Elementor widget
blocks/leadcapture-form/          # Gutenberg block assets (block.json, block.js, editor.css)
assets/css/                       # leadcapture-form.css, admin-settings.css
assets/js/                        # leadcapture-form.js (frontend, vanilla JS)
```

### Namespaces

- `LeadCaptureForm` — main plugin classes
- `LeadCaptureForm\Block` — Gutenberg block
- `LeadCaptureForm\Elementor` — Elementor loader
- `LeadCaptureForm\Elementor\Widgets` — Elementor widgets

### Key Differences from LeadGen App Form

- **Single form-token** (not desktop-id/mobile-id) — LeadCapture.io handles responsiveness internally
- **Embed & popup modes** — `mode="embed"` for inline, `mode="popup"` for click-triggered popup
- **LeadCapture.io pixel script** — loads `https://api.useleadbot.com/lead-bots/get-pixel-script.js`
- **`window.form_token`** — set before script loads (not per-form custom elements)
- **Embed container** — `<div class="leadforms-embd-form"></div>` populated by pixel script
- **Popup trigger** — CSS class `leadforms-trigger-XX` on trigger elements

### Form Integration Methods

```php
// Shortcode — embed mode
[leadcapture_form form-token="GLFT-XXXXX" mode="embed" height="600px"]

// Shortcode — popup mode
[leadcapture_form form-token="GLFT-XXXXX" mode="popup" trigger-class="leadforms-trigger-01"]

// Gutenberg Block — search "LeadCapture Form" in block inserter
// Elementor Widget — drag from "LeadCapture Forms" category
```

### Form Loading Flow

1. PHP renders placeholder with pulse animation
2. JS waits for user interaction (focus/mousemove/scroll/touchstart)
3. Sets `window.form_token` from data attribute
4. Loads pixel script from `api.useleadbot.com` (singleton — only loaded once)
5. Script populates `<div class="leadforms-embd-form">` for embed mode
6. For popup mode, trigger elements use `leadforms-trigger-XX` CSS class

### Update System

Uses `silverassist/wp-github-updater` package. `LeadCaptureFormUpdater` extends `GitHubUpdater` with plugin-specific config (asset pattern `leadcapture-form-v{version}.zip`, 12h cache, AJAX action `leadcapture_check_version`). Updates show in standard WP admin.

### Elementor Integration

- Conditional loading: `\did_action('elementor/loaded')`
- Widget category: `leadcapture-forms`
- Hooks: `elementor/widgets/register`, `elementor/elements/categories_registered`
- Renders via same `render_shortcode()` method as the shortcode

## Quick Reference

| File | Role |
|------|------|
| `leadcapture-form.php` | Main plugin file (Singleton) |
| `includes/LeadCaptureFormBlock.php` | Gutenberg block handler |
| `includes/LeadCaptureFormUpdater.php` | GitHub updater |
| `includes/LeadCaptureFormAdmin.php` | Admin settings page |
| `includes/elementor/WidgetsLoader.php` | Elementor widgets manager |
| `includes/elementor/widgets/LeadCaptureFormWidget.php` | LeadCapture Form widget |
| `assets/js/leadcapture-form.js` | Frontend form loading (Vanilla JS, lazy load) |
| `assets/css/leadcapture-form.css` | Main styles + animations |
| `assets/css/admin-settings.css` | Admin page card-based UI |
