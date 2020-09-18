<?php
/**
 * Plugin Name: Vantiv Gateway for WooCommerce
 * Plugin URI: https://github.com/freeua/woocommerce-gateway-vantiv
 * Description: Take payments on your store using Vantiv.
 * Version: 1.0.0
 * Author: Free UA
 * Author URI: https://freeua.agency/
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'WC_VANTIV_VERSION', '1.0.0' );
define( 'WC_VANTIV_MAIN_FILE', __FILE__ );




function woocommerce_vantiv_missing_wc_notice() {
	/* translators: 1. URL link. */
	echo '<div class="error"><p><strong>' . sprintf( esc_html__( 'Vantiv requires WooCommerce to be installed and active. You can download %s here.', 'woocommerce-gateway-vantiv' ), '<a href="https://woocommerce.com/" target="_blank">WooCommerce</a>' ) . '</strong></p></div>';
}


add_action( 'plugins_loaded', 'woocommerce_gateway_vantiv_init' );

function woocommerce_gateway_vantiv_init() {
	

	if ( ! class_exists( 'WooCommerce' ) ) {
		add_action( 'admin_notices', 'woocommerce_vantiv_missing_wc_notice' );
		return;
	}


	if ( ! class_exists( 'WC_Vantiv' ) ) :

		class WC_Vantiv {

			/**
			 * @var Singleton The reference the *Singleton* instance of this class
			 */
			private static $instance;

			/**
			 * Returns the *Singleton* instance of this class.
			 *
			 * @return Singleton The *Singleton* instance.
			 */
			public static function get_instance() {
				if ( null === self::$instance ) {
					self::$instance = new self();
				}
				return self::$instance;
			}

			/**
			 * Private clone method to prevent cloning of the instance of the
			 * *Singleton* instance.
			 *
			 * @return void
			 */
			private function __clone() {}

			/**
			 * Private unserialize method to prevent unserializing of the *Singleton*
			 * instance.
			 *
			 * @return void
			 */
			private function __wakeup() {}

			/**
			 * Protected constructor to prevent creating a new instance of the
			 * *Singleton* via the `new` operator from outside of this class.
			 */
			private function __construct() {
				add_action( 'admin_init', array( $this, 'install' ) );
				$this->init();
			}

			/**
			 * Init the plugin after plugins_loaded so environment variables are set.
			 *
			 * @since 1.0.0
			 * @version 4.0.0
			 */
			public function init() {

				require_once dirname( __FILE__ ) . '/includes/woocommerce-gatway.php';



				add_filter( 'woocommerce_payment_gateways', array( $this, 'add_gateways' ) );
				add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'plugin_action_links' ) );



				if ( version_compare( WC_VERSION, '3.4', '<' ) ) {
					add_filter( 'woocommerce_get_sections_checkout', array( $this, 'filter_gateway_order_admin' ) );
				}
			}

			/**
			 * Updates the plugin version in db
			 *
			 * @since 3.1.0
			 * @version 4.0.0
			 */
			public function update_plugin_version() {
				delete_option( 'wc_vantiv_version' );
				update_option( 'wc_vantiv_version', WC_VANTIV_VERSION );
			}

			/**
			 * Handles upgrade routines.
			 *
			 * @since 3.1.0
			 * @version 3.1.0
			 */
			public function install() {
				if ( ! is_plugin_active( plugin_basename( __FILE__ ) ) ) {
					return;
				}

				if ( ! defined( 'IFRAME_REQUEST' ) && ( WC_VANTIV_VERSION !== get_option( 'wc_vantiv_version' ) ) ) {
					do_action( 'woocommerce_vantiv_updated' );

					if ( ! defined( 'WC_VANTIV_INSTALLING' ) ) {
						define( 'WC_VANTIV_INSTALLING', true );
					}

					$this->update_plugin_version();
				}
			}

			/**
			 * Add plugin action links.
			 *
			 * @since 1.0.0
			 * @version 4.0.0
			 */
			public function plugin_action_links( $links ) {
				$plugin_links = array(
					'<a href="admin.php?page=wc-settings&tab=checkout&section=vantiv">' . esc_html__( 'Settings', 'woocommerce-gateway-vantiv' ) . '</a>',
				);
				return array_merge( $plugin_links, $links );
			}

			/**
			 * Add the gateways to WooCommerce.
			 *
			 * @since 1.0.0
			 * @version 4.0.0
			 */
			public function add_gateways( $methods ) {
				$methods[] = 'WC_vantiv_Pay';
    			return $methods;
			}


			/**
			 * Modifies the order of the gateways displayed in admin.
			 *
			 * @since 4.0.0
			 * @version 4.0.0
			 */
			public function filter_gateway_order_admin( $sections ) {
				unset( $sections['vantiv'] );
				$sections['vantiv']   = 'Vantiv';
				return $sections;
			}


		}

		WC_Vantiv::get_instance();
	endif;
}


add_action('wp_enqueue_scripts','vantiv_scripts');

function vantiv_scripts() {
//    wp_enqueue_script( 'jquery-plugin-vantiv', plugins_url( '/assets/js/jquery-1.9.0.min.js', __FILE__ ));
    wp_enqueue_script( 'input-mask-script', plugins_url( '/assets/js/jquery.inputmask.min.js', __FILE__ ));
    wp_enqueue_script( 'vantiv-scripts', plugins_url( '/assets/js/vantiv.js', __FILE__ ));
}
