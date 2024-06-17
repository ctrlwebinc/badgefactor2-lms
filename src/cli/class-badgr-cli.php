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

use BadgeFactor2\Helpers\Migration;
use WP_CLI;
use WP_CLI_Command;
use BadgeFactor2\Models\Issuer;
use BadgeFactor2\Models\BadgeClass;
use BadgeFactor2\Models\Assertion;
use BadgeFactor2\BadgrUser;

WP_CLI::add_command( 'badgr', Badgr_CLI::class );

/**
 * Manage Open Badges in Badgr through WP CLI.
 */
class Badgr_CLI extends WP_CLI_Command {

	/**
	 * Adds user.
	 *
	 * @param array $args Arguments.
	 * @param array $assoc_args Associative Arguments.
	 * @return void
	 */
	public function add_user( $args, $assoc_args ) {

		if ( count( $args ) !== 4 ) {
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


	/**
	 * Check if user is verified.
	 *
	 * @param array $args Arguments.
	 * @param array $assoc_args Associative Arguments.
	 * @return void
	 */
	public function check_user_verified( $args, $assoc_args ) {
		if ( count( $args ) !== 1 ) {
			WP_CLI::error( 'Usage: check_user_verified slug' );
		}

		$verified = BadgrProvider::check_user_verified( $args[0] );

		if ( $verified ) {
			WP_CLI::success( 'User is verified' );
		} else {
			WP_CLI::success( 'User is not verified' );
		}
	}


	/**
	 * Get user badgr information.
	 *
	 * @param array $args Arguments.
	 * @param array $assoc_args Associative Arguments.
	 * @return void
	 */
	public function get_user_badgr_info( $args, $assoc_args ) {
		if ( count( $args ) !== 1 ) {
			WP_CLI::error( 'Usage: get_user_badgr_info user_id' );
		}

		$user = get_userdata( $args[0] );

		if ( false === $user ) {
			WP_CLI::error( 'No such user ' . $args[0] );
		}

		$state = get_user_meta( $user->ID, 'badgr_user_state', true );
		$slug  = get_user_meta( $user->ID, 'badgr_user_slug', true );

		WP_CLI::success( sprintf( 'User %s has state %s and slug %s', $args[0], $state, $slug ) );
	}

		/**
	 * Get user badgr information.
	 *
	 * @param array $args Arguments.
	 * @param array $assoc_args Associative Arguments.
	 * @return void
	 */
	public function check_if_user_has_badgr_verfied_email( $args, $assoc_args ) {
		if ( count( $args ) !== 1 ) {
			WP_CLI::error( 'Usage: check_if_user_has_badgr_verfied_email user_id' );
		}

		$user = get_userdata( $args[0] );

		if ( false === $user ) {
			WP_CLI::error( 'No such user ' . $args[0] );
		}

		if ( true === ( new BadgrUser( $user ) )->check_if_user_has_verified_email() ) {
			WP_CLI::success( sprintf( 'User %s has a verified email in Badgr', $args[0]) );
		} else {
			WP_CLI::success( sprintf( 'User %s doesn\'t have a verified email in Badgr', $args[0]) );
		}
	}


	/**
	 * List Issuers.
	 *
	 * @param array $args Arguments.
	 * @param array $assoc_args Associative Arguments.
	 * @return void
	 */
	public function list_issuers( $args, $assoc_args ) {
		if ( count( $args ) !== 0 ) {
			WP_CLI::error( 'Usage: list_issuers' );
		}

		$issuers = BadgrProvider::get_all_issuers();
		if ( false === $issuers ) {
			WP_CLI::error( 'Error retrieving issuers' );
		}

		WP_CLI::success( 'Issuers successfully retrieved : ' . json_encode( $issuers ) );
	}


	/**
	 * Add Issuer.
	 *
	 * @param array $args Arguments.
	 * @param array $assoc_args Associative Arguments.
	 * @return void
	 */
	public function add_issuer( $args, $assoc_args ) {

		if ( !( count( $args ) == 4 || count( $args ) == 5 ) ) {
			WP_CLI::error( 'Usage: add_issuer name email url description [image_filename]' );
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

		if ( count( $args ) == 5 && ( strlen( $args[4] ) < 1 || ! file_exists( $args[4] ) ) ) {
			WP_CLI::error( 'Please provide the name of an existing image file as the 5th argument' );
		}

		$slug = BadgrProvider::add_issuer( $args[0], $args[1], $args[2], $args[3], $args[4] ?? null );

		if ( $slug ) {
			WP_CLI::success( 'Issuer added with slug ' . $slug );
		} else {
			WP_CLI::error( 'Adding issuer failed.' );
		}
	}


	/**
	 * Update Issuer.
	 *
	 * @param array $args Arguments.
	 * @param array $assoc_args Associative Arguments.
	 * @return void
	 */
	public function update_issuer( $args, $assoc_args ) {
		if ( ! ( count( $args ) === 4 || count( $args ) === 5 ) || ( count( $assoc_args) == 1 && !isset($assoc_args['image']) ) ) {
			WP_CLI::error( 'Usage: update_issuer issuer_slug name email url [description] [--image=image_filename]' );
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

		if ( count( $args ) === 5 && strlen( $args[4] ) < 1 ) {
			WP_CLI::error( 'Please provide an issuer description as the 5th argument' );
		}

		if ( isset($assoc_args['image']) && ( strlen( $assoc_args['image'] ) < 1 || ! file_exists( $assoc_args['image'] ) ) ) {
			WP_CLI::error( 'Please provide the name of an existing image file as the argument to --image=' );
		}

		if ( BadgrProvider::update_issuer( $args[0], $args[1], $args[2], $args[3], $args[4] ?? null, $assoc_args['image'] ?? null ) ) {
			WP_CLI::success( 'Updated issuer with slug ' . $args[0] );
		} else {
			WP_CLI::error( 'Updating issuer failed.' );
		}
	}


	/**
	 * Get issuer by slug.
	 *
	 * @param array $args Arguments.
	 * @param array $assoc_args Associative Arguments.
	 * @return void
	 */
	public function get_issuer_by_slug( $args, $assoc_args ) {
		if ( count( $args ) !== 1 ) {
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


	/**
	 * Delete issuer.
	 *
	 * @param array $args Arguments.
	 * @param array $assoc_args Associative Arguments.
	 * @return void
	 */
	public function delete_issuer( $args, $assoc_args ) {
		if ( count( $args ) !== 1 ) {
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


	/**
	 * List BadgeClasses.
	 *
	 * @param array $args Arguments.
	 * @param array $assoc_args Associative Arguments.
	 * @return void
	 */
	public function list_badge_classes( $args, $assoc_args ) {
		if ( count( $args ) !== 0 ) {
			WP_CLI::error( 'Usage: list_badge_classes' );
		}

		$params        = array( 'elements_per_page' => -1 );
		$badge_classes = BadgrProvider::get_all_badge_classes( $params );
		if ( false === $badge_classes ) {
			WP_CLI::error( 'Error retrieving badge classes' );
		}

		WP_CLI::success( 'Badge classes successfully retrieved : ' . json_encode( $badge_classes ) );
	}


	/**
	 * List BadgeClasses by Issuer slug.
	 *
	 * @param array $args Arguments.
	 * @param array $assoc_args Associative Arguments.
	 * @return void
	 */
	public function list_badge_classes_by_issuer_slug( $args, $assoc_args ) {
		if ( count( $args ) !== 1 ) {
			WP_CLI::error( 'Usage: list_badge_classes_by_issuer issuer_slug' );
		}

		if ( strlen( $args[0] ) < 1 ) {
			WP_CLI::error( 'Please provide an issuer slug the 1st argument' );
		}

		$badge_classes = BadgrProvider::get_all_badge_classes_by_issuer_slug( $args[0], array( 'elements_per_page' => -1 ) );
		if ( false === $badge_classes ) {
			WP_CLI::error( 'Error retrieving badge classes' );
		}

		WP_CLI::success( 'Badge classes for issuer ' . $args[0] . 'successfully retrieved : ' . json_encode( $badge_classes ) );
	}


	/**
	 * Add BadgeClass.
	 *
	 * @param array $args Arguments.
	 * @param array $assoc_args Associative Arguments.
	 * @return void
	 */
	public function add_badge_class( $args, $assoc_args ) {
        if ( count( $args ) !== 5 ) {
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

        if ( strlen( $args[4] ) < 1 ) {
            WP_CLI::error( 'Please provide a criteria as the 4th argument' );
        }

        $slug = BadgrProvider::add_badge_class( $args[0], $args[1], $args[2], $args[3], $args[4] );

        if ( $slug ) {
            WP_CLI::success( 'Badge class added with slug ' . $slug );
        } else {
            WP_CLI::error( 'Adding badge class failed.' );
        }
	}


	/**
	 * Get BadgeClass by slug.
	 *
	 * @param array $args Arguments.
	 * @param array $assoc_args Associative Arguments.
	 * @return void
	 */
	public function get_badge_class_by_slug( $args, $assoc_args ) {
		if ( count( $args ) !== 1 ) {
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


	/**
	 * Update BadgeClass.
	 *
	 * @param array $args Arguments.
	 * @param array $assoc_args Associative Arguments.
	 * @return void
	 */
	public function update_badge_class( $args, $assoc_args ) {
        if ( ! ( count( $args ) === 4 || count( $args ) === 5 ) ) {
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

        if ( count( $args ) === 5 && ( strlen( $args[3] ) < 1 || ! file_exists( $args[3] ) ) ) {
            WP_CLI::error( 'Please provide the name of an existing image file as the 4th argument' );
        }

        if ( strlen( $args[4] ) < 1 ) {
            WP_CLI::error( 'Please provide a criteria as the 4th argument' );
        }

        if ( BadgrProvider::update_badge_class( $args[0], $args[1], $args[2], $args[3] ) ) {
            WP_CLI::success( 'Updated badge class with slug ' . $args[0] );
        } else {
            WP_CLI::error( 'Updating badge class failed.' );
        }
	}


	/**
	 * Delete BadgeClass.
	 *
	 * @param array $args Arguments.
	 * @param array $assoc_args Associative Arguments.
	 * @return void
	 */
	public function delete_badge_class( $args, $assoc_args ) {
		if ( count( $args ) !== 1 ) {
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


	/**
	 * Add Assertion.
	 *
	 * @param array $args Arguments.
	 * @param array $assoc_args Associative Arguments.
	 * @return void
	 */
	public function add_assertion( $args, $assoc_args ) {
		if ( count( $args ) !== 2 || 3 < count($assoc_args) ) {
			WP_CLI::error( 'Usage: add_assertion badge_class_slug recipient_identity [--issued_on={date} --evidence_url={url} --evidence_narrative="{narrative}"]' );
		}

		if ( strlen( $args[0] ) < 1 ) {
			WP_CLI::error( 'Please provide a badge class slug as the first argument' );
		}

		if ( strlen( $args[1] ) < 1 || ! filter_var( $args[1], FILTER_VALIDATE_EMAIL ) ) {
			WP_CLI::error( 'Please provide a recipient identity (email) as the 2nd argument' );
		}

		if ( isset($assoc_args['issued_on']) && strlen($assoc_args['issued_on']) > 0 ) {
			$issued_on = $assoc_args['issued_on'];
		} else {
			$issued_on = null;
		}

		if ( isset($assoc_args['evidence_url']) && strlen($assoc_args['evidence_url']) > 0 ) {
			$evidence_url = $assoc_args['evidence_url'];
		} else {
			$evidence_url = null;
		}

		if ( isset($assoc_args['evidence_narrative']) && strlen($assoc_args['evidence_narrative']) > 0 ) {
			$evidence_narrative = $assoc_args['evidence_narrative'];
		} else {
			$evidence_narrative = null;
		}

		$slug = BadgrProvider::add_assertion( $args[0], $args[1], 'email', $issued_on, $evidence_url, $evidence_narrative );

		if ( $slug ) {
			WP_CLI::success( 'Assertion added with slug ' . $slug );
		} else {
			WP_CLI::error( 'Adding assertion failed.' );
		}
	}

	public function update_assertion( $args, $assoc_args ) {
		if ( count( $args ) !== 1 || ( 1 > count($assoc_args) || 3 < count($assoc_args) ) ) {
			WP_CLI::error( 'Usage: update_assertion assertion_slug [--issued_on={date} --evidence_url={url} --evidence_narrative="{narrative}"]' );
		}

		if ( true !== BadgrProvider::update_assertion( $args[0], $assoc_args) ) {
			WP_CLI::error( 'Updating assertion failed.' );
		}

		WP_CLI::success( 'Assertion with slug ' . $args[0] . ' successfully updated.' );

	}


	/**
	 * List Assertions by Issuer.
	 *
	 * @param array $args Arguments.
	 * @param array $assoc_args Associative Arguments.
	 * @return void
	 */
	public function list_assertions_by_issuer( $args, $assoc_args ) {
		if ( count( $args ) !== 1 ) {
			WP_CLI::error( 'Usage: list_assertions_by_issuer issuer_slug' );
		}

		if ( strlen( $args[0] ) < 1 ) {
			WP_CLI::error( 'Please provide an issuer slug the 1st argument' );
		}

		$badge_classes = BadgrProvider::get_all_assertions_by_issuer_slug( $args[0], array( 'elements_per_page' => -1 ) );
		if ( false === $badge_classes ) {
			WP_CLI::error( 'Error retrieving assertions' );
		}

		WP_CLI::success( 'Assertions for issuer ' . $args[0] . 'successfully retrieved : ' . json_encode( $badge_classes ) );
	}


	/**
	 * List Assertions by BadgeClass.
	 *
	 * @param array $args Arguments.
	 * @param array $assoc_args Associative Arguments.
	 * @return void
	 */
	public function list_assertions_by_badge_class( $args, $assoc_args ) {
		if ( count( $args ) !== 1 ) {
			WP_CLI::error( 'Usage: list_assertions_by_badge_class badge_class_slug' );
		}

		if ( strlen( $args[0] ) < 1 ) {
			WP_CLI::error( 'Please provide a badge class slug the 1st argument' );
		}

		$badge_classes = BadgrProvider::get_all_assertions_by_badge_class_slug( $args[0], array( 'elements_per_page' => -1 ) );
		if ( false === $badge_classes ) {
			WP_CLI::error( 'Error retrieving assertion' );
		}

		WP_CLI::success( 'Assertions for badge class ' . $args[0] . 'successfully retrieved : ' . json_encode( $badge_classes ) );
	}


	/**
	 * Get Assertion by slug.
	 *
	 * @param array $args Arguments.
	 * @param array $assoc_args Associative Arguments.
	 * @return void
	 */
	public function get_assertion_by_slug( $args, $assoc_args ) {
		if ( count( $args ) !== 1 ) {
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


	/**
	 * Revoke Assertion.
	 *
	 * @param array $args Arguments.
	 * @param array $assoc_args Associative Arguments.
	 * @return void
	 */
	public function revoke_assertion( $args, $assoc_args ) {
		if ( count( $args ) !== 2 ) {
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

	public function list_user_backpack( $args, $assoc_args ) {
		if ( count( $args ) !== 1 ) {
			WP_CLI::error( 'Usage: list_user_backpack wp_user_id' );
		}

		$user = get_userdata( $args[0] );

		if ( false === $user ) {
			WP_CLI::error( 'No such user ' . $args[0] );
		}

		$badgr_user = new BadgrUser( $user );

		$backpack = BadgrProvider::get_all_assertions_from_user_backpack( $badgr_user );

		if ( false === $backpack ) {
			WP_CLI::error( 'Getting backpack for user  ' . $args[0] . ' failed.' );
		}

		WP_CLI::success( 'Backpack for user ' . $args[0] . ' successfully retrieved : ' . json_encode( $backpack ) );

	}


	/**
	 * Force Auth.
	 *
	 * @param array $args Arguments.
	 * @param array $assoc_args Associative Arguments.
	 * @return void
	 */
	public function force_auth( $args, $assoc_args ) {
		if ( count( $args ) !== 3 ) {
			WP_CLI::error( 'Usage: force_auth access_token refresh_token token_expiration' );
		}

		if ( strlen( $args[0] ) < 1 ) {
			WP_CLI::error( 'Please provide an access token as the 1st argument' );
		}

		if ( strlen( $args[1] ) < 1 ) {
			WP_CLI::error( 'Please provide a refresh token as the 2nd argument' );
		}

		if ( strlen( $args[2] ) < 1 || ! filter_var( $args[2], FILTER_VALIDATE_INT ) || intval( $args[2] ) <= time() ) {
			WP_CLI::error( 'Please provide a timestamp date later than current time as the 3rd argument' );
		}

		$options                                  = get_option( 'badgefactor2_badgr_settings' );
		$options['badgr_server_access_token']     = $args[0];
		$options['badgr_server_refresh_token']    = $args[1];
		$options['badgr_server_token_expiration'] = $args[2];
		update_option( 'badgefactor2_badgr_settings', $options );

		WP_CLI::success( 'Default auth values updated.' );
	}


	/**
	 * View current default client profile.
	 *
	 * @param array $args Arguments.
	 * @param array $assoc_args Associative Arguments.
	 * @return void
	 */
	public function view_current_default_client_profile( $args, $assoc_args ) {
		if ( count( $args ) !== 0 ) {
			WP_CLI::error( 'Usage: view_current_default_client_profile' );
		}

		$client_profile = BadgrProvider::get_profile_associated_to_client_in_use();

		if ( $client_profile ) {
			WP_CLI::success( 'Current profile: ' . json_encode( $client_profile ) );
		} else {
			WP_CLI::error( 'Failed getting current profile' );
		}
	}


	/**
	 * Mark existing users for migration.
	 *
	 * @param array $args Arguments.
	 * @param array $assoc_args Associative Arguments.
	 * @return void
	 */
	public function mark_existing_users_for_migration( $args, $assoc_args ) {
		if ( count( $args ) !== 0 ) {
			WP_CLI::error( 'Usage: mark_existing_users_for_migration' );
		}

		$count = Migration::mark_users_for_migration();
		if ( false === $count ) {
			WP_CLI::error( 'Marking users for migration failed' );
		} else {
			WP_CLI::success( 'Finished marking user for migration: ' . $count . ' users marked' );
		}
	}


	/**
	 * Migrate users and mark as verified.
	 *
	 * @param array $args Arguments.
	 * @param array $assoc_args Associative Arguments.
	 * @return void
	 */
	public function migrate_users_and_mark_as_verified( $args, $assoc_args ) {
		if ( count( $args ) !== 0 ) {
			WP_CLI::error( 'Usage: migrate_users_and_mark_as_verified' );
		}

		$count = Migration::migrate_users( true );

		if ( false === $count ) {
			WP_CLI::error( 'Migrating marked users failed' );
		} else {
			WP_CLI::success( 'Finished migrating marked users: ' . $count . ' users migrated' );
		}
	}


	/**
	 * Migrate Issuers.
	 *
	 * @param array $args Arguments.
	 * @param array $assoc_args Associative Arguments.
	 * @return void
	 */
	public function migrate_issuers( $args, $assoc_args ) {
		if ( count( $args ) !== 0 ) {
			WP_CLI::error( 'Usage: migrate_issuers [ --restrict-to-published | --no-restrict-to-pubished' );
		}

		$only_published = WP_CLI\Utils\get_flag_value( $assoc_args, 'restrict-to-published', $default = false );

		$count = Migration::migrate_issuers( $only_published );

		if ( false === $count ) {
			WP_CLI::error( 'Migrating issuers failed' );
		} else {
			WP_CLI::success( 'Finished migrating issuers: ' . $count . ' issuers migrated' );
		}
	}


	/**
	 * Migrate BadgeClasses.
	 *
	 * @param array $args Arguments.
	 * @param array $assoc_args Associative Arguments.
	 * @return void
	 */
	public function migrate_badge_classes( $args, $assoc_args ) {
		if ( count( $args ) !== 0 ) {
			WP_CLI::error( 'Usage: migrate_badge_classes' );
		}

		$count = Migration::migrate_badge_classes();

		if ( false === $count ) {
			WP_CLI::error( 'Migrating badge classes failed' );
		} else {
			WP_CLI::success( 'Finished migrating badge classes: ' . $count . ' badge classes migrated' );
		}
	}


	/**
	 * Migrate Assertions.
	 *
	 * @param array $args Arguments.
	 * @param array $assoc_args Associative Arguments.
	 * @return void
	 */
	public function migrate_badge_assertions( $args, $assoc_args ) {
		if ( count( $args ) !== 0 ) {
			WP_CLI::error( 'Usage: migrate_badge_assertions' );
		}

		$count = Migration::migrate_badge_assertions();

		if ( false === $count ) {
			WP_CLI::error( 'Migrating badge assertions failed' );
		} else {
			WP_CLI::success( 'Finished migrating badge assertions: ' . $count . ' badge assertions migrated' );
		}
	}

		/**
	 * Migrate Assertions.
	 *
	 * @param array $args Arguments.
	 * @param array $assoc_args Associative Arguments.
	 * @return void
	 */
	public function migrate_pending_approvals( $args, $assoc_args ) {
		if ( count( $args ) !== 0 ) {
			WP_CLI::error( 'Usage: migrate_pending_approvals' );
		}

		$count = Migration::migrate_pending_approvals();

		if ( false === $count ) {
			WP_CLI::error( 'Migrating pending approvals failed' );
		} else {
			WP_CLI::success( 'Finished migrating pending approvals: ' . $count . ' pending approvals migrated' );
		}
	}
}
