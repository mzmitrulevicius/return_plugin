<?php
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
$order = wc_get_order($order_id);
$current_user = wp_get_current_user();

if ($order && $order->get_user_id() == $current_user->ID) :
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

    if (!empty($existing_requests)) :
        $existing_request = $existing_requests[0];
        $box_opened = get_post_meta($existing_request->ID, '_box_opened', true);
        $return_status = get_post_meta($existing_request->ID, '_return_request_status', true);
        ?>
        <div class="woocommerce-account">
            <h2><?php _e('Return Request Already Submitted', 'woocommerce'); ?></h2>
            <p><?php printf(__('A return request for Order #%s was already submitted on %s.', 'woocommerce'), esc_html($order->get_order_number()), esc_html(get_post_meta($existing_request->ID, '_request_date', true))); ?></p>
            <p><?php _e('Reason:', 'woocommerce'); ?> <?php echo esc_html(get_post_meta($existing_request->ID, '_return_reason', true)); ?></p> <!-- Changed line -->
            <p><?php _e('Box was opened:', 'woocommerce'); ?> <?php echo esc_html($box_opened === 'yes' ? 'Yes' : 'No'); ?></p>
            <p><?php _e('Return Status:', 'woocommerce'); ?> <?php echo esc_html($return_status); ?></p>
        </div>
    <?php elseif ($date_diff > 30) : ?>
        <p><?php esc_html_e('You can only return orders within 30 days of purchase.', 'woocommerce'); ?></p>
    <?php else : ?>
        <div class="woocommerce-account">
            <h2><?php printf(__('Return Request for Order #%s', 'woocommerce'), esc_html($order->get_order_number())); ?></h2>
            <form id="wc-return-request-form" method="post">
                <?php wp_nonce_field('submit_return_request', 'return_request_nonce'); ?>
                <h3><?php esc_html_e('Order Information', 'woocommerce'); ?></h3>
                <p><?php printf(__('Order Number: %s', 'woocommerce'), esc_html($order->get_order_number())); ?></p>
                <p><?php printf(__('Order Date: %s', 'woocommerce'), esc_html($order->get_date_created()->date('F j, Y'))); ?></p>
                <p><?php printf(__('Order Status: %s', 'woocommerce'), esc_html(wc_get_order_status_name($order->get_status()))); ?></p>

                <h3><?php esc_html_e('Order Details', 'woocommerce'); ?></h3>
                <table class="shop_table order_details">
                    <thead>
                        <tr>
                            <th class="product-name"><?php esc_html_e('Product', 'woocommerce'); ?></th>
                            <th class="product-total"><?php esc_html_e('Total', 'woocommerce'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($order->get_items() as $item_id => $item) {
                            ?>
                            <tr>
                                <td class="product-name"><?php echo esc_html($item->get_name()); ?> × <?php echo esc_html($item->get_quantity()); ?></td>
                                <td class="product-total"><?php echo wp_kses_post($order->get_formatted_line_subtotal($item)); ?></td>
                            </tr>
                            <?php
                        }
                        ?>
                    </tbody>
                    <tfoot>
                        <?php
                        foreach ($order->get_order_item_totals() as $key => $total) {
                            ?>
                            <tr>
                                <th scope="row"><?php echo esc_html($total['label']); ?></th>
                                <td><?php echo wp_kses_post($total['value']); ?></td>
                            </tr>
                            <?php
                        }
                        ?>
                    </tfoot>
                </table>

                <input type="hidden" name="order_id" value="<?php echo esc_attr($order_id); ?>">

                <label for="return_reason"><?php esc_html_e('Return Reason', 'woocommerce'); ?></label>
                <textarea name="return_reason" id="return_reason" required></textarea>

                <label for="box_opened">
                    <input type="checkbox" name="box_opened" id="box_opened" value="yes">
                    <?php esc_html_e('Box was opened', 'woocommerce'); ?>
                </label>

                <button type="submit" name="wc_return_request"><?php esc_html_e('Submit', 'woocommerce'); ?></button>
            </form>
        </div>

        <?php if (isset($_GET['return_submitted']) && $_GET['return_submitted'] == 'true') : ?>
            <div id="return-request-response">
                <p><?php esc_html_e('Thank you, we will contact you shortly.', 'woocommerce'); ?></p>
            </div>
        <?php endif; ?>
    <?php endif; ?>
<?php else : ?>
    <p><?php esc_html_e('Invalid order or you do not have permission to access this order.', 'woocommerce'); ?></p>
<?php endif; ?>
