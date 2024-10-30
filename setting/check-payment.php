<?php
add_action('admin_menu', 'ksher_register_my_custom_submenu_page');
function ksher_register_my_custom_submenu_page()
{
	add_submenu_page( 'woocommerce','ksher recheck payment', 'Ksher Recheck Payment', 'manage_options', 'ksher-recheck-payment', 'ksher_recheck_payment_submenu_page_callback' ); 
}

function ksher_recheck_payment_submenu_page_callback()
{
	echo '<h3>Ksher Recheck Payment</h3>';
	echo '<p>*ระบบจะเช็ค order status ที่เป็น pending peyment เท่านั้น<br>**ใช้ในการเช็ค order status ของฝั่ง ksher หาก order status ฝั่ง ksher เป็น success ระบบจะเปลี่ยน order status ใน woocommerce ให้ท่าน กรุณากดปุ่ม recheck</p>';
	echo '<button class="ksher-check-btn" id="ksher-check-payment">Recheck</button>';
	echo '<div class="ksher-wrapper"></div>';
}

add_action('wp_ajax_nopriv_ksher_check_payment', 'ksher_check_payment');
add_action('wp_ajax_ksher_check_payment', 'ksher_check_payment');
function ksher_check_payment()
{
	$output = '';
	$query = new WC_Order_Query( array(
		'status' => array('wc-pending'),
		'limit' => 30,
		)
	);

	$orders = $query->get_orders();
	if ( count($orders) !== 0) {
		$count = 0;
		foreach ($orders as $order) {
			$count++;
			$timestamp = current_time('timestamp');
			$nonce_str = bin2hex(random_bytes(16));
			$order_id = $order->get_id();

			$order_ksher_id = get_post_meta($order_id, 'mch_order_no', true) ? get_post_meta($order_id, 'mch_order_no', true) : $order_id ;
			$status = $order->get_status();
			$data = array(
				'appid' =>  get_option('ksher_app_id'),
				'mch_order_no' => $order_ksher_id,
				'nonce_str' => $nonce_str,
				'time_stamp' => $timestamp,
			);

			$privatekey_content = file_get_contents( get_option('ksher_private_key_file') );
			$encoded_sign = ksher_sign_process( $privatekey_content, $data); 
			$data['sign'] = $encoded_sign;

			$output .= 'Mch Order No: ' . $order_ksher_id . '<br>';
			$output .= 'Order ID: ' . $order_id . '<br>';

			$response = wp_remote_post('https://gateway.ksher.com/api/gateway_order_query', array(
					'headers' => array('Content-Type' => 'application/x-www-form-urlencoded'),
					'body' => $data,
				)
			);

			if ($response && array_key_exists('body', $response)) {
				$body = json_decode($response['body'], true);
				if ($body['code'] == 0) {
					if ( ksher_verify_sign( $body['data'], $body['sign']) ) {
							$output .= 'Status In Website: ' . $status . '<br>';
							$output .= 'Status In Ksher: ' . $body['data']['result']. '<br>';
						if ($body['data']['result'] == 'SUCCESS') { // check success
							if ($status == 'pending' || $status == 'cancelled' || $status == 'on-hold') {
								$order->add_order_note( 'Your order is paid! by ksher (by id:' . $body['data']['ksher_order_no']. ') with '. $body['data']['channel'] .' Thank you!', false );
								$order->payment_complete();
								$output .= 'Your order is paid! by ksher (by id:' . $body['data']['ksher_order_no']. ')<br>';
								$output .= 'Changed Status In Website to : Processing<br>';
							} else if ($status == 'processing' || $status == 'completed') {
								//echo esc_html('Order is already paid.', 'ksher');
							} else if ($status == 'refunded') {
								//echo esc_html('Order is refunded.', 'ksher');
							}
						} else {
							$output .= 'Order is Not Paid! by ksher (by id:' . $body['data']['ksher_order_no']. ')<br>';
						}
					} else {
						$output .= 'Not Ksher payment(Invalid Verify)<br>';
					}
				} else {
					$output .= 'Not Ksher payment(Code != 0)' . $body['code'] . '<br>';
				}
			} else {
				$output .= 'Not Ksher payment(No Body)<br>';
			}
			$output .= '------------------------------<br><br>' ;
		}
	} else {
		$output .= 'No Order in Pending Payment';
	}
	echo json_encode(array( 'data' => $output , 'query' => $orders) );
	wp_die();
}







