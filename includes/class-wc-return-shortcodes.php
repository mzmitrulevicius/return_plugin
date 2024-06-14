<?php

class WC_Return_Shortcodes
{
    public function __construct()
    {
        add_shortcode('wc_return_request_form', [$this, 'return_request_form_shortcode']);
        add_shortcode('wc_return_request_form_guest', [$this, 'return_request_form_guest_shortcode']);
        add_shortcode('wc_return_thank_you', [$this, 'return_thank_you_shortcode']);
    }

    public function return_request_form_shortcode($atts)
    {
        ob_start();
        include plugin_dir_path(__FILE__) . '../templates/return-request-form.php';
        return ob_get_clean();
    }

    public function return_request_form_guest_shortcode($atts)
    {
        ob_start();
        include plugin_dir_path(__FILE__) . '../templates/return-request-form-guest.php';
        return ob_get_clean();
    }

    public function thank_you_shortcode()
    {
        ob_start();

        $thank_you_image = esc_url(get_option('thank_you_image'));
        $thank_you_header = esc_html(get_option('thank_you_header'));
        $thank_you_message = esc_html(get_option('thank_you_message'));

        ?>
        <div class="thank-you-page">
            <?php if ($thank_you_image) : ?>
                <img src="<?php echo $thank_you_image; ?>" alt="<?php echo $thank_you_header; ?>">
            <?php endif; ?>
            <h1><?php echo $thank_you_header; ?></h1>
            <p><?php echo $thank_you_message; ?></p>
        </div>
        <?php

        return ob_get_clean();
    }

}

new WC_Return_Shortcodes();
