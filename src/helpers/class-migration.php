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

namespace BadgeFactor2\Helpers;

use BadgeFactor2\BadgrUser;
use BadgeFactor2\Models\Issuer;

/**
 * Migration helper class.
 */
class Migration {

	/**
	 * Undocumented function.
	 *
	 * @return int
	 */
	public static function migrate_badge_assertions() {

		global $wpdb;

		// Get posts of type submission with status approved.
		$assertions = $wpdb->get_results(
			"SELECT ap.*, bcs.meta_value AS badge_class_slug, u.user_email AS recipient, eu.meta_value as evidence_url FROM wp_posts AS ap
			JOIN wp_postmeta AS apm
			ON ap.ID = apm.post_id
			JOIN wp_users AS u
			ON ap.post_author = u.ID
			JOIN wp_postmeta AS bc
			ON ap.ID = bc.post_id
			JOIN wp_postmeta AS bcs
			ON bc.meta_value = bcs.post_id
			LEFT JOIN wp_posts as e
			ON ap.ID = e.post_parent
			LEFT JOIN wp_postmeta as eu
			ON e.ID = eu.post_id
			WHERE ap.post_type = 'submission' AND
			apm.meta_key = '_badgeos_submission_status' AND
			apm.meta_value = 'approved' AND
			bc.meta_key = '_badgeos_submission_achievement_id' AND
			bcs.meta_key = 'badgr_badge_class_slug' AND
			( e.post_type IS NULL OR e.post_type = 'attachment' ) AND
			(eu.meta_key IS NULL OR eu.meta_key = '_wp_attached_file')
			AND NOT EXISTS (
				SELECT aps.meta_id FROM wp_postmeta AS aps
				WHERE aps.meta_key = 'badgr_assertion_slug' AND ap.ID = aps.post_id
			);",
			OBJECT_K
		);

		$count = 0;

		foreach ( $assertions as $assertion_post_id => $assertion_post ) {
			$issued_on = $assertion_post->post_date;

			// Workout full url.
			if ( 'NULL' !== $assertion_post->evidence_url ) {
				$evidence_url = site_url( 'wp_content/uploads/' . $assertion_post->evidence_url );
			} else {
				$evidence_url = null;
			}

			// Add assertion.
			$assertion_slug = BadgrProvider::add_assertion( $assertion_post->badge_class_slug, $assertion_post->recipient, 'email', $issued_on, $evidence_url );

			if ( false === $assertion_slug ) {
				update_post_meta( $assertion_post_id, 'badgr_assertion_failed', 'failed' );
				continue;
			}

			// Save slug in post meta.
			update_post_meta( $assertion_post_id, 'badgr_assertion_slug', $assertion_slug );
			$count++;
		}

		return $count;
	}


	/**
	 * Undocumented function.
	 *
	 * @return int
	 */
	public static function migrate_badge_classes() {
		// Get badges posts without badgr slug enriched with organisation issuer slug.
		global $wpdb;

		$badges = $wpdb->get_results(
			"SELECT bc.*, os.meta_value AS issuer_slug, i.meta_value AS image_name FROM wp_posts AS bc
		JOIN wp_postmeta AS o
		ON bc.ID = o.post_id
		JOIN wp_postmeta AS os
		ON o.meta_value = os.post_id
		JOIN wp_postmeta AS a
		ON bc.ID = a.post_id
		JOIN wp_postmeta as i
		ON a.meta_value = i.post_id
		WHERE bc.post_type = 'badges' AND
		bc.post_status != 'trash' AND
		o.meta_key = 'organisation' AND
		os.meta_key = 'badgr_issuer_slug' AND
		a.meta_key = '_thumbnail_id' AND
		i.meta_key = '_wp_attached_file'
		AND NOT EXISTS
		(SELECT bcs.meta_id FROM wp_postmeta as bcs
		 WHERE bcs.meta_key = 'badgr_badge_class_slug' AND bc.ID = bcs.post_id);",
			OBJECT_K
		);

		$count = 0;

		foreach ( $badges as $badge_post_id => $badge_post ) {

			$class_name  = $badge_post->post_title;
			$issuer_slug = $badge_post->issuer_slug;
			// TODO: trim description.
			$description = $badge_post->post_content;
			$image       = get_home_path() . 'wp-content/uploads/' . $badge_post->image_name;

			$badge_class_slug = BadgrProvider::add_badge_class( $class_name, $issuer_slug, $description, $image );

			if ( false === $badge_class_slug ) {
				update_post_meta( $badge_post_id, 'badgr_badge_class_failed', 'failed' );
				continue;
			}

			// Save slug in post meta.
			update_post_meta( $badge_post_id, 'badgr_badge_class_slug', $badge_class_slug );
			$count++;
		}

		return $count;
	}


