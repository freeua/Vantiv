<?php
/**
 * Plugin Name: Gateway for Vantiv on WC
 * Plugin URI: https://github.com/freeua/gateway-vantiv-woocommerce
 * Description: Take payments on your store using Vantiv.
 * Version: 1.0.1
 * Author: Free UA
 * Author URI: https://freeua.agency/
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'GVWC_VANTIV_VERSION', '1.0.1' );
define( 'GVWC_VANTIV_MAIN_FILE', __FILE__ );


if ( !function_exists( 'gvwc_woocommerce_vantiv_missing_wc_notice' ) ) {
	function gvwc_woocommerce_vantiv_missing_wc_notice () {
		/* translators: 1. URL link. */
		echo '<div class="error"><p><strong>' . sprintf( esc_html__( 'Vantiv requires WooCommerce to be installed and active. You can download %s here.', 'gateway-vantiv-woocommerce' ), '<a href="https://woocommerce.com/" target="_blank">' . esc_html__( 'WooCommerce', 'gateway-vantiv-woocommerce' ) . '</a>' ) . '</strong></p></div>';
	}
}


add_action( 'plugins_loaded', 'gvwc_woocommerce_gateway_vantiv_init' );

if ( !function_exists( 'gvwc_woocommerce_gateway_vantiv_init' ) ) {
	function gvwc_woocommerce_gateway_vantiv_init () {
		

		if ( ! class_exists( 'WooCommerce' ) ) {
			add_action( 'admin_notices', 'gvwc_woocommerce_vantiv_missing_wc_notice' );
			return;
		}


		if ( ! class_exists( 'GVWC_Vantiv' ) ) :

			class GVWC_Vantiv {

				/**
				 * @var Singleton The reference the *Singleton* instance of this class
				 */
				private static $instance;

				/**
				 * Returns the *Singleton* instance of this class.
				 *
				 * @return Singleton The *Singleton* instance.
				 */
				public static function get_instance () {
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
				private function __clone () {}

				/**
				 * Private unserialize method to prevent unserializing of the *Singleton*
				 * instance.
				 *
				 * @return void
				 */
				private function __wakeup () {}

				/**
				 * Protected constructor to prevent creating a new instance of the
				 * *Singleton* via the `new` operator from outside of this class.
				 */
				private function __construct () {
					add_action( 'admin_init', array( $this, 'install' ) );
					$this->init();
				}

				/**
				 * Init the plugin after plugins_loaded so environment variables are set.
				 *
				 * @since 1.0.0
				 * @version 4.0.0
				 */
				public function init () {

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
				public function update_plugin_version () {
					delete_option( 'wc_vantiv_version' );
					update_option( 'wc_vantiv_version', GVWC_VANTIV_VERSION );
				}

				/**
				 * Handles upgrade routines.
				 *
				 * @since 3.1.0
				 * @version 3.1.0
				 */
				public function install () {
					if ( ! is_plugin_active( plugin_basename( __FILE__ ) ) ) {
						return;
					}

					if ( ! defined( 'IFRAME_REQUEST' ) && ( GVWC_VANTIV_VERSION !== get_option( 'wc_vantiv_version' ) ) ) {
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
				public function plugin_action_links ( $links ) {
					$plugin_links = array(
						'<a href="admin.php?page=wc-settings&tab=checkout&section=vantiv">' . esc_html__( 'Settings', 'gateway-vantiv-woocommerce' ) . '</a>',
					);
					return array_merge( $plugin_links, $links );
				}

				/**
				 * Add the gateways to WooCommerce.
				 *
				 * @since 1.0.0
				 * @version 4.0.0
				 */
				public function add_gateways ( $methods ) {
					$methods[] = esc_html__( 'GVWC_Gateway_Vantiv', 'gateway-vantiv-woocommerce' );
	    			return $methods;
				}
				
				/**
				 * Modifies the order of the gateways displayed in admin.
				 *
				 * @since 4.0.0
				 * @version 4.0.0
				 */
				public function filter_gateway_order_admin ( $sections ) {
					unset( $sections['vantiv'] );
					$sections['vantiv']   = esc_html__( 'Vantiv', 'gateway-vantiv-woocommerce' );
					return $sections;
				}
			}

			GVWC_Vantiv::get_instance();
		endif;
	}
}

add_action( 'wp_enqueue_scripts','gvwc_vantiv_scripts', 11 );

if ( !function_exists( 'gvwc_vantiv_scripts' ) ) {
	function gvwc_vantiv_scripts () {
	    wp_enqueue_script( 'input-mask-script', plugins_url( '/assets/js/jquery.inputmask.min.js', __FILE__ ) );
	    wp_enqueue_script( 'vantiv-scripts', plugins_url( '/assets/js/vantiv.js', __FILE__ ) );
	}
}
