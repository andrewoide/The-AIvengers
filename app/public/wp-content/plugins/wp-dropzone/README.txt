=== WP Dropzone ===
Contributors: nazsabuz
Tags: dropzone, file upload, image upload, media upload, media
Requires at least: 6.0
Tested up to: 7.0
Requires PHP: 7.0
Stable tag: 1.1.2
License: GPLv2 or later
License URI: <https://www.gnu.org/licenses/gpl-2.0.html>

Upload files into WordPress media library from front-end with drag-and-drop functionality and customizable options.

== Description ==

WP Dropzone integrates the powerful Dropzone.js library with WordPress, allowing you to upload files directly into the WordPress media library from any post, page, or front-end location. The plugin provides a modern, user-friendly drag-and-drop interface with extensive customization options and advanced features for file management.

### Key Features

* **Drag & Drop Interface** - Modern, intuitive file upload experience
* **Customizable Styling** - Full control over appearance with CSS customization
* **File Validation** - Built-in file type and size validation
* **Image Processing** - Automatic image resizing, cropping, and quality optimization
* **Thumbnail Generation** - Customizable thumbnail previews
* **Security Features** - Nonce verification and user permission checks
* **Action Hooks** - WordPress hooks for customization and integration
* **Translation Ready** - Full internationalization support
* **Performance Optimized** - Assets loaded only when needed

### Shortcode Usage

Insert the dropzone anywhere in your posts, pages, or templates with the shortcode:

`
[wp-dropzone]
`

Or in PHP templates:

`
<?php echo do_shortcode( '[wp-dropzone]' ); ?>
`

### Shortcode Attributes

The following attributes can be used with the `[wp-dropzone]` shortcode:

* `id` - Unique identifier for the dropzone instance (Default: Auto-generated)
  Example: `[wp-dropzone id="myUploader"]`

* `title` - Title displayed above the dropzone (Default: Empty)
  Example: `[wp-dropzone title="Drop Files Here"]`

* `desc` - Description text for the dropzone (Default: Empty)
  Example: `[wp-dropzone desc="Upload your files here"]`

* `accepted-files` - Allowed file types (Default: All files)
  Example: `[wp-dropzone accepted-files="image/*"]`

* `max-files` - Maximum number of files (Default: Unlimited)
  Example: `[wp-dropzone max-files="3"]`

* `auto-process` - Auto-upload files when dropped (Default: true)
  Example: `[wp-dropzone auto-process="false"]`

* `clickable` - Make dropzone clickable (Default: true)
  Example: `[wp-dropzone clickable="false"]`

* `remove-links` - Show remove file buttons (Default: false)
  Example: `[wp-dropzone remove-links="true"]`

* `upload-button-text` - Text for manual upload button (Default: "Upload Files")
  Example: `[wp-dropzone upload-button-text="Upload Selected Files"]`

* `resize-width` - Resize images to specified width (Default: Original)
  Example: `[wp-dropzone resize-width="800"]`

* `resize-height` - Resize images to specified height (Default: Original)
  Example: `[wp-dropzone resize-height="600"]`

* `resize-quality` - Image quality (0.1-1.0) (Default: 0.8)
  Example: `[wp-dropzone resize-quality="0.9"]`

* `resize-method` - Resize method: contain/crop (Default: contain)
  Example: `[wp-dropzone resize-method="crop"]`

* `thumbnail-width` - Thumbnail width in pixels (Default: 120)
  Example: `[wp-dropzone thumbnail-width="150"]`

* `thumbnail-height` - Thumbnail height in pixels (Default: 120)
  Example: `[wp-dropzone thumbnail-height="150"]`

* `thumbnail-method` - Thumbnail method: contain/crop (Default: crop)
  Example: `[wp-dropzone thumbnail-method="contain"]`

* `callback` - Custom JavaScript callbacks for Dropzone events (Advanced)
  Example: `[wp-dropzone callback="success: function(file, response) { document.getElementById('hidden-field').value = response.data; }"]`
  Note: Since this attribute is executed as JavaScript, it is only honored when the page/post containing the shortcode was authored by a user with the `unfiltered_html` capability (Administrators by default). This prevents lower-privileged authors (e.g. Contributors) from injecting scripts that would run in another user's browser.

### Styling Options

