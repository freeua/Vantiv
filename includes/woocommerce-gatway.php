<?php

require_once __DIR__ . '/../vantiv_sdk/vendor/autoload.php';
use cnp\sdk\CnpOnlineRequest;
	use cnp\sdk\XmlParser;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
	if ( ! class_exists( 'GVWC_Gateway_Vantiv' ) ) {
		class GVWC_Gateway_Vantiv extends WC_Payment_Gateway
		{
			public function __construct()
			{
				$this->id                 = 'vantiv';
				$this->medthod_title      = 'Vantiv';
				$this->has_fields         = false;
				$this->init_form_fields();
				$this->init_settings();
				$this->title              = $this->settings['title'];
				$this->testmode           = 'yes' === $this->get_option( 'sandbox' );
				$this->description        = $this->get_option( 'description' );
				$this->method_description = 'Vantiv works by adding payment fields on the checkout and then sending the details to Vantiv.';
				$this->liveurl            = 'https://certtransaction.hostedpayments.com/';
				$this->msg['message']     = "";
				$this->msg['class']       = "";
				$this->order_button_text  = __( 'Proceed to Vantiv', 'woocommerce' );
				$this->supports           = array(
					'products',
					'refunds',
					'tokenization',
					'subscriptions',
					'subscription_cancellation',
					'subscription_suspension',
					'subscription_reactivation',
					'subscription_amount_changes',
					'subscription_date_changes',
					'subscription_payment_method_change',
					'subscription_payment_method_change_customer',
					'subscription_payment_method_change_admin',
				);
				
				if ( version_compare( WOOCOMMERCE_VERSION, '2.0.0', '>=' ) ) {
					add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'save_payment_gateway_settings' ) );
				} else {
					add_action( 'woocommerce_update_options_payment_gateways', array( $this, 'save_payment_gateway_settings' ) );
				}
				add_action( 'woocommerce_receipt_vantiv', array( $this, 'receipt_page' ) );
				
				add_action( 'woocommerce_order_status_processing', array( $this, 'capture_payment' ) );
				add_action( 'woocommerce_order_status_completed', array( $this, 'capture_payment' ) );
				add_action( 'woocommerce_api_wc_gateway_vantiv', array( $this, 'check_ipn_response' ) );
				add_action( 'woocommerce_order_status_cancelled', array( $this, 'cancel_payment' ) );
				add_action( 'woocommerce_order_status_refunded', array( $this, 'cancel_payment' ) );
				
			}
			
			function init_form_fields ()
			{
				$this->form_fields = array(
					'enabled' => array(
						'title'       => __( 'Enable/Disable', 'gateway-vantiv-woocommerce' ),
						'type'        => 'checkbox',
						'label'       => __( 'Enable Vantiv Payment Module.', 'gateway-vantiv-woocommerce' ),
						'default'     => 'no'
					),
					'title' => array(
						'title'       => __( 'Title:', 'gateway-vantiv-woocommerce' ),
						'type'        => 'text',
						'description' => __( 'This controls the title which the user sees during checkout.', 'gateway-vantiv-woocommerce' ),
						'default'     => __( 'Vantiv', 'gateway-vantiv-woocommerce' )
					),
					'description' => array(
						'title'       => __( 'Description:', 'gateway-vantiv-woocommerce' ),
						'type'        => 'textarea',
						'description' => __( 'This controls the description which the user sees during checkout.', 'gateway-vantiv-woocommerce' ),
						'default'     => __( 'Pay securely by Credit or Debit card or internet banking through Vantiv Secure Servers.', 'gateway-vantiv-woocommerce' )
					),
					'AccountID' => array(
						'title'       => __( 'AccountID', 'gateway-vantiv-woocommerce' ),
						'type'        => 'text',
						'description' => __( 'Unique account identifier. Required', 'gateway-vantiv-woocommerce' ),
						'desc_tip'    => true,
					),
					'AccountToken' => array(
						'title'       => __( 'AccountToken', 'gateway-vantiv-woocommerce' ),
						'type'        => 'text',
						'description' => __( 'Unique account token. Required', 'gateway-vantiv-woocommerce' ),
						'desc_tip'    => true,
					),
					'AcceptorID' => array(
						'title'       => __( 'AcceptorID', 'gateway-vantiv-woocommerce' ),
						'type'        => 'text',
						'description' => __( 'Unique acceptor id. Required', 'gateway-vantiv-woocommerce' ),
						'desc_tip'    => true,
					),
					'ApplicationID' => array(
						'title'       => __( 'ApplicationID', 'gateway-vantiv-woocommerce' ),
						'type'        => 'text',
						'description' => __( 'Unique application id. Required', 'gateway-vantiv-woocommerce' ),
						'desc_tip'    => true,
					),
					'ApplicationVersion' => array(
						'title'       => __( 'ApplicationVersion', 'gateway-vantiv-woocommerce' ),
						'type'        => 'text',
						'description' => __( 'Version of application. Required', 'gateway-vantiv-woocommerce' ),
						'desc_tip'    => true,
					),
					'ApplicationName' => array(
						'title'       => __( 'ApplicationName', 'gateway-vantiv-woocommerce' ),
						'type'        => 'text',
						'description' => __( 'Name of application. Required', 'gateway-vantiv-woocommerce' ),
						'desc_tip'    => true,
					),
					'TerminalID' => array(
						'title'       => __( 'TerminalID', 'gateway-vantiv-woocommerce' ),
						'type'        => 'text',
						'description' => __( 'Unique terminal id. Required', 'gateway-vantiv-woocommerce' ),
						'desc_tip'    => true,
					),
					'TerminalCapabilityCode' => array(
						'title'       => __( 'TerminalCapabilityCode', 'gateway-vantiv-woocommerce' ),
						'type'        => 'text',
						'description' => __( 'TerminalCapabilityCode specifies what the capabilities of the Terminal are. Required', 'gateway-vantiv-woocommerce' ),
						'desc_tip'    => true,
					),
					'TerminalEnvironmentCode' => array(
						'title'       => __( 'TerminalEnvironmentCode', 'gateway-vantiv-woocommerce' ),
						'type'        => 'text',
						'description' => __( 'TerminalEnvironmentCode specifies what type of conditions the Terminal is operated in. Required', 'gateway-vantiv-woocommerce' ),
						'desc_tip'    => true,
					),
					'CardholderPresentCode' => array(
						'title'       => __( 'CardholderPresentCode', 'gateway-vantiv-woocommerce' ),
						'type'        => 'text',
						'description' => __( 'CardholderPresentCode specifies the location of the cardholder at the time of the transaction. Required', 'gateway-vantiv-woocommerce' ),
						'desc_tip'    => true,
					),
					'CardInputCode' => array(
						'title'       => __( 'CardInputCode', 'gateway-vantiv-woocommerce' ),
						'type'        => 'text',
						'description' => __( ' CardInputCode specifies the means by which the Card Number or Track Data was acquired. Required', 'gateway-vantiv-woocommerce' ),
						'desc_tip'    => true,
					),
					'CardPresentCode' => array(
						'title'       => __( 'CardPresentCode', 'gateway-vantiv-woocommerce' ),
						'type'        => 'text',
						'description' => __( ' CardPresentCode specifies the location of the card at the time of the transaction. Required', 'gateway-vantiv-woocommerce' ),
						'desc_tip'    => true,
					),
					'MotoECICode' => array(
						'title'       => __( 'MotoECICode', 'gateway-vantiv-woocommerce' ),
						'type'        => 'text',
						'description' => __( 'MotoECI Code is used on MOTO and E-Commerce transactions to identify the type of transaction, and the means in which it was obtained. Required', 'gateway-vantiv-woocommerce' ),
						'desc_tip'    => true,
					),
					'CVVPresenceCode' => array(
						'title'       => __( 'CVVPresenceCode', 'gateway-vantiv-woocommerce' ),
						'type'        => 'text',
						'description' => __( 'CVVPresenceCode specifies the status of the CVV code from the consumer card as it pertains to the transaction. Required', 'gateway-vantiv-woocommerce' ),
						'desc_tip'    => true,
					),
					'CompanyName' => array(
						'title'       => __( 'CompanyName', 'gateway-vantiv-woocommerce' ),
						'type'        => 'text',
						'description' => __( 'Name of merchant. Required', 'gateway-vantiv-woocommerce' ),
						'desc_tip'    => true,
					),
					'WelcomeMessage' => array(
						'title'       => __( 'WelcomeMessage', 'gateway-vantiv-woocommerce' ),
						'type'        => 'text',
						'description' => __( 'Text to display to cardholder. Optional', 'gateway-vantiv-woocommerce' ),
						'desc_tip'    => true,
					),
				);
			}
			
			public function save_payment_gateway_settings ()
			{
				$this->init_settings();
				$post_data = $this->get_post_data();
				$line = array();
				$handle = fopen( __DIR__ . '/../vantiv_sdk/cnp/sdk/cnp_SDK_config.ini', 'w' );
				if ( $handle ) {
					foreach ( $this->get_form_fields() as $key => $field ) {
						if ( $key == 'AccountID' ) {
							$vantivAccountId = $this->get_field_value( $key, $field, $post_data );
						}
						if ( $key == 'AccountToken' ) {
							$vantivPublicKeyID = $this->get_field_value( $key, $field, $post_data );
						}
						if ( $key == 'AcceptorID' ) {
							$vantivAcceptorID = $this->get_field_value( $key, $field, $post_data );
						}
						if ( $key == 'ApplicationID' ) {
							$vantivApplicationID = $this->get_field_value( $key, $field, $post_data );
						}
						if ( $key == 'ApplicationVersion' ) {
							$vantivApplicationVersion = $this->get_field_value( $key, $field, $post_data );
						}
						if ( $key == 'ApplicationName' ) {
							$vantivApplicationName = $this->get_field_value( $key, $field, $post_data );
						}
						if ( $key == 'TerminalID' ) {
							$vantivTerminalID = $this->get_field_value( $key, $field, $post_data );
						}
						if ( $key == 'TerminalCapabilityCode' ) {
							$vantivTerminalCapabilityCode = $this->get_field_value( $key, $field, $post_data );
						}
						if ( $key == 'TerminalEnvironmentCode' ) {
							$vantivTerminalEnvironmentCode = $this->get_field_value( $key, $field, $post_data );
						}
						if ( $key == 'CardholderPresentCode' ) {
							$vantivCardholderPresentCode = $this->get_field_value( $key, $field, $post_data );
						}
						if ( $key == 'CardInputCode' ) {
							$vantivCardInputCode = $this->get_field_value( $key, $field, $post_data );
						}
						if ( $key == 'CardPresentCode' ) {
							$vantivCardPresentCode = $this->get_field_value( $key, $field, $post_data );
						}
						if ( $key == 'MotoECICode' ) {
							$vantivMotoECICode = $this->get_field_value( $key, $field, $post_data );
						}
						if ( $key == 'CVVPresenceCode' ) {
							$vantivCVVPresenceCode = $this->get_field_value( $key, $field, $post_data );
						}
						if ( $key == 'CompanyName' ) {
							$vantivCompanyName = $this->get_field_value( $key, $field, $post_data );
						}
						if ( $key == 'WelcomeMessage' ) {
							$vantivWelcomeMessage = $this->get_field_value( $key, $field, $post_data );
						}
					}
                    $line['AccountID']               = !empty( $vantivAccountId ) ? $vantivAccountId :'';
                    $line['AccountToken']            = !empty( $vantivPublicKeyID ) ? $vantivPublicKeyID :'';
                    $line['AcceptorID']              = !empty( $vantivAcceptorID ) ? $vantivAcceptorID :'';
                    $line['ApplicationID']           = !empty( $vantivApplicationID ) ? $vantivApplicationID :'';
                    $line['ApplicationVersion']      = !empty( $vantivApplicationVersion ) ? $vantivApplicationVersion : '1.1.1';
                    $line['ApplicationName']         = !empty( $vantivApplicationName ) ? $vantivApplicationName : 'XML';
                    $line['TerminalID']              = !empty( $vantivTerminalID ) ? $vantivTerminalID : '01';
                    $line['TerminalCapabilityCode']  = !empty( $vantivTerminalCapabilityCode ) ? $vantivTerminalCapabilityCode : '5';
                    $line['TerminalEnvironmentCode'] = !empty( $vantivTerminalEnvironmentCode ) ? $vantivTerminalEnvironmentCode : '6';
                    $line['CardholderPresentCode']   = !empty( $vantivCardholderPresentCode ) ? $vantivCardholderPresentCode : '7';
                    $line['CardInputCode']           = !empty( $vantivCardInputCode ) ? $vantivCardInputCode : '4';
                    $line['CardPresentCode']         = !empty( $vantivCardPresentCode ) ? $vantivCardPresentCode : '3';
                    $line['MotoECICode']             = !empty( $vantivMotoECICode ) ? $vantivMotoECICode : '7';
                    $line['CVVPresenceCode']         = !empty( $vantivCVVPresenceCode ) ? $vantivCVVPresenceCode : '2';

                    $line['URL'] = 'https://certtransaction.elementexpress.com/';
                    $line['TransactionSetupMethod']    = '1';
                    $line['TerminalType']              = '2';
                    $line['DeviceInputCode']           = '0';
                    $line['Device']                    = '0';
                    $line['Embedded']                  = '0';
                    $line['CVVRequired']               = '1';
                    $line['CompanyName']               = !empty( $vantivCompanyName ) ? $vantivCompanyName : '';
                    $line['AutoReturn']                = '1';
                    $line['WelcomeMessage']            = !empty( $vantivWelcomeMessage ) ? $vantivWelcomeMessage : 'Thank you for your order!';
					$line['AddressEditAllowed']        = '0';
					$line['MarketCode']                = '3';
					$line['DuplicateCheckDisableFlag'] = '1';
					$this->writeConfigPaymentSettings( $line, $handle );
				}
				fclose( $handle );
				foreach ( $this->get_form_fields() as $key => $field ) {
					if ( 'title' !== $this->get_field_type( $field ) ) {
						try {
							$this->settings[ $key ] = $this->get_field_value( $key, $field, $post_data );
						} catch ( Exception $e ) {
							$this->add_error( $e->getMessage() );
						}
					}
				}
				
				return update_option( $this->get_option_key(), apply_filters( 'woocommerce_settings_api_sanitized_fields_' . $this->id, $this->settings ), 'yes' );
			}
			
			public function writeConfigPaymentSettings ( $line, $handle )
			{
				foreach ( $line as $keys => $values ) {
					fwrite( $handle, $keys . '' );
					if ( is_array( $values ) ) {
						foreach ( $values as $key2 => $value2 ) {
							fwrite( $handle, "['" . $key2 . "'] = " . $value2 . PHP_EOL );
						}
					} else {
						fwrite( $handle, ' = ' . $values );
						fwrite( $handle, PHP_EOL );
					}
				}
			}
			
			public function admin_options ()
			{
				echo '<h3>' . __( 'Vantiv Payment Gateway', 'gateway-vantiv-woocommerce' ) . '</h3>';
				echo '<p>' . __( 'Vantiv is most popular payment gateway for online shopping', 'gateway-vantiv-woocommerce' ) . '</p>';
				echo '<table class="form-table">';
				// Generate the HTML For the settings form.
				$this->generate_settings_html();
				echo '</table>';
				
			}
			
			/**
			 *  There are no payment fields for vantiv, but we want to show the description if set.
			 **/
			public function payment_fields ()
			{
				$description = $this->get_description();
				$description = !empty( $description ) ? $description : '';
				$description = trim( $description );
				echo wpautop( wp_kses_post( $description ) );
			}
			
			/**
			 * Process the payment and return the result
			 **/
			function process_payment ( $order_id )
			{
				$order = new WC_Order( $order_id );

				return array(
					'result' => 'success',
					'redirect' => add_query_arg( 'order', $order->get_id(), add_query_arg( 'key', $order->order_key, get_permalink( wc_get_page_id( 'pay' ) ) ) )
				);
			}
			
			/**
			 * Receipt Page
			 **/
			function receipt_page( $order )
			{
				echo $this->generate_form ( $order );
			}
			
			public function generate_form ( $order_id )
			{
				global $woocommerce;

				$order = new WC_Order( $order_id );

				$result_url = add_query_arg( 'wc-api', 'wc_gateway_vantiv', home_url( '/checkout/order-received/' . $order->get_id() ) );
				$html = $this->cnb_form( array(
					'ReturnURL'   => $result_url,
					'Address'     => array(
						'BillingZipcode'    => $order->get_billing_postcode(),
						'BillingName'       => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
						'BillingAddress1'   => $order->get_billing_address_1(),
						'BillingCity'       => $order->get_billing_city(),
						'BillingState'      => $order->get_billing_state(),
					),
					'Transaction' => array(
						'TransactionAmount' => $order->get_total(),
						'ReferenceNumber'   => $order->get_id(),
					)
				) );
				return $html;
			}
			
			public function cnb_form ( $params )
			{
				$initialize = new CnpOnlineRequest();
				$saleResponse = $initialize->saleRequest( $params );
				
				if ( XmlParser::getNode($saleResponse, 'ExpressResponseMessage' ) != 'Success')
					throw new \Exception('Hosted Payment Transaction does not get the right response');
				
				// 1 or 4 means the transaction was a success
				if ( XmlParser::getNode( $saleResponse, 'ExpressResponseMessage' ) == 'Success' ) {
					
					// Redirect to hostedpayments page
					$redirect_url = $this->liveurl . '?TransactionSetupID=' . XmlParser::getNode( $saleResponse, 'TransactionSetupID' );
					wp_redirect( $redirect_url );
				} else {
					//transiction fail
					wc_add_notice( XmlParser::getNode( $saleResponse, 'ExpressResponseMessage' ), 'error' );
				}
				return '';
			}
			
			function check_ipn_response ()
			{
				global $woocommerce;
				if ( isset( $_GET['TransactionSetupID'] ) ) {
					$url = $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
					$template_name = strpos( $url, '/order-received/' ) === false ? '/view-order/' : '/order-received/';
					if ( strpos( $url, $template_name ) !== false ) {
						$start = strpos( $url, $template_name );
						$first_part = substr( $url, $start + strlen( $template_name ) );
						$order_id = substr( $first_part, 0, strpos( $first_part, '?' ) );
					}
					if ( $order_id != '' ) {
						try {
							$order = new WC_Order( $order_id );
							$responseMessage = ( isset( $_GET['ExpressResponseMessage'] ) ) ? sanitize_text_field( $_GET['ExpressResponseMessage'] ) : '';
							$transactionId = ( isset( $_GET['TransactionID'] ) ) ? sanitize_text_field( $_GET['TransactionID'] ) : '';
							$transactionSetupId = ( isset( $_GET['TransactionSetupID'] ) ) ? sanitize_text_field( $_GET['TransactionSetupID'] ) : '';
							$transactionResponseMessage = ( isset( $_GET['ExpressResponseMessage'] ) ) ? sanitize_text_field( $_GET['ExpressResponseMessage'] ) : '';
							$status = ( isset( $_GET['HostedPaymentStatus'] ) ) ? sanitize_text_field( $_GET['HostedPaymentStatus'] ) : '';
							$order_status = $order->get_status();
							if ( $order_status !== 'completed' ) {
								$status = strtolower( $status );
								if ( $status == 'complete' ) {
									$this->msg['message'] = "Thank you for shopping with us. Right now your payment status is pending, We will keep you posted regarding the status of your order through e-mail";
									$this->msg['class'] = 'woocommerce_message woocommerce_message_info';
									$order->payment_complete( $transactionId );
									update_post_meta( $order_id, '_transaction_setup_id', $transactionSetupId );
									$order->add_order_note( 'Vantiv payment successful<br/>Unnique transaction Id from Vantiv: ' . esc_html( $transactionId ) );
									$order->add_order_note( $this->msg['message'] );
									$woocommerce->cart->empty_cart();
									
									$redirect_url = add_query_arg( 'order', $order->get_id(), add_query_arg( 'key', $order->order_key, home_url( '/checkout/order-received/' . $order->get_id() ) ) );
									wp_redirect( $redirect_url );
								} else {
									$this->msg['class'] = 'woocommerce_error';
									$this->msg['message'] = "Thank you for shopping with us. However, the transaction has been declined.";
									$order->add_order_note( 'Transaction Declined: ' . esc_html( $transactionResponseMessage ) );
									wp_redirect( $order->get_cancel_order_url() );
									exit;
								}
							}
						} catch ( Exception $e ) {
							// $errorOccurred = true;
							$this->msg['class'] = 'woocommerce-error';
							$this->msg['message'] = 'Error';
							wp_die( 'Payment Request Failure' );
						}
					}
				}
			}
			
			public function showMessage( $content )
			{
				return '<div class="box ' . $this->msg['class'] . '-box">' . $this->msg['message'] . '</div>' . $content;
			}
			
			// get all pages
			public function get_pages ( $title = false, $indent = true )
			{
				$wp_pages = get_pages( 'sort_column=menu_order' );
				$page_list = array();
				if ( $title ) $page_list[] = $title;
				foreach ( $wp_pages as $page ) {
					$prefix = '';
					// show indented child pages?
					if ( $indent ) {
						$has_parent = $page->post_parent;
						while ( $has_parent ) {
							$prefix .= ' - ';
							$next_page = get_page( $has_parent );
							$has_parent = $next_page->post_parent;
						}
					}
					// add to page list array array
					$page_list[ $page->ID ] = $prefix . $page->post_title;
				}
				return $page_list;
			}
			
			/**
			 * Can the order be refunded via Vantiv?
			 *
			 * @param WC_Order $order Order object.
			 * @return boolean
			 */
			public function can_refund_order ( $order )
			{
				$config = $this->getConfig();
				$has_api_creds = $config['AccountID'] && $config['AccountToken'] && $config['AcceptorID'];
				
				return $order && $order->get_transaction_id() && $has_api_creds;
				
			}//end can_refund_order()
			
			public function getConfig ()
			{
				$config_array = null;
				$ini_file = realpath( dirname( __DIR__ ) ) . '/vantiv_sdk/cnp/sdk/cnp_SDK_config.ini';
                if ( file_exists( $ini_file ) ) {
                    @$config_array = parse_ini_file( $ini_file );
                }

                if ( empty( $config_array ) ) {
					$config_array = array();
				}
				return $config_array;
			}
			
			/**
			 * Process a refund if supported.
			 *
			 *
			 * @param integer $order_id Order ID.
			 * @param float $amount Refund amount.
			 * @param string $reason Refund reason.
			 * @return boolean|WP
			 */
			public function process_refund ( $order_id, $amount = null, $reason = '' ) {
				$order = wc_get_order( $order_id );
					
				if ( !$this->can_refund_order( $order ) ) {
					return new WP_Error( 'error', __( 'Refund failed.', 'woocommerce' ) );
				}

				$config          = $this->getConfig();
				$initialize      = new CnpOnlineRequest();
				$result          = $initialize->refund_transaction( $order, $amount, $reason, $config );
                $responseMessage = XmlParser::getNode( $result, 'ExpressResponseMessage' );
                $refundId        = XmlParser::getNode( $result, 'TransactionID' );
                if ( $responseMessage == 'Approved' ) {
                    update_post_meta( $order_id, '_vantiv_refund_id', XmlParser::getNode( $result, 'TransactionID' ) );
                    $order->add_order_note(
                    // translators: 1: Refund amount, 2: Refund ID
                        sprintf( __( 'Refunded %1$s - Refund ID: %2$s', 'woocommerce' ), $amount, $refundId ) // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
                    );
                    return true;
                } else {
                    return false;
                }

			}//end process_refund()

            /**
             * Capture payment when the order is changed from on-hold to complete or processing
             *
             * @param integer $order_id Order ID.
             */
            public function capture_payment ( $order_id ) {
                $order = wc_get_order( $order_id );

                if ( 'vantiv' === $order->get_payment_method() && 'pending' === $order->get_status() && $order->get_transaction_id() ) {

                    echo $this->generate_form( $order_id );

                }//end if

            }//end capture_payment()

			/**
			 * Cancel pre-auth on refund/cancellation.
			 *
			 * @since 3.1.0
			 * @version 4.2.2
			 * @param  int $order_id
			 */
			public function cancel_payment ( $order_id ) {
				$order = wc_get_order( $order_id );

				if ( 'vantiv' === $order->get_payment_method() ) {
					$captured = 'yes';
					if ( 'no' === $captured ) {
						$this->process_refund( $order_id );
					}
				}
			}
		}
	}
