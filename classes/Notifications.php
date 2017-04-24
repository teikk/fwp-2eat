<?php 
add_action( 'plugins_loaded', array('FWPR_Notifications','init') );
class FWPR_Notifications {
	protected static $instance;

	function __construct(){}
	public static function get_instance() {
		// create an object
		NULL === self::$instance and self::$instance = new self;

		return self::$instance; // return the object		
	}

	/**
	 * Initialize class in wordpress
	 * @return void
	 * @hook plugins_loaded
	 */
	public static function init(){
		$instance = self::get_instance();
		add_action( 'init', array($instance,'addOptionsPage'),50 );
		add_action( 'init', array($instance,'registerFields'),50 );

		add_action( 'fwpr/order/create', array($instance,'adminNotification'), 5, 2 );
		add_action( 'fwpr/order/create', array($instance,'userNotification'), 10, 2 );

		add_action( 'fwpr/notification/message/help', array($instance,'instructionsMessage'));
	}

	public function userNotification($data,$orderID){
		$subject = get_field('fwpr_created_subject','option');
		$message = get_field('fwpr_created_message','option');

		$message = $this->prepareMessage($message, $data, $orderID);
		$this->sendMail($data['mail'],$subject,$message);
	}

	public function adminNotification($data,$orderID){
		$subject = get_field('fwpr_new_order_subject','option');
		$message = get_field('fwpr_new_order_message','option');
		$message = $this->prepareMessage($message, $data, $orderID);
		$to = get_field('fwpr_notification_getter','option');
		$this->sendMail($to,$subject,$message);
	}

	public function prepareMessage( $message, $data, $orderID) {
		$message = str_replace('[order_user]', $data['user'], $message);
		$message = str_replace('[order_address]', $data['address'], $message);
		$message = str_replace('[order_payment]', fwpr_payment_label($data['payment_type']), $message);
		$message = str_replace('[order_mail]', $data['mail'], $message);
		$message = str_replace('[order_phone]', $data['phone'], $message);

		$products = get_field('order_products',$orderID);
		
		$productsHtml = '';
		$totalPrice = 0;
		if( !empty($products) ){
			$productsHtml = apply_filters( 'fwpr/notification/products/wrap/open', '<ul>' );
			foreach ($products as $key => $product) {				 
				$productItem = '<li>'.get_the_title($product['product']).': '.$product['variant'].'</li>';
				$productsHtml .= apply_filters( 'fwpr/notification/date/item',$productItem );
				$dates = $product['dates'];
				if( !empty($dates) ) {
					$totalPrice += $product['price'] * sizeof($dates);
					usort($dates, 'fwpr_sortCartDates');
					$datesHtml = apply_filters( 'fwpr/notification/date/wrap/open', '<ul>' );

					foreach ($dates as $key => $date) {
						$dateItem = '<li>'.$date['date'].'</li>';
						$datesHtml .= apply_filters( 'fwpr/notification/date/item', $dateItem );
					}

					$datesHtml .= apply_filters( 'fwpr/notification/date/wrap/end', '</ul>' );
					$productsHtml .= $datesHtml;
				}
			}
			$productsHtml .= apply_filters( 'fwpr/notification/products/wrap/close', '</ul>' );	
		}
		$globalOptions = get_option('fwpr_global_options');

		$totalPrice = number_format($totalPrice,2).' '.$globalOptions['currency'];
		$message = str_replace('[order_products]', $productsHtml, $message);
		$message = str_replace('[order_price]', $totalPrice, $message);
		return $message;
	}

	public function sendMail($to, $subject, $message) {
		$sender = get_field('fwpr_notification_sender','option');
		$senderName = get_field('fwpr_notification_sender_name','option');

		$headers = array(
			'Content-Type: text/html; charset=UTF-8',			
			);
		if( !empty( $sender ) && !empty($senderName) ) {
			$headers[] = 'From: '.$senderName.' <'.$sender.'>';
		}
		/** Remove filters added by FWPR_Reminders class */
		remove_filter( 'wp_mail_from', array('FWPR_Reminders','mail_from') );
		remove_filter( 'wp_mail_from_name', array('FWPR_Reminders','mail_from_name') );
		remove_filter( 'wp_mail_content_type', array('FWPR_Reminders','content_type') );

		$headers = apply_filters( 'fwpr/notification/mail/headers', $headers );
		if( !empty( $to ) || !empty( $sender ) ) {
			wp_mail( $to, $subject, $message, $headers );
		}
	}

	public function instructionsMessage($help) {
		return 'Umieść shortcode w miejscu, w którym chcesz wyświetlić informację:<br>
				<code>[order_user]</code> - Imię i nazwisko klienta <br>
				<code>[order_address]</code> - Adres zamówienia <br>
				<code>[order_payment]</code> - Typ płatności <br>
				<code>[order_mail]</code> - E-mail użytkownika<br>
				<code>[order_phone]</code> - Numer telefonu użytkownika<br>
				<code>[order_products]</code> - Zamówione produkty<br>
				<code>[order_price]</code> - Należność';
	}

	public function addOptionsPage(){
		if( function_exists('acf_add_options_page') ) {
			acf_add_options_sub_page(array(
				'parent_slug' => 'fwpr_settings',
				'page_title' 	=> 'Powiadomienia',
				'menu_title' 	=> 'Powiadomienia',
				'menu_slug' 	=> 'fwpr-notification-settings',
				'capability' 	=> 'edit_posts',
				));
		}
	}