The following styling attributes can be used to customize the dropzone appearance:

* `border-width` - Border width
  Example: `[wp-dropzone border-width="3px"]`

* `border-style` - Border style (solid, dashed, etc.)
  Example: `[wp-dropzone border-style="dashed"]`

* `border-color` - Border color (hex code)
  Example: `[wp-dropzone border-color="#007cba"]`

* `background` - Background color (hex code)
  Example: `[wp-dropzone background="#f0f0f1"]`

* `margin-bottom` - Bottom margin
  Example: `[wp-dropzone margin-bottom="20px"]`

### Advanced Features

#### Action Hooks

The plugin provides several action hooks for customization:

`
// Before file upload
do_action( 'wp_dropzone_before_upload_file', $file );

// After file upload
do_action( 'wp_dropzone_after_upload_file', $file );

// After media library insertion
do_action( 'wp_dropzone_after_insert_attachment', $attachment_id );
`

#### JavaScript Integration

Access dropzone instance and events:

`
// Get dropzone instance
var dropzone = Dropzone.forElement("#wp-dz-yourID");

// Add event listeners
dropzone.on("success", function(file, response) {
    console.log("File uploaded:", response);
});
`

### Examples

#### Basic Image Upload

`[wp-dropzone accepted-files="image/*" max-files="5" title="Upload Images"]`

#### Document Upload with Restrictions

`[wp-dropzone accepted-files=".pdf,.doc,.docx" title="Upload Documents" desc="PDF, DOC, DOCX files only"]`

#### Styled Upload Area

`[wp-dropzone title="Drop Files Here" desc="Drag and drop files or click to browse" border-style="dashed" border-color="#007cba" background="#f8f9fa"]`

#### Manual Upload Button

`[wp-dropzone auto-process="false" upload-button-text="Upload Selected Files" title="Select Files"]`

== Installation ==

