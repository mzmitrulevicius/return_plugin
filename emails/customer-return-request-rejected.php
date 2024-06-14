<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

echo '= ' . $email_heading . " =\n\n";

echo $additional_content . "\n\n";

echo "=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

echo apply_filters('woocommerce_email_footer_text', get_option('woocommerce_email_footer_text'));