add_filter('cron_schedules','ksher_cron_schedules');
function ksher_cron_schedules($schedules)
{
	if(!isset($schedules["5_min"])){
			$schedules["5_min"] = array(
					'interval' => 5*60,
					'display' => __('Ksher every 5 minutes'));
	}
	return $schedules;
}

function ksher_sync_deactivate() {
	wp_clear_scheduled_hook( 'ksher_payment_check_hook' );
}

add_action('init', function(){
	add_action( 'ksher_payment_check_hook', 'ksher_payment_check_cronjob' );
	register_deactivation_hook( __FILE__, 'ksher_sync_deactivate' );

	if (! wp_next_scheduled ( 'ksher_payment_check_hook' )) {
		wp_schedule_event( time(), '5_min', 'ksher_payment_check_hook' );
	}
});

// add_shortcode('ksher_payment_check','ksher_payment_check_cronjob');
function ksher_payment_check_cronjob()
{
	$output = '';
	$args = array(
		'status' => array('wc-pending'),
    'date_created' => '>' . ( time() - HOUR_IN_SECONDS ),
	);

	$orders = wc_get_orders( $args );

	if ( count($orders) !== 0) {
		$count = 0;
		foreach ($orders as $order) {
			$count++;
			$timestamp = current_time('timestamp');
			$nonce_str = bin2hex(random_bytes(16));
			$order_id = $order->get_id();

			$order_ksher_id = get_post_meta($order_id, 'mch_order_no', true) ? get_post_meta($order_id, 'mch_order_no', true) : $order_id ;
			$status = $order->get_status();
			$data = array(
				'appid' =>  get_option('ksher_app_id'),
				'mch_order_no' => $order_ksher_id,
				'nonce_str' => $nonce_str,
				'time_stamp' => $timestamp,
			);

			$privatekey_content = file_get_contents( get_option('ksher_private_key_file') );
			$encoded_sign = ksher_sign_process( $privatekey_content, $data); 
			$data['sign'] = $encoded_sign;

			$output .= 'Mch Order No: ' . $order_ksher_id . '<br>';
			$output .= 'Order ID: ' . $order_id . '<br>';

			$response = wp_remote_post('https://gateway.ksher.com/api/gateway_order_query', array(
					'headers' => array('Content-Type' => 'application/x-www-form-urlencoded'),
					'body' => $data,
				)
			);

			if ($response && array_key_exists('body', $response)) {
				$body = json_decode($response['body'], true);
				if ($body['code'] == 0) {
					if ( ksher_verify_sign( $body['data'], $body['sign']) ) {
							$output .= 'Status In Website: ' . $status . '<br>';
							$output .= 'Status In Ksher: ' . $body['data']['result']. '<br>';
						if ($body['data']['result'] == 'SUCCESS') { // check success
							if ($status == 'pending' || $status == 'cancelled' || $status == 'on-hold') {
								$order->add_order_note( 'Your order is paid! by ksher (by id:' . $body['data']['ksher_order_no']. ') with '. $body['data']['channel'] .' Thank you!', false );
								$order->payment_complete();
								$output .= 'Your order is paid! by ksher (by id:' . $body['data']['ksher_order_no']. ')<br>';
								$output .= 'Changed Status In Website to : Processing<br>';
							} else if ($status == 'processing' || $status == 'completed') {
								//echo esc_html('Order is already paid.', 'ksher');
							} else if ($status == 'refunded') {
								//echo esc_html('Order is refunded.', 'ksher');
							}
						} else {
							$output .= 'Order is Not Paid! by ksher (by id:' . $body['data']['ksher_order_no']. ')<br>';
						}
					} else {
						$output .= 'Not Ksher payment(Invalid Verify)<br>';
					}
				} else {
					$output .= 'Not Ksher payment(Code != 0)' . $body['code'] . '<br>';
				}
			} else {
				$output .= 'Not Ksher payment(No Body)<br>';
			}
			$output .= '------------------------------<br><br>' ;
		}
	} else {
		$output .= 'No Order in Pending Payment';
	}
	//echo $output;
}
?>