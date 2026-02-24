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
class Transients{
	/**
	 * The database connection instance.
	 *
	 * @since 1.0.0
	 * @var \WPDB
	 */
	protected $db;

	/**
	 * Manager constructor.
	 *
	 * Initializes the database connection using the global $wpdb object.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		global $wpdb;
		$this->db = $wpdb;
	}

	/**
	 * Get all transients from the database.
	 *
	 * @param  array{
	 *     s?: string,
	 *     order?: string,
	 *     order_by?: string,
	 *     filter?: string,
	 *     count?: bool,
	 *     paged?: int,
	 *     per_page?: int
	 * }  $arg  Optional arguments to filter the transients.
	 *
	 * @return array An associative array of transients, where each key is the transient name and the value is an array containing 'name', 'value', and 'expiration'.
	 * @since 1.0.0
	 */
	public function all( $arg = [] ) {

		//Default values
		$defaults = array(
			's'        => '',
			'order'    => 'asc',
			'orderby'  => 'name',
			'filter'   => 'all',
			'count'    => false,
			'paged'    => 1,
			'per_page' => 20
		);
		$args     = wp_parse_args( $arg, $defaults );

		$sql = [];

		/**
		 * SELECT
		 */
		$sql[]     = 'SELECT';
		$count_arg = $args['count'] ?? false;
		$count     = (bool) $count_arg;
		if ( $count ) {
			if ( $args['count'] === 'views' ) {
				$sql[] = implode( ', ', [
					'COUNT(*) AS `all`',
					'SUM(CASE WHEN expiration > UNIX_TIMESTAMP() THEN 1 ELSE 0 END) AS `active`',
					'SUM(CASE WHEN expiration <= UNIX_TIMESTAMP() THEN 1 ELSE 0 END) AS `expired`',
					'SUM(CASE WHEN expiration IS NULL THEN 1 ELSE 0 END) AS `persistent`'
				] );
			} else {
				$sql[] = 'COUNT(*) AS total';
			}
		} else {
			$sql[] = '*';
		}
		/**
		 * FROM
		 */
		$sql[] = 'FROM';
		$sql[] = '(';
		$types = [ '_transient', '_site_transient' ];

		foreach ( $types as $type ) {
			/**
			 * SELECT
			 */
			$subquery   = [ 'SELECT' ];
			$subquery[] = array(
				'o.option_id AS `id`,',
				'o.option_name AS `name`,',
				'o.option_value AS `value`,',
				't.option_value AS `expiration`'
			);

			/**
			 * FROM
			 */
			$subquery[] = "FROM {$this->db->options} o";

			/**
			 * JOIN
			 */
			$substring        = $type . '_timeout_';
			$substring_length = strlen( $type ) + 2;
			$subquery[]       = "LEFT JOIN {$this->db->options} t ON t.option_name = CONCAT('$substring', SUBSTRING(o.option_name,$substring_length))";

			/**
			 * WHERE
			 */
			$like_prefix    = str_replace( '_', '\\_', $type );
			$like_transient = $like_prefix . '\\_%';
			$like_timeout   = $like_prefix . '\\_timeout\\_%';
			$subquery[]     = "WHERE o.option_name LIKE '$like_transient' AND o.option_name NOT LIKE '$like_timeout'";

			//Search
			$search = isset( $args['s'] ) ? trim( $args['s'] ) : '';
			if ( ! empty( $search ) ) {
				$search            = $this->db->esc_like( $search );
				$search_like_trans = $like_transient . $search . '%';
				$statement         = "AND (o.option_name LIKE %s)";
				$subquery[]        = $this->db->prepare( $statement, $search_like_trans );
			}

			// Filter by expiration status
			// 'all' for all transients, 'active' for those that have not expired, and 'expired' for those that have.
			// Default is 'all'.
			$filter = isset( $args['filter'] ) ? $args['filter'] : 'all';
			$filter = in_array( $filter, array( 'all', 'active', 'expired', 'persistent' ), true ) ? $filter : 'all';
			if ( 'active' === $filter ) {
				$subquery[] = "AND t.option_value > UNIX_TIMESTAMP()";
			} elseif ( 'expired' === $filter ) {
				$subquery[] = "AND t.option_value <= UNIX_TIMESTAMP()";
			} elseif ( 'persistent' === $filter ) {
				$subquery[] = "AND t.option_value IS NULL";
			}

			$sql[] = $subquery;
			if ( end( $types ) != $type ) {
				$sql[] = 'UNION ALL ';
			}
		}

		$sql[] = ') AS transients';

		//Only add ORDER BY and LIMIT if COUNT is not present
		if ( ! $count ) {
			/**
			 * ORDER BY
			 */
			$order_by = isset( $args['orderby'] ) ? $args['orderby'] : 'name';
			$order_by = in_array( $order_by, array( 'name', 'value', 'expiration' ), true ) ? $order_by : 'name';
			$order    = isset( $args['order'] ) ? strtoupper( $args['order'] ) : 'DESC';
			$order    = in_array( $order, array( 'ASC', 'DESC' ), true ) ? $order : 'DESC';

			$sql[] = "ORDER BY $order_by $order";

			/**
			 * LIMIT
			 */
			$page     = isset( $args['paged'] ) ? max( 1, absint( (int) $args['paged'] ) ) : 1;
			$per_page = isset( $args['per_page'] ) ? max( 1, absint( (int) $args['per_page'] ) ) : 25;
			$sql[]    = $this->db->prepare( "LIMIT %d, %d", ( $page - 1 ) * $per_page, $per_page );
		}

		// Combine the SQL parts
		$query = [];
		array_walk_recursive( $sql, function ( $value ) use ( &$query ) {
			$query[] = $value;
		} );
		$query = implode( "\n", $query );

		// Query
		if ( empty( $count ) ) {
			$transients = $this->db->get_results( $query, ARRAY_A );
		} elseif ( $count_arg == 'views' ) {
			$transients = $this->db->get_row( $query, ARRAY_A );
		} else {
			$transients = $this->db->get_var( $query );
		}

		// Return transients
		return $transients;
	}

