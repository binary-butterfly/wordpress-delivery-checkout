<?php

defined('ABSPATH') or die('No script kiddies please!');

class DeliveryCheckoutOptions {
    private $options;

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
        add_action( 'admin_init', array( $this, 'page_init' ) );
    }

    public function add_plugin_page() {
        add_options_page(
            'Delivery Checkout',
            'Delivery Checkout',
            'manage_options',
            'delivery-checkout',
            array( $this, 'create_admin_page' )
        );
    }

    public function create_admin_page() {
        $this->options = get_option( 'delivery-checkout' );
        ?>
        <div class="wrap">
            <h1>Delivery Checkout</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields( 'delivery-checkout-group' );
                do_settings_sections( 'delivery-checkout-admin' );
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    public function page_init() {
        register_setting(
            'delivery-checkout-group',
            'delivery-checkout',
            array( $this, 'sanitize' )
        );

        add_settings_section(
            'delivery-checkout-section',
            'Einstellungen',
            array( $this, 'print_section_info' ),
            'delivery-checkout-admin'
        );

        add_settings_field(
            'start',
            'Start-Stunde',
            array( $this, 'start_callback' ),
            'delivery-checkout-admin',
            'delivery-checkout-section'
        );

        add_settings_field(
            'end',
            'End-Stunde',
            array( $this, 'end_callback' ),
            'delivery-checkout-admin',
            'delivery-checkout-section'
        );

        add_settings_field(
            'delay_callback',
            'Vorlaufzeit in Minuten',
            array( $this, 'delay_callback' ),
            'delivery-checkout-admin',
            'delivery-checkout-section'
        );
        add_settings_field(
            'comment_callback',
            'Anmerkung zur Bestellzeit',
            array( $this, 'comment_callback' ),
            'delivery-checkout-admin',
            'delivery-checkout-section'
        );
    }

    public function sanitize( $input ) {
        $new_input = array();
        if( isset( $input['start'] ) )
            $new_input['start'] = absint( $input['start'] );
        if( isset( $input['end'] ) )
            $new_input['end'] = absint( $input['end'] );
        if( isset( $input['delay'] ) )
            $new_input['delay'] = absint( $input['delay'] );
        if( isset( $input['delay'] ) )
            $new_input['comment'] =  sanitize_text_field($input['comment']);
        return $new_input;
    }

    public function print_section_info() {
        print 'Du kannst die vollen Stunden angeben, ab denen und bis zu denen Du eine Bestellung anbieten willst. Außerdem kannst Du die gewünschte Vorlaufzeit in Minuten angeben. Es werden automatisch alle Stunden ausgeblendet, die bereits vorbei sind.';
    }

    public function start_callback() {
        printf(
            '<input type="number" id="start" name="delivery-checkout[start]" value="%s" />',
            isset( $this->options['start'] ) ? esc_attr( $this->options['start']) : ''
        );
    }

    public function end_callback() {
        printf(
            '<input type="number" id="end" name="delivery-checkout[end]" value="%s" />',
            isset( $this->options['end'] ) ? esc_attr( $this->options['end']) : ''
        );
    }

    public function delay_callback() {
        printf(
            '<input type="number" id="delay" name="delivery-checkout[delay]" value="%s" />',
            isset( $this->options['delay'] ) ? esc_attr( $this->options['delay']) : ''
        );
    }
    public function comment_callback() {
        printf(
            '<input type="text" id="comment" name="delivery-checkout[comment]" value="%s" />',
            isset( $this->options['comment'] ) ? esc_attr( $this->options['comment']) : ''
        );
    }
}

if( is_admin() ) {
    new DeliveryCheckoutOptions();
}
