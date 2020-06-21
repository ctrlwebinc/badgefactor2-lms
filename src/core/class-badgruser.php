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

	protected static $user_meta_key_for_client = 'badgr_client_instance';
	
	protected $wp_user = null;
	protected $user_client = null;

	function __construct ( WP_User $wp_user) {
		$this->wp_user = $wp_user;
		$this->get_client_from_user_meta();

		// TODO considering making if no client in user meta
	}

	public static function make_from_user_id ( int $wp_user_id) {
		return new self( new WP_User( $wp_user_id ) );
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

	/* 	// No existing client, make a new one
		$badgr_site_settings = get_option( 'badgefactor2_badgr_settings' );

		// Basic parameters
		$basicParameters['username'] = $wp_user->user_email;
		$basicParameters['as_admin'] = is_admin();
		$basicParameters['badgr_server_flavor'] = $badgr_site_settings['badgr_server_flavour'];

		// Set urls by convention or with custom settings depending on server flavour
		if ( $badgr_site_settings['badgr_server_flavour'] == self::FLAVOR_BADGRIO_01 ) {
			$basicParameters['badgr_server_public_url']  = self::BADGR_IO_URL;
		} elseif ( $badgr_site_settings['badgr_server_flavour'] == self::FLAVOR_LOCAL_R_JAMIROQUAI ) {
			$basicParameters['badgr_server_public_url']  = site_url() . ':' . self::DEFAULT_LOCAL_BADGR_SERVER_PORT;
		} else {
			// Custom
			$basicParameters['badgr_server_public_url'] = $badgr_site_settings['badgr_server_public_url'];
			if ( null !== $badgr_site_settings['badgr_server_internal_url'] ) {
				$basicParameters['badgr_server_internal_url'] = $badgr_site_settings['badgr_server_internal_url'];
			}
		}

		// If not badgr io, get client_id
		if ( $badgr_site_settings['badgr_server_flavour'] != self::FLAVOR_BADGRIO_01 ) {
			$basicParameters['client_id']  = $badgr_site_settings['client_id'];
		}

		// If not password grant, get client_secret
		if ( $badgr_site_settings['badgr_authentication_process_select'] != self::GRANT_PASSWORD ) {
			$basicParameters['badgr_server_client_secret']  = $badgr_site_settings['badgr_server_client_secret'];
		} */

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
		$slug      = BadgrProvider::add_user( $user_data->first_name, $user_data->last_name, $user_data->user_email );

		// If successful set badgr user state to 'created' and save slug.
		if ( false !== $slug ) {
			update_user_meta( $user_id, 'badgr_user_slug', $slug );
			update_user_meta( $user_id, 'badgr_user_state', 'created' );
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
}
