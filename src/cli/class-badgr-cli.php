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

use WP_CLI;
use WP_CLI_Command;

WP_CLI::add_command( 'badgr', Badgr_CLI::class );

/**
 * Manage Open Badges.
 */
class Badgr_CLI extends WP_CLI_Command {

	public function add_user( $args, $assoc_args ) {

		if ( count( $args ) != 3 ) {
			WP_CLI::error( 'Usage: add_user firstname lastname email' );
		}

		if ( ! filter_var( $args[2], FILTER_VALIDATE_EMAIL ) ) {
			WP_CLI::error( 'Please provide a valid email as the 3rd argument' );
		}

		$slug = BadgrProvider::add_user( $args[0], $args[1], $args[2] );

		if ( $slug ) {
			WP_CLI::success( 'User added with slug ' . $slug );
		} else {
			WP_CLI::error( 'Adding user failed.' );
		}
	}

	public function check_user_verified( $args, $assoc_args ) {
		if ( count( $args ) != 1 ) {
			WP_CLI::error( 'Usage: check_user_verified slug' );
		}

		$verified = BadgrProvider::check_user_verified( $args[0] );

		if ( $verified ) {
			WP_CLI::success( 'User is verified' );
		} else {
			WP_CLI::success( 'User is not verified' );
		}
	}

	public function get_user_badgr_info( $args, $assoc_args ) {
		if ( count( $args ) != 1 ) {
			WP_CLI::error( 'Usage: get_user_badgr_info user_id' );
		}

		$user = get_userdata( $args[0] );

		if ( $user == false ) {
			WP_CLI::error( 'No such user ' . $args[0] );
		}

		$state = get_user_meta( $user->ID, 'badgr_user_state', true );
		$slug  = get_user_meta( $user->ID, 'badgr_user_slug', true );

		WP_CLI::success( sprintf( 'User %s has state %s and slug %s', $args[0], $state, $slug ) );
	}

	public function list_issuers( $args, $assoc_args ) {
		if ( count( $args ) != 0 ) {
			WP_CLI::error( 'Usage: list_issuers' );
		}

		$issuers = BadgrProvider::get_all_issuers();
		if ( false == $issuers ) {
			WP_CLI::error( 'Error retrieving issuers' );
		}

		WP_CLI::success( 'Issuers successfully retrieved : ' . json_encode( $issuers ) );
	}

	public function add_issuer( $args, $assoc_args ) {

		if ( count( $args ) != 4 ) {
			WP_CLI::error( 'Usage: add_issuer name email url description' );
		}

		if ( ! filter_var( $args[1], FILTER_VALIDATE_EMAIL ) ) {
			WP_CLI::error( 'Please provide a valid email as the 2nd argument' );
		}

		if ( ! filter_var( $args[2], FILTER_VALIDATE_URL ) ) {
			WP_CLI::error( 'Please provide a valid url as the 3rd argument' );
		}

		if ( strlen( $args[3] ) < 1 ) {
			WP_CLI::error( 'Please provide a description as the 4th argument' );
		}

		$slug = BadgrProvider::add_issuer( $args[0], $args[1], $args[2], $args[3] );

		if ( $slug ) {
			WP_CLI::success( 'Issuer added with slug ' . $slug );
		} else {
			WP_CLI::error( 'Adding issuer failed.' );
		}
	}

	public function add_badge_class( $args, $assoc_args ) {
		if ( count( $args ) != 4 ) {
			WP_CLI::error( 'Usage: add_badge_class name issuer_slug description image_filename' );
		}

		if ( strlen( $args[0] ) < 1 ) {
			WP_CLI::error( 'Please provide a name as the 1st argument' );
		}

		if ( strlen( $args[1] ) < 1 ) {
			WP_CLI::error( 'Please provide an issuer slug as the 2nd argument' );
		}

		if ( strlen( $args[2] ) < 1 ) {
			WP_CLI::error( 'Please provide a description as the 3rd argument' );
		}

		if ( strlen( $args[3] ) < 1 || ! file_exists( $args[3] ) ) {
			WP_CLI::error( 'Please provide the name of an existing image file as the 4th argument' );
		}

		$slug = BadgrProvider::add_badge_class( $args[0], $args[1], $args[2], $args[3] );

		if ( $slug ) {
			WP_CLI::success( 'Badge class added with slug ' . $slug );
		} else {
			WP_CLI::error( 'Adding badge class failed.' );
		}
	}

	public function add_assertion( $args, $assoc_args ) {
		if ( count( $args ) != 3 ) {
			WP_CLI::error( 'Usage: add_assertion issuer_slug badge_class_slug recipient_identity' );
		}

		if ( strlen( $args[0] ) < 1 ) {
			WP_CLI::error( 'Please provide an issuer slug as the 1st argument' );
		}

		if ( strlen( $args[1] ) < 1 ) {
			WP_CLI::error( 'Please provide a badge class slug as the 2nd argument' );
		}

		if ( strlen( $args[2] ) < 1 || ! filter_var( $args[2], FILTER_VALIDATE_EMAIL ) ) {
			WP_CLI::error( 'Please provide a recipient identity (email) as the 3rd argument' );
		}

		$slug = BadgrProvider::add_assertion( $args[0], $args[1], $args[2] );

		if ( $slug ) {
			WP_CLI::success( 'Assertion added with slug ' . $slug );
		} else {
			WP_CLI::error( 'Adding assertion failed.' );
		}
	}
}
