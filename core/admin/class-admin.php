<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    LSDCommerce
 * @subpackage LSDCommerce/admin
 * @author     LSD Plugin <dev@lsdplugin.com>
 */
class LSDCommerce_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
        $this->version = $version;
		// $this->load_admin_dependency();
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() 
	{
		wp_enqueue_style( 'wp-color-picker' );

		// Enquene Spectre CSS in LSDCommerce Admin Only
		if( isset($_REQUEST['page']) && $_REQUEST['page'] == 'lsdcommerce' || strpos( $_REQUEST['page'], 'lsdc' ) !== false ){
			// wp_enqueue_style( 'spectre-exp', LSDC_URL . 'assets/lib/spectre.css/spectre-exp.min.css', array(), '0.5.8', 'all' );
			// wp_enqueue_style( 'SpectreIcons', LSDC_URL . 'assets/lib/spectre/spectre-icons.min.css', array(), '0.5.8', 'all' );
			wp_enqueue_style( 'spectre-css', LSDC_URL . 'assets/lib/spectre.css/spectre.min.css', array(), '0.5.8', 'all' );
		}

		wp_enqueue_style( $this->plugin_name . '-admin', LSDC_URL . 'assets/dev/css/admin/admin.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() 
	{
		// Enquene Admin and Admin AJAX
		wp_enqueue_script( $this->plugin_name, LSDC_URL . 'assets/dev/js/admin/admin.js', array( 'jquery', 'wp-color-picker' ), $this->version, false );

		wp_localize_script( $this->plugin_name , 'lsdc_adm', array(
			'ajax_url' 		=> admin_url( 'admin-ajax.php' ),
			'ajax_nonce'	=> wp_create_nonce('lsdc_nonce'),
			'plugin_url' 	=> LSDC_URL,
		));

		// Enquene Media For Administrator Only
		if ( current_user_can( 'manage_options' ) ) { 
			wp_enqueue_media();
		}
	}

	/**
	 * Load Admin Dependency
	 *
	 * @since    1.0.0
	 */
	public function load_admin_dependency()
	{
		require_once LSDC_PATH . 'core/admin/admin-ajax.php';
	}
	
	/**
	 * Register Menu LSDCommerce in Admin Area
	 *
	 * @since    1.0.0
	 */
	public function register_admin_menu() 
	{ 
		$order_counter = ( get_option( 'lsdc_order_unread_counter' ) > 0 ) ? abs(get_option( 'lsdc_order_unread_counter' )) : '';

		// Add LSDCommerce Menu
		add_menu_page( 
			'LSDCommerce', 
			'LSDCommerce', 
			'manage_options', 
			'lsdcommerce', 
			array( $this, 'lsdc_admin_menu_settings' ), 
			LSDC_URL . 'assets/images/lsdcommerce.png', 
			2
		);

		if( has_action( 'lsdcommerce_licenses_hook' ) ) : 
			add_submenu_page(
				'lsdcommerce',
				__( 'Lisensi', 'lsdcommerce'), //page title
				__( 'Lisensi', 'lsdcommerce'), //menu title
				'manage_options', //capability,
				'lsdcommerce_licenses',//menu slug
				array( $this, 'lsdc_admin_menu_licenses' ) //callback function
			);
		endif;
		
		// Add Order Menu
		add_menu_page( 
			__( 'Order', 'lsdcommerce' ), 
			$order_counter ? sprintf( __( 'Order <span class="awaiting-mod">%d</span>', 'lsdcommerce') , $order_counter ) : __( 'Order', 'lsdcommerce' ),
			'manage_options', 
			'edit.php?post_type=lsdc-order', '', 
			LSDC_URL . 'assets/images/svg/order.svg', 
			3
		);

		// Add Products Menu 
		add_menu_page( 
			 __( 'Products', 'lsdcommerce' ), 
			 __( 'Products', 'lsdcommerce' ), 
			'manage_options', 
			'edit.php?post_type=lsdc-product', '', 
			LSDC_URL . 'assets/images/svg/product.svg', 
			3
		);

			// Submenu Product -> Categories
			add_submenu_page(
				'edit.php?post_type=lsdc-product',
				__( 'Categories', 'lsdcommerce'), //page title
				__( 'Categories', 'lsdcommerce'), //menu title
				'manage_options', //capability,
				'edit-tags.php?taxonomy=lsdc-product-category&post_type=lsdc-product',//menu slug
				'' //callback function
			);
	}

	/**
	 * Loaded LSDCommerce Settings
	 *
	 * @since    1.0.0
	 */
	public function lsdc_admin_menu_settings() 
	{ 
		include_once( LSDC_PATH . ( 'core/admin/settings/common.php' ));
	}

	public function lsdc_admin_menu_licenses()
	{
		include_once( LSDC_PATH . ( 'core/admin/settings/licenses.php' ));
	}

	public static function lsdc_appearance_switch_option(){
		$switch_option = array( 
			// 'lsdc_contoh' => array( __( 'Contoh', 'lsdc' ) ),
		);

		if( has_filter('lsdc_appearance_switch_option') ) {
			$switch_option = apply_filters('lsdc_appearance_switch_option', $switch_option);
		}
		return array_reverse( $switch_option );
	}

}
