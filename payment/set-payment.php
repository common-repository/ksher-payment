<?php
	// SIGN PROCESS.
	function ksher_sign_process( $privatekey_content, $data) {
		$encoded_sign = "";
		$message = "";
		ksort($data);
		foreach ($data as $key => $value) $message .= $key."=".$value;
		$message = mb_convert_encoding($message, "UTF-8");
		$private_key = openssl_get_privatekey($privatekey_content);
		openssl_sign($message, $encoded_sign, $private_key );
		openssl_free_key($private_key);
		$encoded_sign = bin2hex($encoded_sign);
		return $encoded_sign;
	}

	if (get_option('ksher_connection') == 'success') {
		// SET PAYMENT IN WOOCOMMARCE.
		add_filter( 'woocommerce_payment_gateways', 'ksher_add_gateway_class' );
		function ksher_add_gateway_class( $gateways )
		{
			$gateways[] = 'WC_Ksher_Gateway';
			return $gateways;
		}

		add_action( 'plugins_loaded', 'ksher_init_gateway_class' );
		function ksher_init_gateway_class()
		{

			class WC_Ksher_Gateway extends WC_Payment_Gateway
			{
				public function __construct()
				{
					$this->id = 'ksher'; 
					$this->icon = ''; 
					$this->has_fields = true; 
					$this->method_title = 'Ksher Payment';
					$this->method_description = 'Accept payments Ksher payment gateway.'; 
				
					$this->pubkey = <<<EOD
-----BEGIN PUBLIC KEY-----
MFwwDQYJKoZIhvcNAQEBBQADSwAwSAJBAL7955OCuN4I8eYNL/mixZWIXIgCvIVE
ivlxqdpiHPcOLdQ2RPSx/pORpsUu/E9wz0mYS2PY7hNc2mBgBOQT+wUCAwEAAQ==
-----END PUBLIC KEY-----
EOD;

					// gateways can support subscriptions, refunds, saved payment methods,
					// but in this tutorial we begin with simple payments.
					$this->supports = array(
						'products',
						'refunds',
					);

					// Method with all the options fieldsใ
					$this->init_form_fields();
				
					// Load the settings.
					$this->init_settings();
					$this->title = $this->get_option( 'title' );
					$this->description = $this->get_option( 'description' );
					$this->enabled = $this->get_option( 'enabled' );
					$this->testmode = 'yes' === $this->get_option( 'testmode' );
					$this->wechat = $this->get_option( 'wechat' );
					$this->alipay = $this->get_option( 'alipay' );
					$this->truemoney = $this->get_option( 'truemoney' );
					$this->promptpay = $this->get_option( 'promptpay' );
					$this->linepay = $this->get_option( 'linepay' );
					$this->airpay = $this->get_option( 'airpay' );
					$this->atome = $this->get_option( 'atome' );
					$this->kplus = $this->get_option( 'kplus' );	
					//$this->ktbcard = $this->get_option( 'ktbcard' );

					$this->ktccard = $this->get_option( 'ktccard' );

					$this->ktc_instal = $this->get_option( 'ktc_instal' );
          $this->instal_fee_payer = $this->get_option( 'instal_fee_payer' );
					$this->ktc_installment_period = $this->get_option( 'ktc_installment_period');

					$this->savecard = $this->get_option( 'savecard' );

					$this->scb_easy = $this->get_option( 'scb_easy' );
					$this->bbl_deeplink = $this->get_option( 'bbl_deeplink' );

					$this->kbank_instal = $this->get_option( 'kbank_instal' );
					$this->kbank_instal_fee_payer = $this->get_option( 'kbank_instal_fee_payer' );
					$this->kbank_installment_period = $this->get_option( 'kbank_installment_period' );

					$this->baybank_deeplink = $this->get_option( 'baybank_deeplink' );

					

					add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
				}

				public function init_form_fields()
				{
					$this->form_fields = array(
						'enabled' => array(
							'title'       => 'Enable/Disable',
							'label'       => 'Enable Ksher Gateway',
							'type'        => 'checkbox',
							'description' => '',
							'default'     => 'no'
						),
						'title' => array(
							'title'       => 'Title',
							'type'        => 'text',
							'description' => 'This controls the title which the user sees during checkout.',
							'default'     => 'Ksher Payment',
							'desc_tip'    => true,
						),
						'description' => array(
							'title'       => 'Description',
							'type'        => 'textarea',
							'description' => 'This controls the description which the user sees during checkout.',
							'default'     => '',
						),
						'atome' => array(
							'title'       => 'Atome',
							'label'       => ' ',
							'type'        => 'checkbox',
							'description' => '',
							'default'     => 'no'
						),
						'wechat' => array(
							'title'       => 'Wechat',
							'label'       => ' ',
							'type'        => 'checkbox',
							'description' => '',
							'default'     => 'no'
						),
						'alipay' => array(
							'title'       => 'Ali Pay',
							'label'       => ' ',
							'type'        => 'checkbox',
							'description' => '',
							'default'     => 'no'
						),
						'truemoney' => array(
							'title'       => 'True Money',
							'label'       => ' ',
							'type'        => 'checkbox',
							'description' => '',
							'default'     => 'no'
						),
						'promptpay' => array(
							'title'       => 'Promptpay',
							'label'       => ' ',
							'type'        => 'checkbox',
							'description' => '',
							'default'     => 'no'
						),
						'linepay' => array(
							'title'       => 'LinePay',
							'label'       => ' ',
							'type'        => 'checkbox',
							'description' => '',
							'default'     => 'no'
						),
						'airpay' => array(
							'title'       => 'ShopeePay',
							'label'       => ' ',
							'type'        => 'checkbox',
							'description' => '',
							'default'     => 'no'
						),
						'ktccard' => array(
							'title'       => 'Credit Card',
							'label'       => ' ',
							'type'        => 'checkbox',
							'description' => '',
							'default'     => 'no'
						),
						'savecard' => array(
							'title'       => 'Save Card',
							'label'       => 'Enable save card for credit card(KTB Card)',
							'type'        => 'checkbox',
							'description' => '',
							'default'     => 'yes',
						),
						'ktc_instal' => array(
							'title'       => 'KTC installment',
							'label'       => ' ',
							'type'        => 'checkbox',
							'description' => '',
							'default'     => 'no',
						),
						'ktc_installment_period' => array(
							'title'       => 'KTC Installment Period(months) choices for customer',
							'label'       => '',
							'type'        => 'multiselect',
							'description' => 'Multiple select period by holding Control + Click',
							'default'     => array(2,3,4,5,6,7,8,9,10),
							'options' => array(
          			3 => '3 Month',
								4 => '4 Month',
								5 => '5 Month',
								6 => '6 Month',
								7 => '7 Month',
								8 => '8 Month',
								9 => '9 Month',
								10 => '10 Month',
     					)
						),
						'instal_fee_payer' => array(
							'title'       => 'KTC installment Fee Payer',
							'label'       => 'The merchant bears the installment fee',
							'type'        => 'checkbox',
							'description' => '',
							'default'     => 'no',
						),
						'kbank_instal' => array(
							'title'       => 'KBank Installment',
							'label'       => ' ',
							'type'        => 'checkbox',
							'description' => '',
							'default'     => 'no'
						),
						'kbank_installment_period' => array(
							'title'       => 'KBank Installment Period(months)choices for customer',
							'label'       => ' ',
							'type'        => 'multiselect',
							'description' => 'Multiple select period by holding Control + Click',
							'default'     => array(2,3,4,5,6,7,8,9,10),
							'options' => array(
          			3 => '3 Month',
								4 => '4 Month',
								5 => '5 Month',
								6 => '6 Month',
								7 => '7 Month',
								8 => '8 Month',
								9 => '9 Month',
								10 => '10 Month',
     					)
						),
						'kbank_instal_fee_payer' => array(
							'title'       => 'KBank installment Fee Payer',
							'label'       => 'The merchant bears the installment fee',
							'type'        => 'checkbox',
							'description' => '',
							'default'     => 'no',
						),
						'scb_easy' => array(
							'title'       => 'SCB Easy (Mobile Only)',
							'label'       => ' ',
							'type'        => 'checkbox',
							'description' => '',
							'default'     => 'no'
						),
						'bbl_deeplink' => array(
							'title'       => 'BBL (Mobile Only)',
							'label'       => ' ',
							'type'        => 'checkbox',
							'description' => '',
							'default'     => 'no'
						),
						'baybank_deeplink' => array(
							'title'       => 'Krungsri (Mobile Only)',
							'label'       => ' ',
							'type'        => 'checkbox',
							'description' => '',
							'default'     => 'no'
						),
						'kplus' => array(
							'title'       => 'Kplus (Mobile Only)',
							'label'       => ' ',
							'type'        => 'checkbox',
							'description' => '',
							'default'     => 'no'
						),
					);
				}

				// PAYMENT FIELDS.
				public function payment_fields() 
				{
					echo wpautop( 
								wp_kses_post( ($this->description ? $this->description : '') . 
									'<div class="ksher-payment-logo" >' .
									( $this->wechat == 'yes' ? '<img src="'. plugin_dir_url( __FILE__ ) . '../assets/img/ksher-wechat.png">' : '' ) .
									( $this->alipay == 'yes' ? '<img src="'. plugin_dir_url( __FILE__ ) . '../assets/img/ksher-alipay.png">' : '' ) .
									( $this->truemoney == 'yes' ? '<img src="'. plugin_dir_url( __FILE__ ) . '../assets/img/ksher-true.png">' : '' ) .
									( $this->promptpay == 'yes' ? '<img src="'. plugin_dir_url( __FILE__ ) . '../assets/img/ksher-promptpay.png">' : '' ) .
									( $this->linepay == 'yes' ? '<img src="'. plugin_dir_url( __FILE__ ) . '../assets/img/ksher-line.png">' : '' ) .
									( $this->airpay == 'yes' ? '<img src="'. plugin_dir_url( __FILE__ ) . '../assets/img/ksher-shopeepay.png">' : '' ) .
									( $this->ktccard == 'yes' ? '<img src="'. plugin_dir_url( __FILE__ ) . '../assets/img/ksher-credit.png">' : '' ) .
									( $this->ktc_instal == 'yes' ? '<img src="'. plugin_dir_url( __FILE__ ) . '../assets/img/ksher-ktc.png">' : '' ) .
									(($this->kbank_instal == 'yes' ||  $this->kplus == 'yes') ? '<img src="'. plugin_dir_url( __FILE__ ) . '../assets/img/ksher-kbank.png">' : '' ) .
									( $this->scb_easy == 'yes' ? '<img src="'. plugin_dir_url( __FILE__ ) . '../assets/img/ksher-scb.png">' : '' ) .
									( $this->bbl_deeplink == 'yes' ? '<img src="'. plugin_dir_url( __FILE__ ) . '../assets/img/ksher-bbl.png">' : '' ) .
									( $this->baybank_deeplink == 'yes' ? '<img src="'. plugin_dir_url( __FILE__ ) . '../assets/img/ksher-krungsri.png">' : '' ) .
									( $this->atome == 'yes' ? '<img src="'. plugin_dir_url( __FILE__ ) . '../assets/img/ksher-atome.png">' : '' ) .

									'</div>' 
								)
							);
				}

				public function ksher_paramData( $data ) {
					ksort($data);
					$message = '';
					foreach ($data as $key => $value) {
							$message .= $key . "=" . $value;
					}
					$message = mb_convert_encoding($message, "UTF-8");
					return $message;
				}

				public function ksher_verify_sign( $data, $sign ) {
					$sign = pack("H*",$sign);
					$message = $this->ksher_paramData( $data );
					$res = openssl_get_publickey($this->pubkey);
					$result = openssl_verify($message, $sign, $res, OPENSSL_ALGO_MD5);
					openssl_free_key($res);
					return $result;
				}

				// PAYMENT PROCESS.
				public function process_payment( $order_id )
				{
					global $woocommerce;
					$order = wc_get_order( $order_id );
					$data_order = $order->get_data();

					//$key_order = $order->order_key;
					$checkout = wc_get_checkout_url();
					$refer_url = home_url();
					$timestamp = current_time('YmdHis');
					$nonce_str = bin2hex(random_bytes(16));
					$mch_notify_url = home_url() . '/wp-json/ksher/v1/endpoint';

					$items = $order->get_items();
					$product = '';
					$count_product = 0;
					foreach ( $items as $item ) {
						if ($count_product !== 0) {
							$product .= ',' . $item->get_name();
						} else {
							$product .= $item->get_name();
						}
						$count_product++;
					}

					if (strlen($product) > 50) {
						$product = mb_substr($product, 0, 50);
					}

					$redirect_url = $order->get_checkout_order_received_url();
					$total = (int)(round( $order->get_total(), 2 ) * 100);

					$device = 'PC';
					if (wp_is_mobile()) {
						$device = 'H5';
					} 

					$channel_list = array();
					$savecard = false;
					if ($this->wechat == 'yes' ) {
						array_push($channel_list, 'wechat');
					}
					if ($this->alipay == 'yes') {
						array_push($channel_list, 'alipay');
					}
					if ($this->truemoney == 'yes') {
						array_push($channel_list, 'truemoney');
					}
					if ($this->promptpay == 'yes') {
						array_push($channel_list, 'promptpay');
					}
					if ($this->linepay == 'yes') {
						array_push($channel_list, 'linepay');
					}
					if ($this->airpay == 'yes') {
						array_push($channel_list, 'airpay');
					}
					if ($this->atome == 'yes') {
						array_push($channel_list, 'atome');
					}
					if ($this->kplus == 'yes') {
						array_push($channel_list, 'kplus');
					}
					// if ($this->ktbcard == 'yes') {
					// 	array_push($channel_list, 'ktbcard');
					// }
					if ($this->ktccard == 'yes') {
						array_push($channel_list, 'card');
					}
					if ($this->ktc_instal == 'yes') {
						if ($total >= 200000) {
							array_push($channel_list, 'ktc_instal');
						}
					}
					if ($this->savecard == 'yes') {
						$savecard = true;
					}
					if ($this->scb_easy == 'yes') {
						array_push($channel_list, 'scb_easy');
					}
					if ($this->bbl_deeplink == 'yes') {
						array_push($channel_list, 'bbl_deeplink');
					}
					if ($this->kbank_instal == 'yes') {
						array_push($channel_list, 'kbank_instal');
					}
					if ($this->baybank_deeplink == 'yes') {
						array_push($channel_list, 'baybank_deeplink');
					}
					
					$channel_list = implode(',', $channel_list);
					
					$mch_code = '';
					preg_match_all('!\d+!', get_option('ksher_app_id'), $mch_code);
					$mch_order_no =	$data_order['id'] . '-' . $timestamp;

					if (is_user_logged_in() && strpos($channel_list, 'ktbcard') && $savecard) {
						$member_id = strval( 'ks-wp-' . $order->get_user_id());
						$data = array(
							'appid' => get_option('ksher_app_id'),
							'channel_list' => $channel_list,
							'device' => $device,
							'fee_type' => $data_order['currency'],
							'mch_code' => $data_order['id'],
							'mch_order_no' => $mch_order_no,
							'mch_redirect_url' => $redirect_url,
							'mch_redirect_url_fail' => $checkout,
							'nonce_str' => $nonce_str,
							'product_name' => $product,
							'refer_url' => $refer_url,
							'time_stamp' =>	$timestamp,
							'total_fee' => $total,
							'mch_notify_url' => $mch_notify_url,
							'member_id' => $member_id,
							'lang' => 'en',
						);
					} else {
						$data = array(
							'appid' => get_option('ksher_app_id'),
							'channel_list' => $channel_list,
							'device' => $device,
							'fee_type' => $data_order['currency'],
							'mch_code' => $data_order['id'],
							'mch_order_no' => $mch_order_no,
							'mch_redirect_url' => $redirect_url,
							'mch_redirect_url_fail' => $checkout,
							'nonce_str' => $nonce_str,
							'product_name' => $product,
							'refer_url' => $refer_url,
							'time_stamp' =>	$timestamp,
							'total_fee' => $total,
							'mch_notify_url' => $mch_notify_url,
							'lang' => 'en',
						);
					}

					if ( get_option('ksher_color') && is_string(get_option('ksher_color'))) {
						$data['color'] = get_option('ksher_color');
					}


					$instal_fee_payer_channel = array();
					// if ($this->instal_fee_payer == 'yes') {
					// 	$data['instal_fee_payer'] = 2;
					// } 
					// if ($this->instal_fee_payer == 'no') {
					// 	$data['instal_fee_payer'] = 1;
					// }
					if ($this->kbank_instal_fee_payer == 'yes') {
						array_push($instal_fee_payer_channel, 'kbank_instal' ); 
					}
					if ($this->instal_fee_payer == 'yes') {
						array_push($instal_fee_payer_channel, 'ktc_instal' ); 
					}

					if (count($instal_fee_payer_channel)) {
						$data['instal_fee_payer_merchant_channel_list'] = implode(',',$instal_fee_payer_channel);
					}

					$channel_instal_times_list = new stdClass();

					if ($this->ktc_instal == 'yes' && is_array($this->ktc_installment_period) && count($this->ktc_installment_period)) {
						$channel_instal_times_list->ktc_instal = array_map('intval', $this->ktc_installment_period);
					}

					if ($this->kbank_instal == 'yes' && is_array($this->kbank_installment_period) && count($this->kbank_installment_period)) {
						$channel_instal_times_list->kbank_instal = array_map('intval', $this->kbank_installment_period);
					}

					if ($channel_instal_times_list->kbank_instal || $channel_instal_times_list->ktc_instal ) {
						$data['channel_instal_times_list'] = json_encode($channel_instal_times_list);
					}

					if ( get_option('ksher_logo') && is_string(get_option('ksher_logo'))) {
						$data['logo'] = get_option('ksher_logo');
					}

					if ( get_post_meta($data_order['id'], 'mch_order_no') == '' ||  get_post_meta($data_order['id'], 'mch_order_no') == null) {
						add_post_meta($data_order['id'], 'mch_order_no', $mch_order_no, true);
					} else {
						update_post_meta($data_order['id'], 'mch_order_no', $mch_order_no);
					}


					$privatekey_content = file_get_contents( get_option('ksher_private_key_file') );
					$encoded_sign = ksher_sign_process( $privatekey_content, $data); 
					$data['sign'] = $encoded_sign;

					$response = wp_remote_post('https://gateway.ksher.com/api/gateway_pay', array(
							'headers' => array('Content-Type' => 'application/x-www-form-urlencoded'),
							'body' => $data,
						)
					);

					if ( $response && $response['body']) {
						$body = json_decode($response['body'], true);
						if ( $body['code'] == 0 ) {
							$verify = false;
							$verify = $this->ksher_verify_sign( $body['data'], $body['sign'] );

							if ($verify) {
								//  $check = json_encode($data['channel_instal_times_list'], true);
								// error_log('check ' . print_r( $check, true));
								// wc_add_notice( $data['instal_fee_payer_merchant_channel_list'], 'error'  );

								return array(
									'result' => 'success',
									'redirect' => $body['data']['pay_content']
								);
							} else {
								wc_add_notice( 'error' , 'success' );
							}
						} else if ($body['code'] == 1) {
							wc_add_notice( 'Order is Paid', 'error' );
						} else {
							wc_add_notice( 'Order Error ' . $body['msg'] . $member_id , 'error' );
						}
					} else {
						wc_add_notice(  'Connection error.', 'error' );
						return;
					}
				}

				public function process_refund( $order_id, $amount = null, $reason = '' ) 
				{
					$order = wc_get_order( $order_id );
					$data_order = $order->get_data();
					$timestamp = current_time('timestamp');
					$nonce_str = bin2hex(random_bytes(16));
					$mch_order_no = get_post_meta($order_id, 'mch_order_no', true);

					$query_order = array(
						'appid' =>  get_option('ksher_app_id'),
						'mch_order_no' => $mch_order_no ,
						'nonce_str' => $nonce_str,
						'time_stamp' => $timestamp,
					);
		
					$order_privatekey_content = file_get_contents( get_option('ksher_private_key_file') );
					$order_encoded_sign = ksher_sign_process( $order_privatekey_content, $query_order); 
					$query_order['sign'] = $order_encoded_sign;
		
					$order_query = wp_remote_post('https://gateway.ksher.com/api/gateway_order_query', array(
							'headers' => array('Content-Type' => 'application/x-www-form-urlencoded'),
							'body' => $query_order,
						)
					);
					
					if ($order_query) {
						$order_body = json_decode($order_query['body'], true);
						if ($order_body['code'] == 0) {
							$get_channel = $order_body['data']['channel'];
							$ksher_order_no = $order_body['data']['ksher_order_no'];
							$refund_total = (int)(round( $amount, 2 ) * 100);
							$total = (int)(round( $order->get_total(), 2 ) * 100);
							$nonce_str = bin2hex(random_bytes(16));

							$data = array(
								'appid' => get_option('ksher_app_id'),
								'channel_order_no' => $get_channel,
								'ksher_order_no' => $ksher_order_no,
								'fee_type' => $data_order['currency'],
								'mch_order_no' => $mch_order_no,
								'refund_fee' => $refund_total,
								'mch_refund_no' => 'REFUND' . $data_order['id'],
								'nonce_str' => $nonce_str,
								'time_stamp' => $timestamp,
								'total_fee' => $total,
							);

							$privatekey_content = file_get_contents( get_option('ksher_private_key_file') );
							$encoded_sign = ksher_sign_process( $privatekey_content, $data); 
							$data['sign'] = $encoded_sign;
							
							$refund_response = wp_remote_post('http://api.mch.ksher.net/KsherPay/order_refund', array(
									'headers' => array('Content-Type' => 'application/x-www-form-urlencoded'),
									'body' => $data,
								)
							);

							if ($refund_response) {
								$body = json_decode($refund_response['body'], true);
								if ($body['data']['result'] == 'SUCCESS') {
									$order->add_order_note( 'Your order is refund! by ksher (by id:' . $body['data']['ksher_order_no']. ') Amount: ' . $body['data']['fee_type'] . number_format((float)round( $body['data']['refund_fee']/100, 2), 2, '.', ''), false );
									return true;
								} else {
									return new WP_Error( 'broke', esc_html( 'error '. $body['data']['err_msg'], 'ksher' ) );
								}
							} else {
								return new WP_Error( 'broke', esc_html( 'No Response', 'ksher' ) );
							}
						}
					} else {
						return new WP_Error( 'broke', esc_html( 'No Order in Ksher Transation', 'ksher' ) );
					}
				}
			}
		}
	}
?>