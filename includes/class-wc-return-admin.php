<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class WC_Return_Admin
{
    public function __construct()
    {
        add_action('init', [$this, 'register_return_request_post_type']);
        add_action('admin_menu', [$this, 'add_menu_pages']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);
        add_action('add_meta_boxes', [$this, 'add_meta_boxes']);
        add_action('save_post', [$this, 'save_return_request_status'], 10, 2);
        add_filter('wp_insert_post_data', [$this, 'remove_editor_for_return_requests'], 10, 2);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('admin_init', [$this, 'register_settings']);
    }

    public function remove_editor_for_return_requests($data, $postarr)
    {
        if ($data['post_type'] == 'wc_return_request') {
            $data['post_content'] = ''; // Empty the content to remove the editor
        }
        return $data;
    }

    public function add_meta_boxes()
    {
        add_meta_box(
            'wc_return_request_details',
            __('Return Request Details', 'woocommerce'),
            [$this, 'return_request_details_meta_box'],
            'wc_return_request',
            'normal',
            'high'
        );
    }

    public function register_return_request_post_type()
    {
        register_post_type('wc_return_request', [
            'labels' => [
                'name' => __('Return Requests', 'woocommerce-return-plugin'),
                'singular_name' => __('Return Request', 'woocommerce-return-plugin'),
            ],
            'public' => false,
            'has_archive' => false,
            'show_ui' => true,
            'show_in_menu' => false, // Do not automatically add to menu
            'supports' => ['title', 'editor', 'custom-fields'],
        ]);
    }

    public function return_request_details_meta_box($post)
    {
        $order_id = get_post_meta($post->ID, '_order_id', true);
        $order = wc_get_order($order_id);
        $box_opened = get_post_meta($post->ID, '_box_opened', true);
        $return_status = get_post_meta($post->ID, '_return_request_status', true);
        $return_reason = get_post_meta($post->ID, '_return_reason', true); // Get the reason from post meta

        ?>
        <div class="order_data_column">
            <h3><?php _e('Order Details', 'woocommerce'); ?></h3>
            <p><?php printf(__('Order Number: %s', 'woocommerce'), esc_html($order->get_order_number())); ?></p>
            <p><?php printf(__('Order Date: %s', 'woocommerce'), esc_html($order->get_date_created()->date('F j, Y'))); ?></p>
            <p><?php printf(__('Order Status: %s', 'woocommerce'), esc_html(wc_get_order_status_name($order->get_status()))); ?></p>

            <h3><?php _e('Return Request Details', 'woocommerce'); ?></h3>
            <p><?php printf(__('Reason: %s', 'woocommerce'), esc_html($return_reason)); ?></p>
            <p><?php printf(__('Box was opened: %s', 'woocommerce'), esc_html($box_opened === 'yes' ? 'Yes' : 'No')); ?></p>

            <h3><?php _e('Return Request Status', 'woocommerce'); ?></h3>
            <select name="return_request_status" id="return_request_status">
                <option value="pending" <?php selected($return_status, 'pending'); ?>><?php _e('Pending', 'woocommerce'); ?></option>
                <option value="approved" <?php selected($return_status, 'approved'); ?>><?php _e('Approved', 'woocommerce'); ?></option>
                <option value="canceled" <?php selected($return_status, 'canceled'); ?>><?php _e('Canceled', 'woocommerce'); ?></option>
            </select>
        </div>
        <?php
    }

    public function add_menu_pages()
    {
        // Ensure no duplicate menu by checking for existing menu pages
        remove_menu_page('wc-return-requests');

        // Add the top-level menu and submenus
        add_menu_page(
            'Return Requests',
            'Return Requests',
            'manage_woocommerce',
            'wc-return-requests',
            [$this, 'return_requests_dashboard'],
            'dashicons-archive',
            56
        );

        // Remove potential existing submenu pages to avoid duplication
        remove_submenu_page('wc-return-requests', 'wc-return-requests');
        remove_submenu_page('wc-return-requests', 'edit.php?post_type=wc_return_request');
        remove_submenu_page('wc-return-requests', 'wc-return-requests-settings');

        add_submenu_page(
            'wc-return-requests',
            'Dashboard',
            'Dashboard',
            'manage_woocommerce',
            'wc-return-requests',
            [$this, 'return_requests_dashboard']
        );

        add_submenu_page(
            'wc-return-requests',
            'All Return Requests',
            'All Return Requests',
            'manage_woocommerce',
            'edit.php?post_type=wc_return_request'
        );

        add_submenu_page(
            'wc-return-requests',
            'Settings',
            'Settings',
            'manage_woocommerce',
            'wc-return-requests-settings',
            [$this, 'settings_page']
        );
    }

    public function return_requests_dashboard()
    {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Return Requests Dashboard', 'woocommerce-return-plugin'); ?></h1>
            <div id="wc-return-requests">
                <p><?php esc_html_e('Manage all return requests from here.', 'woocommerce-return-plugin'); ?></p>
            </div>
        </div>
        <?php
    }

    public function settings_page()
    {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Return Requests Settings', 'woocommerce-return-plugin'); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('wc_return_requests_settings_group');
                do_settings_sections('wc-return-requests-settings');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    public function enqueue_admin_scripts($hook)
    {
        if ('post.php' != $hook) {
            return;
        }
        wp_enqueue_script('wc_return_request_admin_js', plugin_dir_url(__FILE__) . 'js/wc-return-request-admin.js', array('jquery'), '1.0', true);
    }

    public function save_return_request_status($post_id, $post)
{
    if ($post->post_type == 'wc_return_request') {
        if (isset($_POST['return_request_status'])) {
            update_post_meta($post_id, '_return_request_status', sanitize_text_field($_POST['return_request_status']));
        }
        if (isset($_POST['return_reason'])) {
            update_post_meta($post_id, '_return_reason', sanitize_textarea_field($_POST['return_reason']));
        }
    }
}

public function enqueue_scripts() {
    wp_enqueue_script(
        'wc-return-request-admin',
        plugin_dir_url(__FILE__) . 'js/wc-return-request-admin.js',
        ['jquery'],
        '1.0',
        true
    );
}

// In class-wc-return-admin.php (if not already present)
public function register_settings() {
    register_setting('wc_return_requests_settings_group', 'thank_you_image');
    register_setting('wc_return_requests_settings_group', 'thank_you_header');
    register_setting('wc_return_requests_settings_group', 'thank_you_message');

    add_settings_section('wc_return_requests_settings_section', __('Thank You Page Settings', 'woocommerce'), null, 'wc-return-requests-settings');

    add_settings_field(
        'thank_you_image',
        __('Thank You Image URL', 'woocommerce'),
        [$this, 'image_url_callback'],
        'wc-return-requests-settings',
        'wc_return_requests_settings_section'
    );

    add_settings_field(
        'thank_you_header',
        __('Thank You Header Text', 'woocommerce'),
        [$this, 'header_text_callback'],
        'wc-return-requests-settings',
        'wc_return_requests_settings_section'
    );

    add_settings_field(
        'thank_you_message',
        __('Thank You Message Text', 'woocommerce'),
        [$this, 'message_text_callback'],
        'wc-return-requests-settings',
        'wc_return_requests_settings_section'
    );
}

public function image_url_callback() {
    $thank_you_image = get_option('thank_you_image');
    echo '<input type="text" id="thank_you_image" name="thank_you_image" value="' . esc_attr($thank_you_image) . '" />';
}

public function header_text_callback() {
    $thank_you_header = get_option('thank_you_header');
    echo '<input type="text" id="thank_you_header" name="thank_you_header" value="' . esc_attr($thank_you_header) . '" />';
}

public function message_text_callback() {
    $thank_you_message = get_option('thank_you_message');
    echo '<textarea id="thank_you_message" name="thank_you_message">' . esc_textarea($thank_you_message) . '</textarea>';
}

}

new WC_Return_Admin();