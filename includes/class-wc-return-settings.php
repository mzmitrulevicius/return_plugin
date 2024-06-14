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
            __('Return Requests Settings', 'woocommerce'),
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
        $thank_you_image = get_option('thank_you_image');
        echo '<input type="text" id="thank_you_image" name="thank_you_image" value="' . esc_attr($thank_you_image) . '" />';
        echo '<button type="button" class="button" id="upload_image_button">' . __('Upload Image', 'woocommerce') . '</button>';
        if ($thank_you_image) {
            echo '<img src="' . esc_url($thank_you_image) . '" style="max-width: 100%; height: auto;" />';
        }
    }

    public function thank_you_header_callback()
    {
        $thank_you_header = get_option('thank_you_header');
        echo '<input type="text" id="thank_you_header" name="thank_you_header" value="' . esc_attr($thank_you_header) . '" />';
    }

    public function thank_you_message_callback()
    {
        $thank_you_message = get_option('thank_you_message');
        echo '<textarea id="thank_you_message" name="thank_you_message" rows="5" cols="50">' . esc_textarea($thank_you_message) . '</textarea>';
    }
}

new WC_Return_Settings();