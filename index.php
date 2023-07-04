<?php
/*
Plugin Name: Newsletter
Description: A plugin to capture email addresses and store them in a MySQL database.
Version: 1.0
Author: Michael McDonagh
*/

// Create the database table on plugin activation
function email_capture_create_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'email_capture';

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id INT(11) NOT NULL AUTO_INCREMENT,
        email VARCHAR(100) NOT NULL,
        PRIMARY KEY (id)
    );";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

register_activation_hook(__FILE__, 'email_capture_create_table');

// Create a shortcode for the email capture form
function email_capture_form_shortcode() {
    ob_start();
    ?>
    <div class="newsletter-container">
        <form method="post" action="">
            <input type="email" name="email" placeholder="Enter your email" required="" style="width: 200px; padding: 5px;">
            <input type="submit" name="submit_email" value="Subscribe" style="padding: 5px 10px; background-color: #000; color: #fff; border: none;">
        </form>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('email_capture_form', 'email_capture_form_shortcode');

// Handle the form submission
function email_capture_form_submission() {
    if (isset($_POST['submit_email'])) {
        $email = sanitize_email($_POST['email']);
        
        // Store the email in the database
        global $wpdb;
        $table_name = $wpdb->prefix . 'email_capture';
        $wpdb->insert($table_name, array('email' => $email), array('%s'));
    }
}
add_action('init', 'email_capture_form_submission');

// Add menu page in the WordPress admin dashboard
function email_capture_admin_menu() {
    add_menu_page(
        'Newsletter',   // Page title
        'Newsletter',   // Menu title
        'manage_options',  // Capability required to access the menu page
        'email-capture',   // Menu slug
        'email_capture_admin_page',   // Callback function to render the menu page
        'dashicons-email'  // Menu icon
    );

    // Add submenu page for Mass Sender
    add_submenu_page(
        'email-capture',   // Parent slug
        'Mass Sender',     // Page title
        'Mass Sender',     // Menu title
        'manage_options',  // Capability required to access the menu page
        'mass-sender',     // Menu slug
        'email_capture_mass_sender_admin_page'  // Callback function to render the menu page
    );

    // Add submenu page for Header and Footer Editor
    add_submenu_page(
        'email-capture',   // Parent slug
        'Header and Footer Editor',     // Page title
        'Header and Footer',     // Menu title
        'manage_options',  // Capability required to access the menu page
        'header-footer-editor',     // Menu slug
        'email_capture_header_footer_editor_admin_page'  // Callback function to render the menu page
    );
}
add_action('admin_menu', 'email_capture_admin_menu');

// Render the menu page
function email_capture_admin_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'email_capture';

    // Handle bulk delete action
    if (isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['email_ids'])) {
        $email_ids = $_POST['email_ids'];
        foreach ($email_ids as $email_id) {
            $wpdb->delete($table_name, ['id' => $email_id]);
        }
    }

    // Display the list of emails
    $emails = $wpdb->get_results("SELECT * FROM $table_name");
    ?>
    <div class="wrap">
        <h1>Email Capture</h1>
        <form method="post" action="">
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th class="manage-column column-cb check-column">
                            <input type="checkbox" id="cb-select-all">
                        </th>
                        <th>Email</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($emails as $email) : ?>
                        <tr>
                            <th class="check-column">
                                <input type="checkbox" name="email_ids[]" value="<?php echo $email->id; ?>">
                            </th>
                            <td><?php echo $email->email; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <br>
            <div class="email-capture-bulk-actions">
                <select name="action">
                    <option value="">Bulk Actions</option>
                    <option value="delete">Delete</option>
                </select>
                <input type="submit" class="button action" value="Apply">
            </div>
        </form>
        <br>

    </div>
    <?php
}

