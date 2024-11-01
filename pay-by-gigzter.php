<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              gigzter.com
 * @since             1.0.0
 * @package           Pay_By_Gigzter
 *
 * @wordpress-plugin
 * Plugin Name:       Pay by GigZter
 * Plugin URI:        https://wordpress.org/plugins/pay-by-gigzter/
 * Description:       Pay by Card using GigZter.
 * Version:           1.0.0
 * Author:            Giga Technologies Inc
 * Author URI:        https://gigzter.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       pay-by-gigzter
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'PAY_BY_GIGZTER_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-pay-by-gigzter-activator.php
 */
function activate_pay_by_gigzter() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-pay-by-gigzter-activator.php';
	Pay_By_Gigzter_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-pay-by-gigzter-deactivator.php
 */
function deactivate_pay_by_gigzter() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-pay-by-gigzter-deactivator.php';
	Pay_By_Gigzter_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_pay_by_gigzter' );
register_deactivation_hook( __FILE__, 'deactivate_pay_by_gigzter' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-pay-by-gigzter.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_pay_by_gigzter() {

	$plugin = new Pay_By_Gigzter();
	$plugin->run();

}
run_pay_by_gigzter();

add_action('plugins_loaded', 'init_gigzter_gateway_class');

// added by himanshu 
function init_gigzter_gateway_class() {
	class WC_Gigzter_Gateway extends WC_Payment_Gateway {

		public $domain;

		/**
		 * Constructor for the gateway.
		 */
		public function __construct() {

			$this->domain = 'gigzter_payments';

			$this->id                 = 'gigzter_payments';
			$this->icon               = apply_filters('gigzter_payments_icon', '');
			$this->has_fields         = false;
			$this->method_title       = __( 'Gigzter Payment Gateway', $this->domain );
			$this->method_description = __( 'Allows payments with Gigzter payment gateway.', $this->domain );

			$this->supports = array(
				'refunds'
			);

			// Load the settings.
			$this->init_form_fields();
			$this->init_settings();

			// Define user set variables
			$this->device_id  				= $this->get_option( 'device_id' );
			$this->application_id  			= $this->get_option( 'application_id' );
			$this->brand_color  			= $this->get_option( 'brand_color' );
			$this->title        			= $this->get_option( 'title' );
			$this->description  			= $this->get_option( 'description' );
			$this->statement_descriptor  	= $this->get_option( 'statement_descriptor' );
			$this->logo_url        			= $this->get_option( 'logo_url' );
			$this->base_url  				= $this->get_option( 'base_url' );

			$this->skip_customer_receipt    = $this->get_option( 'skip_customer_receipt');
			$this->custom_email  			= $this->get_option( 'custom_email' );
			$this->custom_phone_number  	= $this->get_option( 'custom_phone_number' );
			$this->custom_address_line  	= $this->get_option( 'custom_address_line' );
			$this->custom_address_line2  	= $this->get_option( 'custom_address_line2' );
			$this->custom_address_city  	= $this->get_option( 'custom_address_city' );
			$this->custom_address_state  	= $this->get_option( 'custom_address_state' );
			$this->custom_address_zip_code  = $this->get_option( 'custom_address_zip_code' );

			// Actions
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
			add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'thankyou_page' ) );
			add_action( 'admin_notices', array( $this, 'pay_by_gigzter_notice' ) );
		}

		// admin notice
		public function pay_by_gigzter_notice() {
			$screen = get_current_screen();
			if(!($this->device_id && $this->application_id) && $screen->base !== 'woocommerce_page_wc-settings') {
				?>
					<div class="error notice-error">
							<p><?php _e( 'Please activate pay by gigzter plugin by adding Device Id and Application ID  <a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=gigzter_payments' ) . '">here</a>. If you continue to face this error notice please contact <a href="mailto:support@gigzter.com" target="_blank">Plugin Author</a>.'); ?></p>
					</div>
				<?php
			}

			if(!($this->device_id && $this->application_id) && $screen->base == 'woocommerce_page_wc-settings' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
				?>
					<div class="error notice-error">
							<p><?php _e( 'Please activate pay by gigzter plugin by adding Device Id and Application ID. If you continue to face this error notice please contact <a href="mailto:support@gigzter.com" target="_blank">Plugin Author</a>.'); ?></p>
					</div>
				<?php
			}

			if($screen->base == 'woocommerce_page_wc-settings' && $_SERVER['REQUEST_METHOD'] === 'POST') {
				if(!(sanitize_text_field($_POST['woocommerce_gigzter_payments_device_id']) && sanitize_text_field($_POST['woocommerce_gigzter_payments_application_id']))){
					?>
						<div class="error notice-error gigzter-notice-error">
								<p><?php _e( 'Please activate pay by gigzter plugin by adding Device Id and Application ID. If you continue to face this error notice please contact <a href="mailto:support@gigzter.com" target="_blank">Plugin Author</a>.'); ?></p>
						</div>
					<?php
				}
			}
		}

		/**
		 * Initialise Gateway Settings Form Fields.
		*/
		public function init_form_fields() {

			$this->form_fields = array(
				'enabled' => array(
					'title'   		=> __( 'Enable/Disable', $this->domain ),
					'type'    		=> 'checkbox',
					'label'   		=> __( 'Enable Gigzter Payment Gateway', $this->domain ),
					'default' 		=> 'yes'
				),
				'device_id' => array(
					'title'       => __( 'Device Id', $this->domain ),
					'type'        => 'text',
					'description' => __( 'Contact support@gigzter.com to get the device id to activate this plugin.', $this->domain ),
					'desc_tip'    => true,
				),
				'application_id' => array(
					'title'       => __( 'Application ID', $this->domain ),
					'type'        => 'text',
					'description' => __( 'Contact support@gigzter.com to get the application id to activate this plugin.', $this->domain ),
					'desc_tip'    => true,
				),
				'brand_color' => array(
					'title'       => __( 'Brand Color', $this->domain ),
					'type'        => 'text',
					'description' => __( 'Provide a six digit hex code that represents the primary brand color used in your portal.', $this->domain ),
					'default'     => __( '1ebc61', $this->domain ),
					'desc_tip'    => true,
				),
				'title' => array(
					'title'       => __( 'Title', $this->domain ),
					'type'        => 'text',
					'description' => __( 'This controls the title which the user sees during checkout.', $this->domain ),
					'default'     => __( 'Pay with Card', $this->domain ),
					'desc_tip'    => true,
				),
				'order_status' => array(
					'title'       => __( 'Order Status', $this->domain ),
					'type'        => 'select',
					'class'       => 'wc-enhanced-select',
					'description' => __( 'Choose whether status you wish after checkout.', $this->domain ),
					'default'     => 'wc-processing',
					'desc_tip'    => true,
					'options'     => wc_get_order_statuses()
				),
				'description' => array(
					'title'       => __( 'Description', $this->domain ),
					'type'        => 'textarea',
					'description' => __( 'Payment method description that the customer will see on your checkout.', $this->domain ),
					'default'     => __('Pay with Card (Debit, Credit), Wallets (Apple Pay, Google Pay), or Bank. <br><img src="https://gigzter.com/verification/pay-options-logo.png" style="width:175px">', $this->domain),
					'desc_tip'    => true,
				),
				'statement_descriptor' => array(
					'title'       => __( 'Business Alias', $this->domain ),
					'type'        => 'text',
					'description' => __( '[Optional] An alias for the registered GigZter business name.', $this->domain ),
					'desc_tip'    => true,
				),
				'logo_url' => array(
					'title'       => __( 'Logo URL', $this->domain ),
					'type'        => 'text',
					'description' => __( '[Optional] Your business logo.', $this->domain ),
					'desc_tip'    => true,
				),
				'base_url' => array(
					'title'       => __( 'Base URL', $this->domain ),
					'type'        => 'text',
					'description' => __( '[Optional] Get a Sandbox URL from GigZter Support for testing purposes.', $this->domain ),
					'default'     => 'https://api.store.gigzter.com',
					'desc_tip'    => true,
				),
				'skip_customer_receipt' => array(
					'title'   		=> __( 'Skip customer receipt', $this->domain ),
					'type'    		=> 'checkbox',
					'label'   		=> __( 'Skip customer receipt', $this->domain ),
					'default' 		=> 'yes'
				),
				'custom_email' => array(
					'title'       => __( 'Email', $this->domain ),
					'type'        => 'text',
					'description' => __( '[Optional] Your business email.', $this->domain ),
					'default'     => '',
					'desc_tip'    => true,
				),
				'custom_phone_number' => array(
					'title'       => __( 'Phone Number', $this->domain ),
					'type'        => 'text',
					'description' => __( '[Optional] Your business phone number.', $this->domain ),
					'default'     => '',
					'desc_tip'    => true,
				),
				'custom_address_line' => array(
					'title'       => __( 'Address Line', $this->domain ),
					'type'        => 'text',
					'description' => __( '[Optional] Your business address line.', $this->domain ),
					'default'     => '',
					'desc_tip'    => true,
				),
				'custom_address_line2' => array(
					'title'       => __( 'Address Line 2', $this->domain ),
					'type'        => 'text',
					'description' => __( '[Optional] Your business address line 2.', $this->domain ),
					'default'     => '',
					'desc_tip'    => true,
				),
				'custom_address_city' => array(
					'title'       => __( 'City', $this->domain ),
					'type'        => 'text',
					'description' => __( '[Optional] Your business city.', $this->domain ),
					'default'     => '',
					'desc_tip'    => true,
				),
				'custom_address_state' => array(
					'title'       => __( 'State', $this->domain ),
					'type'        => 'text',
					'description' => __( '[Optional] Your business state.', $this->domain ),
					'default'     => '',
					'desc_tip'    => true,
				),
				'custom_address_zip_code' => array(
					'title'       => __( 'Zip Code', $this->domain ),
					'type'        => 'text',
					'description' => __( '[Optional] Your business zip code.', $this->domain ),
					'default'     => '',
					'desc_tip'    => true,
				),
			);
		}

		/**
		 * Output for the order received page.
		 */
		public function thankyou_page($order_id) {

			$order						= wc_get_order($order_id);

			$code						= sanitize_text_field($_GET['gcode']);

			$url  						= $this->base_url."/v1/woocommerce/payments/".$code;
			
			$args = array(
				'headers' => array(
					"Content-Type" => "application/json",
					"Accept" => "application/json",
					"x-device-id" => $this->device_id,
					"x-application-id" => $this->application_id
				),
				'timeout' => 50
			);

			$request  = wp_remote_get( $url, $args );
			$response = wp_remote_retrieve_body( $request );

			if ( is_wp_error( $request ) ) {
				echo esc_attr($request->get_error_message());
				return;
			} else {
				$data       			= json_decode($response, true);

				// if paid, set order status to complete
				if($data['status'] === "APPROVED") {
					$status = 'wc-' === substr( $this->get_option('order_status'), 0, 3 ) ? substr( $this->get_option('order_status'), 3 ) : $this->get_option('order_status');
					$order->update_status( $status, __( 'Checkout with Gigzter Payment Gateway', $this->domain ) );
				}
			} 
		
		}

		public function payment_fields() {

			if ( $description = $this->get_description() ) {
				echo wp_kses(wpautop( wptexturize( $description ) ), true);
			}
		}

		/**
		 * Process the payment and return the result.
		 *
		 * @param int $order_id
		 * @return array
		 */
		public function process_payment( $order_id ) {

			$order 				= wc_get_order( $order_id );
			$order_data 	= $order->get_data();

			$line_items		= array();

			foreach ( $order->get_items() as  $item_key => $item_values ) {
				$item_data = $item_values->get_data();
				$line_items[] = $item_data;
			}

			$url					= $this->base_url."/v1/woocommerce/payments";

			$order_data['line_items'] = $line_items;

			// currency convert to USD	
			$currency_code = $order->get_currency();
			if($currency_code !== "USD") {
				$htu_args = array(
					'headers' => array(
						"Content-Type" => "text/plain",
						"apikey" => "1LrdY0SvXLKcnIl5nhF5lQFOXI1nP0vd"
					),
					'timeout' => 10
				);
				$htu_request  = wp_remote_get( "https://api.apilayer.com/fixer/convert?to=USD&from=".$currency_code."&amount=1", $htu_args );
				$htu_response = wp_remote_retrieve_body( $htu_request );
				if ( is_wp_error( $htu_request ) ) {
					echo esc_attr($htu_request->get_error_message());
					return;
				}
				$htu_data       					= json_decode($htu_response, true); 
				$order_data["total"]			= $order_data["total"] * $htu_data["info"]["rate"];
			}
			// currency convert to USD	

			$payload			= $order_data;
			$payload['statement_descriptor'] = $this->statement_descriptor;
			$payload['logo_url'] = $this->logo_url;
			$payload['mode'] = $this->mode;
			$payload['skip_customer_receipt'] = $this->skip_customer_receipt;
			$payload['custom_email'] = $this->custom_email;
			$payload['custom_phone_number'] = $this->custom_phone_number;
			$payload['custom_address_line'] = $this->custom_address_line;
			$payload['custom_address_line2'] = $this->custom_address_line2;
			$payload['custom_address_city'] = $this->custom_address_city;
			$payload['custom_address_state'] = $this->custom_address_state;
			$payload['custom_address_zip_code'] = $this->custom_address_zip_code;


			$args = array(
				'headers' => array(
					"Content-Type" => "application/json",
					"Accept" => "application/json",
					"x-device-id" => $this->device_id,
					"x-application-id" => $this->application_id
				),
				'body' => json_encode($payload),
				'timeout' => 50
			);

			$request  = wp_remote_post( $url, $args );
			$response = wp_remote_retrieve_body( $request );

			if ( is_wp_error( $request ) ) {
				echo esc_attr($request->get_error_message());
				return;
			} else {
				$data       			= json_decode($response, true); 
				if($data['paymentURL']) {
					$back 		= wc_get_checkout_url();;
					$redirect = $this->get_return_url($order);
					$order->update_meta_data( '_gigzter_invoicecode', $data['invoiceCode'] );
					$order->save();
					return array(
						'result'    => 'success',
						'redirect'  =>  $data['paymentURL']."?from=wcm&color=".$this->brand_color."&confirm=false&back=".$back."&order=".$order_data["order_key"]."&order_id=".$order_data["number"]."&redirect=".$redirect.""
						);	
				} else {
					return array(
						'result'    => $response
					);
				}
			}
					
		}

		public function process_refund( $order_id, $amount = null, $reason = '' ) {
			
			$order 				= wc_get_order( $order_id );

			if ( ! $order ) {
				return false;
			}

			// $url					= $this->base_url."/v1/woocommerce/refund";

			$url					= "https://prod-74.westus.logic.azure.com:443/workflows/00b6219024134205b11cf2b0ccdd85ac/triggers/manual/paths/invoke?api-version=2016-06-01&sp=%2Ftriggers%2Fmanual%2Frun&sv=1.0&sig=iPQhtw7APsvDNgk8GGM2jwGBbI1tRNBDUsHjmJklYIA";

			if(!$this->can_refund_order($order)) {
				return false;
			}

			$payload			= array(
				"orderId" => $order_id,
				"gcode" =>  get_post_meta( $order_id, '_gigzter_invoicecode', true ),
				"amount" => $amount,
				"reason" => $reason
			);

			$args = array(
				'headers' => array(
					"Content-Type" => "application/json",
					"Accept" => "application/json",
					"x-device-id" => $this->device_id,
					"x-application-id" => $this->application_id
				),
				'body' => json_encode($payload),
				'timeout' => 50
			);

			$request  = wp_remote_post( $url, $args );
			$response = wp_remote_retrieve_body( $request );

			if ( is_wp_error( $request ) ) {
				echo esc_attr($request->get_error_message());
				return;
			} else {
				$data       			= json_decode($response, true); 
				if($data['data']['refundPayment']['status'] === 'APPROVED' && ($data['data']['refundPayment']['payment']['status'] === 'REFUNDED' || $data['data']['refundPayment']['payment']['status'] === 'PARTIALLY_REFUNDED')) {
					return true;
				} else {
					return false;
				}
			}

		}
	}
}

// pluin action links
add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'plugin_action_links' );

/**
 * Show action links on the plugin screen.
 *
 * @param mixed $links Plugin Action links.
 *
 * @return array
 */
function plugin_action_links( $links ) {

	$action_links = array(
		'settings' => '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=gigzter_payments' ) . '">Settings</a>',
	);

	return array_merge( $action_links, $links );
}

