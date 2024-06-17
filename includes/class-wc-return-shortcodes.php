<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class WC_Return_Shortcodes {
    public function __construct() {
        add_shortcode('wc_return_request_form', [$this, 'return_request_form_shortcode']);
        add_shortcode('wc_return_request_form_guest', [$this, 'return_request_form_guest_shortcode']);
        add_shortcode('wc_return_thank_you', [$this, 'return_thank_you_shortcode']);
    }

    public function return_request_form_shortcode($atts) {
        ob_start();
        include plugin_dir_path(__FILE__) . '../templates/return-request-form.php';
        return ob_get_clean();
    }

    public function return_request_form_guest_shortcode($atts) {
        ob_start();
        include plugin_dir_path(__FILE__) . '../templates/return-request-form-guest.php';
        return ob_get_clean();
    }

    public function thank_you_shortcode() {
        ob_start();

        $thank_you_image = esc_url(get_option('thank_you_image'));
        $thank_you_header = esc_html(get_option('thank_you_header'));
        $thank_you_message = esc_html(get_option('thank_you_message'));

        ?>
        <div class="thank-you-page">
            <?php if ($thank_you_image) : ?>
                <img src="<?php echo $thank_you_image; ?>" alt="<?php echo $thank_you_header; ?>" style="width: <?php echo esc_attr(get_option('thank_you_page_width')); ?>px; height: <?php echo esc_attr(get_option('thank_you_page_height')); ?>px;">
            <?php endif; ?>
            <h1 style="font-size: <?php echo esc_attr(get_option('thank_you_header_font_size')); ?>px; font-family: <?php echo esc_attr(get_option('thank_you_font_family')); ?>;"><?php echo $thank_you_header; ?></h1>
            <p style="font-size: <?php echo esc_attr(get_option('thank_you_paragraph_font_size')); ?>px;"><?php echo $thank_you_message; ?></p>
        </div>
        <?php

        return ob_get_clean();
    }
}

new WC_Return_Shortcodes();
