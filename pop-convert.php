<?php
/*
Plugin Name: Pop Convert ‑ Popups & Smart Bars
Description: Use popups and banners to collect emails and phone numbers from visitors!
Version: 1.0
Author: CartKit
Author URI: https://cartkit.com
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

if (!defined('ABSPATH')) {
    exit;
}

if(!function_exists('add_action')){
    die;
}

// Function to enqueue the script with the token
function pop_convert_custom_enqueue_script() {
    $token = get_option('pop_convert_plugin_token', '');
    
    if (!empty($token) && !wp_script_is('pop-convert-script', 'enqueued')) {
        $script_url = 'https://script.pop-convert.com/production.pc.min.js?token=' . $token;
        wp_enqueue_script('pop-convert-script', $script_url, array(), null, true);
    }
}

add_action('wp_enqueue_scripts', 'pop_convert_custom_enqueue_script');

// Hook to add an admin menu
function pop_convert_custom_plugin_menu() {
    add_menu_page(
        'Pop Convert Settings', // page title
        'Pop Convert', // menu title
        'manage_options', // users that have the manage_options capability can see this page
        'pop-convert', // the slug (urL)
        'pop_convert_page', // the function that is called to render the page
        'dashicons-yes-alt', // icon
        90 // how far down the menu this item should live
    );
}

// Callback function to render the admin page
function pop_convert_page() {
    // Check if user is logged in
    if (!is_user_logged_in()) {
        wp_redirect(wp_login_url());
        exit;
    }

    $error_message = '';
    $token = get_option('pop_convert_plugin_token', '');

    // Check if the form has been submitted
    if (isset($_POST['pop_convert_plugin_token'], $_POST['_wpnonce_pop_convert_plugin_nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_wpnonce_pop_convert_plugin_nonce'], 'pop_convert_plugin_action')))) {
        $submitted_token = sanitize_text_field($_POST['pop_convert_plugin_token']);

        // Validate the token
        if (strlen($submitted_token) > 10) {
            // Token is valid, update it in the database
            update_option('pop_convert_plugin_token', $submitted_token);
            $token = $submitted_token;
            echo '<div id="message" class="updated notice notice-success is-dismissible"><p>Token updated successfully.</p></div>';
            
            // Enqueue the script with the new token
            pop_convert_custom_enqueue_script();
        } else {
            // Token is invalid, show an error message
            $error_message = '<div id="message" class="notice notice-error"><p>Error: The token provided is not valid.</p></div>';
        }
    } else if (isset($_POST['pop_convert_plugin_token'])) {
        // Nonce verification failed
        $error_message = '<div id="message" class="notice notice-error"><p>Error: Security check failed.</p></div>';
    }

    ?>
    <div class="wrap pc-wrap">
        <h1>Pop Convert ‑ Popups & Smart Bars</h1>
        <p>Step 1: Login to Pop Convert</p>
        <a target="_blank" href="https://www.pop-convert.com/signin" class="button button-primary">Login</a>
        <p>Step 2: Add Your Unique Token Below</p>
        <?php echo wp_kses_post($error_message); ?>
        <form method="post" action="">
            <?php wp_nonce_field('pop_convert_plugin_action', '_wpnonce_pop_convert_plugin_nonce'); ?>
            <input type="text" name="pop_convert_plugin_token" value="<?php echo esc_attr($token); ?>" />
            <input type="submit" value="Save Token" class="button button-primary" />
        </form>

        <?php if ($token): ?>
            <div id="success-banner" style="position: fixed; bottom: 0; left: 0; width: 100%; background-color: green; color: white; text-align: center; padding: 10px;">
                <p>Pop Convert is successfully installed on your WordPress site!</p>
            </div>
        <?php endif; ?>
    </div>

    <style>
        .pc-wrap a.button:visited {
            color:  white;
        }
    </style>

    <?php
}

// Add the menu page using the admin_menu hook
add_action('admin_menu', 'pop_convert_custom_plugin_menu');
