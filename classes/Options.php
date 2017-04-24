<?php 
add_action('plugins_loaded',array('FWPR_Options','init'));
class FWPR_Options {
	protected static $instance;
	protected $page_slug = 'fwpr_settings';
	protected $options;
	public static function get_instance() {
		// create an object
		NULL === self::$instance and self::$instance = new self;

		return self::$instance; // return the object
	}
	function __construct(){
		
	}
	public static function init(){
		$instance = self::get_instance();
		$instance->options['global'] = get_option( 'fwpr_global_options' );
		$instance->options['dotpay'] = get_option( 'fwpr_dotpay_options' );
		$instance->options['delivery'] = get_option( 'fwpr_delivery_options' );
		$instance->options['opening_exceptions'] = get_option( 'fwpr_opening_exceptions' );
		add_action('admin_init',array($instance,'register_options'));
		add_action('admin_menu',array($instance,'options_page'));
		add_action('admin_menu',array($instance,'sub_options_page'));

		add_action( 'init', array( $instance,'registerACFOptionPages' ),50 );
		add_action( 'init', array( $instance,'registerACFFieldGroup' ),50 );
	}
	public function get_options(){
		return $this->options;
	}
	public function options_page(){
		add_menu_page(
			__( 'FWPR Settings', 'fwpr' ),
			'FWPR Settings',
			'manage_options',
			$this->page_slug,
			array($this,'render_options'),
			'',
			76
		);	
	}
	public function sub_options_page(){
		// add_submenu_page( string $parent_slug, string $page_title, string $menu_title, string $capability, string $menu_slug, callable $function = '' )
		add_submenu_page( 
			$this->page_slug, 
			'Rejon dostawy', 
			'Rejon dostawy',
			'manage_options',
			'fwpr_delivery_page',
			array($this,'delivery') 
		);
	}
	public function register_options(){
		register_setting( 'fwpr_delivery_page', 'fwpr_delivery_options' );

		add_settings_section( 'fwpr_delivery', __('Ustawienia dostawy','fwpr'), array($this,'global_options'), 'fwpr_delivery_page' );
		add_settings_field( 'fwpr_delivery_area', '', array($this,'global_fields'), 'fwpr_delivery_page', 'fwpr_delivery', array(
			'label_for'         => 'fwpr_delivery_area',
			'name' => 'area',
			'group' => 'delivery',
			'type' => 'text',
			) );

		register_setting($this->page_slug, 'fwpr_global_options');

		register_setting($this->page_slug, 'fwpr_opening_exceptions');

		add_settings_section( 'fwpr_global', __('Ustawienia','fwpr'), array($this,'global_options'), $this->page_slug );

		add_settings_field( 'fwpr_delivery_time', __('Czas dostawy','fwpr'), array($this,'global_fields'), $this->page_slug, 'fwpr_global', array(
			'label_for'         => 'fwpr_delivery_time',
			'name' => 'delivery_time',
			'group' => 'global',
			'type' => 'text',
			'description' => __('Tekst wyświetlany użytkownikowi jako czas wykonania zamówienia','fwpr')
			) );

		add_settings_field( 'fwpr_max_for_tomorrow', __('Maksymalna godzina zamówienia na jutro','fwpr'), array($this,'global_fields'), $this->page_slug, 'fwpr_global', array(
			'label_for'         => 'fwpr_max_for_tomorrow',
			'name' => 'max_for_tomorrow',
			'group' => 'global',
			'type' => 'text',
			// 'description' => __('Tekst wyświetlany użytkownikowi jako czas wykonania zamówienia','fwpr')
			) );

		add_settings_field( 'fwpr_return_url', __('Strona powrotu','fwpr'), array($this,'global_fields'), $this->page_slug, 'fwpr_global', array(
			'label_for'         => 'fwpr_return_url',
			'func' => 'page_select',
			'name' => 'return_page',
			'group' => 'global',
			'type' => 'select',
			'description' => __('Strona, na którą użytkownik zostanie przeniesiony po dokonaniu zamówienia','fwpr')
			) );

		add_settings_field( 'fwpr_order_page', __('Strona składania zamówienia','fwpr'), array($this,'global_fields'), $this->page_slug, 'fwpr_global', array(
			'label_for'         => 'fwpr_order_page',
			'func' => 'page_select',
			'name' => 'order_page',
			'group' => 'global',
			'type' => 'select',
			'description' => __('Strona, na którą użytkownik zostanie przekierowany po kliknięciu przycisku zatwierdzającego w koszyku','fwpr')
			) );

		add_settings_field( 'fwpr_gmap_api', __('Klucz API Google Maps','fwpr'), array($this,'global_fields'), $this->page_slug, 'fwpr_global', array(
			'label_for'         => 'fwpr_gmap_api',
			'name' => 'gmap_api',
			'group' => 'global',
			'type' => 'text'
			) );
		add_settings_field( 'fwpr_min_delivery_price', __('Minimalna kwota zamówienia','fwpr'), array($this,'global_fields'), $this->page_slug, 'fwpr_global', array(
			'label_for'         => 'fwpr_min_delivery_price',
			'name' => 'min_delivery_price',
			'group' => 'global',
			'type' => 'text'
			) );

		add_settings_field( 'fwpr_currency', __('Waluta','fwpr'), array($this,'global_fields'), $this->page_slug, 'fwpr_global', array(
			'label_for'         => 'fwpr_currency',
			'name' => 'currency',
			'group' => 'global',
			'type' => 'select',
			'options' => array(
				'PLN' => 'PLN',
				'EUR' => 'Euro',
				'GBP' => 'GBP',
				'USD' => 'USD',
				)
			) );
		register_setting($this->page_slug, 'fwpr_dotpay_options');

		add_settings_section( 'fwpr_dotpay_settings', __('Ustawienia konta Dotpay','fwpr'), array($this,'dotpay_options'), $this->page_slug );

		add_settings_field( 'fwpr_dotpay_id', __('ID Konta dotpay','fwpr'), array($this,'global_fields'), $this->page_slug, 'fwpr_dotpay_settings', array(
			'label_for'         => 'fwpr_dotpay_id',
			'name' => 'id',
			'group' => 'dotpay',
			'type' => 'text'
			) );
		add_settings_field( 'fwpr_dotpay_pin', __('PIN Konta dotpay','fwpr'), array($this,'global_fields'), $this->page_slug, 'fwpr_dotpay_settings', array(
			'label_for'         => 'fwpr_dotpay_pin',
			'name' => 'pin',
			'group' => 'dotpay',
			'type' => 'text'
			) );
	}
	public function global_options($args){
		//echo '<pre>'; print_r($this->options); echo '</pre>';
	}
	public function global_fields($args){
		if( !empty($args['func']) && $args['func'] == 'page_select' ){
			include FWPR_DIR . 'options-template/return-page-select.php';			
		} elseif( $args['type'] == 'select' ) {
			include FWPR_DIR . 'options-template/select-field.php';
		} else {
			include FWPR_DIR . 'options-template/global-fields.php';			
		}
		if( !empty( $args['description'] ) ){
			echo '<p class="description">'.$args['description'].'</p>';
		}
	}
	public function dotpay_options($args){		
		echo '<p class="description">Podstawowe ustawienia konta Dotpay</p>';
	}
	public function render_options(){
		include FWPR_DIR . 'options-template/options-page.php';
	}
	public function delivery(){
		include FWPR_DIR . 'options-template/delivery-page.php';
	}