	/**
	 * Get a single transient value.
	 *
	 * @param  string  $name  The name of the transient to retrieve.
	 *
	 * @return mixed The value of the transient, or false if it does not exist or has expired.
	 * @since 1.0.0
	 */
	public function get( $name ) {
		$transient = $this->validate_name( $name );

		return $this->is_site_transient( $name ) ? get_site_transient( $transient ) : get_transient( $transient );
	}

	/**
	 * Update a transient value.
	 * If we try to update a transient with a new expiration time,
	 * maintaining the original value, this method will update the expiration time but will return false.
	 * This is documented in the WordPress core: wp-includes/option.php
	 *
	 * @param  string  $name  The name of the transient to update.
	 * @param  mixed  $value  The value to set for the transient.
	 * @param  int  $expiration  The expiration time in seconds. Default is 0, which means the transient will not expire.
	 *
	 * @return bool True on success, false on failure.
	 * @since 1.0.0
	 */
	public function set( $name, $value, $expiration = 0 ) {
		$transient = $this->validate_name( $name );

		return $this->is_site_transient( $name ) ?
			set_site_transient( $transient, $value, $expiration ) :
			set_transient( $transient, $value, $expiration );
	}

	/**
	 * Delete a transient.
	 *
	 * @param  array|string  $name  The name of the transient to delete.
	 *
	 * @return bool True on success, false on failure.
	 * @since 1.0.0
	 */
	public function delete( $name ) {
		if ( is_array( $name ) ) {
			foreach ( $name as $item ) {
				$deleted = $this->delete( $item );
			}

			return true;
		}

		if ( is_string( $name ) ) {
			$transient = $this->validate_name( $name );

			return $this->is_site_transient( $name ) ? delete_site_transient( $transient ) : delete_transient( $transient );
		}

		return false;
	}

	/**
	 * Check if a transient is a site transient.
	 *
	 * @param  string  $name  The name of the transient to check.
	 *
	 * @return bool True if the transient is a site transient, false otherwise.
	 * @since 1.0.0
	 */
	public function is_site_transient( $name ) {
		if ( ! is_string( $name ) ) {
			return false;
		}

		return str_starts_with( $name, '_site_transient_' );
	}

	/**
	 * Validate and sanitize the transient name.
	 *
	 * @param  string  $name  The name of the transient to validate.
	 *
	 * @return string The validated and sanitized transient name.
	 * @since 1.0.0
	 */
	public function validate_name( $name ) {
		$needles = array( '_transient_', '_site_transient_' );

		foreach ( $needles as $needle ) {
			if ( str_starts_with( $name, $needle ) ) {
				$name = substr( $name, strlen( $needle ) );
				break;
			}
		}

		return $name;
	}
}
