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
	public function __construct() {

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
			//$this->enqueue_asset( 'admin.css' );
			//$this->enqueue_asset( 'admin.js' );
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

		?>

		<div class="wrap">
			<h1 class="wp-heading-inline"><?php echo esc_html__( 'Transients', 'leira-transients' ) ?></h1>
			<?php
			if ( isset( $_REQUEST['s'] ) && $search = esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) ) ) {
				/* translators: %s: search keywords */
				printf( ' <span class="subtitle">' . esc_html__( 'Search results for &#8220;%s&#8221;',
						'leira-transients' ) . '</span>', esc_html( $search ) );
			}

			//the cron job table instance
			$table = $this->get_list_table();
			$table->prepare_items();
			$this->admin_notices();
			?>
			<hr class="wp-header-end">
			<h2 class="screen-reader-text"><?php esc_html_e( 'Filter cron jobs list', 'leira-transients' ) ?></h2>
			<form action="<?php echo esc_url( add_query_arg( '', '' ) ) ?>" method="post">
				<?php
				$table->search_box( esc_html__( 'Search Events', 'leira-transients' ), 'event' );
				$table->views();
				$table->display(); //Display the table
				?>
			</form>
			<?php if ( $table->has_items() ): ?>
				<form method="get">
					<?php $table->inline_edit() ?>
				</form>
			<?php endif; ?>
		</div>

		<?php
	}

	/**
	 * On the admin page load. Add content to the page
	 *
	 * @return void
	 */
	public function admin_page_load() {
//		if ( ! current_user_can( $this->capability ) ) {
//			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'leira-transients' ) );
//		}
//
//		//initialize the table here to be able to register default WP_List_Table screen options
//		$this->get_list_table();
//
//		//handle bulk and simple actions
//		$this->handle_actions();
//
//		//add modal thickbox js
//		add_thickbox();
//
//		//Add screen options
//		add_screen_option( 'per_page', array( 'default' => 999 ) );
//
//		//Add screen help
//		get_current_screen()->add_help_tab(
//			array(
//				'id'      => 'overview',
//				'title'   => __( 'Overview', 'leira-transients' ),
//				'content' =>
//					'<p>' . __( 'Cron is the time-based task scheduling system that is available on UNIX systems. WP-Cron is how WordPress handles scheduling time-based tasks in WordPress. Several WordPress core features, such as checking for updates and publishing scheduled post, utilize WP-Cron.',
//						'leira-transients' ) . '</p>' .
//					'<p>' . __( 'WP-Cron works by: on every page load, a list of scheduled tasks is checked to see what needs to be run. Any tasks scheduled to be run will be run during that page load. WP-Cron does not run constantly as the system cron does; it is only triggered on page load. Scheduling errors could occur if you schedule a task for 2:00PM and no page loads occur until 5:00PM.',
//						'leira-transients' ) . '</p>' .
//					'<p>' . __( 'In the scenario where a site may not receive enough visits to execute scheduled tasks in a timely manner, you can call directly or via a server CRON daemon for X number of times the file <strong>wp-cron.php</strong> located in your WordPress installation root folder.',
//						'leira-transients' ) . '</p>' .
//					'',
//			)
//		);
//		get_current_screen()->add_help_tab(
//			array(
//				'id'      => 'screen-content',
//				'title'   => __( 'Screen Content', 'leira-transients' ),
//				'content' =>
//					'<p>' . __( 'You can customize the display of this screen&#8217;s contents in a number of ways:',
//						'leira-transients' ) . '</p>' .
//					'<ul>' .
//					'<li>' . __( 'You can hide/display columns based on your needs and decide how many cron jobs to list per screen using the <strong>Screen Options</strong> tab.',
//						'leira-transients' ) . '</li>' .
//					'<li>' . __( 'You can filter the list of cron jobs by schedule using the text links above the list to only show those with that status. The default view is to show all.',
//						'leira-transients' ) . '</li>' .
//					'<li>' . __( 'The <strong>Search Events</strong> button will search for crons containing the text you type in the box.',
//						'leira-transients' ) . '</li>' .
//					'<li>' . __( 'The cron jobs marked as red in the list table are <strong>orphan cron jobs</strong>, which mean they are scheduled but are not executing any code. This happens mostly when you deactivate a plugin that previously schedules a cron job.',
//						'leira-transients' ) . '</li>' .
//					'<li>' . __( '<strong>Orphan cron jobs</strong> can only be deleted.',
//						'leira-transients' ) . '</li>' .
//					'<li>' . __( 'Those cron jobs marked as blue in the list table are being executed at the moment.',
//						'leira-transients' ) . '</li>' .
//					'</ul>'
//			)
//		);
//
//		$status         = '<p>' . __( 'Your Wordpress Cron Jobs status is:', 'leira-transients' ) . '</p>';
//		$disable_cron   = defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON;
//		$alternate_cron = defined( 'ALTERNATE_WP_CRON' ) && ALTERNATE_WP_CRON;
//		if ( $disable_cron ) {
//			//$status .= __( '<div class="notice notice-error notice-alt inline"><p class="error">The <strong>DISABLE_WP_CRON</strong> constant is set to <strong>TRUE</strong>.</p></div></li>', 'leira-transients' );
//		} else {
//			//$status .= __( '<div class="notice notice-success notice-alt inline"><p class="success">The <strong>DISABLE_WP_CRON</strong> constant is set to <strong>FALSE</strong>.</p></div></li>', 'leira-transients' );
//		}
//
//		$status .= '<ul>' .
//				   //'<li><div class="notice notice-error notice-alt inline"><p class="error">0</p> </div></li>'.
//				   /*
//					* translators: The value of the constant DISABLE_WP_CRON
//					*/
//				   '<li>' . sprintf( __( '<strong>DISABLE_WP_CRON</strong> constant is set to <strong>%s</strong>. ',
//				'leira-transients' ),
//				$disable_cron ? 'TRUE' : 'FALSE' ) . ( $disable_cron ? __( 'Make sure to create a server CRON daemon that points to the file <strong>wp-cron.php</strong> located in your WordPress installation root folder',
//				'leira-transients' ) : '' ) . '</li>' .
//				   /*
//					* translators: The value of the constant ALTERNATE_WP_CRON
//					*/
//				   '<li>' . sprintf( __( '<strong>ALTERNATE_WP_CRON</strong> constant is set to <strong>%s</strong>. ',
//				'leira-transients' ), $alternate_cron ? 'TRUE' : 'FALSE' ) . '</li>' .
//				   '<ul>';
//
//		get_current_screen()->add_help_tab(
//			array(
//				'id'      => 'status',
//				'title'   => __( 'Status', 'leira-transients' ),
//				'content' => $status,
//			)
//		);
//
//		$schedules = '<p>' . __( 'Your Wordpress schedules:', 'leira-transients' ) . '</p>';
//		$schedules .= '<ul>';
//		foreach ( wp_get_schedules() as $schedule ) {
//			$human_readable = $schedule['interval'];
//			$human_readable = $this->human_readable_duration( $human_readable );
//			/*
//			 * translators: The scheduled time to execute the cron
//			 */
//			$schedules .= '<li>' . sprintf( __( '<strong>%1$s</strong>: Every %2$s. ', 'leira-transients' ),
//					$schedule['display'], $human_readable ) . '</li>';
//		}
//		$schedules .= '<ul>';
//
//		get_current_screen()->add_help_tab(
//			array(
//				'id'      => 'schedules',
//				'title'   => __( 'Schedules', 'leira-transients' ),
//				'content' => $schedules,
//			)
//		);
//
//		get_current_screen()->set_help_sidebar(
//			'<p><strong>' . __( 'For more information:', 'leira-transients' ) . '</strong></p>' .
//			'<p>' . __( '<a href="https://developer.wordpress.org/plugins/cron/">Documentation on Cron Jobs</a>',
//				'leira-transients' ) . '</p>' .
//			'<p>' . __( '<a href="https://wordpress.org/support/">Support</a>',
//				'leira-transients' ) . '</p>' . //TODO: Change to github plugin page
//			'<p>' . __( '<a href="https://github.com/arielhr1987/leira-transients/issues">Report an issue</a>',
//				'leira-transients' ) . '</p>'
//		);
//
//		get_current_screen()->set_screen_reader_content(
//			array(
//				'heading_views'      => __( 'Filter Cron Job list', 'leira-transients' ),
//				'heading_pagination' => __( 'Cron Job list navigation', 'leira-transients' ),
//				'heading_list'       => __( 'Cron Job list', 'leira-transients' ),
//			)
//		);
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
		$action = $this->get_list_table()->current_action();
		if ( ! empty( $action ) ) {

			$query_arg = '_wpnonce';
			$checked   = isset( $_REQUEST[ $query_arg ] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST[ $query_arg ] ) ),
					'bulk-cron-jobs' );

			if ( ! $checked ) {
				//no action to handle, just show the page
				return;
			}

			/** @var Leira_Transients_Manager $manager */
			$manager = leira_transients()->manager;

			$redirect = wp_get_referer();
			if ( empty( $redirect ) ) {
				$params   = array(
					'page' => 'leira-transients',
				);
				$redirect = add_query_arg( $params, admin_url( 'tools.php' ) );
			}
			$jobs = isset( $_REQUEST['job'] ) && is_array( $_REQUEST['job'] ) ? wp_unslash( $_REQUEST['job'] ) : array();

			if ( empty( $jobs ) ) {
				//No jobs to execute action
				$this->enqueue_message( 'error',
					__( 'You most select at least one cron job to perform this action', 'leira-transients' ) );
			} else {
				//action logic.
				switch ( $action ) {
					case 'run':
						//$manager->bulk_run( $jobs );
						$manager->run( $jobs );
						$this->enqueue_message( 'success',
							__( 'The selected cron jobs are being executed at this moment', 'leira-transients' ) );
						wp_safe_redirect( $redirect );
						die();
						break;
					case 'delete':
						$manager->bulk_delete( $jobs );
						$this->enqueue_message( 'success',
							__( 'Selected cron jobs were successfully deleted', 'leira-transients' ) );
						wp_safe_redirect( $redirect );
						die();
						break;
					default:

				}
			}
		} else {

			if ( isset( $_REQUEST['action'] ) ) {
				//if we click "Apply" button
				//TODO: This message is show if we search for cron jobs. Show it only if we hit Apply
				//$this->enqueue_message( 'warning', __( 'Please select a bulk action to execute', 'leira-transients' ) );
			}
		}
	}

	/**
	 * Handle Quick Edit action.
	 * A Cron Job is edited by deleting the current one and creating a new one with the new parameters.
	 * @throws DateInvalidTimeZoneException
	 */
	public function ajax_save() {
		/**
		 * Check user capability
		 */
		if ( ! current_user_can( $this->capability ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to edit this cron job.', 'leira-transients' ) );
		}

		/**
		 * Check nonce
		 */
		$query_arg = '_inline_edit';
		$checked   = isset( $_REQUEST[ $query_arg ] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST[ $query_arg ] ) ),
				'cronjobinlineeditnonce' );
		if ( ! $checked ) {
			wp_die( esc_html__( 'Your link has expired, refresh the page and try again.', 'leira-transients' ) );
		}

		/**
		 * Validate input data
		 */
		$values = array(
			'event'    => '',
			'_action'  => '',
			'md5'      => '',
			'schedule' => '',
			'time'     => '',
			'mm'       => '',
			'jj'       => '',
			'aa'       => '',
			'hh'       => '',
			'mn'       => '',
			'ss'       => '',
			'offset'   => 0 //UTC
		);
		foreach ( $values as $key => $value ) {
			if ( ! isset( $_REQUEST[ $key ] ) || trim( sanitize_text_field( wp_unslash( $_REQUEST[ $key ] ) ) ) == '' ) {
				wp_die( esc_html__( 'Missing parameters. Refresh the page and try again.', 'leira-transients' ) );
			}
			$request_value = sanitize_text_field( wp_unslash( $_REQUEST[ $key ] ) );
			if ( in_array( $key, array( 'mm', 'jj', 'hh', 'mn', 'ss' ) ) ) {
				//add leading zeros to date time fields
				$request_value = str_pad( $request_value, 2, "0", STR_PAD_LEFT );
			}
			$values[ $key ]            = $request_value;
			$schedules                 = wp_get_schedules();
			$schedules['__single_run'] = array();
			if ( $key === 'schedule' && $schedules = wp_get_schedules() && ! isset( $schedules[ $request_value ] ) ) {
				wp_die( esc_html__( 'Incorrect schedule. Please select a valid schedule from the dropdown menu and try again.',
					'leira-transients' ) );
			}
			if ( $key == 'aa' ) {

			}
		}

		/**
		 * Validate execution datetime input data
		 */
		$format        = 'Y-m-d H:i:s';
		$date_str      = sprintf( '%s-%s-%s %s:%s:%s', $values['aa'], $values['mm'], $values['jj'], $values['hh'],
			$values['mn'], $values['ss'] );
		$timezone_name = timezone_name_from_abbr( '', ( 0 - $values['offset'] ) * 60, 1 );
		try{
			$timezone = new DateTimeZone( $timezone_name );
		}catch ( Exception $e ){
			//UTC by default
			$timezone = new DateTimeZone( 'UTC' );
		}
		$date = DateTime::createFromFormat( $format, $date_str, $timezone );

		if ( $date && $date->format( $format ) === $date_str ) {
			// The Y (a 4-digit year)
			//returns TRUE for any integer with any number of digits,
			//so changing the comparison from == to === fixes the issue.
			//date time is valid
		} else {
			//invalid date time information
			wp_die( esc_html__( 'Invalid "Execution" datetime. Please select a valid datetime and try again.',
				'leira-transients' ) );
		}
		//convert to UTC
		$date->setTimezone( new DateTimeZone( timezone_name_from_abbr( '', 0, 1 ) ) );

		/**
		 * Edit the Cron Job
		 */

		/**
		 * @var Leira_Cron_Jobs_Manager $manager
		 */
		$manager = leira_cron_jobs()->manager;
		$args    = isset( $_REQUEST['args'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['args'] ) ) : '';
		$args    = @json_decode( $args, true );
		if ( ! is_array( $args ) ) {
			$args = array();
		}
		$edited = $manager->edit(
			$values['event'],
			$values['md5'],
			$values['time'],
			$values['schedule'],
			$date->format( 'U' ),
			$args
		);

		if ( ! $edited ) {
			//The cron job does not exist, or WP wasn't able to create it
			wp_die( esc_html__( 'An Error occurred while editing the cron job. Refresh the page and try again.',
				'leira-transients' ) );
		}

		/**
		 * Output the row table with the new updated data
		 */
		$GLOBALS['hook_suffix'] = '';//avoid notice error
		$table                  = $this->get_list_table();

		$action = $manager->get_cron_action( $values['event'] );
		$table->single_row( array(
			'event'    => $values['event'],
			'action'   => ! empty( $action ) ? $action : '',
			'args'     => ! empty( $args ) ? wp_json_encode( $args ) : '',
			'schedule' => $values['schedule'],
			'time'     => $date->format( 'U' ),
			'md5'      => md5( serialize( $args ) ),
		) );
		wp_die();
	}

	/**
	 * Enqueue an admin flash message notice
	 *
	 * @param $type
	 * @param $text
	 */
	protected function enqueue_message( $type, $text ) {
		leira_transients()->notify->add( $type, $text );
	}

	/**
	 * Display admin flash notices
	 */
	public function admin_notices() {
		echo leira_transients()->notify->display();
	}

	/**
	 * Convert seconds to human-readable string
	 *
	 * @param  integer  $seconds
	 *
	 * @return string
	 */
	public function human_readable_duration( $seconds ) {

		$points                  = array(
			'year'   => 31556926,
			'month'  => 2629743,
			'week'   => 604800,
			'day'    => 86400,
			'hour'   => 3600,
			'minute' => 60,
			'second' => 1
		);
		$human_readable_duration = array();
		foreach ( $points as $point => $value ) {
			if ( $elapsed = floor( $seconds / $value ) ) {
				$seconds                   = $seconds % $value;
				$s                         = $elapsed > 1 ? 's' : '';
				$human_readable_duration[] = sprintf( _n( "%s $point", "%s $point$s", $elapsed ), (int) $elapsed );
			}
		}

		return implode( ', ', $human_readable_duration );
	}

	/**
	 * Change the admin footer text on Cron Jobs page
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

				ob_start(); ?>
				<a href="https://wordpress.org/support/plugin/leira-transients/reviews/?filter=5" target="_blank"
				   class="leira-transients-admin-rating-link"
				   data-rated="<?php esc_attr_e( 'Thanks :)', 'leira-transients' ) ?>"
				   data-nonce="<?php echo esc_html( wp_create_nonce( 'footer-rated' ) ) ?>">
					&#9733;&#9733;&#9733;&#9733;&#9733;
				</a>
				<?php $link = ob_get_clean();
				ob_start();

				/*
				 * translators: The link to review the plugin
				 */
				printf( esc_html__( 'If you like Cron Jobs please consider leaving a %s review. It will help us to grow the plugin and make it more popular. Thank you.',
					'leira-transients' ), wp_kses_post( $link ) ) ?>

				<?php $footer_text = ob_get_clean();
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
		$checked   = isset( $_REQUEST[ $query_arg ] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST[ $query_arg ] ) ),
				$action );
		if ( ! $checked ) {
			wp_send_json_error( __( 'Your link has expired, refresh the page and try again.', 'leira-transients' ) );
		}

		update_option( 'leira-transients-footer-rated', 1 );
		wp_send_json_success();
	}
}
