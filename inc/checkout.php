<?php

defined('ABSPATH') or die('No script kiddies please!');

/*
 * adds delivery time field
 */
add_filter('woocommerce_checkout_fields', function($fields) {
    $params = get_option('delivery-checkout');
    if (!$params || gettype($params) !== 'array') {
        return $fields;
    }
    if (!array_key_exists('start', $params) || !array_key_exists('end', $params) || !array_key_exists('delay', $params)) {
        return $fields;
    }
    // we don't need a delivery time when there's nothing to ship
    if (!WC()->cart->needs_shipping()) {
        return $fields;
    }
    $order_array = array(
        'delivery_time' => array(
            'type' => 'select',
            'label' => 'Gewünschte Lieferzeit',
            'required' => 1,
            'options' => generate_delivery_times($params),
        )
    );
    if (array_key_exists('comment', $params) && $params['comment']) {
        $order_array['delivery_time']['description'] = $params['comment'];
    }
        $fields['order'] = array_merge($order_array, $fields['order']);
    return $fields;
});

/*
 * generate all current delivery times
 */
function generate_delivery_times($params) {
    $step = 1;
    $now = new DateTime( 'now', wp_timezone() );
    $now->add(new DateInterval('PT' . strval($params['delay']) . 'M'));
    $times = array();
    foreach (range(intval($params['start']), intval($params['end']), $step) as $time) {
        if (intval($now->format('H')) > $time - 1) {
            continue;
        }
        $times[$time] = $time . ' - ' . ($time + 1) . ' Uhr';
    }
    return $times;
}

/*
 * force postcode in checkout
 */
add_action('template_redirect', function() {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET')
        return;
    if (!is_checkout())
        return;
    if (WC()->customer->get_shipping_postcode())
        return;
    if (!WC()->cart->needs_shipping())
        return;
    WC()->session->set('show-postcode-error', true);
    wc_add_notice( 'Bitte wählen Sie zunächst den Versand aus.', 'error' );
    wp_redirect( wc_get_cart_url() );
    die;
});

/*
 * CSS additions for cart
 */
add_action('wp_head', function() {
?>
    <style>
        #calc_shipping_city_field, #calc_shipping_country_field {
            display: none !important;
        }
        .shipping-calculator-form {
            display: block !important;
        }
        #calc_shipping_postcode_field:before {
            content: "Postleitzahl:";
        }
    </style>
<?php
});


/*
 * saves delivery time at creating order
 */
add_action('woocommerce_checkout_create_order', function($order, $data) {
    if (!array_key_exists('delivery_time', $data))
        return;
    $order->update_meta_data('_delivery_time', $data['delivery_time']);
}, 10, 2);


/*
 * shows delivery time in thank you page
 */
add_filter('woocommerce_thankyou', function($order_id) {
    $order = new WC_Order($order_id);
    $delivery_time = intval($order->get_meta('_delivery_time', true));
    if (!$delivery_time)
        return;
    ?>
    <p>
        <strong>Gewünschte Lieferzeit</strong><br>
        <?php echo($delivery_time); ?> - <?php echo($delivery_time + 1); ?> Uhr
    </p>
    <?php
}, 10, 2);


/*
 * shows delivery time in admin backend
 */
add_action('woocommerce_admin_order_data_after_shipping_address', function($order) {
    $delivery_time = intval($order->get_meta('_delivery_time', true));
    if (!$delivery_time)
        return;
    ?>
    <p>
        <strong>Gewünschte Lieferzeit</strong><br>
        <?php echo($delivery_time); ?> - <?php echo($delivery_time + 1); ?> Uhr
    </p>
    <?php
}, 10, 1);
