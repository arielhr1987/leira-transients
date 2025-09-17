<?php

namespace Leira_Transients\Admin;

use WP_List_Table;
use WP_Locale;

/**
 * Class Leira_Transients\Admin\List_Table
 * This class extends the WP_List_Table class to create a custom list table for managing transients.
 *
 * @since 1.0.0
 */
class List_Table extends WP_List_Table{

	/**
	 * Constructor for the List_Table class.
	 * This initializes the table with the necessary arguments.
	 *
	 * @param $args
	 *
	 * @since 1.0.0
	 */
	public function __construct( $args ) {
		parent::__construct( array(
			'singular' => 'transient',
			'plural'   => 'transients',
			'ajax'     => false,
			'screen'   => isset( $args['screen'] ) ? $args['screen'] : null,
		) );
	}

	/**
	 * Get the columns for the table.
	 * This defines the headers for the table.
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public function get_columns() {
		return array(
			'cb'         => '<input type="checkbox" />',
			'name'       => 'Name',
			'value'      => 'Value',
			'expiration' => 'Expiration'
		);
	}

	/**
	 * Get the sortable columns for the table.
	 * This defines which columns can be sorted.
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public function get_sortable_columns() {
		return [
			'name'       => array( 'name', 'asc' ),
			'expiration' => 'expiration',
		];
	}

	/**
	 * Get the primary column name.
	 * This is used for screen readers and mobile views.
	 *
	 * @return string
	 * @since 1.0.0
	 */
	protected function get_primary_column_name() {
		return 'name'; // Helps screen readers & mobile views
	}

	/**
	 * Get the bulk actions for the table.
	 * This defines the actions that can be performed on multiple items.
	 *
	 * @return array
	 * @since 1.0.0
	 */
	protected function get_bulk_actions() {
		return [
			'delete' => 'Delete',
		];
	}

	/**
	 * Get the nonce action for bulk actions.
	 * This is used for security when performing bulk actions.
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function get_wpnonce_action() {
		return 'bulk-' . $this->_args['plural'];
	}

	/**
	 * Render the checkbox column.
	 * This is used for bulk actions.
	 *
	 * @param  array  $item  The current item in the table.
	 *
	 * @return string
	 * @since 1.0.0
	 */
	protected function column_cb( $item ) {
		return sprintf( '<input type="checkbox" name="transient[]" value="%s" />', esc_attr( $item['name'] ) );
	}

	/**
	 * Render the name column.
	 * This is the primary column that displays the transient name.
	 *
	 * @param  array  $item  The current item in the table.
	 *
	 * @return string
	 * @since 1.0.0
	 */
	protected function column_name( $item ) {
		$name      = isset( $item['name'] ) ? $item['name'] : '';
		$transient = leira_transients()->transients->validate_name( $name );

		$out = sprintf( '<strong>%s</strong>', esc_html( $transient ) );

		$out .= '<div class="hidden" id="inline_' . esc_attr( $name ) . '">';
		foreach ( $item as $key => $value ) {
			$out .= sprintf( '<div class="%s">%s</div>', esc_attr( $key ), esc_attr( $value ) );
		}
		$out .= '</div>';

		return $out;
	}

	/**
	 * Render the value column.
	 * This is the column that displays the transient value.
	 *
	 * @param  array  $item  The current item in the table.
	 *
	 * @return string
	 * @since 1.0.0
	 */
	protected function column_value( $item ) {
		$value = '<div class="nowrap">' . esc_html( $item['value'] ) . '</div>';
		$type  = $this->get_transient_value_type( $item );
		$type  = '<strong class="badge">' . $type . '</strong>';

		return $value . $type;
	}

	/**
	 * Render the expiration column.
	 * This is the expiration column that displays the transient expiration time.
	 *
	 * @param  array  $item  The current item in the table.
	 *
	 * @return string
	 * @since 1.0.0
	 */
	protected function column_expiration( $item ) {
		$expiration = $item['expiration'];

		if ( empty( $expiration ) ) {
			$status    = '<span class="badge badge-green">' . __( 'Persistent', 'leira-transients' ) . '</span>';
			$timestamp = '<span class="na">—</span>';
		} else {
			$format    = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );
			$formatted = date_i18n( $format, $expiration );

			if ( $expiration < time() ) {
				$status = '<span class="badge badge-red">' . __( 'Expired', 'leira-transients' ) . '</span>';
			} else {
				$status = '<span class="badge badge-green">' . __( 'Active', 'leira-transients' ) . '</span>';
			}
			$timestamp = sprintf(
				'<time datetime="%s" class="published" data-format="%s">%s</time>',
				esc_attr( gmdate( 'Y-m-d\TH:i:s\Z', $expiration ) ),
				esc_attr( $format ),
				'' //esc_html( $formatted )
			);
		}

