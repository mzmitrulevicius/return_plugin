<?php
/*
Plugin Name: WooCommerce Return Requests
Description: Handle return requests for WooCommerce orders.
Version: 2.0
Author: Nesas
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

// Initialize the classes
//new WC_Return_Admin();
new WC_Return_Handler();
new WC_Return_Settings();
new WC_Return_Shortcodes();
new WC_Return_User();

// Register the email classes
function wc_register_return_request_emails($email_classes) {
    $email_classes['WC_Email_Return_Request_Submitted'] = include_once plugin_dir_path(__FILE__) . 'emails/class-wc-email-return-request-submitted.php';
    $email_classes['WC_Email_Return_Request_Approved_Opened'] = include_once plugin_dir_path(__FILE__) . 'emails/class-wc-email-return-request-approved-opened.php';
    $email_classes['WC_Email_Return_Request_Approved_Unopened'] = include_once plugin_dir_path(__FILE__) . 'emails/class-wc-email-return-request-approved-unopened.php';
    $email_classes['WC_Email_Return_Request_Rejected'] = include_once plugin_dir_path(__FILE__) . 'emails/class-wc-email-return-request-rejected.php';

    return $email_classes;
}
add_filter('woocommerce_email_classes', 'wc_register_return_request_emails');

// Register the email settings
function wc_add_return_request_email_settings($settings) {
    $settings[] = include_once plugin_dir_path(__FILE__) . 'emails/class-wc-email-return-request-submitted.php';
    $settings[] = include_once plugin_dir_path(__FILE__) . 'emails/class-wc-email-return-request-approved-opened.php';
    $settings[] = include_once plugin_dir_path(__FILE__) . 'emails/class-wc-email-return-request-approved-unopened.php';
    $settings[] = include_once plugin_dir_path(__FILE__) . 'emails/class-wc-email-return-request-rejected.php';

    // Adding custom settings for the return request approved opened email
    $settings[] = array(
        'title'       => __('Return Request Approved (Opened) Email', 'woocommerce'),
        'type'        => 'title',
        'id'          => 'wc_email_return_request_approved_opened_settings'
    );

    $settings[] = array(
        'title'       => __('Email Subject', 'woocommerce'),
        'desc'        => __('Subject of the email sent when a return request is approved and the box is opened.', 'woocommerce'),
        'id'          => 'wc_email_return_request_approved_opened_subject',
        'type'        => 'text',
        'css'         => 'min-width:300px;',
        'default'     => 'Your Return Request has been Approved',
        'desc_tip'    => true,
        'autoload'    => false,
    );

    $settings[] = array(
        'title'       => __('Email Heading', 'woocommerce'),
        'desc'        => __('Heading of the email sent when a return request is approved and the box is opened.', 'woocommerce'),
        'id'          => 'wc_email_return_request_approved_opened_heading',
        'type'        => 'text',
        'css'         => 'min-width:300px;',
        'default'     => 'Your Return Request has been Approved',
        'desc_tip'    => true,
        'autoload'    => false,
    );

    $settings[] = array(
        'title'       => __('Email Content', 'woocommerce'),
        'desc'        => __('Content of the email sent when a return request is approved and the box is opened.', 'woocommerce'),
        'id'          => 'wc_email_return_request_approved_opened_content',
        'type'        => 'textarea',
        'css'         => 'min-width:300px;',
        'default'     => 'Your return request for order {order_number} has been approved.',
        'desc_tip'    => true,
        'autoload'    => false,
    );

    $settings[] = array(
        'type'        => 'sectionend',
        'id'          => 'wc_email_return_request_approved_opened_settings'
    );

    return $settings;
}
add_filter('woocommerce_get_settings_emails', 'wc_add_return_request_email_settings');

function enqueue_custom_return_request_styles() {
    wp_enqueue_style('custom-return-request-styles', plugin_dir_url(__FILE__) . 'assets/css/custom-return-request.css');
}
add_action('wp_enqueue_scripts', 'enqueue_custom_return_request_styles');

function enqueue_thank_you_styles() {
    $thank_you_slug = get_option('thank_you_slug', 'return-form-submitted'); // Default to 'thank-you' if not set
    if (is_page($thank_you_slug)) {
        wp_enqueue_style('thank-you-page', plugin_dir_url(__FILE__) . 'assets/css/thank-you-page.css');
    }
}
add_action('wp_enqueue_scripts', 'enqueue_thank_you_styles');

register_activation_hook(__FILE__, 'set_default_options');
function set_default_options() {
    if (get_option('thank_you_slug') === false) {
        add_option('thank_you_slug', 'thank-you');
    }
}





