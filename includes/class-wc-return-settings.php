<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class WC_Return_Settings
{
    public function __construct()
    {
        add_action('admin_init', [$this, 'register_settings']);
    }

    public function register_settings()
    {
        register_setting('wc_return_requests_settings_group', 'thank_you_image');
        register_setting('wc_return_requests_settings_group', 'thank_you_header');
        register_setting('wc_return_requests_settings_group', 'thank_you_message');

        add_settings_section(
            'wc_return_requests_settings_section',
            __('Thank You Page Settings', 'woocommerce'),
            null,
            'wc-return-requests-settings'
        );

        add_settings_field(
            'thank_you_image',
            __('Thank You Page Image', 'woocommerce'),
            [$this, 'thank_you_image_callback'],
            'wc-return-requests-settings',
            'wc_return_requests_settings_section'
        );

        add_settings_field(
            'thank_you_header',
            __('Thank You Page Header', 'woocommerce'),
            [$this, 'thank_you_header_callback'],
            'wc-return-requests-settings',
            'wc_return_requests_settings_section'
        );

        add_settings_field(
            'thank_you_message',
            __('Thank You Page Message', 'woocommerce'),
            [$this, 'thank_you_message_callback'],
            'wc-return-requests-settings',
            'wc_return_requests_settings_section'
        );
    }

    public function thank_you_image_callback()
    {
        $image = get_option('thank_you_image');
        echo '<input type="text" id="thank_you_image" name="thank_you_image" value="' . esc_attr($image) . '" />';
        echo '<button type="button" class="button upload_image_button">' . __('Upload Image', 'woocommerce') . '</button>';
        if ($image) {
            echo '<br><img src="' . esc_url($image) . '" style="max-width: 300px;"/>';
        }
    }

    public function thank_you_header_callback()
    {
        $header = get_option('thank_you_header');
        echo '<input type="text" id="thank_you_header" name="thank_you_header" value="' . esc_attr($header) . '" />';
    }

    public function thank_you_message_callback()
    {
        $message = get_option('thank_you_message');
        echo '<textarea id="thank_you_message" name="thank_you_message" rows="5" cols="50">' . esc_textarea($message) . '</textarea>';
    }
}

if (!isset($GLOBALS['wc_return_settings'])) {
    $GLOBALS['wc_return_settings'] = new WC_Return_Settings();
}
