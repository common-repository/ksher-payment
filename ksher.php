<?php
/**
	* Plugin Name: Ksher Payment
	* Plugin URI:  https://www.ksher.com
	* Description: Ksher Gateway Plugin is a WordPress plugin designed specifically for WooCommerce. The plugin adds support for Ksher Payment Gateway payment method to WooCommerce.
	* Version:     1.0.12
	* Author:      Ksher
	* Text Domain: ksher
	* License:     KSHER
	* License URI: https://www.ksher.com
	*/
if ( in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option( 'active_plugins' ))) ) {

	add_action('wp_enqueue_scripts', 'ksher_enqueue_style');
	function ksher_enqueue_style()
	{
			wp_register_style( 'ksher-style', plugin_dir_url( __FILE__ ) . 'assets/css/style.css' );
			wp_enqueue_style( 'ksher-style' );
	}

	add_action('admin_enqueue_scripts', 'ksher_enqueue_style_admin');
	function ksher_enqueue_style_admin($hook)
	{
		wp_register_style( 'ksher-admin-style', plugin_dir_url( __FILE__ ) . 'assets/css/admin-style.css' );
		wp_enqueue_style( 'ksher-admin-style' );

		wp_register_script( 'upload-script', plugin_dir_url( __FILE__ ) . 'assets/js/upload.js', array( 'jquery' ) );
		$script_data_array = array(
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
		);

		wp_localize_script( 'upload-script', 'ul', $script_data_array );
		wp_enqueue_script( 'upload-script' );

		wp_register_script( 'connection-script', plugin_dir_url( __FILE__ ) . 'assets/js/connection.js', array( 'jquery' ) );
		$script_data_array = array(
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
		);
		wp_localize_script( 'connection-script', 'ul', $script_data_array );
		wp_enqueue_script( 'connection-script' );

		wp_register_script( 'check-payment-script', plugin_dir_url( __FILE__ ) . 'assets/js/check_payment.js', array( 'jquery' ) );
		$script_data_array = array(
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
		);
		wp_localize_script( 'check-payment-script', 'ul', $script_data_array );
		wp_enqueue_script( 'check-payment-script' );
	}

	include('setting/setting.php');
	include('setting/check-payment.php');
	include('payment/set-payment.php');
	include('order-received/order-received.php');
}