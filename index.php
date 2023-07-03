<?php
/*
Plugin Name: Newsletter
Description: A plugin to capture email addresses and store them in a MySQL database.
Version: 1.0
Author: Michael McDonagh
*/

// Create a shortcode for the email capture form
function email_capture_form_shortcode() {
    ob_start();
    ?>
    <div class="newsletter-container">
        <form method="post" action="">
        <input type="email" name="email" placeholder="Enter your email" required>
        <input type="submit" name="submit_email" value="Subscribe">
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

// Create the database table on plugin activation
function create_email_capture_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'email_capture';
    
    $charset_collate = $wpdb->get_charset_collate();
    
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
register_activation_hook(__FILE__, 'create_email_capture_table');

// Add a custom admin menu and page
function email_capture_admin_menu() {
    add_menu_page(
        'Email List',
        'Email List',
        'manage_options',
        'email-capture-list',
        'email_capture_list_page'
    );
}
add_action('admin_menu', 'email_capture_admin_menu');

// Display the email list page
function email_capture_list_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'email_capture';
    
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        // Delete selected emails
        $selected_emails = $_POST['emails'];
        
        if (!empty($selected_emails)) {
            foreach ($selected_emails as $email_id) {
                $wpdb->delete($table_name, array('id' => $email_id), array('%d'));
            }
            
            echo '<div class="notice notice-success"><p>Email(s) deleted successfully.</p></div>';
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'send_email') {
        // Send email to all emails
        $subject = $_POST['subject'];
        $message = $_POST['message'];
        
        $emails = $wpdb->get_results("SELECT email FROM $table_name");
        
        if (!empty($emails)) {
            foreach ($emails as $email) {
                $message = "<h1>" + $subject + "</h1><br><br>" + $message; 
                $headers = array('Content-Type: text/html; charset=UTF-8');
                wp_mail($email->email, $subject, $message, $headers);
            }
            
            echo '<div class="notice notice-success"><p>Email sent successfully to all recipients.</p></div>';
        }
    }
    
    $emails = $wpdb->get_results("SELECT * FROM $table_name");
    
    echo '<div class="wrap">';
    echo '<h1>Email List</h1>';
    
    if (!empty($emails)) {
        echo '<form method="post" action="">';
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead>';
        echo '<tr>';
        echo '<th scope="col" class="manage-column column-cb check-column"><input type="checkbox" id="cb-select-all"></th>';
        echo '<th scope="col" class="manage-column">Email</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
        
        foreach ($emails as $email) {
            echo '<tr>';
            echo '<th scope="row" class="check-column"><input type="checkbox" name="emails[]" value="' . $email->id . '"></th>';
            echo '<td>' . $email->email . '</td>';
            echo '</tr>';
        }
        
        echo '</tbody>';
        echo '<tfoot>';
        echo '<tr>';
        echo '<th scope="col" class="manage-column column-cb check-column"><input type="checkbox" id="cb-select-all-2"></th>';
        echo '<th scope="col" class="manage-column">Email</th>';
        echo '</tr>';
        echo '</tfoot>';
        echo '</table>';
        
        echo '<div class="bulk-actions">';
        echo '<select name="action">';
        echo '<option value="-1">Bulk Actions</option>';
        echo '<option value="delete">Delete</option>';
        echo '<option value="send_email">Send Email</option>';
        echo '</select>';
        
        // Show the input boxes for sending email
        echo '<div class="email-inputs" style="display: none;">';
        echo '<input type="text" name="subject" placeholder="Subject" required>';
        echo '<textarea name="message" placeholder="Message" required></textarea>';
        echo '</div>';
        
        echo '<input type="submit" name="doaction" value="Apply" class="button action">';
        echo '</div>';
        
        echo '</form>';
    } else {
        echo 'No emails found.';
    }
    
    echo '</div>';
}

// Enqueue necessary scripts and styles for the admin page
function email_capture_admin_enqueue_scripts($hook) {
    if ($hook === 'toplevel_page_email-capture-list') {
        wp_enqueue_script('admin-email-capture', plugin_dir_url(__FILE__) . 'admin-email-capture.js', array('jquery'), '1.0', true);
        wp_enqueue_style('admin-email-capture', plugin_dir_url(__FILE__) . 'admin-email-capture.css');
    }
}
add_action('admin_enqueue_scripts', 'email_capture_admin_enqueue_scripts');