		return $timestamp . '<br>' . $status;
	}

	/**
	 * Render the search box.
	 * This method overrides the default search box to customize its appearance.
	 * Adds a hidden input for the current filter if one is set.
	 *
	 * @param  string  $text  The text to display in the search box.
	 * @param  string  $input_id  The ID attribute for the search input field.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function search_box( $text, $input_id ) {
		if ( empty( $_REQUEST['s'] ) && ! $this->has_items() ) {
			return;
		}

		parent::search_box( $text, $input_id );

		if ( ! empty( $_REQUEST['filter'] ) ) {
			echo '<input type="hidden" name="filter" value="' . esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['filter'] ) ) ) . '" />';
		}

		echo '<input type="hidden" name="page" value="leira-transients" />';
	}

	/**
	 * Get the views for the table.
	 * This defines the different views (e.g., all, active, expired) that can be filtered.
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public function get_views() {
		//All available filters
		$filters = [
			'all'        => __( 'All', 'leira-transients' ),
			'active'     => __( 'Active', 'leira-transients' ),
			'expired'    => __( 'Expired', 'leira-transients' ),
			'persistent' => __( 'Persistent', 'leira-transients' ),
		];

		//Selected filter, default to 'all'
		$current = isset( $_GET['filter'] ) ? sanitize_text_field( wp_unslash( $_GET['filter'] ) ) : 'all';
		$current = wp_unslash( $current );
		if ( ! in_array( $current, array_keys( $filters ), true ) ) {
			$current = 'all'; // Default to 'all' if the status is not recognized
		}

		//Base URL to create the links
		$base_url = admin_url( 'tools.php' );

		//Create the views
		$views = [];
		foreach ( $filters as $name => $label ) {
			//Class for the current view
			$class = ( $current === $name ) ? 'current' : '';
			//Create the URL for the view
			$url_params = array(
				'page'     => 'leira-transients',
				'filter'   => $name,
				'per_page' => $this->get_items_per_page( 'tools_page_leira_transients_per_page' ),
				//'paged'    => $this->get_pagenum(),
				's'        => isset( $_REQUEST['s'] ) ? esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) ) : '',
				'orderby'  => isset( $_GET['orderby'] ) ? esc_attr( sanitize_text_field( wp_unslash( $_GET['orderby'] ) ) ) : 'name',
				'order'    => isset( $_GET['order'] ) ? esc_attr( sanitize_text_field( wp_unslash( $_GET['order'] ) ) ) : 'desc',
			);
			$url        = add_query_arg( $url_params, $base_url );
			//Count the transients for the view
			$count = leira_transients()->transients->all( array(
				'filter' => $name,
				'count'  => true,
			) );
			//Create the view link
			$views[ $name ] = sprintf(
				'<a href="%s" class="%s">%s <span class="count">(%d)</span></a>',
				esc_url( $url ),
				$class,
				$label,
				$count
			);
		}

		return $views;
	}

	/**
	 * Get the query arguments for fetching data.
	 * This method retrieves the search, order, order_by, and filter parameters from the request.
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public function get_query_args() {
		return array(
			's'       => isset( $_REQUEST['s'] ) ? strtolower( sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) ) : '',
			'order'   => isset( $_GET['order'] ) ? strtolower( sanitize_text_field( wp_unslash( $_GET['order'] ) ) ) : 'asc',
			'orderby' => isset( $_GET['orderby'] ) ? strtolower( sanitize_text_field( wp_unslash( $_GET['orderby'] ) ) ) : 'name',
			'filter'  => isset( $_GET['filter'] ) ? strtolower( sanitize_text_field( wp_unslash( $_GET['filter'] ) ) ) : 'all'
		);
	}

	/**
	 * Handle row actions.
	 * This method is overridden to ensure proper handling of row actions.
	 *
	 * @param $item
	 * @param $column_name
	 * @param $primary
	 *
	 * @return string
	 */
	protected function handle_row_actions( $item, $column_name, $primary ) {
		$row_actions = '';

		if ( $column_name === $primary ) {
			$actions     = array(
				'inline hide-if-no-js' => sprintf(
					'<button type="button" class="button-link editinline" aria-label="%s" aria-expanded="false">%s</button>',
					/*
					 * translators: the transient name
					 */
					esc_attr( sprintf( __( 'Quick edit &#8220;%s&#8221;', 'leira-transients' ), $item['name'] ) ),
					esc_html( __( 'Quick&nbsp;Edit', 'leira-transients' ) )
				),
				'delete'               => sprintf( '<a href="%s" class="submitdelete" aria-label="%s">%s</a>',
					esc_url( wp_nonce_url( add_query_arg( [
						'action'      => 'delete',
						'action1'     => 'delete',
						'transient[]' => urlencode( $item['name'] ),
					] ), $this->get_wpnonce_action() ) ),
					__( 'Delete', 'leira-transients' ),
					__( 'Delete', 'leira-transients' )
				)
			);
			$row_actions = $this->row_actions( $actions, false );
		}

		return $row_actions;
	}


	/**
	 * Generate a single row of the table.
	 * This method is overridden to add custom classes and IDs to each row.
	 *
	 * @param $item
	 *
	 * @return void
	 */
	public function single_row( $item ) {
		$class = array( 'transient-tr' );
		$id    = isset( $item['name'] ) ? ( $this->_args['singular'] . '-' . $item['name'] ) : '';
		echo sprintf( '<tr id="%s" class="%s">', esc_html( $id ), esc_html( implode( ' ', $class ) ) );
		$this->single_row_columns( $item );
		echo '</tr>';
	}

	/**
	 * Prepare the items for display.
	 * This method fetches the data and prepares it for rendering in the table.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function prepare_items() {

		// Respect a user's per-page setting (Screen Options tab)
		$per_page = $this->get_items_per_page( 'tools_page_leira_transients_per_page' ); // Items per page
		$page     = $this->get_pagenum();                                                // Current page number

		//Calculate total items
		$args          = $this->get_query_args();
		$args['count'] = true;
		$total_items   = leira_transients()->transients->all( $args );
		$total_items   = is_numeric( $total_items ) ? (int) $total_items : 0;

		//Fetch items
		$args        = array_merge( $args, array(
			'count'    => false,
			'paged'    => $page,
			'per_page' => $per_page,
		) );
		$data        = leira_transients()->transients->all( $args );
		$this->items = $data;

		//Set pagination args
		$this->set_pagination_args( [
			'total_items' => $total_items,
			'per_page'    => $per_page,
			'total_pages' => ceil( $total_items / $per_page ),
		] );
	}

	/**
	 * Get the type of the transient value.
	 *
	 * This method analyzes the transient value and returns its type as a string.
	 *
	 * @param  mixed  $transient  The transient value to analyze. Can be a string or array.
	 *
	 * @return string The type of the transient value.
	 * @since 1.0.0
	 */
	public function get_transient_value_type( $transient ) {

		if ( is_array( $transient ) ) {
			$transient = isset( $transient['value'] ) ? $transient['value'] : '';
		}
		$transient = is_string( $transient ) ? $transient : '';

		// Default type
		$type = esc_html__( 'unknown', 'leira-transients' );

		// Try to un-serialize
		$value = maybe_unserialize( $transient );

		if ( is_array( $value ) ) {
			$type = esc_html__( 'array', 'leira-transients' );// Array
		} elseif ( is_object( $value ) ) {
			$type = esc_html__( 'object', 'leira-transients' );// Object
		} elseif ( is_serialized( $value ) ) {
			$type = esc_html__( 'serialized', 'leira-transients' );// Serialized array
		} elseif ( wp_strip_all_tags( $value ) !== $value ) {
			$type = esc_html__( 'html', 'leira-transients' );// HTML
		} elseif ( is_scalar( $value ) ) {
			// Scalar
			if ( is_numeric( $value ) ) {
				if ( 10 === strlen( $value ) ) {
					$type = esc_html__( 'timestamp?', 'leira-transients' );// Likely a timestamp
				} elseif ( in_array( $value, array( '0', '1' ), true ) ) {
					$type = esc_html__( 'boolean?', 'leira-transients' );// Likely a boolean
				} else {
					$type = esc_html__( 'numeric', 'leira-transients' );// Any number
				}
			} elseif ( is_string( $value ) && is_object( json_decode( $value ) ) ) {
				$type = esc_html__( 'json', 'leira-transients' );// JSON
			} else {
				$type = esc_html__( 'scalar', 'leira-transients' );// Scalar
			}
		} elseif ( empty( $value ) ) {
			$type = esc_html__( 'empty', 'leira-transients' );// Empty
		}

		// Return type
		return $type;
	}

	/**
	 * Outputs the hidden row displayed when inline editing
	 *
	 * @since 1.0.0
	 */
	public function inline_edit() {

		echo leira_transients()->admin->render_template(
			__DIR__ . '/inline-edit-form.php',
			array( 'table' => $this )
		);
	}
}
