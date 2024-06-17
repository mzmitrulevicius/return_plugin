<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}
class WC_Return_Settings {
    public function __construct() {
        add_action('admin_init', [$this, 'register_settings']);
    }

    public function register_settings() {
        register_setting('wc_return_requests_settings_group', 'thank_you_image');
        register_setting('wc_return_requests_settings_group', 'thank_you_header');
        register_setting('wc_return_requests_settings_group', 'thank_you_message');
        register_setting('wc_return_requests_settings_group', 'thank_you_page_width');
        register_setting('wc_return_requests_settings_group', 'thank_you_page_height');
        register_setting('wc_return_requests_settings_group', 'thank_you_header_font_size');
        register_setting('wc_return_requests_settings_group', 'thank_you_paragraph_font_size');
        register_setting('wc_return_requests_settings_group', 'thank_you_font_family');
        register_setting('wc_return_requests_settings_group', 'thank_you_slug');

        add_settings_section('wc_return_requests_general_section', __('General Settings', 'woocommerce-return-plugin'), null, 'wc-return-requests-settings');
        add_settings_section('wc_return_requests_thank_you_section', __('Thank You Page Settings', 'woocommerce-return-plugin'), null, 'wc-return-requests-settings-thank-you');

        add_settings_field('thank_you_image', __('Thank You Page Image', 'woocommerce-return-plugin'), [$this, 'thank_you_image_callback'], 'wc-return-requests-settings-thank-you', 'wc_return_requests_thank_you_section');
        add_settings_field('thank_you_header', __('Thank You Page Header', 'woocommerce-return-plugin'), [$this, 'thank_you_header_callback'], 'wc-return-requests-settings-thank-you', 'wc_return_requests_thank_you_section');
        add_settings_field('thank_you_message', __('Thank You Page Message', 'woocommerce-return-plugin'), [$this, 'thank_you_message_callback'], 'wc-return-requests-settings-thank-you', 'wc_return_requests_thank_you_section');
        add_settings_field('thank_you_page_width', __('Thank You Page Image Width', 'woocommerce-return-plugin'), [$this, 'thank_you_page_width_callback'], 'wc-return-requests-settings-thank-you', 'wc_return_requests_thank_you_section');
        add_settings_field('thank_you_page_height', __('Thank You Page Image Height', 'woocommerce-return-plugin'), [$this, 'thank_you_page_height_callback'], 'wc-return-requests-settings-thank-you', 'wc_return_requests_thank_you_section');
        add_settings_field('thank_you_header_font_size', __('Header Font Size', 'woocommerce-return-plugin'), [$this, 'thank_you_header_font_size_callback'], 'wc-return-requests-settings-thank-you', 'wc_return_requests_thank_you_section');
        add_settings_field('thank_you_paragraph_font_size', __('Paragraph Font Size', 'woocommerce-return-plugin'), [$this, 'thank_you_paragraph_font_size_callback'], 'wc-return-requests-settings-thank-you', 'wc_return_requests_thank_you_section');
        add_settings_field('thank_you_font_family', __('Font Family', 'woocommerce-return-plugin'), [$this, 'thank_you_font_family_callback'], 'wc-return-requests-settings-thank-you', 'wc_return_requests_thank_you_section');
        add_settings_field('thank_you_slug', __('Thank You Page Slug', 'woocommerce-return-plugin'), [$this, 'thank_you_slug_callback'], 'wc-return-requests-settings-thank-you', 'wc_return_requests_thank_you_section');
    }

    public function thank_you_image_callback() {
        $thank_you_image = get_option('thank_you_image');
        echo '<input type="text" id="thank_you_image" name="thank_you_image" value="' . esc_attr($thank_you_image) . '" />';
        echo '<button type="button" class="button" id="upload_image_button">' . __('Upload Image', 'woocommerce') . '</button>';
        if ($thank_you_image) {
            echo '<img src="' . esc_url($thank_you_image) . '" style="max-width: 100%; height: auto;" />';
        }
    }

    public function thank_you_header_callback() {
        $thank_you_header = get_option('thank_you_header');
        echo '<input type="text" id="thank_you_header" name="thank_you_header" value="' . esc_attr($thank_you_header) . '" />';
    }

    public function thank_you_message_callback() {
        $thank_you_message = get_option('thank_you_message');
        echo '<textarea id="thank_you_message" name="thank_you_message" rows="5" cols="50">' . esc_textarea($thank_you_message) . '</textarea>';
    }

    public function thank_you_page_width_callback() {
        $value = get_option('thank_you_page_width', '');
        echo '<input type="text" id="thank_you_page_width" name="thank_you_page_width" value="' . esc_attr($value) . '" />';
    }

    public function thank_you_page_height_callback() {
        $value = get_option('thank_you_page_height', '');
        echo '<input type="text" id="thank_you_page_height" name="thank_you_page_height" value="' . esc_attr($value) . '" />';
    }

    public function thank_you_header_font_size_callback() {
        $value = get_option('thank_you_header_font_size', '');
        echo '<input type="text" id="thank_you_header_font_size" name="thank_you_header_font_size" value="' . esc_attr($value) . '" />';
    }

    public function thank_you_paragraph_font_size_callback() {
        $value = get_option('thank_you_paragraph_font_size', '');
        echo '<input type="text" id="thank_you_paragraph_font_size" name="thank_you_paragraph_font_size" value="' . esc_attr($value) . '" />';
    }

    public function thank_you_font_family_callback() {
        $value = get_option('thank_you_font_family', '');
        echo '<input type="text" id="thank_you_font_family" name="thank_you_font_family" value="' . esc_attr($value) . '" />';
    }

    public function thank_you_slug_callback() {
        $value = get_option('thank_you_slug', '');
        echo '<input type="text" id="thank_you_slug" name="thank_you_slug" value="' . esc_attr($value) . '" />';
    }
}
