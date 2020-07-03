<?php
/**
 * Badge Factor 2
 * Copyright (C) 2019 ctrlweb
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * @package Badge_Factor_2
 */

namespace BadgeFactor2;

use BadgeFactor2\BadgrClient;
use \WP_User;

/**
 * BadgrUser Class.
 */
class BadgrUser {

	public static $user_meta_key_for_client = 'badgr_client_instance';
	public static $options_key_for_badgr_admin = 'badgefactor2_badgr_admin';
	
	protected $wp_user = null;
	protected $user_client = null;

	function __construct ( WP_User $wp_user) {
		$this->wp_user = $wp_user;
		$this->get_client_from_user_meta();

		// TODO considering making if no client in user meta
	}

	public static function get_admin_instance() {
		$admin_instance = get_option( self::$options_key_for_badgr_admin);

		if ( false !== $admin_instance && '' != $admin_instance) {
			return $admin_instance;
		}

		return null;
	}

	public function set_as_admin_instance( ) {
		update_option( self::$options_key_for_badgr_admin, $this);
	}

	public static function make_from_user_id ( int $wp_user_id) {
		return new self( new WP_User( $wp_user_id ) );
	}

	public function get_wp_username( ) {
		return $this->wp_user->user_nicename;
	}

	protected function get_client_from_user_meta () {
		$this->user_client = get_user_meta( $this->wp_user->ID, self::$user_meta_key_for_client, true );
	}
	
	public function save_client () {
		if ( null !== $this->user_client ) {
			update_user_meta( $this->wp_user->ID, self::$user_meta_key_for_client, $this->user_client);
		}
	}

	public function set_client( BadgrClient $badgr_client) {
		$badgr_client->badgr_user = $this;
		$this->user_client = $badgr_client;
		$this->save_client();
	}

	public function get_client () {
		return $this->user_client;
	}

	public function is_same_user( BadgrUser $other_badgr_user ) {
		if ( $this->wp_user->ID == $other_badgr_user->wp_user->ID ) {
			return true;
		}

		return false;
	}

	public static function getOrMakeUserClient( WPUser $wp_user = null ) {

		// If no user passed, use the current user
		if ( null === $wp_user ) {
			$wp_user = wp_get_current_user();
			if ( $wp_user->ID == 0) {
				throw new \Exception('Can\'t determine user for client creation');
			}
		}
		// Look in user metas for existing client
		// TODO Transfer responsibility of user client fetching to BadgrUser
		$client = get_user_meta( $wp_user->ID, self::$user_meta_key_for_client, true );

		if ( null!== $client && '' !== $client) {
			return $client;
		}

		// Make client
		$client = BadgrClient::makeClientFromSavedOptions();

		// Set user
		$badgr_user = new BadgrUser( $wp_user );
		$badgr_user->set_client( $client );

		return $client;

	}

	/**
	 * BadgrUser Init.
	 *
	 * @return void
	 */
	public static function init_hooks() {
		add_action( 'init', array( BadgrUser::class, 'init' ), 9966 );
		add_action( 'cmb2_admin_init', array( BadgrUser::class, 'cmb2_admin_init' ) );
	}

	/**
	 * Init Hooks.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'user_register', array( BadgrUser::class, 'new_user_registers' ), 9966 );
		add_action( 'profile_update', array( BadgrUser::class, 'update_user' ), 9966 );
		add_action( 'wp_authenticate', array( BadgrUser::class, 'keep_passwords_synched' ), 9966 );
	}

	public static function keep_passwords_synched( $username ) {

		// This hook seems to also be called during logout. Username is then null

		if ( null == $username ) {
			// It is a logout operation, nothing needs doing for us
			return;
		}

		$password_from_login = $_POST['pwd'];

		// check if password is valid: if not return
		$auth_result = wp_authenticate( $username, $password_from_login);
		if ( is_wp_error( $auth_result )) {
			return;
		}

		// Password is valid

		// Check if password coresponds to current password
		$current_password = get_user_meta( $auth_result->ID, 'badgr_password', true );
		if ( $current_password === $auth_result) {
			// It does, return
			return;
		}
		// It doesn't, so change password
		$user_slug = get_user_meta( $auth_result->ID, 'badgr_user_slug', true );

		// Perform password change using old and new
		if ( false !== BadgrProvider::change_user_password( $user_slug, $current_password, $password_from_login)) {
			// Record the new one as the old one
			update_user_meta( $auth_result->ID, 'badgr_password', $password_from_login);

		}

	}

	/**
	 * CMB2 Admin Init hook.
	 *
	 * @return void
	 */
	public static function cmb2_admin_init() {
		// TODO.
	}

	/**
	 * User registration hook.
	 *
	 * @param int $user_id User ID.
	 * @return void
	 */
	public static function new_user_registers( $user_id ) {
		// Set badgr user state to 'to_be_created'.
		update_user_meta( $user_id, 'badgr_user_state', 'to_be_created' );

		// Add user to badgr.
		$user_data = get_userdata( $user_id );
		$temporary_password = self::generate_random_password();
		$slug      = BadgrProvider::add_user( $user_data->first_name, $user_data->last_name, $user_data->user_email, $temporary_password );

		// If successful set badgr user state to 'created' and save slug and save previous password.
		if ( false !== $slug ) {
			update_user_meta( $user_id, 'badgr_user_slug', $slug );
			update_user_meta( $user_id, 'badgr_user_state', 'created' );
			update_user_meta( $user_id, 'badgr_password', $temporary_password);
		}
	}

	/**
	 * Profile update hook.
	 *
	 * @param int $user_id User ID.
	 * @return void
	 */
	public static function update_user( $user_id ) {
		$badgr_user_state = get_user_meta( $user_id, 'badgr_user_state', true );
		if ( null !== $badgr_user_state && 'created' === $badgr_user_state ) {
			$user   = get_userdata( $user_id );
			$result = BadgrProvider::update_user(
				get_user_meta( $user->ID, 'badgr_user_slug', true ),
				$user->first_name,
				$user->last_name,
				$user->user_email
			);
		}
	}

	/**
	 * Confirms whether or not current user is verified.
	 *
	 * @return boolean
	 */
	public static function confirm_current_user_verified() {
		// If the user already has the capability, just return.
		if ( current_user_can( 'badgefactor2_use_badgr' ) ) {
			return true;
		}

		// If the user doesn't yet have the capability, check at badgr server.
		$user             = wp_get_current_user();
		$badgr_user_state = get_user_meta( $user->ID, 'badgr_user_state', true );
		if ( null !== $badgr_user_state && 'created' === $badgr_user_state ) {
			$is_verified = BadgrProvider::check_user_verified( get_user_meta( $user->ID, 'badgr_user_slug', true ) );
			if ( true === $is_verified ) {
				$user->add_cap( 'badgefactor2_use_badgr' );
				return true;
			}
		}

		return false;
	}

	/**
	 * Generates a random password.
	 *
	 * @return string Randomly generated password.
	 */
	protected static function generate_random_password() {
		$alphabet        = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
		$pass            = array( 'p' ); // Start with a letter.
		$alpha_max_index = strlen( $alphabet ) - 1;
		for ( $i = 0; $i < 11; $i++ ) {
			$n      = rand( 0, $alpha_max_index );
			$pass[] = $alphabet[ $n ];
		}
		return implode( $pass );
	}
}
