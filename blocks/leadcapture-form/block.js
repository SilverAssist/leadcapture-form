/**
 * LeadCapture Form Plugin - Gutenberg Block
 *
 * WordPress Gutenberg block for inserting LeadCapture.io forms
 * with embed and popup mode support.
 *
 * @file block.js
 * @version 1.0.0
 * @author Silver Assist
 * @requires wp.blocks, wp.element, wp.components, wp.i18n, wp.blockEditor
 * @since 1.0.0
 */

(function (blocks, element, components, i18n, blockEditor) {
  "use strict";

  var registerBlockType = blocks.registerBlockType;
  var el = element.createElement;
  var Fragment = element.Fragment;
  var TextControl = components.TextControl;
  var PanelBody = components.PanelBody;
  var Placeholder = components.Placeholder;
  var SelectControl = components.SelectControl;
  var __ = i18n.__;
  var InspectorControls = blockEditor.InspectorControls;
  var useBlockProps = blockEditor.useBlockProps;

  /**
   * Custom icon for the LeadCapture Form block.
   */
  var leadCaptureIcon = el("svg", {
    width: 24,
    height: 24,
    viewBox: "0 0 24 24",
    fill: "none",
    xmlns: "http://www.w3.org/2000/svg"
  },
    el("path", {
      d: "M20 6H4C2.89 6 2 6.89 2 8V16C2 17.11 2.89 18 4 18H20C21.11 18 22 17.11 22 16V8C22 6.89 21.11 6 20 6ZM20 16H4V8H20V16ZM6 10H18V12H6V10ZM6 14H14V16H6V14Z",
      fill: "currentColor"
    })
  );

  /**
   * Register the LeadCapture Form block.
   */
  registerBlockType("leadcapture/form-block", {
    title: __("LeadCapture Form", "leadcapture-form"),
    description: __("Insert a LeadCapture.io form with embed or popup mode support.", "leadcapture-form"),
    icon: leadCaptureIcon,
    category: "widgets",
    keywords: [
      __("form", "leadcapture-form"),
      __("leadcapture", "leadcapture-form"),
      __("popup", "leadcapture-form"),
      __("embed", "leadcapture-form")
    ],

    attributes: {
      formToken: {
        type: "string",
        default: ""
      },
      mode: {
        type: "string",
        default: "embed"
      },
      triggerClass: {
        type: "string",
        default: ""
      },
      height: {
        type: "string",
        default: ""
      }
    },

    supports: {
      html: false,
      align: ["wide", "full"],
      spacing: {
        margin: true,
        padding: true
      }
    },

    /**
     * Edit function - renders the block in the editor.
     *
     * @param {Object} props Block properties.
     * @returns {Element} React element.
     */
    edit: function (props) {
      var attributes = props.attributes;
      var setAttributes = props.setAttributes;
      var formToken = attributes.formToken;
      var mode = attributes.mode;
      var triggerClass = attributes.triggerClass;
      var height = attributes.height;
      var blockProps = useBlockProps();

      /**
       * Generate preview text based on current attributes.
       *
       * @returns {string} Preview text.
       */
      var getPreviewText = function () {
        if (!formToken.trim()) {
          return __("LeadCapture Form (Not Configured)", "leadcapture-form");
        }
        if (mode === "popup") {
          return __("LeadCapture Form (Popup Mode)", "leadcapture-form");
        }
        return __("LeadCapture Form (Embed Mode)", "leadcapture-form");
      };

      var isConfigured = formToken.trim() !== "";

      return el(Fragment, {},
        // Inspector Controls (Sidebar).
        el(InspectorControls, {},
          el(PanelBody, {
            title: __("Form Configuration", "leadcapture-form"),
            initialOpen: true
          },
            el(TextControl, {
              label: __("Form Token", "leadcapture-form"),
              help: __("Your LeadCapture.io form token (e.g., GLFT-XXXX). Find it in Settings → Publish.", "leadcapture-form"),
              value: formToken,
              onChange: function (value) {
                setAttributes({ formToken: value });
              },
              placeholder: "GLFT-XXXXXXXXXXXXXXXXXXXXXXX"
            }),
            el(SelectControl, {
              label: __("Display Mode", "leadcapture-form"),
              help: __("Embed displays the form inline. Popup loads the script for popup trigger.", "leadcapture-form"),
              value: mode,
              onChange: function (value) {
                setAttributes({ mode: value });
              },
              options: [
                { label: __("Embed (Inline Form)", "leadcapture-form"), value: "embed" },
                { label: __("Popup (Trigger on Click)", "leadcapture-form"), value: "popup" }
              ]
            }),
            mode === "popup" && el(TextControl, {
              label: __("Trigger Class", "leadcapture-form"),
              help: __("CSS class that triggers the popup (e.g., leadforms-trigger-XX).", "leadcapture-form"),
              value: triggerClass,
              onChange: function (value) {
                setAttributes({ triggerClass: value });
              },
              placeholder: "leadforms-trigger-XX"
            }),
            mode === "embed" && el(TextControl, {
              label: __("Placeholder Height", "leadcapture-form"),
              help: __("Height of the loading placeholder (e.g., 600px, 50vh). Leave empty for default.", "leadcapture-form"),
              value: height,
              onChange: function (value) {
                setAttributes({ height: value });
              },
              placeholder: "600px"
            })
          )
        ),

        // Block Content.
        el("div", blockProps,
          el(Placeholder, {
            icon: leadCaptureIcon,
            label: getPreviewText(),
            instructions: isConfigured
              ? __("Form is configured and ready. Use the sidebar to modify settings.", "leadcapture-form")
              : __("Enter your form token in the sidebar to get started.", "leadcapture-form"),
            className: isConfigured ? "is-configured" : "needs-configuration"
          },
            isConfigured && el("div", {
              style: {
                marginTop: "15px",
                padding: "10px",
                backgroundColor: "#f8f9fa",
                borderRadius: "4px",
                fontSize: "13px"
              }
            },
              el("div", {},
                el("strong", {}, __("Token:", "leadcapture-form")), " ", formToken
              ),
              el("div", {},
                el("strong", {}, __("Mode:", "leadcapture-form")), " ", mode
              ),
              mode === "popup" && triggerClass && el("div", {},
                el("strong", {}, __("Trigger:", "leadcapture-form")), " ", triggerClass
              ),
              mode === "embed" && height && el("div", {},
                el("strong", {}, __("Height:", "leadcapture-form")), " ", height
              )
            )
          )
        )
      );
    },

    /**
     * Save function - returns null for server-side rendering.
     *
     * @returns {null} Server-side rendered.
     */
    save: function () {
      // Server-side rendering via render_callback in PHP.
      return null;
    }
  });

})(
  window.wp.blocks,
  window.wp.element,
  window.wp.components,
  window.wp.i18n,
  window.wp.blockEditor
);
