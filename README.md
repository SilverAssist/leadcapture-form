# LeadCapture Form Plugin

WordPress plugin for embedding LeadCapture.io forms via shortcode, Gutenberg block, or Elementor widget with embed and popup mode support.

## Features

- **Embed Mode**: Inline form embedding directly in page content
- **Popup Mode**: Click-triggered popup forms using LeadCapture.io trigger system
- **Gutenberg Block**: Visual block editor with intuitive sidebar controls and server-side rendering
- **Elementor Widget**: Native Elementor widget with drag-and-drop functionality
- **Shortcode Support**: Simple `[leadcapture_form]` shortcode for direct integration
- **Lazy Loading**: Performance-optimized — forms load only on user interaction
- **Automatic Updates**: Built-in GitHub update system keeps the plugin current
- **Settings Hub Integration**: Centralized admin interface via Silver Assist Settings Hub
- **Vanilla JavaScript**: No jQuery dependency for lightweight frontend
- **Translation Ready**: Full internationalization support

## Requirements

- WordPress 5.0 or higher
- PHP 8.2 or higher

## Installation

### Method 1: WordPress Admin Dashboard (Recommended)

1. Download the `leadcapture-form.zip` file from GitHub Releases
2. Go to `Plugins` → `Add New` → `Upload Plugin`
3. Choose the ZIP file and click `Install Now`
4. Click `Activate Plugin`

### Method 2: Manual Installation via FTP

1. Extract the `leadcapture-form.zip` file
2. Upload the `leadcapture-form` folder to `/wp-content/plugins/`
3. Activate from the WordPress Plugins page

### Method 3: WP-CLI

```bash
wp plugin install leadcapture-form.zip --activate
```

### Verification

After installation, you should see:
- **Gutenberg Block**: "LeadCapture Form" block in the block editor
- **Elementor Widget**: "LeadCapture Form" widget in the "LeadCapture Forms" category (if Elementor is installed)
- **Shortcode Support**: `[leadcapture_form]` shortcode ready to use
- **Admin Page**: Settings → LeadCapture (or Silver Assist → LeadCapture if Settings Hub is active)

## Usage

### Gutenberg Block (Recommended)

1. In the WordPress editor, click "+" to add a new block
2. Search for "LeadCapture Form"
3. Configure settings in the sidebar:
   - **Form Token**: Your LeadCapture.io form token (e.g., `GLFT-XXXXX`)
   - **Mode**: Embed (inline) or Popup (click-triggered)
   - **Height**: Custom placeholder height for embed mode
   - **Trigger Class**: CSS class for popup trigger elements
4. Publish — the form renders server-side

### Elementor Widget

1. Open Elementor editor
2. Search for "LeadCapture Form" or browse the "LeadCapture Forms" category
3. Drag & drop the widget
4. Configure form token, mode, and styling options
5. Publish

### Shortcode

```
# Embed mode (inline form)
[leadcapture_form form-token="GLFT-XXXXX" mode="embed" height="600px"]

# Popup mode (click-triggered)
[leadcapture_form form-token="GLFT-XXXXX" mode="popup" trigger-class="leadforms-trigger-01"]
```

### Parameters

| Parameter | Required | Default | Description |
|-----------|----------|---------|-------------|
| `form-token` | Yes | — | LeadCapture.io form token (e.g., `GLFT-XXXXX`) |
| `mode` | No | `embed` | Display mode: `embed` (inline) or `popup` (click-triggered) |
| `height` | No | `600px` | Placeholder height for embed mode |
| `trigger-class` | No | — | CSS class for popup trigger elements |

## How It Works

### Embed Mode
1. PHP renders a placeholder with pulse animation
2. JavaScript waits for user interaction (focus, mousemove, scroll, touch)
3. Sets `window.form_token` from the form token attribute
4. Loads the LeadCapture.io pixel script from `api.useleadbot.com`
5. Script populates the `<div class="leadforms-embd-form">` container

### Popup Mode
1. PHP renders a hidden container with popup configuration
2. JavaScript loads the pixel script on user interaction
3. Popup triggers via CSS class `leadforms-trigger-XX` on buttons/links

### Lazy Loading
The pixel script is loaded only once (singleton pattern) regardless of how many form instances are on the page. A callback queue ensures all forms are initialized after the script loads.

## Automatic Updates

The plugin includes automatic updates via GitHub Releases:

- **Automatic Detection**: WordPress checks every 12 hours
- **Native Experience**: Updates appear in the standard Plugins page
- **One-Click Updates**: Install with a single click
- **Manual Check**: Settings → LeadCapture → Check for Updates

## Plugin Structure

```
leadcapture-form/
├── leadcapture-form.php              # Main plugin file (Singleton)
├── includes/
│   ├── LeadCaptureFormBlock.php      # Gutenberg block handler
│   ├── LeadCaptureFormUpdater.php    # GitHub updater
│   ├── LeadCaptureFormAdmin.php      # Admin interface
│   └── elementor/
│       ├── WidgetsLoader.php         # Elementor widgets loader
│       └── widgets/
│           └── LeadCaptureFormWidget.php # Elementor widget
├── blocks/leadcapture-form/
│   ├── block.json                    # Block metadata
│   ├── block.js                      # Block editor JS
│   └── editor.css                    # Block editor styles
├── assets/
│   ├── css/
│   │   ├── leadcapture-form.css      # Frontend styles
│   │   └── admin-settings.css        # Admin page styles
│   └── js/
│       └── leadcapture-form.js       # Frontend JS (Vanilla)
├── vendor/                           # Composer dependencies
├── scripts/                          # Build & version scripts
├── .github/                          # Workflows & copilot config
├── composer.json
├── CHANGELOG.md
├── README.md
└── LICENSE
```

## JavaScript API

```javascript
// Load a specific form container manually
window.LeadCaptureForm.loadForm("container-id");

// Check if the pixel script has been loaded
window.LeadCaptureForm.isLoaded();

// Get count of form containers on the page
window.LeadCaptureForm.getContainerCount();
```

## Support & Documentation

- **Change History**: [CHANGELOG.md](CHANGELOG.md)
- **Issues**: [GitHub Issues](https://github.com/SilverAssist/leadcapture-form/issues)
- **Releases**: [GitHub Releases](https://github.com/SilverAssist/leadcapture-form/releases)

## License

This plugin is licensed under the Polyform Noncommercial License 1.0.0.

---

**Made with ❤️ by [Silver Assist](https://silverassist.com)**
