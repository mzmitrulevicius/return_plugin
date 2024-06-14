<?php
/**
 * Return Request Submitted email
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

echo $email_heading . "\n\n";

echo sprintf( __( 'A return request for your order #%d has been submitted.', 'woocommerce' ), $order->get_order_number() ) . "\n\n";

echo __( 'Thank you.', 'woocommerce' ) . "\n\n";

echo "****************************************************\n\n";

do_action( 'woocommerce_email_order_details', $order, $sent_to_admin, $plain_text, $email );
do_action( 'woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, $email );
do_action( 'woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text, $email );
do_action( 'woocommerce_email_footer', $email );
