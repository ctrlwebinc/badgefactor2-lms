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

/**
 * BadgrUser Class.
 */
class BadgrUser {

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

	// Get the user's specific client
	public function get_client () {
		throw new \Exception( 'Not yet implemented.');
	}

}