	public function registerFields(){
		if( function_exists('acf_add_local_field_group') ):

		acf_add_local_field_group(array (
			'key' => 'group_58e207a563357',
			'title' => 'Maile systemowe',
			'fields' => array (
				array (
					'key' => 'field_58e207ad93ac1',
					'label' => 'Przyjęcie zamówienia',
					'name' => '',
					'type' => 'tab',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'placement' => 'left',
					'endpoint' => 0,
				),
				array (
					'key' => 'field_58e207bf93ac2',
					'label' => 'Temat',
					'name' => 'fwpr_created_subject',
					'type' => 'text',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'default_value' => '',
					'placeholder' => '',
					'prepend' => '',
					'append' => '',
					'maxlength' => '',
				),
				array (
					'key' => 'field_58e207c793ac3',
					'label' => 'Wiadomość',
					'name' => 'fwpr_created_message',
					'type' => 'wysiwyg',
					'instructions' => apply_filters( 'fwpr/notification/message/help', '' ),
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'default_value' => '',
					'tabs' => 'all',
					'toolbar' => 'full',
					'media_upload' => 1,
				),
				// array (
				// 	'key' => 'field_58e207d693ac4',
				// 	'label' => 'Potwierdzenie realizacji',
				// 	'name' => '',
				// 	'type' => 'tab',
				// 	'instructions' => '',
				// 	'required' => 0,
				// 	'conditional_logic' => 0,
				// 	'wrapper' => array (
				// 		'width' => '',
				// 		'class' => '',
				// 		'id' => '',
				// 	),
				// 	'placement' => 'left',
				// 	'endpoint' => 0,
				// ),
				// array (
				// 	'key' => 'field_58e2080f93ac5',
				// 	'label' => 'Temat',
				// 	'name' => 'accept_subject',
				// 	'type' => 'text',
				// 	'instructions' => '',
				// 	'required' => 0,
				// 	'conditional_logic' => 0,
				// 	'wrapper' => array (
				// 		'width' => '',
				// 		'class' => '',
				// 		'id' => '',
				// 	),
				// 	'default_value' => '',
				// 	'placeholder' => '',
				// 	'prepend' => '',
				// 	'append' => '',
				// 	'maxlength' => '',
				// ),
				// array (
				// 	'key' => 'field_58e2081793ac6',
				// 	'label' => 'Wiadomość',
				// 	'name' => 'accept_message',
				// 	'type' => 'wysiwyg',
				// 	'instructions' => '',
				// 	'required' => 0,
				// 	'conditional_logic' => 0,
				// 	'wrapper' => array (
				// 		'width' => '',
				// 		'class' => '',
				// 		'id' => '',
				// 	),
				// 	'default_value' => '',
				// 	'tabs' => 'all',
				// 	'toolbar' => 'full',
				// 	'media_upload' => 1,
				// ),
				array (
					'key' => 'field_58e20c605ecd3',
					'label' => 'Nowe zamówienie',
					'name' => '',
					'type' => 'tab',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'placement' => 'left',
					'endpoint' => 0,
				),
				array (
					'key' => 'field_58e20c6f5ecd4',
					'label' => 'Temat',
					'name' => 'fwpr_new_order_subject',
					'type' => 'text',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'default_value' => '',
					'placeholder' => '',
					'prepend' => '',
					'append' => '',
					'maxlength' => '',
				),
				array (
					'key' => 'field_58e20c8f5ecd5',
					'label' => 'Wiadomość',
					'name' => 'fwpr_new_order_message',
					'type' => 'wysiwyg',
					'instructions' => apply_filters( 'fwpr/notification/message/help', '' ),
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'default_value' => '',
					'tabs' => 'all',
					'toolbar' => 'full',
					'media_upload' => 1,
				),
				array (
					'key' => 'field_58e20d7bb15ce',
					'label' => 'Pozostałe ustawienia',
					'name' => '',
					'type' => 'tab',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'placement' => 'left',
					'endpoint' => 0,
				),
				array (
					'key' => 'field_58e20d89b15cf',
					'label' => 'Nadawca wiadomości',
					'name' => 'fwpr_notification_sender',
					'type' => 'text',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'default_value' => '',
					'placeholder' => '',
					'prepend' => '',
					'append' => '',
					'maxlength' => '',
				),
				array (
					'key' => 'field_58e21e12b3e35',
					'label' => 'Nazwa nadawcy',
					'name' => 'fwpr_notification_sender_name',
					'type' => 'text',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'default_value' => '',
					'placeholder' => '',
					'prepend' => '',
					'append' => '',
					'maxlength' => '',
				),
				array (
					'key' => 'field_58e20d99b15d0',
					'label' => 'Odbiorca wiadomości',
					'name' => 'fwpr_notification_getter',
					'type' => 'text',
					'instructions' => 'Na ten adres będą wysyłane powiadomienia o nowych zamówieniach',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'default_value' => '',
					'placeholder' => '',
					'prepend' => '',
					'append' => '',
					'maxlength' => '',
				),
			),
			'location' => array (
				array (
					array (
						'param' => 'options_page',
						'operator' => '==',
						'value' => 'fwpr-notification-settings',
					),
				),
			),
			'menu_order' => 0,
			'position' => 'normal',
			'style' => 'default',
			'label_placement' => 'top',
			'instruction_placement' => 'label',
			'hide_on_screen' => '',
			'active' => 1,
			'description' => '',
		));

		endif;
	}
}