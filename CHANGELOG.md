# Changelog

All notable changes to the LeadCapture Form Plugin will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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
