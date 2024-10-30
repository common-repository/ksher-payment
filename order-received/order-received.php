<?php
// CHECK PAYMENT IS COMPLETE.

	function ksher_paramData( $data ) {
		ksort($data);
		$message = '';
		foreach ($data as $key => $value) {
				$message .= $key . "=" . $value;
		}
		$message = mb_convert_encoding($message, "UTF-8");
		return $message;
	}

	function ksher_verify_sign( $data, $sign ) {
		$pubkey = <<<EOD
-----BEGIN PUBLIC KEY-----
MFwwDQYJKoZIhvcNAQEBBQADSwAwSAJBAL7955OCuN4I8eYNL/mixZWIXIgCvIVE
ivlxqdpiHPcOLdQ2RPSx/pORpsUu/E9wz0mYS2PY7hNc2mBgBOQT+wUCAwEAAQ==
-----END PUBLIC KEY-----
EOD;
		$sign = pack("H*",$sign);
		$message = ksher_paramData( $data );
		$res = openssl_get_publickey($pubkey);
		$result = openssl_verify($message, $sign, $res, OPENSSL_ALGO_MD5);
		openssl_free_key($res);
		return $result;
	}

	add_action('woocommerce_thankyou', 'ksher_check_payment_is_complete', 10, 1);
	function ksher_check_payment_is_complete( $order_id )
	{
		global $woocommerce;
		if ( isset($_GET['mch_order_no']) && $_GET['mch_order_no'] ) {

			//$make_order = explode('-', sanitize_text_field($_GET['mch_order_no']));

			$order = wc_get_order( $order_id );
			$status = $order->get_status();

			$timestamp = current_time('timestamp');
			$nonce_str = bin2hex(random_bytes(16));
		
			$data = array(
				'appid' =>  get_option('ksher_app_id'),
				'mch_order_no' =>  sanitize_text_field($_GET['mch_order_no']),
				'nonce_str' => $nonce_str,
				'time_stamp' => $timestamp,
			);

			$privatekey_content = file_get_contents( get_option('ksher_private_key_file') );
			$encoded_sign = ksher_sign_process( $privatekey_content, $data); 
			$data['sign'] = $encoded_sign;

			$response = wp_remote_post('https://gateway.ksher.com/api/gateway_order_query', array(
					'headers' => array('Content-Type' => 'application/x-www-form-urlencoded'),
					'body' => $data,
				)
			);

			if ($response) {
				$body = json_decode($response['body'], true);
				if ($body['code'] == 0) {
					if ( ksher_verify_sign( $body['data'], $body['sign']) ) {
						if ($body['data']['result'] == 'SUCCESS') {
							if ($status == 'pending' || $status == 'on-hold') {
								$order->add_order_note( 'Your order is paid! by ksher (by id:' . $body['data']['ksher_order_no']. ') with '. $body['data']['channel'] .' Thank you!', false );
								$order->payment_complete();
								wc_reduce_stock_levels( $make_order );
								$woocommerce->cart->empty_cart();
								//echo esc_html('Order is paid.', 'ksher');
							} else if ($status == 'processing' || $status == 'completed') {
								//echo esc_html('Order is already paid.', 'ksher');
							} else if ($status == 'refunded') {
								//echo esc_html('Order is refunded.', 'ksher');
							}
						} else {
							//echo esc_html('No paid', 'ksher');
						}
					} else {
						//echo esc_html('Error Verify', 'ksher') . $body['code'];
					}
				} else {
					//echo esc_html('Error Code', 'ksher') . $body['code'];
				}
			} else {
				//echo esc_html('No paid.', 'ksher');
			}

		}
	}

	add_action( 'rest_api_init', 'ks_action_hook');
	function ks_action_hook()
	{
		register_rest_route( 'ksher/v1', '/endpoint', array(
				'methods'  => 'POST',
				'callback' => 'ksher_order_check_status',
				'permission_callback' => '__return_true'
			) 
		);
	}

	function ksher_order_check_status( $request ) {
		$data = $request;
		$result = 'FAIL';
		$message = '';
		if ($data) {
			$req_body = $data->get_body();
			$body = json_decode($req_body, true);
			if ($body && $body['code'] == 0) {
				if ( $body['data']['mch_order_no'] ) {
					$order_no = explode('-', $body['data']['mch_order_no']);
					$order = wc_get_order( $order_no[0] );
					if ($order) {
						$status = $order->get_status();
						if ( ksher_verify_sign( $body['data'], $body['sign']) ) {
							if ($body['data']['result'] == 'SUCCESS') {
								$result = 'SUCCESS';
								$message = 'OK';
								if ($status == 'pending' || $status == 'on-hold') {
									$order->add_order_note( 'Your order is paid! by ksher (by id:' . $body['data']['ksher_order_no']. ') with '. $body['data']['channel'] .' Thank you!', false );
									$order->payment_complete();
									wc_reduce_stock_levels( $make_order );
									//$woocommerce->cart->empty_cart();
									//echo esc_html('Order is paid.', 'ksher');
								} else if ($status == 'processing' || $status == 'completed') {
									//echo esc_html('Order is already paid.', 'ksher');
								} else if ($status == 'refunded') {
									//echo esc_html('Order is refunded.', 'ksher');
								}
							} else {
								$result = 'FAIL';
								$message = 'not success';
							}
						} else {
							$result = 'FAIL';
							$message = 'verify fail';
							//echo esc_html('Error Verify', 'ksher') . $body['code'];
						}
					}
				}
			} else {
				$result = 'FAIL';
				$message = 'code != 0';
				//echo esc_html('Error Code', 'ksher') . $body['code'];
			}
		} else {
			$result = 'FAIL';
			$message = 'not have data';
			//echo esc_html('No paid.', 'ksher');
		}

		//error_log( print_r( $data, true ) );
		
		return [
			'result' => $result,
			'msg' => $message,
		];
	}

?>