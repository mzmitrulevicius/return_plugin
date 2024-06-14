<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class WC_Email_Return_Request_Approved_Unopened extends WC_Email {

    public function __construct() {
        $this->id = 'wc_return_request_approved_unopened';
        $this->title = 'Return Request Approved (Box Not Opened)';
        $this->description = 'This email is sent when a return request is approved and the box was not opened.';

        $this->template_html = 'emails/customer-return-request-approved-unopened.php';
        $this->template_plain = 'emails/plain/customer-return-request-approved-unopened.php';
        $this->template_base = plugin_dir_path(__FILE__) . '../'; // Adjust this line

        // Call parent constructor
        parent::__construct();

        // Triggers for this email
        add_action('wc_return_request_status_approved_unopened_notification', [$this, 'trigger'], 10, 2);

        // Load settings
        $this->subject = $this->get_option('subject', $this->get_default_subject());
        $this->heading = $this->get_option('heading', $this->get_default_heading());
        $this->additional_content = $this->get_option('additional_content', $this->get_default_additional_content());
    }

    public function get_default_subject() {
        return __('Your Return Request has been Approved', 'woocommerce');
    }

    public function get_default_heading() {
        return __('Your Return Request has been Approved', 'woocommerce');
    }

    public function trigger($order_id, $order = false) {
        if ($order_id && ! is_a($order, 'WC_Order')) {
            $order = wc_get_order($order_id);
        }

        if (is_a($order, 'WC_Order')) {
            $this->object = $order;
            $this->recipient = $this->object->get_billing_email();
            $this->placeholders['{order_date}'] = wc_format_datetime($this->object->get_date_created());
            $this->placeholders['{order_number}'] = $this->object->get_order_number();
        } else {
            return;
        }

        if (! $this->is_enabled() || ! $this->get_recipient()) {
            return;
        }

        $this->send($this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments());
    }

    public function get_content_html() {
        ob_start();
        wc_get_template($this->template_html, [
            'order' => $this->object,
            'email_heading' => $this->get_heading(),
            'additional_content' => $this->format_string($this->additional_content),
            'sent_to_admin' => false,
            'plain_text' => false,
            'email' => $this,
        ], '', $this->template_base);
        return ob_get_clean();
    }

    public function get_content_plain() {
        ob_start();
        wc_get_template($this->template_plain, [
            'order' => $this->object,
            'email_heading' => $this->get_heading(),
            'additional_content' => $this->format_string($this->additional_content),
            'sent_to_admin' => false,
            'plain_text' => true,
            'email' => $this,
        ], '', $this->template_base);
        return ob_get_clean();
    }

    public function init_form_fields() {
        $this->form_fields = [
            'enabled' => [
                'title' => __('Enable/Disable', 'woocommerce'),
                'type' => 'checkbox',
                'label' => __('Enable this email notification', 'woocommerce'),
                'default' => 'yes',
            ],
            'subject' => [
                'title' => __('Subject', 'woocommerce'),
                'type' => 'text',
                'description' => sprintf(__('Defaults to: %s', 'woocommerce'), $this->get_default_subject()),
                'placeholder' => '',
                'default' => '',
                'desc_tip' => true,
            ],
            'heading' => [
                'title' => __('Email Heading', 'woocommerce'),
                'type' => 'text',
                'description' => sprintf(__('Defaults to: %s', 'woocommerce'), $this->get_default_heading()),
                'placeholder' => '',
                'default' => '',
                'desc_tip' => true,
            ],
            'additional_content' => [
                'title' => __('Additional content', 'woocommerce'),
                'description' => __('Text to appear below the main email content.', 'woocommerce'),
                'css' => 'width:400px; height:75px;',
                'placeholder' => __('N/A', 'woocommerce'),
                'type' => 'textarea',
                'default' => $this->get_default_additional_content(),
                'desc_tip' => true,
            ],
            'email_type' => [
                'title' => __('Email type', 'woocommerce'),
                'type' => 'select',
                'description' => __('Choose which format of email to send.', 'woocommerce'),
                'default' => 'html',
                'class' => 'email_type wc-enhanced-select',
                'options' => $this->get_email_type_options(),
                'desc_tip' => true,
            ],
        ];
    }

    public function get_default_additional_content() {
        return __('Thank you for your order.', 'woocommerce');
    }

    public function format_string($string) {
        return str_replace(
            array_keys($this->placeholders),
            array_values($this->placeholders),
            $string
        );
    }
}

return new WC_Email_Return_Request_Approved_Unopened();
