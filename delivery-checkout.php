<?php
/*
Plugin Name: Delivery Checkout
Plugin URI: https://binary-butterfly.de
Description: Fügt ein Feld in das Checkout hinzu, wo die Lieferzeit grob angegeben werden kann.
Version: 0.1.0
Author: binary butterfly GmbH
Author URI: https://binary-butterfly.de
Text Domain: delivery-checkout
Domain Path: /languages
License: GPLv2 or later
*/

defined('ABSPATH') or die('No script kiddies please!');

require 'inc/checkout.php';
require 'inc/options.php';

register_activation_hook( __FILE__, function() {
    $params = get_option('delivery-checkout');
    if (!$params) {
        update_option('delivery-checkout', array(
            'start' => 15,
            'end' => 21,
            'delay' => 30,
            'comment' => ''
        ));
    }
});
