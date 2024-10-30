<?php
	add_option( 'ksher_connection', 'fail', '', 'yes' );

	add_filter('upload_mimes', 'ksher_enable_extended_upload');
	function ksher_enable_extended_upload ( $mime_types =array() )
	{
		$mime_types['pem']  = 'application/x-pem-file';
		return $mime_types;
	}

	add_action( 'admin_menu', 'ksher_admin_menu' );
	function ksher_admin_menu()
	{
		add_menu_page(
			__( 'Ksher', 'ksher' ),
			__( 'Ksher', 'ksher' ),
			'manage_options',
			'ksher',
			'ksher_page_contents',
			'dashicons-tide	',
			99
		);
	}

	add_action( 'admin_enqueue_scripts', 'ksher_include_js' );
  function ksher_include_js() {
    if ( ! did_action( 'wp_enqueue_media' ) ) {
      wp_enqueue_media();
    }
  }

	add_action( 'admin_init', 'ksher_register_setting' );
	function ksher_register_setting()
	{
		add_settings_section('ksher_section', 'Ksher Gateway Setting', null, 'ksher_infomation');
		add_settings_field('ksher_app_id', 'AppID*', 'ksher_app_id_field_display', 'ksher_infomation', 'ksher_section');
		add_settings_field('ksher_mch_code', 'Merchant Number*', 'ksher_mch_code_field_display', 'ksher_infomation', 'ksher_section');  
		add_settings_field('ksher_private_key_file','Private Key* (.pem)', 'ksher_private_key_file_display', 'ksher_infomation', 'ksher_section'); 
		add_settings_field('ksher_color','Color', 'ksher_color_display', 'ksher_infomation', 'ksher_section'); 
		add_settings_field('ksher_logo','Logo', 'ksher_logo_display', 'ksher_infomation', 'ksher_section'); 

		register_setting('ksher_section', 'ksher_app_id');
		register_setting('ksher_section', 'ksher_mch_code');
		register_setting('ksher_section', 'ksher_private_key_file');
		register_setting('ksher_section', 'ksher_color');
		register_setting('ksher_section', 'ksher_logo');
	}

	function ksher_logo_display() {
		?>
			<?php if ( get_option('ksher_logo') ) : ?>
				<a href="#" class="ksher-upload-image"><img style="width:100px;" src="<?php echo get_option('ksher_logo'); ?>" /></a>
				<a href="#" class="ksher-remove-image">Remove image</a>
				<input type="hidden" id="ksher_logo" name="ksher_logo-img" value="<?php echo get_option('ksher_logo'); ?>" />
			<?php else: ?>
				<a href="#" class="ksher-upload-image">Upload image</a>
				<a href="#" class="ksher-remove-image" style="display:none">Remove image</a>
				<input type="hidden" id="ksher_logo" name="ksher_logo-img" value="" />
			<?php endif; ?>
		<?php
	}

	function ksher_color_display() {
		?>
			<input class="regular-text"  name="ksher_color" type="color" id="ksher_color" value="<?php echo get_option('ksher_color'); ?>" />
			
		<?php
	}

	function ksher_app_id_field_display()
	{
		?>
			<input class="regular-text" maxlength="8" name="ksher_app_id" type="text" id="ksher_app_id" value="<?php echo get_option('ksher_app_id'); ?>" />
			<p><?php echo esc_html('You can check App ID from website Ksher Merchant ', 'ksher') ?><a href="https://merchant.ksher.net/" target="_blank"><strong><?php echo __('Click', 'ksher');?></strong></a></p>
		<?php
	}

	function ksher_mch_code_field_display()
	{
		?>
			<input class="regular-text" maxlength="8" name="ksher_mch_code" type="text" id="ksher_mch_code" value="<?php echo get_option('ksher_mch_code'); ?>" />
			<p><?php echo esc_html('You can check Merchant Number from website Ksher Merchant ', 'ksher') ?> <a href="https://merchant.ksher.net/" target="_blank"><strong><?php echo __('Click', 'ksher');?></strong></a></p>
		<?php
	}

	function ksher_private_key_file_display()
	{
		?>
			<?php
				if ( get_option('ksher_private_key_file') ) {
					echo '<span id="ksher-private-key-filename" class="ksher-success">Your filename is ' . wp_basename( get_option('ksher_private_key_file') ) . '</span>';
				} else {
					echo '<span id="ksher-private-key-filename" class="ksher-warning">' . esc_html('No file Upload(.pem only', 'ksher') . '</span>';
				}
			?>
			<input id="ksher-private-key-file" type="file"  name="ksher_private_key_file" accept=".pem" />
			<input type="button" class="ksher-upload-btn button button-primary" value="Select File">
			<p><?php echo __('You can download private key from website Ksher Merchant', 'ksher') ?> <a href="https://merchant.ksher.net/" target="_blank"><strong><?php echo __('Click', 'ksher');?></strong></a></p>
		<?php
	}

	function ksher_page_contents()
	{
		?>
			<div id="ksher-message"></div>
			<form method="POST" id="ksher-form" enctype="multipart/form-data">
				<?php settings_fields("ksher_section"); ?>
 				<?php do_settings_sections("ksher_infomation"); ?>
				<p><?php echo __('You can set up <strong>Payment Channel</strong> in page =>', 'ksher'); ?> <a href=" <?php echo admin_url() . 'admin.php?page=wc-settings&tab=checkout'; ?>" ><strong><?php echo esc_html('Click', 'ksher'); ?></strong></a></p>
				<input type="button" name="submit" id="ksher-submit" class="button button-primary" value="<?php echo __('Save Setting and Check Connection', 'ksher'); ?>"  />
			</form>
			<hr/>
			<h3><?php echo __('Connection Status', 'ksher'); ?></h3>
			<?php if ( get_option('ksher_app_id') !== '' &&  get_option('ksher_private_key_file') !== '') : ?>
				<span id="ksher-check-result">
					<?php if ( get_option('ksher_connection') == 'success' ) :?>
						<p class="ksher-success"><?php echo __('You Website is Connected', 'ksher'); ?></p>
					<?php else: ?>
						<p class="ksher-fail"><?php echo __('Not Connect', 'ksher'); ?></p>
					<?php endif; ?>
				</span>
			<?php else: ?>
				<span id="ksher-check-result">
					<p><?php echo __('Please input your AppID and upload Private Key and Save Setting', 'ksher'); ?></p>
				</span>
			<?php endif; ?>
		<?php
	}

	add_action('wp_ajax_nopriv_file_upload', 'ksher_file_upload_callback');
	add_action('wp_ajax_file_upload', 'ksher_file_upload_callback');
	function ksher_file_upload_callback()
	{
		$upload_dir = wp_upload_dir();
		$create_dir = wp_mkdir_p( $upload_dir['basedir'] . '/ksher-upload' );

		if ($create_dir) {
			$file = array();
			$file['base'] = $upload_dir['basedir'] . '/ksher-upload';
			$file['file'] = '.htaccess';
			$file['content'] = 'deny from all';
			if ( wp_mkdir_p( $file['base'] ) && ! file_exists( trailingslashit( $file['base'] ) . $file['file'] ) ) {
				$file_handle = @fopen( trailingslashit( $file['base'] ) . $file['file'], 'w' );
					fwrite( $file_handle, $file['content'] );
					fclose( $file_handle );
			}

			$_filter = true;
			add_filter( 'upload_dir', function( $arr ) use( &$_filter ) {
				if ( $_filter ) {
						$folder = WP_CONTENT_DIR . '/uploads/ksher-upload'; // No trailing slash at the end.
						$arr['path'] = $folder;
						$arr['url'] = $folder;
						$arr['subdir'] = $folder;
				}
				return $arr;
			});

			$upload = wp_upload_bits( $_FILES["file"]["name"], null, file_get_contents($_FILES["file"]["tmp_name"]));
			$_filter = false;
			if ($_FILES["file"]["name"]) {
				update_option('ksher_private_key_file', $upload['url']);
			}
		}
		echo get_option('ksher_private_key_file') ? get_option('ksher_private_key_file') : '0';
		wp_die();
	}

	add_action('wp_ajax_nopriv_ksher_update_appid', 'ksher_update_appid');
	add_action('wp_ajax_ksher_update_appid', 'ksher_update_appid');
	function ksher_update_appid()
	{
		$ksher_app_id = '';
		if (isset($_POST['ksher_app_id'])) {
			$ksher_app_id = sanitize_text_field($_POST['ksher_app_id']);
		}	

		$ksher_mch_code = '';
		if (isset($_POST['ksher_mch_code'])) {
			$ksher_mch_code = sanitize_text_field($_POST['ksher_mch_code']);
		}

		$ksher_color = '';
		if (isset($_POST['ksher_color'])) {
			$ksher_color = sanitize_text_field($_POST['ksher_color']);
		}

    $ksher_logo = '';
		if (isset($_POST['ksher_logo'])) {
			$ksher_logo = sanitize_text_field($_POST['ksher_logo']);
		}

		update_option('ksher_app_id', $ksher_app_id);
		update_option('ksher_mch_code', $ksher_mch_code);
		update_option('ksher_color', $ksher_color);
		update_option('ksher_logo', $ksher_logo);

		echo json_encode(array( 'data' => $ksher_app_id ) );
		wp_die();
	}

	add_action('wp_ajax_nopriv_ksher_check_connection', 'ksher_check_connection');
	add_action('wp_ajax_ksher_check_connection', 'ksher_check_connection');
	function ksher_check_connection()
	{
		$ksher_app_id = '';
		if (isset($_POST['ksher_app_id'])) {
			$ksher_app_id = sanitize_text_field($_POST['ksher_app_id']);
		}
		$ksher_private_key_url = '';
		if (isset($_POST['ksher_private_key_url'])) {
			$ksher_private_key_url = sanitize_text_field($_POST['ksher_private_key_url']);
		}

		$nonce_str = bin2hex(random_bytes(16));
		$timestamp = current_time('timestamp');

		$data = array(
			'mch_appid' => $ksher_app_id,
			'time_stamp' => $timestamp,
			'nonce_str' => $nonce_str,
		);

		$privatekey_content = file_get_contents( $ksher_private_key_url );
		$encoded_sign = ksher_sign_process( $privatekey_content, $data);
		$data['sign'] = $encoded_sign;

		$response = wp_remote_post('https://api.mch.ksher.net/KsherPay/merchant_info', array(
				'headers' => array('Content-Type' => 'application/x-www-form-urlencoded'),
				'body' => $data,
			)
		);

		if ( $response ) {
			$body = json_decode($response['body'], true);
			if ( array_key_exists( 'mch_appid', $body['data'])) {
				echo json_encode(array( 
						'data' =>$body['data']['mch_appid'] , 
						'result' => 'true',
					) 
				);
				update_option('ksher_connection', 'success');
				wp_die();
			} else {
				echo json_encode(array( 
						'data' => $response,
						'result' => 'fail',
					) 
				);
				update_option('ksher_connection', 'fail');
				wp_die();
			}
		} 
	}
?>