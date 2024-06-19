<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}
require_once(ABSPATH . 'wp-admin/includes/file.php'); // Ensure this line is included
require_once(ABSPATH . 'wp-admin/includes/media.php'); // Add this line
require_once(ABSPATH . 'wp-admin/includes/image.php'); // Add this line

class WC_Return_Handler {

    public function __construct() {
        add_action('init', [$this, 'handle_return_request_submission']);
        add_action('save_post_wc_return_request', [$this, 'handle_status_change'], 10, 3);
    }
    public function enqueue_admin_scripts($hook) {
    if ($hook != 'toplevel_page_wc-return-requests' && $hook != 'woocommerce_page_wc-return-requests-settings') {
        return;
    }
    wp_enqueue_media();
    wp_enqueue_script('wc_return_request_admin_js', plugin_dir_url(__FILE__) . 'includes/js/wc-return-request-admin.js', array('jquery'), '1.0', true);
    wp_enqueue_style('wc_return_request_admin_css', plugin_dir_url(__FILE__) . 'includes/css/wc-return-request-admin.css', array(), '1.0');
}


    public function handle_return_request_submission() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['wc_return_request'])) {
            // Verify the nonce
            if (!isset($_POST['return_request_nonce']) || !wp_verify_nonce($_POST['return_request_nonce'], 'submit_return_request')) {
                $this->add_notice('Invalid nonce.', 'error');
                return;
            }

            // Validate the form data
            $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
            $order_email = isset($_POST['guest_email']) ? sanitize_email($_POST['guest_email']) : '';
            if (!$order_id || (!$order_email && !is_user_logged_in())) {
                $this->add_notice('Please fill in all required fields.', 'error');
                return;
            }

            // Check if the order exists
            $order = wc_get_order($order_id);
            if (!$order || ($order->get_user_id() !== get_current_user_id() && !current_user_can('manage_woocommerce') && $order->get_billing_email() !== $order_email)) {
                $this->add_notice('Invalid order.', 'error');
                return;
            }

            // Check if a return request already exists for this order
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
                $this->add_notice('A return request for this order has already been submitted.', 'error');
                return;
            }

            // Save the return request
            $return_request = [
                'post_title' => 'Return Request for Order ' . $order->get_order_number(),
                'post_status' => 'publish',
                'post_author' => get_current_user_id(),
                'post_type' => 'wc_return_request',
                'meta_input' => [
                    '_order_id' => $order_id,
                    '_request_date' => current_time('mysql'),
                ],
            ];

            $return_request_id = wp_insert_post($return_request);

            if ($return_request_id) {
                foreach ($order->get_items() as $item_id => $item) {
    if (isset($_POST['return_qty'][$item_id]) && $_POST['return_qty'][$item_id] > 0) {
        update_post_meta($return_request_id, '_return_qty_' . $item_id, sanitize_text_field($_POST['return_qty'][$item_id]));
        update_post_meta($return_request_id, '_return_reason_' . $item_id, sanitize_textarea_field($_POST['return_reason'][$item_id]));
        update_post_meta($return_request_id, '_box_opened_' . $item_id, isset($_POST['box_opened'][$item_id]) ? 'yes' : 'no');
        update_post_meta($return_request_id, '_bottle_broken_' . $item_id, isset($_POST['bottle_broken'][$item_id]) ? 'yes' : 'no');

        // Handle image uploads
      if (isset($_FILES['return_images']['name'][$item_id])) {
    $file_urls = [];
    foreach ($_FILES['return_images']['name'][$item_id] as $key => $value) {
        if ($value) {
            $file = [
                'name' => $_FILES['return_images']['name'][$item_id][$key],
                'type' => $_FILES['return_images']['type'][$item_id][$key],
                'tmp_name' => $_FILES['return_images']['tmp_name'][$item_id][$key],
                'error' => $_FILES['return_images']['error'][$item_id][$key],
                'size' => $_FILES['return_images']['size'][$item_id][$key],
            ];

            $_FILES = ['upload' => $file];
            $attachment_id = media_handle_upload('upload', 0);

            if (!is_wp_error($attachment_id)) {
                $file_urls[] = wp_get_attachment_url($attachment_id);
            }
        }
    }

    if (!empty($file_urls)) {
        update_post_meta($return_request_id, '_return_images_' . $item_id, $file_urls);
    }
}




                    }
                }

                $this->add_notice('Your return request has been submitted successfully.', 'success');

                // Send email to the appropriate email address
                $this->send_return_request_email($order->get_billing_email(), $order_id, $_POST['return_reason']);

                // Redirect to the thank you page
                wp_redirect(get_permalink(get_page_by_path('return-form-submitted')) . '?return_submitted=true');
                exit;
            } else {
                $this->add_notice('An error occurred. Please try again.', 'error');
            }
        }
    }

    private function add_notice($message, $type = 'success') {
        if (function_exists('wc_add_notice')) {
            wc_add_notice($message, $type);
        } else {
            // Fallback method if wc_add_notice is not available
            add_action('wp_footer', function() use ($message, $type) {
                echo '<div class="woocommerce-message ' . esc_attr($type) . '">' . esc_html($message) . '</div>';
            });
        }
    }

    private function send_return_request_email($email, $order_id, $return_reasons) {
        $subject = get_option('wc_return_request_email_subject', 'Your Return Request');
        $message = get_option('wc_return_request_email_message', 'Thank you for your return request. We will process it soon.');

        $placeholders = [
            '{order_id}' => $order_id,
            '{return_reason}' => implode(", ", $return_reasons),
        ];

        $message = str_replace(array_keys($placeholders), array_values($placeholders), $message);

        wp_mail($email, $subject, $message);
    }

    public function handle_status_change($post_id, $post, $update) {
        if ($post->post_type != 'wc_return_request') {
            return;
        }

        $status = isset($_POST['return_request_status']) ? sanitize_text_field($_POST['return_request_status']) : '';

        if ($status == 'approved') {
            $order_id = get_post_meta($post_id, '_order_id', true);
            $order = wc_get_order($order_id);
            if ($order) {
                $box_opened = get_post_meta($post_id, '_box_opened', true);
                if ($box_opened == 'yes') {
                    $email_class = WC()->mailer()->emails['WC_Email_Return_Request_Approved_Opened'];
                } else {
                    $email_class = WC()->mailer()->emails['WC_Email_Return_Request_Approved_Unopened'];
                }
                $email_class->trigger($order_id, $order);
            }
        } elseif ($status == 'canceled') {
            $order_id = get_post_meta($post_id, '_order_id', true);
            $order = wc_get_order($order_id);
            if ($order) {
                $email_class = WC()->mailer()->emails['WC_Email_Return_Request_Rejected'];
                $email_class->trigger($order_id, $order);
            }
        }
    }

}


new WC_Return_Handler();
