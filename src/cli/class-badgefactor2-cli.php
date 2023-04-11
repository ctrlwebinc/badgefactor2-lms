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

use BadgeFactor2\AssertionPrivacy;
use BadgeFactor2\BadgrProvider;
use BadgeFactor2\Helpers\Migration;
use BadgeFactor2\Post_Types\BadgePage;
use WP_CLI;
use WP_CLI_Command;

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

	public function make_badge_request_forms_require_login( $args, $assoc_args ) {
		if ( ! class_exists( 'GFCommon' ) ) {
			WP_CLI::error( sprintf( 'Gravity Forms is not active!' ) );
		}

		$badge_pages = BadgePage::all();
		$count = count( $badge_pages );
		$progress = WP_CLI\Utils\make_progress_bar( sprintf( '%d badge pages to verify.', $count ), $count );
		$fixed = 0;
		foreach ( BadgePage::all() as $badge_page ) {
			$metas = get_post_meta( $badge_page->ID );

			if ( 'gravityforms' === $metas['badge_request_form_type'][0] && isset( $metas['badge_request_form_id'][0] ) ) {
				$gravityform = \GFAPI::get_form( $metas['badge_request_form_id'][0] );
				if ( $gravityform && ( ! isset( $gravityform['requireLogin'] ) || false === $gravityform['requireLogin'] ) ) {
					$gravityform['requireLogin'] = true;
					$result = \GFAPI::update_form( $gravityform );
					if ( ! $result ) {
						WP_CLI::error( sprintf( 'An unknown error has occured.' ) );
					}
					$fixed++;
				}
			}
			$progress->tick();
		}
		WP_CLI::log( sprintf( '%d badge request forms fixed!', $fixed ) );

	}

	public function update_badge_requests_meta_content ( $args, $assoc_args ) {
		global $wpdb;

		if ( count( $args ) > 0 ) {
			WP_CLI::error( 'Usage: update_badge_requests_meta_content' );
		}

		$results = $wpdb->get_results( "
			SELECT p.ID, post_content, meta_value 
			FROM {$wpdb->posts} p
			JOIN {$wpdb->postmeta} pm
			ON p.ID = pm.post_id
			WHERE meta_key = 'content'
			AND meta_value = 'Formulaire soumis'", 
			OBJECT 
		);

		$to_update = count( $results );
		$updated = 0;

		

		if ( $to_update > 0 ) {
			WP_CLI::log( sprintf( '%d badge requests to update', $to_update ) );

			foreach( $results as $post ) {
				
				// update post meta
				update_post_meta( $post->ID, 'content', $post->post_content );

				// update post, set post_content to blank
				$wpdb->update( 
					$wpdb->posts, 
					array( 
						'post_content' => '' 
					), 
					array( 'ID' => $post->ID ), 
					array( 
						'%s',   // value1
					), 
					array( '%d' ) 
				);
								
				// stamp
				update_post_meta( $post->ID, 'cli_update_stamp', 'CLI update on ' . date( 'Y-m-d H:i:s' ) );

				WP_CLI::log( sprintf( 'Post ID %d updated', $post->ID ) );
				
				$updated++;
			}
			
			WP_CLI::log( sprintf( '%d badge requests updated!', $updated ) );
		} else {
			WP_CLI::log( 'There is nothing to update.' );
		}
	}

	/**
	 * Generates assertions using a list of recipients provided in a csv file.
	 * There should be a header line which will be ignored, and the line
	 * format should be: badge_class_slug, email, assertion_date, badge_name
	 *
	 * @param array $args Arguments.
	 * @param array $assoc_args Associative arguments.
	 * @return void
	 */
	public function batch_process_assertions( $args, $assoc_args ) 
	{
		$today = date('Y-m-d');

		// Check if csv file is provided.
		if ( count( $args ) !== 1 ) {
			WP_CLI::error( 'Usage: wp bf2 batch_process_assertions /path/to/filename.csv' );
		}

		// Check if dry-run mode is activated.
		$dry_run = isset( $assoc_args['dry-run'] );
		if ( $dry_run ) {
			WP_CLI::line( 'Dry-run mode enabled.' );
		}

		$recipients = array();
		$file_to_read = fopen( $args[0], 'r');
		if ( ! $file_to_read ) {
			WP_CLI::error( 'Cannot open the csv file! Check your path and filename and try again.' );
		}

		WP_CLI::line( 'Reading csv file...' );

		// Skip first line.
		fgetcsv( $file_to_read );

		// Generate an array from the remainder of the csv file.
		while ( ! feof( $file_to_read ) ) {
			$recipients[] = fgetcsv($file_to_read, 1000, ',');
		}
		fclose( $file_to_read );

		// Generate an array of validated data.
		$progress = WP_CLI\Utils\make_progress_bar( 'Validating data...', count( $recipients ) );
		$badges = array();
		foreach( $recipients as $recipient ) {

			$badge_class_slug = $recipient[0];

			// Validate each badge class only once.
			if ( ! isset( $badges[$badge_class_slug] ) ) {
				$badge_class = BadgrProvider::get_badge_class_by_badge_class_slug( $badge_class_slug );

				// Exists if badge class does not exist.
				if ( false === $badge_class ) {
					WP_CLI::error( 'Badge class does not exist: ' . $badge_class_slug );
				}
	
				$badges[$badge_class_slug] = array();
			}

			$email          = $recipient[1];
			$assertion_date = ( isset( $recipient[2] ) && ! empty( $recipient[2] ) ) ? 
				$recipient[2] :
				$today;

			// Eliminate duplicate recipient data from csv file.
			if ( ! isset( $badges[$badge_class_slug][$email] ) ) {
				$badges[$badge_class_slug][$email] = $date;
			}
			$progress->tick();
		}
		$progress->finish();

		// Generate assertions in Badgr.
		$progress = \WP_CLI\Utils\make_progress_bar( 'Generating assertions...', count( $badges ) );
		foreach ( $badges as $badge_class => $emails ) {
			foreach ( $emails as $email => $date ) {
				if ( ! $dry_run ) {
					$slug = BadgrProvider::add_assertion( $badge_class, $email, 'email', $date );
				}
			}
			$progress->tick();
		}
		$progress->finish();
	}
}
