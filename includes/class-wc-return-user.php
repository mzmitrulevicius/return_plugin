<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class WC_Return_User
{
    public function __construct()
    {
        add_action('woocommerce_my_account_my_orders_actions', [$this, 'add_return_button_to_orders'], 10, 2);
        add_action('template_redirect', [$this, 'handle_return_request_submission']);
        add_shortcode('wc_return_request_form', [$this, 'display_return_request_form']);
    }

    public function add_return_button_to_orders($actions, $order)
    {
        $order_date = $order->get_date_created();
        $current_date = current_time('mysql');
        $date_diff = date_diff(date_create($order_date), date_create($current_date))->days;

        if ($order->has_status(['completed', 'processing']) && $date_diff <= 30) {
            $actions['return'] = [
                'url' => esc_url(add_query_arg(['return-request' => 'true', 'order_id' => $order->get_id()], site_url('return-request'))),
                'name' => __('Request Return', 'woocommerce'),
            ];
        }
        return $actions;
    }

    public function display_return_request_form()
    {
        ob_start();
        if (isset($_GET['order_id'])) {
            include plugin_dir_path(__FILE__) . '../templates/return-request-form.php';
        }
        return ob_get_clean();
    }

    public function handle_return_request_submission()
    {
        if (isset($_POST['wc_return_request'])) {
            check_admin_referer('submit_return_request', 'return_request_nonce');

            $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
            $return_reason = isset($_POST['return_reason']) ? sanitize_textarea_field($_POST['return_reason']) : '';
            $box_opened = isset($_POST['box_opened']) ? 'yes' : 'no';

            $order = wc_get_order($order_id);
            if (!$order || ($order->get_user_id() != get_current_user_id())) {
                $this->add_notice('Invalid order.', 'error');
                return;
            }

            // Check for existing return requests
            $existing_requests = get_posts([
                'post_type' => 'wc_return_request',
                'post_status' => 'any',
                'meta_query' => [
                    [
                        'key' => '_order_id',
                        'value' => $order_id,
                    ]
                ]
            ]);

            if (!empty($existing_requests)) {
                $this->add_notice('A return request for this order already exists.', 'error');
                wp_redirect(wc_get_account_endpoint_url('orders'));
                exit;
            }

            $return_request = [
                'post_title' => 'Return Request for Order ' . $order->get_order_number(),
                'post_content' => $return_reason,
                'post_status' => 'pending',
                'post_author' => get_current_user_id(),
                'post_type' => 'wc_return_request',
                'meta_input' => [
                    '_order_id' => $order->get_id(),
                    '_request_date' => current_time('mysql'),
                    '_return_request_status' => 'pending', // Initial status
                    '_box_opened' => $box_opened, // Capture checkbox value
                ],
            ];

            $return_request_id = wp_insert_post($return_request);

            if ($return_request_id) {
                $this->add_notice('Return request submitted.', 'success');
            } else {
                $this->add_notice('An error occurred. Please try again.', 'error');
            }

            wp_redirect(site_url('/return-form-submitted'));
            exit;
        }
    }

    private function add_notice($message, $type = 'success')
    {
        if (function_exists('wc_add_notice')) {
            wc_add_notice($message, $type);
        } else {
            // Fallback method if wc_add_notice is not available
            add_action('wp_footer', function() use ($message, $type) {
                echo '<div class="woocommerce-message ' . esc_attr($type) . '">' . esc_html($message) . '</div>';
            });
        }
    }
}

new WC_Return_User();
