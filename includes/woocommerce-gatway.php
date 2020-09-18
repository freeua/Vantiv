<?php
//@ini_set( 'display_errors', 1 );
require_once __DIR__.'/../vantiv_sdk/vendor/autoload.php';
use cnp\sdk\CnpOnlineRequest;
use cnp\sdk\XmlParser;


if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}

if ( ! class_exists( 'WC_vantiv_Pay' ) ) {
    class WC_vantiv_Pay extends WC_Payment_Gateway{
        public function __construct(){
            $this->id = 'vantiv';
            $this->medthod_title = 'Vantiv';
            $this->has_fields = false;

            $this->init_form_fields();
            $this->init_settings();
            $this->title = $this->settings['title'];
            $this->testmode  = 'yes' === $this->get_option( 'sandbox' );
            //$this->icon = apply_filters('woocommerce_vantiv_icon', plugins_url( '/img/vantiv.png', __FILE__ ));
            $this->description= $this->get_option( 'description' );
            $this->method_description = 'Vantiv works by adding payment fields on the checkout and then sending the details to Vantiv.';
            //$this->merchant_id = $this->settings['merchant_id'];
            //$this->salt = $this->settings['salt'];
            //$this->redirect_page_id = $this->settings['redirect_page_id'];
            $this->liveurl = 'https://secure.vantiv.in/_payment';

            $this->msg['message'] = "";
            $this->msg['class'] = "";

            add_action('init', array(&$this, 'check_vantiv_response'));
            if ( version_compare( WOOCOMMERCE_VERSION, '2.0.0', '>=' ) ) {
                add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( &$this, 'save_payment_gateway_settings' ) );
            } else {
                add_action( 'woocommerce_update_options_payment_gateways', array( &$this, 'save_payment_gateway_settings' ) );
            }
            add_action('woocommerce_receipt_vantiv', array(&$this, 'receipt_page'));
        }

        function init_form_fields(){
            $this->form_fields = array(
                'enabled'     => array(
                    'title'       => __('Enable/Disable', 'vantiv'),
                    'type'        => 'checkbox',
                    'label'       => __('Enable Vantiv Payment Module.', 'vantiv'),
                    'default'     => 'no'),
                'title'       => array(
                    'title'       => __('Title:', 'vantiv'),
                    'type'        => 'text',
                    'description' => __('This controls the title which the user sees during checkout.', 'vantiv'),
                    'default'     => __('Vantiv', 'vantiv')),
                'description' => array(
                    'title'       => __('Description:', 'vantiv'),
                    'type'        => 'textarea',
                    'description' => __('This controls the description which the user sees during checkout.', 'vantiv'),
                    'default'     => __('Pay securely by Credit or Debit card or internet banking through Vantiv Secure Servers.', 'vantiv')),
                'public_key'  => array(
                    'title'       => __('Public key', 'vantiv'),
                    'type'        => 'text',
                    'description' => __('Public key Vantiv. Required', 'vantiv'),
                    'desc_tip'    => true,
                ),
                'account_token'  => array(
                    'title'       => __('Private key', 'Vantiv'),
                    'type'        => 'text',
                    'description' => __('Secret token used for authentication. Required', 'vantiv'),
                    'desc_tip'    => true,
                ),
                'account_id'  => array(
                    'title'       => __('AccountID', 'Vantiv'),
                    'type'        => 'text',
                    'description' => __('Unique account identifier. Required', 'vantiv'),
                    'desc_tip'    => true,
                ),
                'user_name'  => array(
                    'title'       => __('User name', 'Vantiv'),
                    'type'        => 'text',
                    'description' => __('The username supplied by Litle for accessing the site. Required', 'vantiv'),
                    'desc_tip'    => true,
                ),
                'password'  => array(
                    'title'       => __('Password', 'Vantiv'),
                    'type'        => 'password',
                    'description' => __('The password supplied by Litle for accessing the site. Required', 'vantiv'),
                    'desc_tip'    => true,
                ),
                'acceptor_id'  => array(
                    'title'       => __('MerchantID', 'Vantiv'),
                    'type'        => 'text',
                    'description' => __('Merchant ID. Required', 'vantiv'),
                    'desc_tip'    => true,
                ),
                'market_code'  => array(
                    'title'       => __('Transaction Method', 'Vantiv'),
                    'type'        => 'text',
                    'description' => __('Type of industry. Required', 'vantiv'),
                    'desc_tip'    => true,
                ),
                'report_group'  => array(
                    'title'       => __('Report Group', 'Vantiv'),
                    'type'        => 'text',
                    'description' => __('The exact name of the report group you want all orders to be added to. Optional', 'vantiv'),
                    'desc_tip'    => true,
                ),
                'sandbox'      => array(
                    'title'       => __('Sandbox', 'vantiv'),
                    'label'       => __('Enable', 'vantiv'),
                    'type'        => 'checkbox',
                    'description' => __('Sandbox mode', 'vantiv'),
                    'desc_tip'    => true,
                ),
                //				'debugging'  => array(
                //					'title'       => __('Debugging', 'Vantiv'),
                //					'type'        => 'text',
                //					'description' => __('Receive emails containing the data sent to and from Litle (does not include credit card information).', 'vantiv'),
                //					'desc_tip'    => true,
                //				),
                //				'debugging_email'  => array(
                //					'title'       => __('Debugging Email', 'Vantiv'),
                //					'type'        => 'text',
                //					'description' => __('Enter an email that will receive debug information via email.', 'vantiv'),
                //					'desc_tip'    => true,
                //				),
                //			'application_id'  => array(
                //				'title'       => __('ApplicationID', 'Vantiv'),
                //				'type'        => 'text',
                //				'description' => __('Unique application identifier. Required', 'vantiv'),
                //				'desc_tip'    => true,
                //			),
                //			'application_name'  => array(
                //				'title'       => __('ApplicationName', 'Vantiv'),
                //				'type'        => 'text',
                //				'description' => __('Name of application. Required', 'vantiv'),
                //				'desc_tip'    => true,
                //			),
                //			'application_version'  => array(
                //				'title'       => __('ApplicationVersion', 'Vantiv'),
                //				'type'        => 'text',
                //				'description' => __('Version of application. Required', 'vantiv'),
                //				'desc_tip'    => true,
                //			),
                //			'transaction_amount'  => array(
                //				'title'       => __('TransactionAmount', 'Vantiv'),
                //				'type'        => 'text',
                //				'description' => __('Transaction amount. Required', 'vantiv'),
                //				'desc_tip'    => true,
                //			),
                //			'reference_number'  => array(
                //				'title'       => __('ReferenceNumber', 'Vantiv'),
                //				'type'        => 'text',
                //				'description' => __('Reference number. Required', 'vantiv'),
                //				'desc_tip'    => true,
                //			),
                //			'ticket_number'  => array(
                //				'title'       => __('TicketNumber', 'Vantiv'),
                //				'type'        => 'text',
                //				'description' => __('Ticket number. Required', 'vantiv'),
                //				'desc_tip'    => true,
                //			),
                //			'partial_approved_flag'  => array(
                //				'title'       => __('PartialApprovedFlag', 'Vantiv'),
                //				'type'        => 'text',
                //				'description' => __('Specifies if POS supports partial approvals. Required', 'vantiv'),
                //				'desc_tip'    => true,
                //			),
                //			'terminal_id'  => array(
                //				'title'       => __('TerminalID', 'Vantiv'),
                //				'type'        => 'text',
                //				'description' => __('Terminal identifier. Required', 'vantiv'),
                //				'desc_tip'    => true,
                //			),
                //			'lane_number'  => array(
                //				'title'       => __('LaneNumber', 'Vantiv'),
                //				'type'        => 'text',
                //				'description' => __('Lane Number. Required', 'vantiv'),
                //				'desc_tip'    => true,
                //			),
            );
        }
	
	    public function save_payment_gateway_settings() {
            $this->init_settings();
            $post_data = $this->get_post_data();
            $line = array();
            $handle = fopen( __DIR__.'/../vantiv_sdk/cnp/sdk/cnp_SDK_config.ini', 'w' );
            if ( $handle ) {
                foreach ( $this->get_form_fields() as $key => $field ) {
                    //account_token Sandbox  market_code debugging debugging_email
                    if ( $key == 'public_key' ) {
                        $vantivPublicKeyID = $this->get_field_value( $key, $field, $post_data );
                    }
                    if ( $key == 'sandbox' ) {
                        $sandbox = $this->get_field_value( $key, $field, $post_data );
                    }
                    if ( $key == 'user_name' ) {
                        $account_name = $this->get_field_value( $key, $field, $post_data );
                    }
                    if ( $key == 'acceptor_id' ) {
                        $currency_merchant_map = $this->get_field_value( $key, $field, $post_data );
                    }
                    if ( $key == 'password' ) {
                        $password = $this->get_field_value( $key, $field, $post_data );
                    }
                    if ( $key == 'report_group' ) {
                        $report_group = $this->get_field_value( $key, $field, $post_data );
                    }
                }
                $line['user'] = $account_name;
                $line['password'] = $password;
                $line['currency_merchant_map ']['DEFAULT'] = $currency_merchant_map;

                if ( $sandbox == 'yes' ) {
                    $line['url'] = 'https://www.testvantivcnp.com/sandbox/communicator/online';
                    //$line['url'] = 'https://transact.vantivprelive.com/vap/communicator/online';
                } else {
                    $line['url'] = 'https://payments.vantivcnp.com/vap/communicator/online';
                }
                $line['proxy'] = '';
                $line['batch_requests_path'] = '';
                $line['cnp_requests_path'] = '';
                $line['sftp_username'] = '';
                $line['sftp_password'] = '';
                $line['batch_url'] = '';
                $line['tcp_port'] = '';
                $line['tcp_timeout'] = '';
                $line['sftp_timeout'] = '';
                # ssl should be usd by default
                $line['tcp_ssl'] = '1';
                $line['print_xml'] = '0';
                $line['useEncryption'] = "false";
                $line['vantivPublicKeyID'] = $vantivPublicKeyID;;
                $line['gpgPassphrase'] = "";
                $line['multiSite'] = "false";
                $line['multiSiteUrl1'] = '';
                $line['multiSiteUrl2'] = '';
                $line['printMultiSiteDebug'] = "false";
                $line['multiSiteErrorThreshold'] = '5';
                $line['maxHoursWithoutSwitch'] = '48';
                $line['deleteBatchFiles'] = "";
                $this->writeConfigPaymentSettings($line,$handle);
                fwrite($handle, "timeout =  500".  PHP_EOL);
                fwrite($handle, "reportGroup = " . $report_group .  PHP_EOL);
            }

            fclose($handle);
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
	    public function writeConfigPaymentSettings( $line,$handle ) {
            foreach ( $line as $keys => $values ) {
                fwrite( $handle, $keys . '' );
                if ( is_array( $values ) ) {
                    foreach ( $values as $key2 => $value2 ) {
                        fwrite( $handle,"['" . $key2 . "'] = " . $value2 .  PHP_EOL );
                    }
                } else {
                    fwrite( $handle,' = ' . $values );
                    fwrite( $handle, PHP_EOL );
                }
            }
        }
	    public function admin_options(){
            echo '<h3>'.__('Vantiv Payment Gateway', 'vantiv').'</h3>';
            echo '<p>'.__('Vantiv is most popular payment gateway for online shopping').'</p>';
            echo '<table class="form-table">';
                // Generate the HTML For the settings form.
            $this->generate_settings_html();
            echo '</table>';

        }

        /**
        *  There are no payment fields for vantiv, but we want to show the description if set.
        **/
        public function payment_fields(){
            global $wp;
            $description          = $this->get_description();
             $description          = ! empty( $description ) ? $description : '';


            if ( $this->testmode ) {
                /* translators: link to vantiv testing page */
                $description .= '<br/>' . '(TEST MODE ENABLED. In test mode, you can use the card number 4457010000000009 with any CVC and a valid expiration date)';
            }

            $description = trim( $description );

            echo  wpautop( wp_kses_post( $description ) );

            //if($this->description) echo wpautop(wptexturize($this->description));

            $this->elements_form();
        }

        /**
        * Renders the Vantiv elements form.
        *
        * @since 4.0.0
        * @version 4.0.0
        **/
        public function elements_form() {
        ?>
            <fieldset id="wc-<?php echo esc_attr( $this->id ); ?>-cc-form" class="wc-credit-card-form wc-payment-form" style="background:transparent;">
                <?php do_action( 'woocommerce_credit_card_form_start', $this->id ); ?>

                <div class="form-row form-row-wide">
                    <label for="cc"><?php esc_html_e( 'Card Number', 'woocommerce-gateway-vantiv' ); ?> <span class="required">*</span></label>
                    <input id="cc" type="tel" style="width: 100%;"  name="ccnumber" pattern="\d{4} \d{4} \d{4} \d{4}" class="masked" title="16-digit number" maxlength="19" placeholder="XXXX XXXX XXXX XXXX">
                </div>

                <div class="form-row form-row-first">
                    <label for="expiration"><?php esc_html_e( 'Expiry Date', 'woocommerce-gateway-vantiv' ); ?> <span class="required">*</span></label>
                    <input id="expiration" style="width: 100%;" class="masked" maxlength="5" type="tel" autocomplete="off" autocorrect="off" name="exp-date" placeholder="MM/YY" value=""  >
                </div>

                <div class="form-row form-row-last">
                    <label for="vantiv-cvc-element"><?php esc_html_e( 'Card Code (CVC)', 'woocommerce-gateway-vantiv' ); ?> <span class="required">*</span></label>
                    <input id="vantiv-cvc-element" style="width: 100%;" maxlength="3" type="password" autocomplete="off"  autocorrect="off" spellcheck="false" name="cvc" inputmode="numeric"  placeholder="CVC" value="">
                </div>
                <div class="clear"></div>
                <!-- Used to display form errors -->
                <div class="vantiv-source-errors" role="alert"></div>
                <br />
                <?php do_action( 'woocommerce_credit_card_form_end', $this->id ); ?>
                <div class="clear"></div>
            </fieldset>
        <?php
      }

        /**
         * Receipt Page
         **/
        function receipt_page($order){
            echo '<p>'.__('Thank you for your order, please click the button below to pay with Vantiv.', 'vantiv').'</p>';
            echo $this->generate_vantiv_form($order);
        }
        /**
         * Generate vantiv button link
         **/
        public function generate_vantiv_form($order_id){

            global $woocommerce;

            $order = new WC_Order($order_id);
            $txnid = $order_id.'_'.date("ymds");

            $redirect_url = ($this->redirect_page_id=="" || $this->redirect_page_id==0)?get_site_url() . "/":get_permalink($this->redirect_page_id);

            $productinfo = "Order $order_id";

            $str = "$this->merchant_id|$txnid|$order->order_total|$productinfo|$order->billing_first_name|$order->billing_email|||||||||||$this->salt";
            $hash = hash('sha512', $str);

            $vantiv_args = array(
                    'key' => $this->merchant_id,
                    'txnid' => $txnid,
                    'amount' => $order->order_total,
                    'productinfo' => $productinfo,
                    'firstname' => $order->billing_first_name,
                    'lastname' => $order->billing_last_name,
                    'address1' => $order->billing_address_1,
                    'address2' => $order->billing_address_2,
                    'city' => $order->billing_city,
                    'state' => $order->billing_state,
                    'country' => $order->billing_country,
                    'zipcode' => $order->billing_zip,
                    'email' => $order->billing_email,
                    'phone' => $order->billing_phone,
                    'surl' => $redirect_url,
                    'furl' => $redirect_url,
                    'curl' => $redirect_url,
                    'hash' => $hash,
                    'pg' => 'NB'
            );

            $vantiv_args_array = array();

            foreach($vantiv_args as $key => $value){
                $vantiv_args_array[] = "<input type='hidden' name='$key' value='$value'/>";
            }
            return '<form action="'.$this->liveurl.'" method="post" id="vantiv_payment_form">
                ' . implode('', $vantiv_args_array) . '
                    <input type="submit" class="button-alt" id="submit_vantiv_payment_form" value="'.__('Pay via Vantiv', 'vantiv').'" /> <a class="button cancel" href="'.$order->get_cancel_order_url().'">'.__('Cancel order &amp; restore cart', 'vantiv').'</a>
                    <script type="text/javascript">
                        jQuery(function(){
                            jQuery("body").block(
                            {
                                message: "<img src=\"'.$woocommerce->plugin_url().'/assets/images/ajax-loader.gif\" alt=\"Redirecting…\" style=\"float:left; margin-right: 10px;\" />'.__('Thank you for your order. We are now redirecting you to Payment Gateway to make payment.', 'vantiv').'",
                                overlayCSS:
                                {
                                    background: "#fff",
                                    opacity: 0.6
                                },
                                css: {
                                    padding:        20,
                                    textAlign:      "center",
                                    color:          "#555",
                                    border:         "3px solid #aaa",
                                    backgroundColor:"#fff",
                                    cursor:         "wait",
                                    lineHeight:"32px"
                                }
                            });
                            jQuery("#submit_vantiv_payment_form").click();
                        });
                    </script>
            </form>';


        }

        /**
         * Process the payment and return the result
        **/
        function process_payment( $order_id ) {
            global $woocommerce;
            $order = new WC_Order($order_id);

            $sale_info = array(
                'orderId'       => $order_id,
                'id'            => $order->get_user_id(),
                'amount'        => str_replace( array('.', '' ), '', $order->order_total ),
                'orderSource'   => 'ecommerce',
                'billToAddress' => array(
                    'name'         => $order->billing_first_name . ' ' . $order->billing_last_name,
                    'addressLine1' => $order->billing_address_1,
                    'city'         => $order->billing_city,
                    'state'        => $order->billing_state,
                    'zip'          => $order->billing_postcode,
                    'country'      => $order->billing_country,
                ),
                'card'          => array(
                    'number'            => str_replace( array(' ', '' ), '', $_POST['ccnumber'] ),
                    'expDate'           => str_replace( array( '/', ' '), '', $_POST['exp-date'] ),
                    'cardValidationNum' => ( isset( $_POST['cvc'] ) ) ? $_POST['cvc'] : '',
                    'type'              => 'MC',
                )
            );

            $initialize = new CnpOnlineRequest();
            $saleResponse = $initialize->saleRequest($sale_info);

            /*var_dump(XmlParser::getNode( $saleResponse,'message' ) );
            die(); */

            if ( XmlParser::getNode( $saleResponse,'message' ) != 'Approved' )
                throw new \Exception( 'CnpSaleTransaction does not get the right response' );

            // 1 or 4 means the transaction was a success
            if( XmlParser::getNode($saleResponse,'message') == 'Approved' ) {
                // Payment successful
                $order->add_order_note( __( 'Vantiv complete payment.', 'woo-gateway-vantiv' ) );

                // paid order marked
                $order->payment_complete();
                // this is important part for empty cart
                $woocommerce->cart->empty_cart();
                // Redirect to thank you page
                return array(
                    'result'   => 'success',
                    'redirect' => $this->get_return_url( $order ),
                );
            } else {
                //transiction fail
                wc_add_notice( XmlParser::getNode($saleResponse,'message'), 'error' );
                $order->add_order_note( 'Error: '. XmlParser::getNode($saleResponse,'message') );
            }
        }

        public function validate_fields(){
            $card_number = str_replace( array(' ', '' ), '', $_POST['ccnumber'] );
            $card_exp_date = str_replace( array( '/', ' '), '', $_POST['exp-date'] );
            $card_cvc = $_POST['cvc'];
            if ( empty( $card_number ) ) {
                wc_add_notice(  'Card Number is required!', 'error' );
                return false;
            }
            if ( empty( $card_exp_date ) ) {
                wc_add_notice(  'Card Expiry Date is required!', 'error' );
                return false;
            }
            if ( empty( $card_cvc ) ) {
                wc_add_notice(  'Card Code (CVC) is required!', 'error' );
                return false;
            }
            if ( strlen( $card_number ) < 16 ) {
                wc_add_notice(  'The card number is incomplete!', 'error' );
                return false;
            }
            if ( strlen( $card_exp_date ) < 4 ) {
                wc_add_notice(  'The card`s expiration date is incomplete!', 'error' );
                return false;
            }
            if ( strlen( $card_cvc ) < 3 ) {
                wc_add_notice(  'The card`s security code is incomplete!', 'error' );
                return false;
            }
            return true;

        }

        /**
        * Check for valid vantiv server callback
        **/
        function check_vantiv_response(){
            global $woocommerce;
            if(isset($_REQUEST['txnid']) && isset($_REQUEST['mihpayid'])){
                $order_id_time = $_REQUEST['txnid'];
                $order_id = explode('_', $_REQUEST['txnid']);
                $order_id = (int)$order_id[0];
                if($order_id != ''){
                    try{
                        $order = new WC_Order($order_id);
                        $merchant_id = $_REQUEST['key'];
                        $amount = $_REQUEST['Amount'];
                        $hash = $_REQUEST['hash'];

                        $status = $_REQUEST['status'];
                        $productinfo = "Order $order_id";
                        echo $hash;
                        echo "{$this->salt}|$status|||||||||||{$order->billing_email}|{$order->billing_first_name}|$productinfo|{$order->order_total}|$order_id_time|{$this->merchant_id}";
                        $checkhash = hash('sha512', "{$this->salt}|$status|||||||||||{$order->billing_email}|{$order->billing_first_name}|$productinfo|{$order->order_total}|$order_id_time|{$this->merchant_id}");
                        $transauthorised = false;
                        if($order->status !=='completed'){
                            if($hash == $checkhash) {

                                $status = strtolower($status);

                                if($status=="success"){
                                    $transauthorised = true;
                                    $this->msg['message'] = "Thank you for shopping with us. Your account has been charged and your transaction is successful. We will be shipping your order to you soon.";
                                    $this->msg['class'] = 'woocommerce_message';
                                    if($order->status == 'processing'){

                                    }else{
                                        $order->payment_complete();
                                        $order->add_order_note('Vantiv payment successful<br/>Unnique Id from Vantiv: '.$_REQUEST['mihpayid']);
                                        $order->add_order_note($this->msg['message']);
                                        $woocommerce->cart->empty_cart();
                                    }
                                }else if($status=="pending"){
                                    $this->msg['message'] = "Thank you for shopping with us. Right now your payment staus is pending, We will keep you posted regarding the status of your order through e-mail";
                                    $this->msg['class'] = 'woocommerce_message woocommerce_message_info';
                                    $order->add_order_note('Vantiv payment status is pending<br/>Unnique Id from Vantiv: '.$_REQUEST['mihpayid']);
                                    $order->add_order_note($this->msg['message']);
                                    $order->update_status('on-hold');
                                    $woocommerce->cart->empty_cart();
                                } else{
                                    $this->msg['class'] = 'woocommerce_error';
                                    $this->msg['message'] = "Thank you for shopping with us. However, the transaction has been declined.";
                                    $order->add_order_note('Transaction Declined: '.$_REQUEST['Error']);

                                    //Here you need to put in the routines for a failed
                                    //transaction such as sending an email to customer
                                    //setting database status etc etc
                                }
                            }else{
                                $this->msg['class'] = 'woocommerce-error';
                                $this->msg['message'] = "Security Error. Illegal access detected";


                                //Here you need to simply ignore this and dont need
                                //to perform any operation in this condition
                            }
                            if($transauthorised==false){
                                $order->update_status('failed');
                                $order->add_order_note('Failed');
                                $order->add_order_note($this->msg['message']);

                            }
                            add_action('the_content', array(&$this, 'showMessage'));
                        }
                    }catch(Exception $e){
                        // $errorOccurred = true;
                        $this->msg['class'] = 'woocommerce-error';
                        $this->msg['message'] = "Error";

                    }

                }

            }

        }

        public function showMessage($content){
            return '<div class="box '.$this->msg['class'].'-box">'.$this->msg['message'].'</div>'.$content;
        }
         // get all pages
        public function get_pages($title = false, $indent = true) {
            $wp_pages = get_pages('sort_column=menu_order');
            $page_list = array();
            if ($title) $page_list[] = $title;
            foreach ($wp_pages as $page) {
                $prefix = '';
                // show indented child pages?
                if ($indent) {
                    $has_parent = $page->post_parent;
                    while($has_parent) {
                        $prefix .=  ' - ';
                        $next_page = get_page($has_parent);
                        $has_parent = $next_page->post_parent;
                    }
                }
                // add to page list array array
                $page_list[$page->ID] = $prefix . $page->post_title;
            }
            return $page_list;
        }
    }
}
