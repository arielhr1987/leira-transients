<?php

namespace Leira_Transients\Includes;

use Leira_Transients\Admin\Admin;
use Leira_Transients\Admin\Notifications;
use Leira_Transients\Admin\Transients;

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current version of the plugin.
 *
 * @since      1.0.0
 * @package    Leira_Transients
 * @subpackage Includes
 * @author     Ariel <arielhr1987@gmail.com>
 *
 * @property Admin admin Handles all admin functionality
 * @property Transients transients Manages all transients
 * @property Notifications notify Handles all admin notifications
 */
class Plugin{

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $name The string used to uniquely identify this plugin.
	 */
	protected $name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $version The current version of the plugin.
	 */
	protected $version;

	/**
	 * Singleton instance
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var null|self $instance The single instance of the class.
	 */
	protected static $instance = null;

	/**
	 * The instances registered in the plugin.
	 *
	 * @since 1.0.0
	 * @access   protected
	 * @var array
	 */
	protected $instances = array();

	/**
	 * The Singleton method
	 *
	 * @return Plugin|null
	 */
	public static function instance() {
		if ( self::$instance === null ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	protected function __construct() {
		if ( defined( 'LEIRA_TRANSIENTS_VERSION' ) ) {
			$this->version = LEIRA_TRANSIENTS_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->name = 'leira-transients';
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @return    string    The name of the plugin.
	 * @since     1.0.0
	 */
	public function name() {
		return $this->name;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @return    string    The version number of the plugin.
	 * @since     1.0.0
	 */
	public function version() {
		return $this->version;
	}

	/**
	 * Run the loader to execute all the hooks with WordPress.
	 *
	 * @return void
	 * @since    1.0.0
	 */
	public function run() {
		/**
		 * Set the plugin text domain for translation
		 */
		add_action( 'plugins_loaded', array( $this, 'load_plugin_textdomain' ) );

		/**
		 * Register all the hooks related to the admin area functionality of the plugin.
		 */
		if ( is_admin() ) {

			//Admin logic
			$this->admin = new Admin();
			$this->transients = new Transients();
			$this->notify     = new Notifications();

			add_action( 'admin_enqueue_scripts', array( $this->admin, 'enqueue_scripts' ) );
			add_action( 'admin_menu', array( $this->admin, 'admin_menu' ) );
			add_filter( 'set-screen-option', array( $this->admin, 'filter_set_screen_option' ), 10, 3 );
			add_action( 'wp_ajax_leira-transient-save', array( $this->admin, 'ajax_save' ) );

			/**
			 * Rate us
			 */
			add_filter( 'admin_footer_text', array( $this->admin, 'admin_footer_text' ), 1000 );
			add_action( 'wp_ajax_leira-transients-footer-rated', array( $this->admin, 'footer_rated' ) );
		}
	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @return void
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain( $this->name(), false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Gets an instance from the loader
	 *
	 * @param  string  $key
	 *
	 * @return mixed|null The instance
	 *
	 * @since     1..0
	 * @access    public
	 *
	 */
	public function __get( $key ) {
		return isset( $this->instances[ $key ] ) ? $this->instances[ $key ] : null;
	}

	/**
	 * Sets an instance in the loader
	 *
	 * @param  string  $key
	 * @param  mixed  $value
	 *
	 * @since     1.0.0
	 * @access    public
	 *
	 */
	public function __set( $key, $value ) {
		$this->instances[ $key ] = $value;
	}
}
