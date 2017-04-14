<?php 

add_action( 'plugins_loaded',array('FWPR_Reminders','init') );
add_filter( 'wp_mail_from', array('FWPR_Reminders','mail_from') );
add_filter( 'wp_mail_from_name', array('FWPR_Reminders','mail_from_name') );
add_filter( 'wp_mail_content_type', array('FWPR_Reminders','content_type') );
/**
 * Handles mail reminders about ending order
 */
class FWPR_Reminders {
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
		add_action( 'wp',array( $instance,'getOrders' ) );

		add_action( 'init',array( $instance,'registerFieldGroup' ) );
		add_action( 'admin_menu', array( $instance,'registerOptionPage' ),50 );

		add_filter( 'fwpr/reminder/message/help', array( $instance,'messageTags' ) );
		add_filter( 'fwpr/reminder/product-template/help', array( $instance,'productTags' ) );
	}

	/**
	 * Get orders that need reminding
	 * @return void Send reminder to clients
	 */
	public function getOrders(){
		$current_date = current_time( 'd/m/Y' );

		$reminder = get_field('fwpr_reminder','option');
		if( empty($reminder) ) {
			$reminder = 1;
		}
		$current_date = DateTime::createFromFormat('d/m/Y', $current_date)->getTimestamp();
		$nextDay = strtotime('+'.$reminder.' day',$current_date);
		$nextDay = date( 'd/m/Y',$nextDay );

		/**
		 * Get orders that have desired date in their array
		 * @var array
		 */
		$orders = get_posts(
			array(
				'post_type' => 'fwpr_order',
				'posts_per_page' => -1,
				'meta_query' => array(
						array(
							'key' => '_fwpr_order_dates',
							'value' => $nextDay,
							'compare' => 'LIKE'
						)
					)
				)
		);

		foreach ($orders as $key => $order) {
			$rows = get_field('order_products',$order->ID);
			$notify = array();
			$to = get_field( 'order_mail',$order->ID );
			$name = get_field('order_user',$order->ID);
			$subject = get_field('fwpr_reminder_subject','option');
			/**
			 * Get products that have been notified already
			 * @var array
			 */
			$notified = get_post_meta($order->ID,'_fwpr_reminder_sent',true);			
			if( empty($notified) ) {
				$notified = array();
			}
			if( $rows ) {
				foreach ($rows as $key => $row) {
					$dates = $row['dates'];
					if( $dates ) {
						$lastDate = end( $dates );
						/**
						 * Skip the prouduct if the reminder was sent previously for chosen date
						 */
						if( array_key_exists($row['product'], $notified) ) {
							if( in_array($nextDay, $notified[$row['product']]) ) {
								continue;
							}
						}
						if( $lastDate['date'] == $nextDay ) {
							$notify[$key]['product'] = $row['product'];
							$notify[$key]['variant'] = $row['variant'];
							$notify[$key]['end_date'] = $lastDate['date'];


							$notified[$row['product']][] = $lastDate['date'];
						}
					}
				}
			}
			$sent = false;
			if( !empty( $notify ) ) {
				$sent = wp_mail( $to, $subject, $this->setupMail( $notify,$name ) );
			}
			if( $sent = true ) {
				/**
				 * Add product to notified if mail has been sent successfully
				 */
				update_post_meta( $order->ID, '_fwpr_reminder_sent', $notified );
			}
		}		
	}

	/**
	 * Add ACF Options Page to dashboard
	 * Has settings for reminder mails
	 * @return void 
	 */
	public function registerOptionPage(){
		if( function_exists('acf_add_options_page') ) {
			acf_add_options_page(array(
					'page_title' 	=> 'Przypomnienia mailowe',
					'menu_title' 	=> 'Przypomnienia mailowe',
					'menu_slug' 	=> 'fwpr-reminder-settings',
					'capability' 	=> 'edit_posts',
				));
		}
	}

	/**
	 * Add ACF Field group to options page
	 * Has needed settings for reminder mail
	 *
	 * Uses 'fwpr/reminder/message/help' to modify the message field help text
	 * Mostly used to display tags that might be used inside the mail content
	 * 
	 * @return void 
	 */
	public function registerFieldGroup(){
		if( function_exists('acf_add_local_field_group') ):

		acf_add_local_field_group(array (
			'key' => 'group_58ef77872f6ff',
			'title' => 'Ustawienia przypomnień',
			'fields' => array (
				array (
					'key' => 'field_58ef78a31ab75',
					'label' => 'Kiedy wysłać przypomnienie',
					'name' => 'fwpr_reminder',
					'type' => 'number',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'default_value' => 1,
					'placeholder' => '',
					'prepend' => '',
					'append' => 'dni wstecz',
					'min' => 1,
					'max' => 7,
					'step' => '',
				),
				array (
					'key' => 'field_58ef781cd8284',
					'label' => 'Nadawca wiadomości',
					'name' => 'fwpr_sender',
					'type' => 'email',
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
				),
				array (
					'key' => 'field_58ef7d77399a9',
					'label' => 'Nazwa nadawcy',
					'name' => 'fwpr_sender_name',
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
					'key' => 'field_58ef782cd8285',
					'label' => 'Temat wiadomości',
					'name' => 'fwpr_reminder_subject',
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
					'key' => 'field_58ef7849d8286',
					'label' => 'Treść wiadomości',
					'name' => 'fwpr_reminder_message',
					'type' => 'wysiwyg',
					'instructions' => apply_filters('fwpr/reminder/message/help',''),
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
					'delay' => 0,
				),
				array (
					'key' => 'fwpr_58ef7849d8286',
					'label' => 'Szablon produktu',
					'name' => 'fwpr_reminder_template',
					'type' => 'wysiwyg',
					'instructions' => apply_filters('fwpr/reminder/product-template/help',''),
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
					'delay' => 0,
				),
			),
			'location' => array (
				array (
					array (
						'param' => 'options_page',
						'operator' => '==',
						'value' => 'fwpr-reminder-settings',
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

	/**
	 * Setup mail content to be sent
	 *
	 * Uses 'fwpr/reminder/message' to further modify the mail
	 * @param  integer $postID       Post ID which is being reminded
	 * @param  string $variantName  Name of ordered variant
	 * @param  string $endDate      Ending date of the order
	 * @param  string $customerName Name of the customer
	 * @return string               Modified HTML mail content
	 */
	public function setupMail( $notify,$customerName ){
		$content = get_field('fwpr_reminder_message','option');
		// $content = str_replace('[product_title]', get_the_title( $postID ), $content);
		// $content = str_replace('[variant_name]', $variantName, $content);
		// $content = str_replace('[end_date]', $endDate, $content);
		$content = str_replace('[customer_name]', $customerName, $content);
		$productTemplate = get_field('fwpr_reminder_template','option');
		$endingHtml = '';
		foreach ($notify as $key => $order) {
			$template = str_replace('[product_title]', get_the_title( $order['product'] ),$productTemplate);
			$template = str_replace('[variant_name]', $order['variant'],$template);
			$template = str_replace('[end_date]', $order['end_date'],$template);
			$endingHtml .= $template;
		}
		$content = str_replace('[ending_orders]', $endingHtml, $content);
		echo '<pre>'; print_r($content); echo '</pre>';
		$content = apply_filters( 'fwpr/reminder/message', $content, $notify );
		return $content;
	}

	public function messageTags($help){
		$help = '[customer_name] - Nazwa klienta <br>';
		return $help;
	}

	public function productTags($help){
		$help = '[product_title] - Tytuł produktu <br>';
		$help .= '[variant_name] - Nazwa wariantu <br>';
		$help .= '[end_date] - Data końca <br>';

		return $help;
	}
	/**
	 * Change mail sender email adress
	 * @param  string $email Default wordpress email adress
	 * @return string        Changed email adress
	 */
	public static function mail_from($email){
		$mail = get_field('fwpr_sender','option');
		return $mail;
	}

	/**
	 * Change mail sender name
	 * @param  string $name Default wordpress mail sender name
	 * @return strign       Changed name
	 */
	public static function mail_from_name($name){
		$name = get_field('fwpr_sender_name','option');
		return $name;
	}

	/**
	 * Set mail content type
	 * @return string Mail content type
	 */
	public static function content_type() {
		return "text/html";
	}
}