// Render the Mass Sender menu page
function email_capture_mass_sender_admin_page() {
    ?>
    <div class="wrap">
        <h1>Mass Sender</h1>
        <form method="post" action="">
            <div id="email-sending-section">
                <h2>Send Email Newsletter</h2>
                <label for="email_subject">Subject:</label>
                <input type="text" name="email_subject" id="email_subject" required style="width: 100%; padding: 5px;">
                <br><br>
                <label for="email_message">Message:</label>
                <?php
                $settings = array(
                    'media_buttons' => false,
                    'textarea_rows' => 10,
                    'teeny'         => true,
                );
                wp_editor('', 'email_message', $settings);
                ?>
                <br><br>
                <input type="hidden" name="action" value="send_email">
                <input type="submit" class="button-primary" value="Send Email" style="padding: 5px 10px; background-color: #000; color: #fff; border: none;">
            </div>
        </form>
        <br>
    </div>
    <script>
        (function($) {
            $(document).ready(function() {
                $('#toggle-email-sending-section').change(function() {
                    var isChecked = $(this).is(':checked');
                    $('#email-sending-section').toggle(isChecked);
                });
            });
        })(jQuery);
    </script>
    <?php

    // Handle the email sending form submission
    if (isset($_POST['action']) && $_POST['action'] === 'send_email') {
        global $wpdb;
        $table_name = $wpdb->prefix . 'email_capture';
        $subject = sanitize_text_field($_POST['email_subject']);
        $message = wp_kses_post($_POST['email_message']);

        // Retrieve all emails from the database
        $emails = $wpdb->get_col("SELECT email FROM $table_name");

        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
        );

        $header_content = get_option('email_capture_header_content', '<h1>Header</h1>');
        $footer_content = get_option('email_capture_footer_content', '<footer>Footer</footer>');
        $css_content = get_option('email_capture_css_content', '');

        // Send the email to each recipient
        foreach ($emails as $email) {
            $message_with_styles = '
                <html>
                <head>
                    <style type="text/css">
                        ' . $css_content . '
                    </style>
                </head>
                <body>
                    ' . $header_content . '
                    <br><br>
                    ' . $message . '
                    <br><br>
                    ' . $footer_content . '
                </body>
                </html>';

            wp_mail($email, $subject . " - FloorStore Direct", $message_with_styles, $headers);
        }

        // Display a success message
        echo '<div class="notice notice-success"><p>Email sent to all recipients.</p></div>';
    }
}

// Render the Header and Footer Editor menu page
function email_capture_header_footer_editor_admin_page() {
    // Save header and footer content
    if (isset($_POST['action']) && $_POST['action'] === 'save_header_footer') {
        $header_content = wp_kses_post($_POST['header_content']);
        $footer_content = wp_kses_post($_POST['footer_content']);
        $css_content = sanitize_textarea_field($_POST['css_content']);
    
        // Save the content in the database
        update_option('email_capture_header_content', $header_content);
        update_option('email_capture_footer_content', $footer_content);
        update_option('email_capture_css_content', $css_content);

        // Display a success message
        echo '<div class="notice notice-success"><p>Header, Footer, and CSS content saved.</p></div>';
    }

    // Retrieve header and footer content from the database
    $header_content = get_option('email_capture_header_content', '<h1>Header</h1>');
    $footer_content = get_option('email_capture_footer_content', '<footer>Footer</footer>');
    $css_content = get_option('email_capture_css_content', '');
    ?>
    <div class="wrap">
        <h1>Header and Footer Editor</h1>
        <form method="post" action="">
            <h2>Header</h2>
            <textarea name="header_content" rows="10" style="width: 100%;"><?php echo esc_textarea($header_content); ?></textarea>
            <br><br>
            <h2>Footer</h2>
            <textarea name="footer_content" rows="10" style="width: 100%;"><?php echo esc_textarea($footer_content); ?></textarea>
            <br><br>
            <h2>CSS</h2>
            <textarea name="css_content" rows="10" style="width: 100%;"><?php echo esc_textarea($css_content); ?></textarea>
            <br><br>
            <input type="hidden" name="action" value="save_header_footer">
            <input type="submit" class="button-primary" value="Save Header, Footer, and CSS" style="padding: 5px 10px; background-color: #000; color: #fff; border: none;">
        </form>
        <br>
    </div>
    <?php
}

// Enqueue scripts and styles for the admin page
function email_capture_admin_enqueue_scripts() {
    wp_enqueue_style('email-capture-admin-css', plugin_dir_url(__FILE__) . 'admin.css');
}
add_action('admin_enqueue_scripts', 'email_capture_admin_enqueue_scripts');