	/**
	 * Undocumented function.
	 *
	 * @param boolean $only_published Migrate only published.
	 * @return int
	 */
	public static function migrate_issuers( $only_published = false ) {
		// Get all posts of organisation type.
		$query = new WP_Query(
			array(
				'post_type'    => 'organisation',
				'nopaging'     => true,
				'meta_key'     => Issuer::$meta_key_for_badgr_issuer_slug,
				'meta_compare' => 'NOT EXISTS',
				'post_status'  => $only_published ? 'publish' : 'any',
			)
		);

		$posts_to_process = $query->posts;
		$count            = 0;

		// For each post.
		foreach ( $posts_to_process as $post_to_process ) {
			// Extract name.
			$name = $post_to_process->post_name;
			// Extract email.
			$email = $name . '@example.net';
			// Extract url.
			$url = 'https://' . $name . '.cadre21.org';
			// Extract description.
			if ( strlen( $post_to_process->post_content ) > 0 ) {
				$description = $post_to_process->post_content;
			} else {
				$description = 'Émetteur ' . $name;
			}

			// Create an issuer.
			$slug = BadgrProvider::add_issuer( $name, $email, $url, $description );

			if ( false === $slug ) {
				return false;
			}
			// Save slug in post meta.
			update_post_meta( $post_to_process->ID, Issuer::$meta_key_for_badgr_issuer_slug, $slug );
			$count++;
		}

		return $count;
	}


	/**
	 * Undocumented function
	 *
	 * @return object
	 */
	public static function mark_users_for_migration() {
		// Query for users withtout a badgr_user_state <meta class="">
		// Set to 'to_be_created'.
		global $wpdb;

		return $wpdb->query(
			"INSERT INTO wp_usermeta (user_id,meta_key,meta_value)
		SELECT u.id, 'badgr_user_state', 'to_be_created' FROM wp_users AS u
		WHERE NOT EXISTS
		(SELECT m.umeta_id FROM wp_usermeta as m
		WHERE m.`meta_key` = 'badgr_user_state' AND u.id = m.user_id) AND u.id != 1;"
		);

	}


	/**
	 * Undocumented function
	 *
	 * @param boolean $mark_as_verified Set to true to mark users as verified as they are processed.
	 * @return int|boolean
	 */
	public static function migrate_users( $mark_as_verified = false ) {
		$count                = 0;
		$consecutive_failures = 0;

		// Get users in a 'to_be_created' state.
		$users_to_process = get_users(
			array(
				'meta_key'   => BadgrUser::$meta_key_for_user_state,
				'meta_value' => 'to_be_created',
				'number'     => 50,
				'paged'      => 1,
			)
		);

		$user_to_process_count = count( $users_to_process );
		while ( 0 < $user_to_process_count ) {

			foreach ( $users_to_process as $user_to_process ) {
				// Skip admin user.
				if ( 1 === $user_to_process->ID ) {
					continue;
				}

				// Create user and mark as created.
				$temporary_password = Text::generate_random_password();
				$slug               = BadgrProvider::add_user( $user_to_process->first_name, $user_to_process->last_name, $user_to_process->user_email, $temporary_password );

				// If successful set badgr user state to 'created' and save slug and save previous password.
				if ( false !== $slug ) {
					update_user_meta( $user_to_process->ID, BadgrUser::$meta_key_for_badgr_user_slug, $slug );
					update_user_meta( $user_to_process->ID, BadgrUser::$meta_key_for_user_state, 'created' );
					update_user_meta( $user_to_process->ID, 'badgr_password', $temporary_password );
					$consecutive_failures = 0;
				} else {
					update_user_meta( $user_to_process->ID, BadgrUser::$meta_key_for_user_state, 'failed_to_create' );

					if ( $consecutive_failures > 2 ) {
						return false;
					}
					// Sleep to avoid Badgr throttling us.
					sleep( 15 * ( $consecutive_failures + 1 ) );

					$consecutive_failures++;
				}

				// Add role if mark as verified flag is set.
				if ( $mark_as_verified ) {
					$user_to_process->add_cap( 'badgefactor2_use_badgr' );
				}

				$count++;

				$users_to_process = get_users(
					array(
						'meta_key'   => BadgrUser::$meta_key_for_user_state,
						'meta_value' => 'to_be_created',
						'number'     => 50,
						'paged'      => 1,
					)
				);

				$user_to_process_count = count( $users_to_process );

			}
		}

		return $count;
	}
}
