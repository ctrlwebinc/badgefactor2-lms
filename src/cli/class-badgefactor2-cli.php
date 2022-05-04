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
use BadgeFactor2\Post_Types\BadgePage;
use BadgeFactor2\AssertionPrivacy;
use BadgeFactor2\BadgrProvider;

WP_CLI::add_command( 'bf2', BadgeFactor2_CLI::class );

/**
 * Manage Open Badges in Badge Factor 2.
 */
class BadgeFactor2_CLI extends WP_CLI_Command {

	/**
	 * Undocumented function.
	 *
	 * @param array $args Arguments.
	 * @param array $assoc_args Associative arguments.
	 * @return void
	 */
	public function list_issuers( $args, $assoc_args ) {
		if ( count( $args ) !== 0 ) {
			WP_CLI::error( 'Usage: list_issuers' );
		}

		$issuers = Issuer::all( -1 );
		if ( false === $issuers ) {
			WP_CLI::error( 'Error retrieving issuers' );
		}

		WP_CLI::success( 'Issuers successfully retrieved : ' . json_encode( $issuers, JSON_PRETTY_PRINT ) );
	}

	/**
	 * Undocumented function
	 *
	 * @param array $args Arguments.
	 * @param array $assoc_args Associative arguments.
	 * @return void
	 */
	public function create_badge_pages_from_badges( $args, $assoc_args ) {
		if ( count( $args ) !== 0 ) {
			WP_CLI::error( 'Usage: create_badge_pages_from_badges' );
		}

		$count = BadgePage::create_from_badges();

		if ( false === $count ) {
			WP_CLI::error( 'Migrating badges failed' );
		} else {
			WP_CLI::success( 'Finished migrating badgees: ' . $count . ' badge pages created' );
		}
	}

	/**
	 * Undocumented function
	 *
	 * @param array $args Arguments.
	 * @param array $assoc_args Associative arguments.
	 * @return void
	 */
	public function create_courses_from_badges( $args, $assoc_args ) {
		if ( count( $args ) !== 0 ) {
			WP_CLI::error( 'Usage: create_courses_from_badges' );
		}

		$count = BadgePage::create_courses_from_badges();

		if ( false === $count ) {
			WP_CLI::error( 'Migrating courses failed' );
		} else {
			WP_CLI::success( 'Finished migrating courses: ' . $count . ' courses created' );
		}
	}

	/**
	 * Undocumented function
	 *
	 * @param array $args Arguments.
	 * @param array $assoc_args Associative arguments.
	 * @return void
	 */
	public function link_badge_pages_and_courses( $args, $assoc_args ) {
		if ( count( $args ) !== 0 ) {
			WP_CLI::error( 'Usage: link_badge_pages_and_courses' );
		}

		$count = Migration::link_badge_pages_and_courses();

		if ( false === $count ) {
			WP_CLI::error( 'Linking badge pages and courses failed' );
		} else {
			WP_CLI::success( 'Finished linking badge pages and courses: ' . $count . ' courses and badge pages linked' );
		}
	}

	/**
	 * Undocumented function
	 *
	 * @param array $args Arguments.
	 * @param array $assoc_args Associative arguments.
	 * @return void
	 */
	public function mark_links_to_remove_from_courses( $args, $assoc_args ) {
		if ( count( $args ) !== 0 ) {
			WP_CLI::error( 'Usage: mark_links_to_remove_from_courses' );
		}

		$count = Migration::mark_links_to_remove_from_courses();

		if ( false === $count ) {
			WP_CLI::error( 'Link marking failed' );
		} else {
			WP_CLI::success( 'Finished marking links from courses: ' . $count . ' links marked' );
		}
	}

	/**
	 * Undocumented function
	 *
	 * @param array $args Arguments.
	 * @param array $assoc_args Associative arguments.
	 * @return void
	 */
	public function removed_marked_links_from_courses( $args, $assoc_args ) {
		if ( count( $args ) !== 0 ) {
			WP_CLI::error( 'Usage: removed_marked_links_from_courses' );
		}

		$count = Migration::removed_marked_links_from_courses();

		if ( false === $count ) {
			WP_CLI::error( 'Link removal failed' );
		} else {
			WP_CLI::success( 'Finished removing marked links from courses: ' . $count . ' links removed' );
		}
	}

	/**
	 * Undocumented function
	 *
	 * @param array $args Arguments.
	 * @param array $assoc_args Associative arguments.
	 * @return void
	 */
	public function test_product_category_change( $args, $assoc_args ) {
		if ( count( $args ) !== 0 ) {
			WP_CLI::error( 'Usage: test_product_category_change' );
		}
		// meta badgefactor_product_id post_type badges > course_product post_type course
		// course also needs metas is_product => on and price => $123,00
		$product_id = 245379;
		wp_remove_object_terms( $product_id, 'simple', 'product_type' );
		wp_set_object_terms( $product_id, 'course', 'product_type', true );

		$product_id = 1888;
		wp_remove_object_terms( $product_id, 'badge', 'product_type' );
		wp_set_object_terms( $product_id, 'course', 'product_type', true );

		WP_CLI::success( 'Category change test completed.' );
	}