1. Upload the `wp-dropzone` folder to your `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Use the `[wp-dropzone]` shortcode in your posts, pages, or templates

### Requirements

* WordPress 6.0 or higher
* PHP 7.0 or higher
* Modern web browser with JavaScript enabled

== Frequently Asked Questions ==

= Do I need to be logged in to upload files? =

Yes, only logged-in users can upload files for security reasons. Guest users will see a login prompt when they try to upload files.

= What file types are supported? =

All file types are supported by default. You can restrict file types using the `accepted-files` attribute. For example: `accepted-files="image/*"` for images only, or `accepted-files=".pdf,.doc,.docx"` for specific document types.

= Can I customize the appearance and styling? =

Yes, you can customize the dropzone appearance using shortcode attributes like `border-color`, `background`, `border-style`, etc. For advanced styling, you can add custom CSS targeting the `.wp-dropzone` class.

= Can I resize images automatically? =

Yes, use the `resize-width`, `resize-height`, `resize-quality`, and `resize-method` attributes to automatically resize uploaded images. This helps optimize storage and loading times.

= How do I show thumbnails for uploaded files? =

Thumbnails are generated automatically for images. You can customize thumbnail size using `thumbnail-width`, `thumbnail-height`, and `thumbnail-method` attributes.

= Can I integrate with custom forms? =

Yes, you can use the `dom-id` attribute to copy uploaded file URLs to form fields. The plugin will automatically populate hidden input fields with the uploaded file URLs.

= How do I handle multiple files? =

Use the `max-files` attribute to limit the number of files. For example: `max-files="5"` allows up to 5 files. Users can upload multiple files at once by dragging multiple files or using Ctrl/Cmd+click.

= Can I disable automatic upload? =

Yes, set `auto-process="false"` to require users to click an upload button. Customize the button text with `upload-button-text` attribute.

= How do I handle upload errors? =

The plugin provides user-friendly error messages for common issues like file size limits, unsupported file types, and upload failures. Check browser console for detailed error information.

= Can I use this in Gutenberg blocks or page builders? =

Yes, you can use the shortcode in any WordPress editor that supports shortcodes, including Gutenberg blocks, Elementor, Beaver Builder, and other page builders.

= Is there a way to track uploads or integrate with other plugins? =

Yes, the plugin provides action hooks like `wp_dropzone_after_upload_file` and `wp_dropzone_after_insert_attachment` that you can use to integrate with other plugins or add custom functionality.

= What happens to uploaded files? =

Files are uploaded directly to the WordPress media library and become available in your Media section. They maintain their original filenames and are organized by upload date.

= Can I restrict uploads to specific user roles? =

Currently, any logged-in user can upload files. For role-based restrictions, you can use the plugin's action hooks to add custom permission checks in your theme or custom plugin.

= Does the plugin work with multisite installations? =

Yes, the plugin works with WordPress multisite installations. Each site will have its own media library and upload settings.

= How do I troubleshoot upload issues? =

Check your server's PHP upload limits (upload_max_filesize, post_max_size), ensure JavaScript is enabled, verify file permissions on wp-content/uploads directory, and check browser console for JavaScript errors.

== Screenshots ==

1. Default dropzone interface
2. Custom styled upload area
3. File preview with thumbnails
4. Upload progress indication
5. Admin settings and options

== Changelog ==

= 1.1.2 =

* **SECURITY FIX** - Fixed authenticated (Contributor+) stored cross-site scripting vulnerability via the 'callback' shortcode attribute (CVE-2025-13989). The callback is now only honored when the shortcode's containing post was authored by a user with the `unfiltered_html` capability -- the same trust boundary WordPress core uses to decide whether a user's post content may contain raw `<script>` tags
* **SECURITY FIX** - Escaped/sanitized shortcode attributes (`id`, `border-width`, `border-style`, `border-color`, `background`, `margin-bottom`, `thumbnail-width`, `thumbnail-height`) before use inside the inline `<style>` block to prevent a related stored XSS via style-tag breakout
* Added: JavaScript-side event name whitelist and dangerous-pattern checks as defense-in-depth for callbacks from trusted authors
* Updated: WordPress compatibility to 7.0

= 1.1.1 =

* **SECURITY FIX** - Fixed authenticated arbitrary file upload vulnerability (CVE-2025-12775)
* Added: Capability check requiring `upload_files` permission for all file uploads
* Added: File type validation before writing chunks to disk
* Added: Dangerous file extension blacklist to prevent execution of malicious files
* Added: Temporary file cleanup on success and error
* Improved: Chunked upload security by using system temp directory instead of uploads directory
* Improved: File validation now occurs before any disk writes
* Improved: Error handling in JavaScript to correctly display WordPress `wp_send_json_error` responses in Dropzone UI
* Improved: PHP backend now sends proper HTTP error status codes (400, 403) for upload failures
* Improved: Dropzone area is now disabled for users without upload permissions

= 1.1.0 =

* Added: FSE theme support
* Added: Improved error handling and user feedback
* Added: Enhanced security with nonce verification
* Added: Translation support and POT file
* Improved: Code structure and documentation
* Improved: Performance optimizations
* Updated: WordPress compatibility to 6.8
* Fixed: Minor bugs and typos

= 1.0.7 =

* Added: Action hooks for before/after upload events

= 1.0.6 =

* Security Fix: Removed guest upload feature
* Updated: Dropzone library to latest version
* Warning: Plugin reactivation required after upgrade

= 1.0.5 =

* Added: WordPress 4.9.x compatibility
* Improved: Code structure and organization

= 1.0.4 =

* Improved: Overall code structure

= 1.0.3 =

* Added: Thumbnail resize functionality

= 1.0.2 =

* Added: Custom ID support
* Added: Native Dropzone events support
* Improved: Asset loading and performance

= 1.0.1 =

* Added: Image resize and crop options
* Added: Image quality control

= 1.0.0 =

* Initial release with basic functionality

== Upgrade Notice ==

= 1.1.2 =
**SECURITY UPDATE** - Critical security fix for authenticated (Contributor+) stored cross-site scripting (XSS) vulnerability via the 'callback' shortcode attribute (CVE-2025-13989), plus a related stored XSS via unescaped styling attributes. All users should update immediately.

= 1.1.1 =
**SECURITY UPDATE** - Critical security fix for authenticated arbitrary file upload vulnerability (CVE-2025-12775). All users should update immediately. This update adds capability checks, file validation, and improved security measures.

= 1.1.0 =
Major update with FSE theme support, improved security, and translation support.

= 1.0.6 =
Security update: Guest uploads removed. Please reactivate the plugin after upgrading.
