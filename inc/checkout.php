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