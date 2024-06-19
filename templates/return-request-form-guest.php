<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['order_id'], $_POST['guest_email'])) {
    $order_id = intval($_POST['order_id']);
    $order_email = sanitize_email($_POST['guest_email']);
    $order = wc_get_order($order_id);

    if ($order && $order->get_billing_email() === $order_email) {
        $order_date = $order->get_date_created();
        $current_date = current_time('mysql');
        $date_diff = date_diff(date_create($order_date), date_create($current_date))->days;

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
            $existing_request = $existing_requests[0];
            $box_opened = get_post_meta($existing_request->ID, '_box_opened', true);
            ?>
            <div class="woocommerce-account">
                <h2><?php _e('Return Request Already Submitted', 'woocommerce'); ?></h2>
                <p><?php printf(__('A return request for Order #%s was already submitted on %s.', 'woocommerce'), esc_html($order->get_order_number()), esc_html(get_post_meta($existing_request->ID, '_request_date', true))); ?></p>
                <p><?php _e('Reason:', 'woocommerce'); ?> <?php echo esc_html($existing_request->post_content); ?></p>
                <p><?php _e('Box was opened:', 'woocommerce'); ?> <?php echo esc_html($box_opened === 'yes' ? 'Yes' : 'No'); ?></p>
            </div>
        <?php } elseif ($date_diff > 30) { ?>
            <p><?php esc_html_e('You can only return orders within 30 days of purchase.', 'woocommerce'); ?></p>
        <?php } else { ?>
            <div class="woocommerce-account">
                <h2 class="text-center"><?php printf(__('Return Request for Order #%s', 'woocommerce'), esc_html($order->get_order_number())); ?></h2>
                <form id="wc-return-request-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" enctype="multipart/form-data">
                    <?php wp_nonce_field('submit_return_request', 'return_request_nonce'); ?>
                    <h3 class="text-center"><?php esc_html_e('Order Information', 'woocommerce'); ?></h3>
                    <div id="return-order-info">
                    <p><?php printf(__('Order Number: %s', 'woocommerce'), esc_html($order->get_order_number())); ?></p>
                    <p><?php printf(__('Order Date: %s', 'woocommerce'), esc_html($order->get_date_created()->date('F j, Y'))); ?></p>
                    <p><?php printf(__('Order Status: %s', 'woocommerce'), esc_html(wc_get_order_status_name($order->get_status()))); ?></p>
                    </div>
                    <h3 class="text-center"><?php esc_html_e('Order Details', 'woocommerce'); ?></h3>
                    <table class="shop_table order_details">
                        <tbody>
                            <?php foreach ($order->get_items() as $item_id => $item) :
                                $product = $item->get_product();
                                $product_name = $product->get_name();
                                $product_image = wp_get_attachment_image_src(get_post_thumbnail_id($product->get_id()), 'single-post-thumbnail');
                                $qty = $item->get_quantity();
                            ?>
                                <tr>
                                    <td class="product-image">
                                        <img src="<?php echo $product_image[0]; ?>" alt="<?php echo esc_attr($product_name); ?>" style="width: 75px; height: auto;">
                                    </td>
                                    <td class="product-info">
                                        <div>
                                            <strong><?php echo esc_html($product_name); ?></strong>
                                            <p><?php _e('Quantity to Return', 'woocommerce'); ?>: 
                                                <select name="return_qty[<?php echo $item_id; ?>]">
                                                    <?php for ($i = 0; $i <= $qty; $i++) : ?>
                                                        <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                                    <?php endfor; ?>
                                                </select>
                                            </p>
                                            <p>
                                                <label>
                                                    <input type="checkbox" name="box_opened[<?php echo $item_id; ?>]" value="yes">
                                                    <?php _e('Box was opened', 'woocommerce'); ?>
                                                </label>
                                            </p>
                                            <p>
                                                <label>
                                                    <input type="checkbox" name="bottle_broken[<?php echo $item_id; ?>]" value="yes">
                                                    <?php _e('Bottle was broken', 'woocommerce'); ?>
                                                </label>
                                            </p>
                                        
                                        </div>
                                    </td>
                                    <td class="return-reason">
                                        <label for="return_reason[<?php echo $item_id; ?>]"><?php _e('Reason for Return', 'woocommerce'); ?></label>
                                        <textarea name="return_reason[<?php echo $item_id; ?>]" id="return_reason[<?php echo $item_id; ?>]" rows="2" cols="50"></textarea>
                                        <p>
                                                <label for="return_image[<?php echo $item_id; ?>]" class="button"><?php _e('Choose File', 'woocommerce'); ?></label>
                                                <input type="file" name="return_image[<?php echo $item_id; ?>]" id="return_image[<?php echo $item_id; ?>]">
                                            </p>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <input type="hidden" name="order_id" value="<?php echo esc_attr($order_id); ?>">
                    <input type="hidden" name="action" value="wc_return_request">
                    <input type="hidden" name="guest_email" value="<?php echo esc_attr($order_email); ?>">

                    <button type="submit" name="wc_return_request"><?php esc_html_e('Submit', 'woocommerce'); ?></button>
                </form>
            </div>

            <?php if (isset($_GET['return_submitted']) && $_GET['return_submitted'] == 'true') { ?>
                <div id="return-request-response">
                    <p><?php esc_html_e('Thank you, we will contact you shortly.', 'woocommerce'); ?></p>
                </div>
            <?php } ?>
        <?php } ?>
    <?php } else { ?>
        <p><?php esc_html_e('Invalid order or you do not have permission to access this order.', 'woocommerce'); ?></p>
    <?php } ?>
<?php } else { ?>
    <div class="woocommerce-account">
        <h2 id="return-request-guest-header"><?php _e('Return Request', 'woocommerce'); ?></h2>
        <form id="guest-return-request-form" method="post">
            <label for="order_id"><?php esc_html_e('Order ID', 'woocommerce'); ?></label>
            <input type="text" name="order_id" id="order_id" required placeholder="0000">

            <label for="guest_email"><?php esc_html_e('Email', 'woocommerce'); ?></label>
            <input type="email" name="guest_email" id="guest_email" required placeholder="nesashemp@nesashemp.com">

            <button type="submit"><?php esc_html_e('Find Order', 'woocommerce'); ?></button>
        </form>
    </div>
<?php } ?>
