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
 * Manage Open Badges in Badgr.
 */
class Badgr_CLI extends WP_CLI_Command {

	public function add_user( $args, $assoc_args ) {

		if ( count( $args ) != 4 ) {
			WP_CLI::error( 'Usage: add_user firstname lastname email password' );
		}

		if ( ! filter_var( $args[2], FILTER_VALIDATE_EMAIL ) ) {
			WP_CLI::error( 'Please provide a valid email as the 3rd argument' );
		}

		if ( strlen( $args[3] ) < 8 ) {
			WP_CLI::error( 'Please provide a password of at least 8 characters as the 4th argument' );
		}

		$slug = BadgrProvider::add_user( $args[0], $args[1], $args[2], $args[3] );

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

	public function update_issuer( $args, $assoc_args ) {
		if ( ! ( count( $args ) == 4 || count( $args ) == 5 ) ) {
			WP_CLI::error( 'Usage: update_issuer issuer_slug name email url [description]' );
		}

		if ( strlen( $args[0] ) < 1 ) {
			WP_CLI::error( 'Please provide an issuer slug as the 1st argument' );
		}

		if ( strlen( $args[1] ) < 1 ) {
			WP_CLI::error( 'Please provide an issuer name as the 2nd argument' );
		}

		if ( ! filter_var( $args[2], FILTER_VALIDATE_EMAIL ) ) {
			WP_CLI::error( 'Please provide an issuer email as the 3rd argument' );
		}

		if ( ! filter_var( $args[3], FILTER_VALIDATE_URL ) ) {
			WP_CLI::error( 'Please provide an issuer url as the 4th argument' );
		}

		if ( count( $args ) == 5 && strlen( $args[4] ) < 1 ) {
			WP_CLI::error( 'Please provide an issuer description as the 5th argument' );
		}

		if ( BadgrProvider::update_issuer( $args[0], $args[1], $args[2], $args[3], $args[4] ) ) {
			WP_CLI::success( 'Updated issuer with slug ' . $args[0] );
		} else {
			WP_CLI::error( 'Updating issuer failed.' );
		}
	}

	public function get_issuer_by_slug( $args, $assoc_args ) {
		if ( count( $args ) != 1 ) {
			WP_CLI::error( 'Usage: get_issuer_by_slug slug' );
		}

		if ( strlen( $args[0] ) < 1 ) {
			WP_CLI::error( 'Please provide an issuer slug as the 1st argument' );
		}

		$issuer = BadgrProvider::get_issuer_by_slug( $args[0] );

		if ( $issuer ) {
			WP_CLI::success( 'Issuer ' . $args[0] . ' ' . json_encode( $issuer ) );
		} else {
			WP_CLI::error( 'Fetching issuer with slug ' . $args[0] . ' failed.' );
		}
	}

	public function delete_issuer( $args, $assoc_args ) {
		if ( count( $args ) != 1 ) {
			WP_CLI::error( 'Usage: delete_issuer slug' );
		}

		if ( strlen( $args[0] ) < 1 ) {
			WP_CLI::error( 'Please provide an issuer slug as the 1st argument' );
		}

		WP_CLI::confirm( 'Are you sure you want to delete issuer ?' );

		if ( BadgrProvider::delete_issuer( $args[0] ) ) {
			WP_CLI::success( 'Issuer ' . $args[0] . ' successfully deleted.' );
		} else {
			WP_CLI::error( 'Deleting issuer with slug ' . $args[0] . ' failed.' );
		}
	}

	public function list_badge_classes( $args, $assoc_args ) {
		if ( count( $args ) != 0 ) {
			WP_CLI::error( 'Usage: list_badge_classes' );
		}

		$badge_classes = BadgrProvider::get_all_badge_classes(
			$params    = array(
				'elements_per_page' => -1,
			)
		);
		if ( false == $badge_classes ) {
			WP_CLI::error( 'Error retrieving badge classes' );
		}

		WP_CLI::success( 'Badge classes successfully retrieved : ' . json_encode( $badge_classes ) );
	}

	public function list_badge_classes_by_issuer_slug( $args, $assoc_args ) {
		if ( count( $args ) != 1 ) {
			WP_CLI::error( 'Usage: list_badge_classes_by_issuer issuer_slug' );
		}

		if ( strlen( $args[0] ) < 1 ) {
			WP_CLI::error( 'Please provide an issuer slug the 1st argument' );
		}

		$badge_classes = BadgrProvider::get_all_badge_classes_by_issuer_slug( $args[0], array( 'elements_per_page' => -1, ) );
		if ( false == $badge_classes ) {
			WP_CLI::error( 'Error retrieving badge classes' );
		}

		WP_CLI::success( 'Badge classes for issuer ' . $args[0] . 'successfully retrieved : ' . json_encode( $badge_classes ) );
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

	public function get_badge_class_by_slug( $args, $assoc_args ) {
		if ( count( $args ) != 1 ) {
			WP_CLI::error( 'Usage: get_badge_class_by_slug slug' );
		}

		if ( strlen( $args[0] ) < 1 ) {
			WP_CLI::error( 'Please provide a badge class slug as the 1st argument' );
		}

		$badge_class = BadgrProvider::get_badge_class_by_badge_class_slug( $args[0] );

		if ( $badge_class ) {
			WP_CLI::success( 'Badge class ' . $args[0] . ' ' . json_encode( $badge_class ) );
		} else {
			WP_CLI::error( 'Fetching badge class with slug ' . $args[0] . ' failed.' );
		}
	}

	public function update_badge_class( $args, $assoc_args ) {
		if ( ! ( count( $args ) == 3 || count( $args ) == 4 ) ) {
			WP_CLI::error( 'Usage: update_badge_class badge_class_slug name description [image_filename]' );
		}

		if ( strlen( $args[0] ) < 1 ) {
			WP_CLI::error( 'Please provide a badge_class_slug as the 1st argument' );
		}

		if ( strlen( $args[1] ) < 1 ) {
			WP_CLI::error( 'Please provide a name as the 2nd argument' );
		}

		if ( strlen( $args[2] ) < 1 ) {
			WP_CLI::error( 'Please provide a description as the 3rd argument' );
		}

		if ( count( $args ) == 4 && ( strlen( $args[3] ) < 1 || ! file_exists( $args[3] ) ) ) {
			WP_CLI::error( 'Please provide the name of an existing image file as the 4th argument' );
		}

		if ( BadgrProvider::update_badge_class( $args[0], $args[1], $args[2], $args[3] ) ) {
			WP_CLI::success( 'Updated badge class with slug ' . $args[0] );
		} else {
			WP_CLI::error( 'Updating badge class failed.' );
		}
	}

	public function delete_badge_class( $args, $assoc_args ) {
		if ( count( $args ) != 1 ) {
			WP_CLI::error( 'Usage: delete_badge_class slug' );
		}

		if ( strlen( $args[0] ) < 1 ) {
			WP_CLI::error( 'Please provide a badge class slug as the 1st argument' );
		}

		WP_CLI::confirm( 'Are you sure you want to delete badge class ?' );

		if ( BadgrProvider::delete_badge_class( $args[0] ) ) {
			WP_CLI::success( 'Badge class ' . $args[0] . ' successfully deleted.' );
		} else {
			WP_CLI::error( 'Deleting badge class with slug ' . $args[0] . ' failed.' );
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

	public function list_assertions_by_issuer( $args, $assoc_args ) {
		if ( count( $args ) != 1 ) {
			WP_CLI::error( 'Usage: list_assertions_by_issuer issuer_slug' );
		}

		if ( strlen( $args[0] ) < 1 ) {
			WP_CLI::error( 'Please provide an issuer slug the 1st argument' );
		}

		$badge_classes = BadgrProvider::get_all_assertions_by_issuer_slug( $args[0], array( 'elements_per_page' => -1, ) );
		if ( false == $badge_classes ) {
			WP_CLI::error( 'Error retrieving assertions' );
		}

		WP_CLI::success( 'Assertions for issuer ' . $args[0] . 'successfully retrieved : ' . json_encode( $badge_classes ) );
	}

	public function list_assertions_by_badge_class( $args, $assoc_args ) {
		if ( count( $args ) != 1 ) {
			WP_CLI::error( 'Usage: list_assertions_by_badge_class badge_class_slug' );
		}

		if ( strlen( $args[0] ) < 1 ) {
			WP_CLI::error( 'Please provide a badge class slug the 1st argument' );
		}

		$badge_classes = BadgrProvider::get_all_assertions_by_badge_class_slug( $args[0], array( 'elements_per_page' => -1, ) );
		if ( false == $badge_classes ) {
			WP_CLI::error( 'Error retrieving assertion' );
		}

		WP_CLI::success( 'Assertions for badge class ' . $args[0] . 'successfully retrieved : ' . json_encode( $badge_classes ) );
	}

	public function get_assertion_by_slug( $args, $assoc_args ) {
		if ( count( $args ) != 1 ) {
			WP_CLI::error( 'Usage: get_assertion_by_slug slug' );
		}

		if ( strlen( $args[0] ) < 1 ) {
			WP_CLI::error( 'Please provide an assertion slug as the 1st argument' );
		}

		$badge_class = BadgrProvider::get_assertion_by_assertion_slug( $args[0] );

		if ( $badge_class ) {
			WP_CLI::success( 'Assertion ' . $args[0] . ' ' . json_encode( $badge_class ) );
		} else {
			WP_CLI::error( 'Fetching assertion with slug ' . $args[0] . ' failed.' );
		}
	}

	public function revoke_assertion( $args, $assoc_args ) {
		if ( count( $args ) != 2 ) {
			WP_CLI::error( 'Usage: revoke_assertion slug reason' );
		}

		if ( strlen( $args[0] ) < 1 ) {
			WP_CLI::error( 'Please provide an assertion slug as the 1st argument' );
		}

		if ( strlen( $args[1] ) < 1 ) {
			WP_CLI::error( 'Please provide a reason for revocation as 2nd argument' );
		}

		WP_CLI::confirm( 'Are you sure you want to revoke assertion ?' );

		if ( BadgrProvider::revoke_assertion( $args[0], $args[1] ) ) {
			WP_CLI::success( 'Assertion ' . $args[0] . ' successfully revoked.' );
		} else {
			WP_CLI::error( 'Revoking assertion with slug ' . $args[0] . ' failed.' );
		}
	}

	public function force_auth( $args, $assoc_args ) {
		if ( count( $args ) != 3 ) {
			WP_CLI::error( 'Usage: force_auth access_token refresh_token token_expiration' );
		}

		if ( strlen( $args[0] ) < 1 ) {
			WP_CLI::error( 'Please provide an access token as the 1st argument' );
		}

		if ( strlen( $args[1] ) < 1 ) {
			WP_CLI::error( 'Please provide a refresh token as the 2nd argument' );
		}

		if ( strlen( $args[2] ) < 1 || ! filter_var( $args[2], FILTER_VALIDATE_INT ) || intval($args[2]) <= time() ) {
			WP_CLI::error( 'Please provide a timestamp date later than current time as the 3rd argument' );
		}

		$options = get_option( 'badgefactor2_badgr_settings' );
		$options['badgr_server_access_token'] = $args[0];
		$options['badgr_server_refresh_token'] = $args[1];
		$options['badgr_server_token_expiration'] = $args[2];
		update_option( 'badgefactor2_badgr_settings', $options );

		WP_CLI::success( 'Default auth values updated.');
	}

	public function view_current_default_client_profile ($args, $assoc_args) {
		if ( count( $args ) != 0 ) {
			WP_CLI::error( 'Usage: view_current_default_client_profile' );
		}

		$client_profile = BadgrProvider::get_profile_associated_to_client_in_use();

		if ( $client_profile ) {
			WP_CLI::success( 'Current profile: ' . json_encode( $client_profile ) );
		} else {
			WP_CLI::error( 'Failed getting current profile' );
		}
	}

	public function mark_existing_users_for_migration( $args, $assoc_args ) {
		if ( count( $args ) != 0 ) {
			WP_CLI::error( 'Usage: mark_existing_users_for_migration' );
		}

		$count = BadgrUser::mark_existing_users_for_migration();
		if ( false ===  $count ) {
			WP_CLI::error( 'Marking users for migration failed' );
		} else {
			WP_CLI::success( 'Finished marking user for migration: ' . $count . ' users marked' );
		}
	}
}
