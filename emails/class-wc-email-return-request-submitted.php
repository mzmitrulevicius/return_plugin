<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class WC_Email_Return_Request_Submitted extends WC_Email {

    public function __construct() {
        $this->id             = 'wc_return_request_submitted';
        $this->title          = 'Return Request Submitted';
        $this->description    = 'This email is sent to the customer when a return request is submitted.';
        $this->customer_email = true;

        $this->heading = 'Your Return Request';
        $this->subject = 'Return Request Submitted';

        $this->template_html  = 'emails/customer-return-request-submitted.php';
        $this->template_plain = 'emails/plain/customer-return-request-submitted.php';

        parent::__construct();
    }

    public function trigger( $order_id, $order = false ) {
        if ( $order_id && ! is_a( $order, 'WC_Order' ) ) {
            $order = wc_get_order( $order_id );
        }

        if ( is_a( $order, 'WC_Order' ) ) {
            $this->object = $order;
            $this->recipient = $this->object->get_billing_email();
            $this->placeholders['{order_date}'] = wc_format_datetime( $this->object->get_date_created() );
            $this->placeholders['{order_number}'] = $this->object->get_order_number();
        }

        if ( ! $this->is_enabled() || ! $this->get_recipient() ) {
            return;
        }

        $this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
    }

    public function get_content_html() {
        return wc_get_template_html( $this->template_html, array(
            'order'         => $this->object,
            'email_heading' => $this->get_heading(),
            'sent_to_admin' => false,
            'plain_text'    => false,
            'email'         => $this,
        ) );
    }

    public function get_content_plain() {
        return wc_get_template_html( $this->template_plain, array(
            'order'         => $this->object,
            'email_heading' => $this->get_heading(),
            'sent_to_admin' => false,
            'plain_text'    => true,
            'email'         => $this,
        ) );
    }
}

return new WC_Email_Return_Request_Submitted();
