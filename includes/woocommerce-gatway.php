<?php

require_once __DIR__ . '/../vantiv_sdk/vendor/autoload.php';
use cnp\sdk\CnpOnlineRequest;
use cnp\sdk\XmlParser;

if ( ! defined('ABSPATH') ) {
    exit;
}

if ( ! class_exists('WC_Gateway_Vantiv') ) {
    class WC_Gateway_Vantiv extends WC_Payment_Gateway
    {
        public function __construct ()
        {
            $this->id                 = 'vantiv';
            $this->medthod_title      = 'Vantiv';
            $this->has_fields         = false;
            $this->init_form_fields();
            $this->init_settings();
            $this->title              = $this->settings['title'];
            $this->testmode           = 'yes' === $this->get_option('sandbox');
            $this->description        = $this->get_option('description');
            $this->method_description = 'Vantiv works by adding payment fields on the checkout and then sending the details to Vantiv.';
            $this->liveurl            = 'https://certtransaction.hostedpayments.com/';
            $this->msg['message']     = "";
            $this->msg['class']       = "";
			$this->order_button_text  = __('Proceed to Vantiv', 'woocommerce');

            add_action('init', array( &$this, 'check_vantiv_response' ));
            if ( version_compare(WOOCOMMERCE_VERSION, '2.0.0', '>=') ) {
                add_action('woocommerce_update_options_payment_gateways_' . $this->id, array( &$this, 'save_payment_gateway_settings' ));
            } else {
                add_action('woocommerce_update_options_payment_gateways', array( &$this, 'save_payment_gateway_settings' ));
            }
            add_action('woocommerce_receipt_vantiv', array( &$this, 'receipt_page' ));
        }

        function init_form_fields ()
        {
            $this->form_fields = array(
                'enabled'     => array(
                    'title'       => __('Enable/Disable', 'gateway-vantiv-woocommerce'),
                    'type'        => 'checkbox',
                    'label'       => __('Enable Vantiv Payment Module.', 'gateway-vantiv-woocommerce'),
                    'default'     => 'no'
                ),
                'title'       => array(
                    'title'       => __('Title:', 'gateway-vantiv-woocommerce'),
                    'type'        => 'text',
                    'description' => __('This controls the title which the user sees during checkout.', 'gateway-vantiv-woocommerce'),
                    'default'     => __('Vantiv', 'gateway-vantiv-woocommerce')
                ),
                'description' => array(
                    'title'       => __('Description:', 'gateway-vantiv-woocommerce'),
                    'type'        => 'textarea',
                    'description' => __('This controls the description which the user sees during checkout.', 'gateway-vantiv-woocommerce'),
                    'default'     => __('Pay securely by Credit or Debit card or internet banking through Vantiv Secure Servers.', 'gateway-vantiv-woocommerce')
                ),
				'AccountID'  => array(
					'title'       => __( 'AccountID', 'gateway-vantiv-woocommerce' ),
					'type'        => 'text',
					'description' => __( 'Unique account identifier. Required', 'gateway-vantiv-woocommerce' ),
					'desc_tip'    => true,
				),
				'AccountToken'  => array(
					'title'       => __( 'AccountToken', 'gateway-vantiv-woocommerce' ),
					'type'        => 'text',
					'description' => __( 'Unique account token. Required', 'gateway-vantiv-woocommerce' ),
					'desc_tip'    => true,
				),
				'AcceptorID'  => array(
					'title'       => __( 'AcceptorID', 'gateway-vantiv-woocommerce' ),
					'type'        => 'text',
					'description' => __( 'Unique acceptor id. Required', 'gateway-vantiv-woocommerce' ),
					'desc_tip'    => true,
				),
				'ApplicationID'  => array(
					'title'       => __( 'ApplicationID', 'gateway-vantiv-woocommerce' ),
					'type'        => 'text',
					'description' => __( 'Unique application id. Required', 'gateway-vantiv-woocommerce' ),
					'desc_tip'    => true,
				),
				'ApplicationVersion'  => array(
					'title'       => __( 'ApplicationVersion', 'gateway-vantiv-woocommerce' ),
					'type'        => 'text',
					'description' => __( 'Unique application version. Required', 'gateway-vantiv-woocommerce' ),
					'desc_tip'    => true,
				),
				'ApplicationName'  => array(
					'title'       => __( 'ApplicationName', 'gateway-vantiv-woocommerce' ),
					'type'        => 'text',
					'description' => __( 'Unique application name. Required', 'gateway-vantiv-woocommerce' ),
					'desc_tip'    => true,
				),
				'TerminalID'  => array(
					'title'       => __( 'TerminalID', 'gateway-vantiv-woocommerce' ),
					'type'        => 'text',
					'description' => __( 'Unique terminal id. Required', 'gateway-vantiv-woocommerce' ),
					'desc_tip'    => true,
				),
				'TerminalCapabilityCode'  => array(
					'title'       => __( 'TerminalCapabilityCode', 'gateway-vantiv-woocommerce' ),
					'type'        => 'text',
					'description' => __( 'Unique terminal capability code. Required', 'gateway-vantiv-woocommerce' ),
					'desc_tip'    => true,
				),
				'TerminalEnvironmentCode'  => array(
					'title'       => __( 'TerminalEnvironmentCode', 'gateway-vantiv-woocommerce' ),
					'type'        => 'text',
					'description' => __( 'Unique terminal environment code. Required', 'gateway-vantiv-woocommerce' ),
					'desc_tip'    => true,
				),
				'CardholderPresentCode'  => array(
					'title'       => __( 'CardholderPresentCode', 'gateway-vantiv-woocommerce' ),
					'type'        => 'text',
					'description' => __( 'Unique card holder present code. Required', 'gateway-vantiv-woocommerce' ),
					'desc_tip'    => true,
				),
				'CardInputCode'  => array(
					'title'       => __( 'CardInputCode', 'gateway-vantiv-woocommerce' ),
					'type'        => 'text',
					'description' => __( 'Unique card input code. Required', 'gateway-vantiv-woocommerce' ),
					'desc_tip'    => true,
				),
				'CardPresentCode'  => array(
					'title'       => __( 'CardPresentCode', 'gateway-vantiv-woocommerce' ),
					'type'        => 'text',
					'description' => __( 'Unique card present code. Required', 'gateway-vantiv-woocommerce' ),
					'desc_tip'    => true,
				),
				'MotoECICode'  => array(
					'title'       => __( 'MotoECICode', 'gateway-vantiv-woocommerce' ),
					'type'        => 'text',
					'description' => __( 'Unique moto ECI code. Required', 'gateway-vantiv-woocommerce' ),
					'desc_tip'    => true,
				),
				'CVVPresenceCode'  => array(
					'title'       => __( 'CVVPresenceCode', 'gateway-vantiv-woocommerce' ),
					'type'        => 'text',
					'description' => __( 'Unique CVV presence code. Required', 'gateway-vantiv-woocommerce' ),
					'desc_tip'    => true,
				),
                'CompanyName'  => array(
					'title'       => __( 'CompanyName', 'gateway-vantiv-woocommerce' ),
					'type'        => 'text',
					'description' => __( 'Your Company Name. Required', 'gateway-vantiv-woocommerce' ),
					'desc_tip'    => true,
				),
                'WelcomeMessage'  => array(
					'title'       => __( 'WelcomeMessage', 'gateway-vantiv-woocommerce' ),
					'type'        => 'text',
					'description' => __( 'Welcome Message after order success. Required', 'gateway-vantiv-woocommerce' ),
					'desc_tip'    => true,
				),
                'sandbox'      => array(
                    'title'       => __('Sandbox', 'gateway-vantiv-woocommerce'),
                    'label'       => __('Enable', 'gateway-vantiv-woocommerce'),
                    'type'        => 'checkbox',
                    'description' => __('Sandbox mode', 'gateway-vantiv-woocommerce'),
                    'desc_tip'    => true,
                ),
            );
        }
    
        public function save_payment_gateway_settings ()
        {
            $this->init_settings();
            $post_data = $this->get_post_data();
            $line = array();
            $handle = fopen(__DIR__ . '/../vantiv_sdk/cnp/sdk/cnp_SDK_config.ini', 'w');
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
					if ( $key == 'sandbox' ) {
						$sandbox = $this->get_field_value( $key, $field, $post_data );
					}
				}
				$line['AccountID']               = $vantivAccountId;
				$line['AccountToken']            = $vantivPublicKeyID;
				$line['AcceptorID']              = $vantivAcceptorID;
				$line['ApplicationID']           = $vantivApplicationID;
				$line['ApplicationVersion']      = $vantivApplicationVersion;
				$line['ApplicationName']         = $vantivApplicationName;
				$line['TerminalID']              = $vantivTerminalID;
				$line['TerminalCapabilityCode']  = $vantivTerminalCapabilityCode;
				$line['TerminalEnvironmentCode'] = $vantivTerminalEnvironmentCode;
				$line['CardholderPresentCode']   = $vantivCardholderPresentCode;
				$line['CardInputCode']           = $vantivCardInputCode;
				$line['CardPresentCode']         = $vantivCardPresentCode;
				$line['MotoECICode']             = $vantivMotoECICode;
				$line['CVVPresenceCode']         = $vantivCVVPresenceCode;
				if ( $sandbox == 'yes' ) {
					$line['url'] = 'https://certtransaction.hostedpayments.com/';
				} else {
					$line['url'] = 'https://certtransaction.hostedpayments.com/';
				}

				$line['TransactionSetupMethod'] = '1';
				$line['DeviceInputCode'] = '0';
				$line['Device'] = '0';
				$line['Embedded'] = '0';
				$line['CVVRequired'] = '1';
				$line['CompanyName'] = $vantivCompanyName;
				$line['AutoReturn'] = '1';
				$line['WelcomeMessage'] = $vantivWelcomeMessage;
				$line['AddressEditAllowed'] = '0';
				$line['MarketCode'] = '3';
				$line['DuplicateCheckDisableFlag'] = '1';
                $this->writeConfigPaymentSettings($line, $handle);
            }
            fclose($handle);
            foreach ( $this->get_form_fields() as $key => $field ) {
                if ( 'title' !== $this->get_field_type($field) ) {
                    try {
                        $this->settings[ $key ] = $this->get_field_value($key, $field, $post_data);
                    } catch ( Exception $e ) {
                        $this->add_error($e->getMessage());
                    }
                }
            }

            return update_option($this->get_option_key(), apply_filters('woocommerce_settings_api_sanitized_fields_' . $this->id, $this->settings), 'yes');
        }
        public function writeConfigPaymentSettings ( $line,$handle )
        {
            foreach ( $line as $keys => $values ) {
                fwrite($handle, $keys . '');
                if ( is_array($values) ) {
                    foreach ( $values as $key2 => $value2 ) {
                        fwrite($handle, "['" . $key2 . "'] = " . $value2 .  PHP_EOL);
                    }
                } else {
                    fwrite($handle, ' = ' . $values);
                    fwrite($handle, PHP_EOL);
                }
            }
        }
        public function admin_options ()
        {
            echo '<h3>' . __('Vantiv Payment Gateway', 'gateway-vantiv-woocommerce') . '</h3>';
            echo '<p>' . __('Vantiv is most popular payment gateway for online shopping', 'gateway-vantiv-woocommerce') . '</p>';
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
            $description = ! empty($description) ? $description : '';

//            if ( $this->testmode ) {
//                /* translators: link to vantiv testing page */
//                $description .= '<br/>' . '(' . __('TEST MODE ENABLED. In test mode, you can use the card number 4457010000000009 4761739001020076 with any CVC and a valid expiration date', 'gateway-vantiv-woocommerce') . ')';
//            }

            $description = trim($description);

            echo  wpautop(wp_kses_post($description));

//            $this->elements_form();
        }

        /**
        * Renders the Vantiv elements form.
        *
        * @since 4.0.0
        * @version 4.0.0
        **/
        public function elements_form ()
        {
        ?>
<!--            <fieldset id="wc---><?php //echo esc_attr($this->id); ?><!---cc-form" class="wc-credit-card-form wc-payment-form" style="background:transparent;">-->
<!--                --><?php //do_action('woocommerce_credit_card_form_start', $this->id); ?>
<!---->
<!--                <div class="form-row form-row-wide">-->
<!--                    <label for="cc">--><?php //esc_html_e('Card Number', 'gateway-vantiv-woocommerce'); ?><!-- <span class="required">*</span></label>-->
<!--                    <input id="cc" type="tel" style="width: 100%;"  name="ccnumber" pattern="\d{4} \d{4} \d{4} \d{4}" class="masked" title="16-digit number" maxlength="19" placeholder="XXXX XXXX XXXX XXXX">-->
<!--                </div>-->
<!---->
<!--                <div class="form-row form-row-first">-->
<!--                    <label for="expiration">--><?php //esc_html_e('Expiry Date', 'gateway-vantiv-woocommerce'); ?><!-- <span class="required">*</span></label>-->
<!--                    <input id="expiration" style="width: 100%;" class="masked" maxlength="5" type="tel" autocomplete="off" autocorrect="off" name="exp-date" placeholder="MM/YY" value=""  >-->
<!--                </div>-->
<!---->
<!--                <div class="form-row form-row-last">-->
<!--                    <label for="vantiv-cvc-element">--><?php //esc_html_e('Card Code (CVC)', 'gateway-vantiv-woocommerce'); ?><!-- <span class="required">*</span></label>-->
<!--                    <input id="vantiv-cvc-element" style="width: 100%;" maxlength="3" type="password" autocomplete="off"  autocorrect="off" spellcheck="false" name="cvc" inputmode="numeric"  placeholder="CVC" value="">-->
<!--                </div>-->
<!--                <div class="clear"></div>-->
                <!-- Used to display form errors -->
<!--                <div class="vantiv-source-errors" role="alert"></div>-->
<!--                <br />-->
<!--                --><?php //do_action('woocommerce_credit_card_form_end', $this->id); ?>
<!--                <div class="clear"></div>-->
<!--            </fieldset>-->
        <?php
        }

        /**
         * Receipt Page
         **/
        function receipt_page ( $order )
        {
            echo '<p>' . __('Thank you for your order, please click the button below to pay with Vantiv.', 'vantiv') . '</p>';
            echo $this->generate_vantiv_form($order);
        }
        /**
         * Generate vantiv button link
         **/
        public function generate_vantiv_form ( $order_id )
        {

//            global $woocommerce;
//
//            $order = new WC_Order($order_id);
//            $txnid = $order_id . '_' . date("ymds");
//
//            $redirect_url = ( $this->redirect_page_id == "" || $this->redirect_page_id == 0 ) ? get_site_url() . "/" : get_permalink($this->redirect_page_id);
//
//            $productinfo = "Order $order_id";
//
//            $str = "$this->merchant_id|$txnid|$order->order_total|$productinfo|$order->billing_first_name|$order->billing_email|||||||||||$this->salt";
//            $hash = hash('sha512', $str);
//
//            $vantiv_args = array(
//                    'key'         => $this->merchant_id,
//                    'txnid'       => $txnid,
//                    'amount'      => $order->order_total,
//                    'productinfo' => $productinfo,
//                    'firstname'   => $order->billing_first_name,
//                    'lastname'    => $order->billing_last_name,
//                    'address1'    => $order->billing_address_1,
//                    'address2'    => $order->billing_address_2,
//                    'city'        => $order->billing_city,
//                    'state'       => $order->billing_state,
//                    'country'     => $order->billing_country,
//                    'zipcode'     => $order->billing_zip,
//                    'email'       => $order->billing_email,
//                    'phone'       => $order->billing_phone,
//                    'surl'        => $redirect_url,
//                    'furl'        => $redirect_url,
//                    'curl'        => $redirect_url,
//                    'hash'        => $hash,
//                    'pg'          => 'NB'
//            );
//
//            $vantiv_args_array = array();
//
//            foreach ( $vantiv_args as $key => $value ) {
//                $vantiv_args_array[] = "<input type='hidden' name='$key' value='$value'/>";
//            }
//            return '<form action="' . $this->liveurl . '" method="post" id="vantiv_payment_form">
//                ' . implode('', $vantiv_args_array) . '
//                    <input type="submit" class="button-alt" id="submit_vantiv_payment_form" value="' . __('Pay via Vantiv', 'gateway-vantiv-woocommerce') . '" /> <a class="button cancel" href="' . $order->get_cancel_order_url() . '">' . __('Cancel order &amp; restore cart', 'gateway-vantiv-woocommerce') . '</a>
//                    <script type="text/javascript">
//                        jQuery(function(){
//                            jQuery("body").block(
//                            {
//                                message: "<img src=\"' . $woocommerce->plugin_url() . '/assets/images/ajax-loader.gif\" alt=\"Redirecting…\" style=\"float:left; margin-right: 10px;\" />' . __('Thank you for your order. We are now redirecting you to Payment Gateway to make payment.', 'gateway-vantiv-woocommerce') . '",
//                                overlayCSS:
//                                {
//                                    background: "#fff",
//                                    opacity: 0.6
//                                },
//                                css: {
//                                    padding:        20,
//                                    textAlign:      "center",
//                                    color:          "#555",
//                                    border:         "3px solid #aaa",
//                                    backgroundColor:"#fff",
//                                    cursor:         "wait",
//                                    lineHeight:"32px"
//                                }
//                            });
//                            jQuery("#submit_vantiv_payment_form").click();
//                        });
//                    </script>
//            </form>';


        }

        /**
         * Process the payment and return the result
        **/
        function process_payment ( $order_id ) {
//            ini_set('display_errors', 1);
//            ini_set('display_startup_errors', 1);
//            error_reporting(E_ALL);
            global $woocommerce;
            $order = new WC_Order( $order_id );
            $sale_info = array(
                'ReturnURL'     => $this->get_return_url( $order ),
                'Address' => array(
                    'BillingZipcode'      => $order->billing_postcode,
                    'BillingName'         => $order->billing_first_name . ' ' . $order->billing_last_name,
                    'BillingAddress1'     => $order->billing_address_1,
                    'BillingCity'         => $order->billing_city,
                    'BillingState'        => $order->billing_state,
//                    'BillingCountry'      => $order->billing_country,
                ),
                'Transaction'   => array(
                    'TransactionAmount' => ($order->order_total),
                    'ReferenceNumber'   => $order_id,
                )
            );

            $initialize = new CnpOnlineRequest();
            $saleResponse = $initialize->saleRequest( $sale_info );

//            var_dump(XmlParser::getNode( $saleResponse, 'ExpressResponseMessage' ));
//            var_dump($saleResponse);
//            die();
            if ( XmlParser::getNode( $saleResponse, 'ExpressResponseMessage' ) != 'Success' )
                throw new \Exception( 'CnpSaleTransaction does not get the right response' );

            // 1 or 4 means the transaction was a success
            if ( XmlParser::getNode( $saleResponse, 'ExpressResponseMessage' ) == 'Success' ) {
                // Payment successful
                $order->add_order_note( __( 'Vantiv complete payment.', 'gateway-vantiv-woocommerce' ) );

                // paid order marked
                $order->payment_complete();
                // this is important part for empty cart
                $woocommerce->cart->empty_cart();
                // Redirect to thank you page
                $redirect_url = 'https://certtransaction.hostedpayments.com/?TransactionSetupID=' . XmlParser::getNode( $saleResponse, 'TransactionSetupID' );
                return array(
                    'result'   => 'success',
                    'redirect' => $redirect_url,
                );
            } else {
                //transiction fail
                wc_add_notice( XmlParser::getNode( $saleResponse, 'ExpressResponseMessage' ), 'error' );
                $order->add_order_note( 'Error: ' . XmlParser::getNode( $saleResponse, 'ExpressResponseMessage' ) );
            }
        }

        public function validate_fields ()
        {
        }

        /**
        * Check for valid vantiv server callback
        **/
        function check_vantiv_response ()
        {
            global $woocommerce;
//            HostedPaymentStatus=Complete&
//            TransactionSetupID=50B9D0D5-FCE8-4DE4-B104-B2C0FAD96BF9&
//            TransactionID=64472707&
//            ExpressResponseCode=0&
//            ExpressResponseMessage=Approved&
//            CVVResponseCode=P&
//            ApprovalNumber=71632A
//            &LastFour=0076&
//            ValidationCode=637FBF528A454262&
//            CardLogo=Visa&
//            ApprovedAmount=1.00&
//            BillingAddress1=1+Main+St.&
//            BillingZipcode=01803-3747&
//            Bin=476173&
//            Entry=Manual&
//            NetTranID=000303563439789&
//            TranDT=2020-10-29%2010:39:03
//            var_dump($_REQUEST);
//            if ( isset($_REQUEST['txnid']) && isset($_REQUEST['mihpayid']) ) {
//                $order_id_time = ( isset($_REQUEST['txnid']) ) ?  sanitize_text_field($_REQUEST['txnid'])  : '';
//                $order_id = explode('_', $order_id_time);
//                $order_id = (int) $order_id[0];
//                if ( $order_id != '' ) {
//                    try {
//                        $order = new WC_Order($order_id);
//                        $merchant_id = ( isset($_REQUEST['key']) ) ?  sanitize_text_field($_REQUEST['key'])  : '';
//                        $amount = ( isset($_REQUEST['Amount']) ) ?  sanitize_text_field($_REQUEST['Amount'])  : '';
//                        $hash = ( isset($_REQUEST['hash']) ) ?  sanitize_text_field($_REQUEST['hash'])  : '';
//
//                        $status = ( isset($_REQUEST['status']) ) ?  sanitize_text_field($_REQUEST['status'])  : '';
//                        $productinfo = "Order $order_id";
//                        echo $hash;
//                        echo "{$this->salt}|$status|||||||||||{$order->billing_email}|{$order->billing_first_name}|$productinfo|{$order->order_total}|$order_id_time|{$this->merchant_id}";
//                        $checkhash = hash('sha512', "{$this->salt}|$status|||||||||||{$order->billing_email}|{$order->billing_first_name}|$productinfo|{$order->order_total}|$order_id_time|{$this->merchant_id}");
//                        $transauthorised = false;
//                        if ( $order->status !== 'completed' ) {
//                            if ( $hash == $checkhash ) {
//                                $status = strtolower($status);
//
//                                if ( $status == "success" ) {
//                                    $transauthorised = true;
//                                    $this->msg['message'] = "Thank you for shopping with us. Your account has been charged and your transaction is successful. We will be shipping your order to you soon.";
//                                    $this->msg['class'] = 'woocommerce_message';
//                                    if ( $order->status == 'processing' ) {
//
//                                    } else {
//                                        $order->payment_complete();
//                                        $order->add_order_note('Vantiv payment successful<br/>Unnique Id from Vantiv: ' . esc_html($_REQUEST['mihpayid']));
//                                        $order->add_order_note($this->msg['message']);
//                                        $woocommerce->cart->empty_cart();
//                                    }
//                                } else if ( $status == "pending" ) {
//                                    $this->msg['message'] = "Thank you for shopping with us. Right now your payment staus is pending, We will keep you posted regarding the status of your order through e-mail";
//                                    $this->msg['class'] = 'woocommerce_message woocommerce_message_info';
//                                    $order->add_order_note('Vantiv payment status is pending<br/>Unnique Id from Vantiv: ' . esc_html($_REQUEST['mihpayid']));
//                                    $order->add_order_note($this->msg['message']);
//                                    $order->update_status('on-hold');
//                                    $woocommerce->cart->empty_cart();
//                                } else {
//                                    $this->msg['class'] = 'woocommerce_error';
//                                    $this->msg['message'] = "Thank you for shopping with us. However, the transaction has been declined.";
//                                    $order->add_order_note('Transaction Declined: ' . esc_html($_REQUEST['Error']));
//
//                                    //Here you need to put in the routines for a failed
//                                    //transaction such as sending an email to customer
//                                    //setting database status etc etc
//                                }
//                            } else {
//                                $this->msg['class'] = 'woocommerce-error';
//                                $this->msg['message'] = "Security Error. Illegal access detected";
//
//                                //Here you need to simply ignore this and dont need
//                                //to perform any operation in this condition
//                            }
//                            if ( $transauthorised == false ) {
//                                $order->update_status('failed');
//                                $order->add_order_note('Failed');
//                                $order->add_order_note($this->msg['message']);
//
//                            }
//                            add_action('the_content', array( &$this, 'showMessage' ));
//                        }
//                    } catch ( Exception $e ) {
//                        // $errorOccurred = true;
//                        $this->msg['class'] = 'woocommerce-error';
//                        $this->msg['message'] = 'Error';
//                    }
//                }
//            }
        }

        public function showMessage ( $content )
        {
            return '<div class="box ' . $this->msg['class'] . '-box">' . $this->msg['message'] . '</div>' . $content;
        }
         // get all pages
        public function get_pages ( $title = false, $indent = true )
        {
            $wp_pages = get_pages('sort_column=menu_order');
            $page_list = array();
            if ( $title ) $page_list[] = $title;
            foreach ( $wp_pages as $page ) {
                $prefix = '';
                // show indented child pages?
                if ( $indent ) {
                    $has_parent = $page->post_parent;
                    while ( $has_parent ) {
                        $prefix .=  ' - ';
                        $next_page = get_page($has_parent);
                        $has_parent = $next_page->post_parent;
                    }
                }
                // add to page list array array
                $page_list[ $page->ID ] = $prefix . $page->post_title;
            }
            return $page_list;
        }
		/**
		 * Logging method.
		 *
		 * @param string $message Log message.
		 * @param string $level   Optional. Default 'info'. Possible values:
		 *                        emergency|alert|critical|error|warning|notice|info|debug.
		 */
		public static function log($message, $level='info')
		{
			if (self::$log_enabled) {
				if (empty(self::$log)) {
					self::$log = wc_get_logger();
				}
				
				self::$log->log($level, $message, [ 'source' => 'vantiv' ]);
			}
			
		}//end log()
    }
    
}