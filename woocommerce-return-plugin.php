<?php
/*
Plugin Name: WooCommerce Return Requests
Description: Handle return requests for WooCommerce orders.
Version: 1.0
Author: Your Name
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Include required files
include_once plugin_dir_path(__FILE__) . 'includes/class-wc-return-admin.php';
include_once plugin_dir_path(__FILE__) . 'includes/class-wc-return-handler.php';
include_once plugin_dir_path(__FILE__) . 'includes/class-wc-return-settings.php';
include_once plugin_dir_path(__FILE__) . 'includes/class-wc-return-shortcodes.php';
include_once plugin_dir_path(__FILE__) . 'includes/class-wc-return-user.php';

// Initialize the classes only once
if (class_exists('WC_Return_Admin') && !isset($GLOBALS['wc_return_admin'])) {
    $GLOBALS['wc_return_admin'] = new WC_Return_Admin();
}

if (class_exists('WC_Return_Handler') && !isset($GLOBALS['wc_return_handler'])) {
    $GLOBALS['wc_return_handler'] = new WC_Return_Handler();
}

if (class_exists('WC_Return_Settings') && !isset($GLOBALS['wc_return_settings'])) {
    $GLOBALS['wc_return_settings'] = new WC_Return_Settings();
}

if (class_exists('WC_Return_Shortcodes') && !isset($GLOBALS['wc_return_shortcodes'])) {
    $GLOBALS['wc_return_shortcodes'] = new WC_Return_Shortcodes();
}

if (class_exists('WC_Return_User') && !isset($GLOBALS['wc_return_user'])) {
    $GLOBALS['wc_return_user'] = new WC_Return_User();
}

// Register the email classes
function wc_register_return_request_emails($email_classes) {
    $email_classes['WC_Email_Return_Request_Submitted'] = include_once plugin_dir_path(__FILE__) . 'emails/class-wc-email-return-request-submitted.php';
    $email_classes['WC_Email_Return_Request_Approved_Opened'] = include_once plugin_dir_path(__FILE__) . 'emails/class-wc-email-return-request-approved-opened.php';
    $email_classes['WC_Email_Return_Request_Approved_Unopened'] = include_once plugin_dir_path(__FILE__) . 'emails/class-wc-email-return-request-approved-unopened.php';
    $email_classes['WC_Email_Return_Request_Canceled'] = include_once plugin_dir_path(__FILE__) . 'emails/class-wc-email-return-request-canceled.php';

    return $email_classes;
}
add_filter('woocommerce_email_classes', 'wc_register_return_request_emails');

// Register the email settings
function wc_add_return_request_email_settings($settings) {
    $settings[] = include_once plugin_dir_path(__FILE__) . 'emails/class-wc-email-return-request-submitted.php';
    $settings[] = include_once plugin_dir_path(__FILE__) . 'emails/class-wc-email-return-request-approved-opened.php';
    $settings[] = include_once plugin_dir_path(__FILE__) . 'emails/class-wc-email-return-request-approved-unopened.php';
    $settings[] = include_once plugin_dir_path(__FILE__) . 'emails/class-wc-email-return-request-canceled.php';

    return $settings;
}
add_filter('woocommerce_get_settings_emails', 'wc_add_return_request_email_settings');

// Enqueue scripts and styles
function enqueue_custom_return_request_styles() {
    wp_enqueue_style('custom-return-request-styles', plugin_dir_url(__FILE__) . 'assets/css/custom-return-request.css');
}
add_action('wp_enqueue_scripts', 'enqueue_custom_return_request_styles');

function enqueue_thank_you_styles() {
    if (is_page('thank-you')) { // Adjust the condition to target the correct page
        wp_enqueue_style('thank-you-page', plugin_dir_url(__FILE__) . 'assets/css/thank-you-page.css');
    }
}
add_action('wp_enqueue_scripts', 'enqueue_thank_you_styles');
