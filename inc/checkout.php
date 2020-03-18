<?php

add_filter('woocommerce_checkout_fields', function($fields) {
    $params = get_option('delivery-checkout');
    if (!$params || gettype($params) !== 'array') {
        return $fields;
    }
    if (!array_key_exists('start', $params) || !array_key_exists('end', $params) || !array_key_exists('delay', $params)) {
        return array();
    }

    $order_array = array(
        'delivery_time' => array(
            'type' => 'select',
            'label' => 'Gewünschte Lieferzeit',
            'required' => 1,
            'default' => WC()->session->get('delivery_time', ''),
            'options' => generate_delivery_times($params),
        )
    );
    if (array_key_exists('comment', $params) && $params['comment']) {
        $order_array['delivery_time']['description'] = $params['comment'];
    }
        $fields['order'] = array_merge($order_array, $fields['order']);
    return $fields;
});

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
 * Hinzufügen zur Thank You Page
 */
add_filter('woocommerce_thankyou', function($order_id) {
    $order = new WC_Order($order_id);
    $delivery_time = intval($order->get_meta('billing_delivery_time', true));
    ?>
    <p>
        <strong>Gewünschte Lieferzeit</strong><br>
        <?php echo($delivery_time); ?> - <?php echo($delivery_time + 1); ?> Uhr
    </p>
    <?php
}, 10, 2);


add_action('template_redirect', function() {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET')
        return;
    if (!is_checkout())
        return;
    if (WC()->customer->get_shipping_postcode())
        return;
    WC()->session->set('show-postcode-error', true);
    wc_add_notice( 'Bitte wählen Sie zunächst den Versand aus.', 'error' );
    wp_redirect( wc_get_cart_url() );
    die;
});

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
