<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class WC_Return_Shortcodes {
    public function __construct() {
        add_shortcode('wc_return_request_form', [$this, 'return_request_form_shortcode']);
        add_shortcode('wc_return_request_form_guest', [$this, 'return_request_form_guest_shortcode']);
                add_shortcode('wc_return_thank_you', [$this, 'thank_you_shortcode']);

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
        $thank_you_page_width = esc_html(get_option('thank_you_page_width', '100%'));
        $thank_you_page_height = esc_html(get_option('thank_you_page_height', 'auto'));
        $thank_you_header_font_size = esc_html(get_option('thank_you_header_font_size', '24px'));
        $thank_you_paragraph_font_size = esc_html(get_option('thank_you_paragraph_font_size', '16px'));
        $thank_you_font_family = esc_html(get_option('thank_you_font_family', 'Arial, sans-serif'));

        ?>
    <div class="thank-you-page" style="font-family: <?php echo $thank_you_font_family; ?>; max-width: <?php echo esc_attr($thank_you_content_width); ?>;">
            <?php if ($thank_you_image) : ?>
                <img src="<?php echo $thank_you_image; ?>" alt="<?php echo $thank_you_header; ?>" style="width: <?php echo $thank_you_page_width; ?>; height: <?php echo $thank_you_page_height; ?>;">
            <?php endif; ?>
            <h1 style="font-size: <?php echo $thank_you_header_font_size; ?>;"><?php echo $thank_you_header; ?></h1>
            <p style="font-size: <?php echo $thank_you_paragraph_font_size; ?>;"><?php echo $thank_you_message; ?></p>
        </div>
        <?php

        return ob_get_clean();
    }

}

new WC_Return_Shortcodes();
