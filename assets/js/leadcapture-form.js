/**
 * LeadCapture Form Plugin JavaScript
 *
 * Frontend functionality for the LeadCapture Form plugin.
 * Implements minimal user interaction patterns and lazy loading
 * for LeadCapture.io pixel script.
 *
 * @file leadcapture-form.js
 * @version 1.0.0
 * @author Silver Assist
 * @since 1.0.0
 */

(function () {
  "use strict";

  /**
   * Global settings from PHP localization.
   *
   * @type {Object}
   * @property {Array}  instances      - Array of shortcode instances from PHP.
   * @property {string} pixelScriptUrl - URL of the LeadCapture.io pixel script.
   */
  const settings = window.leadCaptureFormSettings || {};
  const pixelScriptUrl = settings.pixelScriptUrl || "https://api.useleadbot.com/lead-bots/get-pixel-script.js";

  /**
   * Whether the pixel script has already been loaded.
   *
   * @type {boolean}
   */
  let scriptLoaded = false;

  /**
   * Whether the pixel script is currently loading.
   *
   * @type {boolean}
   */
  let scriptLoading = false;

  /**
   * Queue of callbacks to execute when the script finishes loading.
   *
   * @type {Function[]}
   */
  let onLoadCallbacks = [];

  /**
   * Map of form containers and their configurations.
   *
   * @type {Map<string, Object>}
   */
  const formContainers = new Map();

  /**
   * Whether user has interacted with the page.
   *
   * @type {boolean}
   */
  let hasInteracted = false;

  /**
   * Initialize when DOM is ready.
   *
   * @since 1.0.0
   */
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initialize);
  } else {
    initialize();
  }

  /**
   * Main initialization function.
   *
   * Finds all LeadCapture form containers on the page
   * and sets up minimal user interaction detection.
   *
   * @since 1.0.0
   */
  function initialize() {
    const containers = document.querySelectorAll(".leadcapture-form-container");

    if (containers.length === 0) {
      return;
    }

    containers.forEach(function (container) {
      const formToken = container.getAttribute("data-form-token");
      const mode = container.getAttribute("data-mode") || "embed";
      const triggerClass = container.getAttribute("data-trigger-class") || "";
      const height = container.getAttribute("data-height") || "";

      if (!formToken) {
        return;
      }

      var config = {
        element: container,
        formToken: formToken,
        mode: mode,
        triggerClass: triggerClass,
        height: height,
        isLoaded: false
      };

      formContainers.set(container.id || "form-" + formContainers.size, config);

      // Apply custom placeholder height if specified.
      if (mode === "embed" && height) {
        applyCustomHeight(container, height);
      }
    });

    // Set up minimal user interaction pattern for lazy loading.
    setupMinimalUserInteraction();
  }

  /**
   * Apply custom height to placeholder element.
   *
   * @since 1.0.0
   * @param {HTMLElement} container - The form container element.
   * @param {string}      height   - The height value (e.g., "600px", "50vh").
   */
  function applyCustomHeight(container, height) {
    var placeholder = container.querySelector(".leadcapture-form-placeholder");
    if (!placeholder) {
      return;
    }

    // Validate height format.
    if (/^\d+(\.\d+)?(px|em|rem|vh|vw|%)?$/.test(height)) {
      var heightWithUnit = /\d$/.test(height) ? height + "px" : height;
      placeholder.style.minHeight = heightWithUnit;
    }
  }

  /**
   * Set up minimal user interaction detection.
   *
   * Implements the minimal interaction pattern where the pixel script
   * only loads after user interaction (focus, mousemove, scroll, touchstart).
   * This improves initial page load performance.
   *
   * @since 1.0.0
   */
  function setupMinimalUserInteraction() {
    var events = ["focus", "mousemove", "scroll", "touchstart"];

    /**
     * Handle first user interaction.
     * Removes all listeners and triggers form loading.
     *
     * @since 1.0.0
     */
    function onFirstInteraction() {
      if (hasInteracted) {
        return;
      }

      hasInteracted = true;

      // Remove all interaction listeners.
      events.forEach(function (event) {
        document.removeEventListener(event, onFirstInteraction);
      });

      // Load all form instances.
      loadAllForms();
    }

    // Register interaction listeners.
    events.forEach(function (event) {
      document.addEventListener(event, onFirstInteraction, { once: true, passive: true });
    });
  }

  /**
   * Load all form instances.
   *
   * Iterates through all registered form containers and initiates loading.
   * For embed mode, loads the pixel script with the appropriate form token.
   * For popup mode, just loads the pixel script (LeadCapture handles the popup).
   *
   * @since 1.0.0
   */
  function loadAllForms() {
    formContainers.forEach(function (config) {
      if (config.isLoaded) {
        return;
      }

      loadForm(config);
    });
  }

  /**
   * Load a single form instance.
   *
   * Sets the global form_token and loads the pixel script.
   * LeadCapture.io uses `window.form_token` to identify which form to render.
   *
   * @since 1.0.0
   * @param {Object} config - Form configuration object.
   */
  function loadForm(config) {
    // Set the form token globally (LeadCapture.io reads this).
    window.form_token = config.formToken;

    // Load the pixel script.
    loadPixelScript(function () {
      config.isLoaded = true;

      if (config.mode === "embed") {
        // Show the form content and hide placeholder.
        showFormContent(config.element);
      }
    });
  }

  /**
   * Load the LeadCapture.io pixel script.
   *
   * Creates a script element and appends it to the document body.
   * Uses a singleton pattern to prevent duplicate script loading.
   *
   * @since 1.0.0
   * @param {Function} [callback] - Callback function to execute on load.
   */
  function loadPixelScript(callback) {
    // If already loaded, execute callback immediately.
    if (scriptLoaded) {
      if (callback) {
        callback();
      }
      return;
    }

    // If currently loading, queue the callback.
    if (scriptLoading) {
      if (callback) {
        onLoadCallbacks.push(callback);
      }
      return;
    }

    scriptLoading = true;

    if (callback) {
      onLoadCallbacks.push(callback);
    }

    // Check if script already exists in DOM.
    var existingScript = document.getElementById("leadcapture-pixel-script");
    if (existingScript) {
      scriptLoaded = true;
      scriptLoading = false;
      executeCallbacks();
      return;
    }

    // Create and load the script.
    var script = document.createElement("script");
    script.id = "leadcapture-pixel-script";
    script.src = pixelScriptUrl;
    script.async = true;

    script.onload = function () {
      scriptLoaded = true;
      scriptLoading = false;
      executeCallbacks();
    };

    script.onerror = function () {
      scriptLoading = false;
      console.error("LeadCapture Form: Failed to load pixel script from", pixelScriptUrl);
    };

    document.body.appendChild(script);
  }

  /**
   * Execute all queued callbacks after script load.
   *
   * @since 1.0.0
   */
  function executeCallbacks() {
    var callbacks = onLoadCallbacks.slice();
    onLoadCallbacks = [];

    callbacks.forEach(function (cb) {
      try {
        cb();
      } catch (e) {
        console.error("LeadCapture Form: Callback error", e);
      }
    });
  }

  /**
   * Show form content and hide the loading placeholder.
   *
   * Transitions from the pulse animation placeholder to the actual
   * LeadCapture.io form content.
   *
   * @since 1.0.0
   * @param {HTMLElement} container - The form container element.
   */
  function showFormContent(container) {
    // Add loaded class to trigger CSS transitions.
    container.classList.add("loaded");

    // Show form content.
    var formContent = container.querySelector(".leadcapture-form-content");
    if (formContent) {
      formContent.style.display = "block";
    }
  }

  /**
   * Public API for manual form control.
   *
   * Exposes methods for external control of forms,
   * useful for debugging and advanced integrations.
   *
   * @since 1.0.0
   * @namespace
   * @global
   */
  window.LeadCaptureForm = {
    /**
     * Manually trigger form loading.
     *
     * @since 1.0.0
     * @param {string} [containerId] - Specific container ID to load. Loads all if omitted.
     */
    loadForm: function (containerId) {
      if (containerId) {
        var config = formContainers.get(containerId);
        if (config && !config.isLoaded) {
          loadForm(config);
        }
      } else {
        loadAllForms();
      }
    },

    /**
     * Check if forms have been loaded.
     *
     * @since 1.0.0
     * @return {boolean} True if the pixel script has been loaded.
     */
    isLoaded: function () {
      return scriptLoaded;
    },

    /**
     * Get the number of registered form containers.
     *
     * @since 1.0.0
     * @return {number} Number of form containers.
     */
    getContainerCount: function () {
      return formContainers.size;
    }
  };

})();
