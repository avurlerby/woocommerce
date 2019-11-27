<?php 
/**
 * MoneyGram Payment Gateway
 *
 * Provides a MoneyGram Payment Gateway.
 *
 * @class 		MG_Gateway_Money_Gram
 * @extends		MG_Payment_Gateway
 * @version		1.1.1
 * @package		WooCommerce/Classes/Payment
 * @author 		Afolabi Omotoso
 */
// Exit if accessed directly
if (!defined('ABSPATH')) exit;

class MG_Gateway_Money_Gram extends MG_Payment_Gateway {

    /**
     * Constructor for the gateway.
     */
	public function __construct() {
		$this->id                 = 'moneygram';
		$this->icon               = apply_filters('woocommerce_moneygram_icon', plugins_url('mg.png', __FILE__));
		$this->has_fields         = false;
		$this->method_title       = __( 'MoneyGram', 'woocommerce' );
		$this->method_description = __( 'Permits payments using MoneyGram.', 'woocommerce' );

		// Load the settings.
		$this->init_form_fields();
		$this->init_settings();

		// Define user set variables
		$this->title        = $this->get_option( 'title' );
		$this->description  = $this->get_option( 'description' );
		$this->instructions = $this->get_option( 'instructions', $this->description );
		$this->order_stat 	= $this->get_option( 'order_stat');

		// Actions
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
    	add_action( 'woocommerce_thankyou_moneygram', array( $this, 'thankyou_page' ) );

    	// Customer Emails
    	add_action( 'woocommerce_email_before_order_table', array( $this, 'email_instructions' ), 10, 3 );
    }

    /**
     * Initialise Gateway Settings Form Fields
     */
    public function init_form_fields() {

    	$this->form_fields = array(
			'enabled' => array(
				'title'				=> __( 'Enable/Disable', 'woocommerce' ),
				'type'				=> 'checkbox',
				'label'				=> __( 'Enable Money Gram?', 'woocommerce' ),
				'default'			=> 'yes'
			),
			'order_stat' => array(
				'title'				=> __( 'Order status', 'woocommerce' ),
				'type'				=> 'select',
				'description'		=> __( 'The setting controls the status that\'s being displayed on the order when it\'s placed.', 'woocommerce' ),
				'default'			=> 'on-hold',
				'desc_tip'			=> false,
				'options'			=> array(
					'on-hold'		=> __( 'On Hold', 'woocommerce' ),
					'processing'	=> __( 'Processing', 'woocommerce' ),
				 )
			),
			'title' => array(
				'title'				=> __( 'Title', 'woocommerce' ),
				'type'				=> 'text',
				'description'		=> __( 'This controls the title which the user sees during checkout.', 'woocommerce' ),
				'default'			=> __( 'Money Gram', 'woocommerce' ),
				'desc_tip'			=> false,
			),
			'description' => array(
				'title'				=> __( 'Description', 'woocommerce' ),
				'type'				=> 'textarea',
				'description'		=> __( 'Payment method description that the customer will see on your checkout.', 'woocommerce' ),
				'default'			=> __( '', 'woocommerce' ),
				'desc_tip'			=> false,
			),
			'instructions' => array(
				'title'				=> __( 'Instructions', 'woocommerce' ),
				'type'				=> 'textarea',
				'description'		=> __( 'Instructions that will be added to the thank you page and emails.', 'woocommerce' ),
				'default'			=> '',
				'desc_tip'			=> false,
			),
			
		);
    }

    /**
     * Output for the order received page.
     */
	public function thankyou_page() {
		if ( $this->instructions )
        	echo wpautop( wptexturize( $this->instructions ) );
	}

    /**
     * Add content to the WC emails.
     *
     * @access public
     * @param WC_Order $order
     * @param bool $sent_to_admin
     * @param bool $plain_text
     */
	public function email_instructions( $order, $sent_to_admin, $plain_text = false ) {
        if ( $this->instructions && ! $sent_to_admin && 'moneygram' === $order->get_payment_method() ) {
			
			// Go ahead only if the order has one of our statusses.
			if($order->has_status( 'on-hold' ) || $order->has_status( 'processing' ) ) {

				echo wpautop( wptexturize( $this->instructions ) ) . PHP_EOL;
			
			}
			
		}
	}

    /**
     * Process the payment and return the result
     *
     * @param int $order_id
     * @return array
     */
	public function process_payment( $order_id ) {

		$order = wc_get_order( $order_id );

		// Mark as on-hold (we're awaiting the wunion)
		$order->update_status( $this->order_stat , __( 'Awaiting MoneyGram payment.', 'woocommerce' ) );

		// Reduce stock levels
		// $order->reduce_order_stock();
		wc_reduce_stock_levels($order_id);
		
		// Remove cart
		WC()->cart->empty_cart();

		// Return thankyou redirect
		return array(
			'result' 	=> 'success',
			'redirect'	=> $this->get_return_url( $order )
		);
	}
}
?>