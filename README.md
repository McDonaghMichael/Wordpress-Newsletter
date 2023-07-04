# Email Capture (Newsletter) Plugin

This is a WordPress plugin that allows users to capture email addresses and manage them through an admin dashboard. It provides functionalities such as creating a database table, displaying a form to capture email addresses, storing the captured emails in the database, sending mass emails to the captured email addresses, and editing the header and footer content of the email template.

## Installation

To install the Email Capture Plugin, follow these steps:

1. Download the plugin files from the [GitHub repository](https://github.com/example/repository).
2. Upload the plugin folder to the `wp-content/plugins/` directory on your WordPress installation.
3. Activate the plugin through the 'Plugins' menu in WordPress.

## Usage

### Creating the Database Table

When the plugin is activated, it automatically creates a database table to store the captured email addresses.

### Shortcode for Email Capture Form

Use the `[email_capture_form]` shortcode to display the email capture form on any page or post. Example usage: `[email_capture_form]`.

### Admin Dashboard

The plugin adds a menu page called "Newsletter" to the WordPress admin dashboard. This page allows administrators to manage the captured email addresses.

#### Email Capture Page

The "Email Capture" page displays a list of captured email addresses. Administrators can select and delete multiple email addresses using bulk actions.

#### Mass Sender Page

The "Mass Sender" page allows administrators to send a mass email to all the captured email addresses. Administrators can specify the subject and content of the email. The email is sent individually to each recipient.

#### Header and Footer Editor Page

The "Header and Footer Editor" page enables administrators to edit the header, footer, and CSS content of the email template. Changes made on this page will be reflected in the mass email sent from the "Mass Sender" page.

## Hooks and Functions

### Hooks

- `register_activation_hook(__FILE__, 'email_capture_create_table')`: Creates the database table when the plugin is activated.
- `add_shortcode('email_capture_form', 'email_capture_form_shortcode')`: Registers the shortcode `[email_capture_form]` to display the email capture form.
- `add_action('init', 'email_capture_form_submission')`: Handles the form submission to capture email addresses.
- `add_action('admin_menu', 'email_capture_admin_menu')`: Adds menu pages to the WordPress admin dashboard.
- `add_action('admin_enqueue_scripts', 'email_capture_admin_enqueue_scripts')`: Enqueues scripts and styles for the admin page.

### Functions

- `email_capture_create_table()`: Creates the database table for storing email addresses.
- `email_capture_form_shortcode()`: Renders the email capture form shortcode.
- `email_capture_form_submission()`: Handles the form submission to capture email addresses and store them in the database.
- `email_capture_admin_menu()`: Adds menu pages to the WordPress admin dashboard.
- `email_capture_admin_page()`: Renders the Email Capture page in the admin dashboard.
- `email_capture_mass_sender_admin_page()`: Renders the Mass Sender page in the admin dashboard.
- `email_capture_header_footer_editor_admin_page()`: Renders the Header and Footer Editor page in the admin dashboard.
- `email_capture_admin_enqueue_scripts()`: Enqueues scripts and styles for the admin page.

## Styling

The plugin includes an `admin.css` file that provides custom styling for the admin pages. The `email-capture-admin-css` style is enqueued for the admin pages using the `email_capture_admin_enqueue_scripts()` function.

## License

This plugin is released under the [MIT License](https://opensource.org/licenses/MIT).

## Contributions

Contributions to the Email Capture Plugin are welcome. Feel free to submit bug reports, feature requests, or pull requests to the [GitHub repository](https://github.com/example/repository).