	/**
	 * Add ACF Options Page to dashboard
	 * Has settings for reminder mails
	 * @return void 
	 */
	public function registerACFOptionPages(){
		if( function_exists('acf_add_options_page') ) {
			acf_add_options_sub_page(array(
					'parent_slug' => 'fwpr_settings',
					'page_title' 	=> 'Ustawienia kalendarza',
					'menu_title' 	=> 'Ustawienia kalendarza',
					'menu_slug' 	=> 'fwpr-datepicker-settings',
					'capability' 	=> 'edit_posts',
				));

			acf_add_options_sub_page(array(
					'parent_slug' => 'fwpr_settings',
					'page_title' 	=> 'Przypomnienia',
					'menu_title' 	=> 'Przypomnienia',
					'menu_slug' 	=> 'fwpr-reminder-settings',
					'capability' 	=> 'edit_posts',
				));
		}
		do_action( 'fwpr/options/pages' );
	}
	public function registerACFFieldGroup(){
		if( function_exists('acf_add_local_field_group') ):

		acf_add_local_field_group(array (
			'key' => 'group_58f73416d1369',
			'title' => 'Weekendy',
			'fields' => array (
				array (
					'key' => 'field_58f7342cda4dd',
					'label' => 'Włącz zamawianie na weekend',
					'name' => 'fwpr_enable_weekends',
					'type' => 'true_false',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'message' => '',
					'default_value' => 1,
					'ui' => 1,
					'ui_on_text' => 'Tak',
					'ui_off_text' => 'Nie',
				),
			),
			'location' => array (
				array (
					array (
						'param' => 'options_page',
						'operator' => '==',
						'value' => 'fwpr-datepicker-settings',
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

		acf_add_local_field_group(array (
			'key' => 'group_58f730286adb4',
			'title' => 'Wyłączone daty',
			'fields' => array (
				array (
					'key' => 'field_58f7309c514b2',
					'label' => 'Wybierz daty do wykluczenia',
					'name' => 'fwpr_disabled_dates',
					'type' => 'repeater',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'collapsed' => '',
					'min' => 0,
					'max' => 0,
					'layout' => 'block',
					'button_label' => 'Dodaj datę',
					'sub_fields' => array (
						array (
							'key' => 'field_58f731dd5c24a',
							'label' => 'Data',
							'name' => 'data',
							'type' => 'date_picker',
							'instructions' => '',
							'required' => 0,
							'conditional_logic' => 0,
							'wrapper' => array (
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'display_format' => 'd/m/Y',
							'return_format' => 'd/m/Y',
							'first_day' => 1,
						),
					),
				),
			),
			'location' => array (
				array (
					array (
						'param' => 'options_page',
						'operator' => '==',
						'value' => 'fwpr-datepicker-settings',
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