<?php
/*
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
 */

/**
 * @package Badge_Factor_2
 */

namespace BadgeFactor2;

class BadgrUser {

	public static function init_hooks() {
		add_action( 'init', array( BadgrUser::class, 'init' ), 9966 );
		add_action( 'cmb2_admin_init', array( BadgrUser::class, 'cmb2_admin_init' ) );
	}

	public static function init() {
		add_action( 'user_register', array( BadgrUser::class, 'new_user_registers' ), 9966 );
	}

	public static function cmb2_admin_init() {

	}

	public static function new_user_registers($user_id) {
		// Set badgr user state to 'to_be_created'
		update_user_meta( $user_id, 'badgr_user_state', 'to_be_created');

		// Add user to badgr
		$user_data = get_userdata( $user_id );
		$slug = BadgrProvider::addUser($user_data->first_name, $user_data->last_name, $user_data->user_email);

		// If successful set badgr user state to 'created'
		if ($slug != false ) {
			update_user_meta( $user_id, 'badgr_user_slug', $slug);
			update_user_meta( $user_id, 'badgr_user_state', 'created');
		}
	}

}
