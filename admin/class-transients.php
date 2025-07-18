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
	 * @return array An associative array of transients, where each key is the transient name and the value is an array containing 'name', 'value', and 'expiration'.
	 * @since 1.0.0
	 */
	public function all() {
		$sql = "
            SELECT *
            FROM {$this->db->options}
            WHERE option_name LIKE '_transient_%'
        ";

		$results = $this->db->get_results( $sql );

		$transients = [];

		foreach ( $results as $row ) {
			$name       = preg_replace( '/^_transient_timeout_|^_transient_/', '', $row->option_name );
			$is_timeout = str_starts_with( $row->option_name, '_transient_timeout_' );

			if ( ! isset( $transients[ $name ] ) ) {
				$transients[ $name ] = [
					'name'       => $name,
					'value'      => null,
					'expiration' => null,
				];
			}

			if ( $is_timeout ) {
				$transients[ $name ]['expiration'] = (int) $row->option_value;
			} else {
				$transients[ $name ]['value'] = maybe_unserialize( $row->option_value );
			}
		}

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
		return get_transient( $name );
	}

	/**
	 * Update a transient value.
	 *
	 * @param  string  $name  The name of the transient to update.
	 * @param  mixed  $value  The value to set for the transient.
	 * @param  int  $expiration  The expiration time in seconds. Default is 0, which means the transient will not expire.
	 *
	 * @return bool True on success, false on failure.
	 * @since 1.0.0
	 */
	public function update( $name, $value, $expiration = 0 ) {
		return set_transient( $name, $value, $expiration );
	}

	/**
	 * Delete a transient.
	 *
	 * @param  string  $name  The name of the transient to delete.
	 *
	 * @return bool True on success, false on failure.
	 * @since 1.0.0
	 */
	public function delete( $name ) {
		return delete_transient( $name );
	}
}
