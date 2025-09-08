<?php

namespace Leira_Transients\Admin;

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two example hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @link       https://github.com/arielhr1987/leira-transients
 * @since      1.0.0
 * @package    Leira_Transients
 * @subpackage Leira_Transients/admin
 * @author     Ariel <arielhr1987@gmail.com>
 */
class Admin{

	/**
	 * @var string
	 */
	protected $capability = 'manage_options';

	/**
	 * The admin list table instance
	 *
	 * @var List_Table|null
	 */
	protected $list_table = null;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {}

	/**
	 * Get the capability required to access the admin page
	 *
	 * @return string
	 */
	public function get_capability() {
		return $this->capability;
	}

	/**
	 * Returns the admin list table instance
	 *
	 * @return List_Table|null
	 */
	public function get_list_table() {
		if ( $this->list_table === null ) {
			$this->list_table = new List_Table( array(
				'screen' => get_current_screen()
			) );
		}

		return $this->list_table;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @param  string  $hook  The current admin page hook.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles( $hook ) {}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @param  string  $hook  The current admin page hook.
	 *
	 * @return void
	 * @since    1.0.0
	 */
	public function enqueue_scripts( $hook ) {

		if ( $hook == 'tools_page_leira-transients' ) {
			$this->enqueue_asset( 'admin.css' );
			$this->enqueue_asset( 'admin.js' );
		}
	}

	/**
	 * Enqueue an asset file (JS or CSS) from the build directory.
	 * This function will automatically include the asset file and its dependencies if available.
	 *
	 * @param  string  $filename  The name of the asset file to enqueue, e.g., 'script.js' or 'style.css'.
	 *
	 * @return void
	 */
	public function enqueue_asset( $filename ) {
		$extension = strtolower( pathinfo( $filename, PATHINFO_EXTENSION ) );

		// Only allow JS or CSS
		if ( ! in_array( $extension, [ 'js', 'css' ], true ) ) {
			return;
		}

		$handle     = leira_transients()->name() . pathinfo( $filename, PATHINFO_FILENAME );
		$build_dir  = plugin_dir_path( dirname( __FILE__ ) ) . 'build/';
		$build_url  = plugin_dir_url( dirname( __FILE__ ) ) . 'build/';
		$asset_path = $build_dir . pathinfo( $filename, PATHINFO_FILENAME ) . '.asset.php';
		$asset_file = $build_dir . $filename;

		$asset = file_exists( $asset_path )
			? include $asset_path
			: [
				'dependencies' => [],
				'version'      => file_exists( $asset_file ) ? filemtime( $asset_file ) : false,
			];

		// Choose the appropriate WordPress function
		if ( $extension === 'js' ) {
			wp_enqueue_script(
				$handle,
				$build_url . $filename,
				$asset['dependencies'],
				$asset['version'],
				true // Load in footer
			);
		} else {
			wp_enqueue_style(
				$handle,
				$build_url . $filename,
				[], //$asset['dependencies'],
				$asset['version']
			);
		}
	}

	/**
	 * Add the admin menu item
	 *
	 * @return void
	 */
	public function admin_menu() {
		$hook = add_management_page(
			__( 'Transients', 'leira-transients' ),
			__( 'Transients', 'leira-transients' ),
			$this->capability,
			'leira-transients',
			array( $this, 'render_admin_page' )
		);

		if ( ! empty( $hook ) ) {
			add_action( "load-$hook", array( $this, 'admin_page_load' ) );
		}
	}

	/**
	 * Render the admin page
	 *
	 * @return void
	 */
	public function render_admin_page() {
		if ( ! current_user_can( $this->capability ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'leira-transients' ) );
		}

		$table = $this->get_list_table();
		$table->prepare_items();

		echo '<div class="wrap">';
		echo '<h1 class="wp-heading-inline">Transients</h1>';

		//Display a search subtitle if a search query is set
		$s = isset( $_REQUEST['s'] ) ? urlencode( sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) ) : '';
		if ( strlen( $s ) ) {
			echo '<span class="subtitle">';
			printf(
			/* translators: %s: Search query. */
				__( 'Search results for: %s', 'leira-transients' ),
				'<strong>' . esc_html( urldecode( $s ) ) . '</strong>'
			);
			echo '</span>';
		}

		//show admin notices
		echo wp_kses_post( leira_transients()->notify->display() );

		echo '<hr class="wp-header-end">';

		echo $table->views();

		echo '<form id="leira-transients-search" method="get">';
		$table->search_box( 'Search Transients', 'transients' );
		echo '</form>';