	public function encrypt( $args, $assoc_args ) {
		if ( count( $args ) !== 1 ) {
			WP_CLI::error( 'Usage: encrypt "clear text to encrypt"' );
		}

		WP_CLI::success( BadgrUser::encrypt_decrypt( 'encrypt', $args[0]) );

	}

	public function decrypt( $args, $assoc_args ) {
		if ( count( $args ) !== 1 ) {
			WP_CLI::error( 'Usage: decrypt "base64 cypher text to decode"' );
		}

		WP_CLI::success( BadgrUser::encrypt_decrypt( 'decrypt', $args[0]) );

	}

	public function suppress_old_entities( $args, $assoc_args ) {
		Migration::suppress_old_entities();
	}

	/**
	 * Undocumented function.
	 *
	 * @return void
	 */
	public function reencrypt_user_passwords() {
		foreach ( get_users() as $user ) {
			$badgr_password = \get_user_meta( $user->ID, 'badgr_password', true );
			if ( strlen( $badgr_password ) === 12 ) {
				$encrypted_badgr_password = BadgrUser::encrypt_decrypt( 'encrypt', $badgr_password );
				\update_user_meta( $user->ID, 'badgr_password', $encrypted_badgr_password );
				$client = BadgrUser::get_or_make_user_client( $user, true );
				// \update_user_meta( $user->ID, 'badgr_client_instance', $client );
			}
		}
		WP_CLI::success( 'reencrypted!' );
	}

	public function add_assertion_privacy_flags_table() {
		AssertionPrivacy::create_table();
	}

	public function check_assertion_privacy_flag( $args, $assoc_args ) {
		if ( count( $args ) !== 2 ) {
			WP_CLI::error( 'Usage: check_assertion_privacy_flag class_slug user_id' );
		}
		WP_CLI::success( AssertionPrivacy::has_privacy_flag( $args[0], $args[1]));
	}

	public function get_assertion_privacy_toggle_info ( $args, $assoc_args ) {
		if ( count( $args ) !== 1 ) {
			WP_CLI::error( 'Usage: get_assertion_privacy_toggle_info class_slug' );
		}

		WP_CLI::success( json_encode(AssertionPrivacy::generate_ajax_callback_parameters( $args[0] )));
	}
	public function rebake_missing_evidence( $args, $assoc_args ) {
		if ( count( $args ) !== 0 ) {
			WP_CLI::error( 'Usage: rebake_missing_evidence' );
		}

		global $wpdb;

		// Get badge-requests for period between migration and recent correction 
		$requests = $wpdb->get_results(
			"SELECT br.ID, a.meta_value AS assertion, d.meta_value AS dates, c.meta_value AS evidence FROM wp_posts AS br
			LEFT JOIN wp_postmeta AS a
			ON a.post_id = br.ID
			LEFT JOIN wp_postmeta AS d
			ON d.post_id = br.ID
			LEFT JOIN wp_postmeta AS c
			ON c.post_id = br.ID 
			WHERE br.post_type = 'badge-request'
			AND a.meta_key = 'assertion'
			AND d.meta_key = 'dates'
			AND c.meta_key = 'content'
			AND c.meta_value != 'Formulaire soumis'
			AND d.meta_value REGEXP '\"granted\";s:19:\"(2021-(06|07|08|09|10|11|12)|2021-05-1|2021-05-09|2022-)' = 1
			AND d.meta_value NOT REGEXP '\"granted\";s:19:\"2022-0(4-28|4-29|4-30|5)' = 1;",
			OBJECT_K
		);

		$count_success = 0;
		$count_failed = 0;
		$count_url_not_found = 0;

		foreach ( $requests AS $k => $request) {
			// get evidence url
			$matches = [];
			$url_matching = preg_match("/(<a href=')(.*)(' target=')/",$request->evidence, $matches);
			if (1 !== $url_matching ) {
				$count_url_not_found++;
				add_post_meta($request->ID,'bf2_mass_rebake', 'url issue', true);
				continue;
			}
			$url = site_url($matches[2]);
			// rebake
			$result = BadgrProvider::update_assertion( $request->assertion, ['evidence_url' => $url ] );
			//$result = false;

			// add rebaked meta
			if ( false === $result) {
				add_post_meta($request->ID,'bf2_mass_rebake', 'update failed', true);
			} else {
				add_post_meta($request->ID,'bf2_mass_rebake', '2022-05-03', true);
			}
			
			//die(json_encode([$url,$result,$request->assertion]));
		}
	
		WP_CLI::success( 'Command completed: ' . $count_success . ' updated, ' . $count_failed . ' badgr update failed, ' . $count_url_not_found . ' url issues.');
		
	}
}