		echo '<form method="post">';
		//echo '<input type="hidden" name="page" value="leira-transients" />';
		$table->display();
		echo '</form>';

		if ( $table->has_items() ) {
			echo $table->inline_edit();
		}

		echo '</div>';

	}

	/**
	 * On the admin page load. Add content to the page
	 *
	 * @return void
	 */
	public function admin_page_load() {
		if ( ! current_user_can( $this->capability ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'leira-transients' ) );
		}

		//TODO: For testing purpose only
		//set_transient( 'test_transient', 'This is a test transient value', 3600 );

		//initialize the table here to be able to register default WP_List_Table screen options
		$this->get_list_table();

		//Add screen options per_page values
		add_screen_option( 'per_page' );

		//handle bulk and simple actions
		$this->handle_actions();

		//Screen reader support content
		get_current_screen()->set_screen_reader_content(
			array(
				'heading_views'      => __( 'Filter Transients', 'leira-transients' ),
				'heading_pagination' => __( 'Transients list navigation', 'leira-transients' ),
				'heading_list'       => __( 'Transients list', 'leira-transients' ),
			)
		);
	}

	/**
	 * Filter the per_page screen option for the admin list table
	 *
	 * @param $false
	 * @param $option
	 * @param $value
	 *
	 * @return int
	 */
	public function filter_set_screen_option( $false, $option, $value ) {

		if ( $option === 'tools_page_leira_transients_per_page' ) {
			$value = (int) $value;
			if ( $value > 0 && $value < 1000 ) {
				return $value;
			}
		}

		return $false;
	}

	/**
	 * Handle actions
	 */
	protected function handle_actions() {
		/**
		 * Check user capability
		 */
		if ( ! current_user_can( $this->capability ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to perform this action.',
				'leira-transients' ) );
		}

		$table = $this->get_list_table();

		/**
		 * Check if a bulk action is being processed
		 */
		$action = $table->current_action();

		if ( $action === false ) {
			return; // No bulk action is being processed
		}

		/**
		 * Prepare redirect URL to avoid resubmission
		 */
		$params          = $table->get_query_args();
		$params['page']  = 'leira-transients';
		$params['paged'] = $table->get_pagenum();
		//$params['per_page'] = $this->get_list_table()->get_items_per_page( 'tools_page_leira_transients_per_page' );

		$redirect_url = add_query_arg( $params, admin_url( 'tools.php' ) );

		/**
		 * Check security nonce
		 */
		$nonce = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, $table->get_wpnonce_action() ) ) {
			// enqueue message
			leira_transients()->notify->error(
				__( 'Security check failed, please try again.', 'leira-transients' )
			);
			wp_safe_redirect( $redirect_url );
			die();
		}

		/**
		 * Process the action
		 */
		switch ( $action ) {
			case 'delete':
				//bulk and single delete action
				$transients        = isset( $_REQUEST['transient'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['transient'] ) ) : array();
				$is_site_transient = isset( $_REQUEST['is_site_transient'] ) ? sanitize_key( wp_unslash( $_REQUEST['is_site_transient'] ) ) : false;
				$is_site_transient = boolval( $is_site_transient );

				if ( ! is_array( $transients ) ) {
					leira_transients()->notify->error(
						__( 'Security check failed, please try again.', 'leira-transients' )
					);
					wp_safe_redirect( $redirect_url );
					//wp_die( __( 'Security check failed', 'leira-transients' ) );
				}

				$transients = array_filter( $transients, 'is_string' );        // removes arrays/objects
				$transients = array_map( 'sanitize_text_field', $transients ); // sanitize each value
				$transients = array_values( $transients );                     //reindex array

				//Delete transients
				$deleted = leira_transients()->transients->delete( $transients, $is_site_transient );

				//Delete result message
				if ( $deleted ) {
					leira_transients()->notify->success( __( 'Transients deleted', 'leira-transients' ) );
				} else {
					leira_transients()->notify->error( __( 'No transients were deleted', 'leira-transients' ) );
				}

				//redirect to avoid resubmission
				wp_safe_redirect( $redirect_url );
				break;
			case 'leira-transient-save':
				//handled via ajax
				$name              = isset( $_REQUEST['name'] ) ? sanitize_text_field( wp_unslash($_REQUEST['name']) ) : '';
				$original_name     = isset( $_REQUEST['original-name'] ) ? sanitize_text_field( wp_unslash($_REQUEST['original-name']) ) : '';
				$expiration        = isset( $_REQUEST['expiration'] ) ? sanitize_text_field( wp_unslash($_REQUEST['expiration']) ) : '';
				$value             = isset( $_REQUEST['value'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['value'] ) ) : '';
				$is_site_transient = isset( $_REQUEST['is_site_transient'] ) ? sanitize_key( wp_unslash( $_REQUEST['is_site_transient'] ) ) : false;
				$is_site_transient = boolval( $is_site_transient );

				// Validate name
				if ( empty( $name ) ) {
					wp_die( esc_html__( 'Please select a valid transient name.', 'leira-transients' ) );
				}

				//Validate expiration
				$expiration = strtotime( $expiration );
				if ( $expiration === false || $expiration === - 1 ) {
					wp_die( esc_html__( 'Please select a valid expiration date/time.', 'leira-transients' ) );
				}

				//If the name has changed, remove the original transient
				if ( leira_transients()->transients->validate_name( $original_name ) !== $name ) {
					//leira_transients()->transients->delete( $original_name );
				}

				// Update the transient
				$edited = leira_transients()->transients->set(
					$name,
					$value,
					$expiration - time(),
					$is_site_transient
				);

				//Return the updated row
				if ( ! $edited ) {
					//WP wasn't able to set the transient.
					//IMPORTANT: If we try to update a transient with a new expiration time, maintaining the original value, $edited will be false.
					//wp_die( esc_html__( 'An error occurred while editing the transient.', 'leira-transients' ) );
				}

				//Output the row table with the new updated data
				$GLOBALS['hook_suffix'] = '';//avoid notice error
				$table                  = $this->get_list_table();

				$table->single_row( compact( 'name', 'value', 'expiration' ) );

				wp_die();
				break;
			default:
				// nothing to do
		}

		die();
	}

	/**
	 * Handle Quick Edit action.
	 */
	public function ajax_save() {
		$this->handle_actions();
	}

	/**
	 * Change the admin footer text on Transient page
	 * Give us a rate
	 *
	 * @param $footer_text
	 *
	 * @return string
	 * @since 1.2.3
	 */
	public function admin_footer_text( $footer_text ) {
		$current_screen = get_current_screen();

		//Pages where we are going to show footer review
		$pages = array(
			'tools_page_leira-transients',
		);

		if ( isset( $current_screen->id ) && in_array( $current_screen->id, $pages ) ) {
			// Change the footer text
			if ( ! get_option( 'leira-transients-footer-rated' ) ) {
				$link = '<a href="https://wordpress.org/support/plugin/leira-transients/reviews/?filter=5" target="_blank"
				   class="leira-transients-admin-rating-link"
				   data-rated="%s"
				   data-nonce="%s">
					&#9733;&#9733;&#9733;&#9733;&#9733;
				</a>';

				$link = sprintf( $link,
					esc_attr__( 'Thanks :)', 'leira-transients' ),
					esc_html( wp_create_nonce( 'footer-rated' ) )
				);

				$footer_text = sprintf(
				/* translators: The link to review the plugin */
					esc_html__( 'If you like Transients please consider leaving a %s review. It will help us to grow the plugin and make it more popular. Thank you.',
						'leira-transients' ),
					wp_kses_post( $link )
				);
			}
		}

		return $footer_text;
	}

	/**
	 * When the user clicks the review link in the backend
	 *
	 * @since 1.0.0
	 */
	public function footer_rated() {
		/**
		 * Check capabilities
		 */
		if ( ! current_user_can( $this->capability ) ) {
			wp_send_json_error( __( 'You do not have sufficient permissions to perform this action.',
				'leira-transients' ) );
		}

		/**
		 * Check nonce
		 */
		$action    = 'footer-rated';
		$query_arg = '_wpnonce';
		$checked   = isset( $_REQUEST[ $query_arg ] )
		             && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST[ $query_arg ] ) ), $action );

		if ( ! $checked ) {
			wp_send_json_error( __( 'Your link has expired, refresh the page and try again.', 'leira-transients' ) );
		}

		update_option( 'leira-transients-footer-rated', 1 );
		wp_send_json_success();
	}

	/**
	 * Render a template file with variables.
	 *
	 * @param  string  $template  The template file path.
	 * @param  array  $vars  Variables to pass into the template.
	 *
	 * @return string Rendered HTML.
	 */
	function render_template( $template, $vars = [] ) {
		// Ensure .php extension
		if ( substr( $template, - 4 ) !== '.php' ) {
			$template .= '.php';
		}

		if ( ! file_exists( $template ) ) {
			return sprintf( '<!-- Template %s not found -->', esc_html( $template ) );
		}

		// Make variables available
		if ( ! empty( $vars ) && is_array( $vars ) ) {
			extract( $vars, EXTR_SKIP );
		}

		ob_start();
		include $template;

		return ob_get_clean();
	}
}
