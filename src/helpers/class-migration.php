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
 *
 * @phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound
 */

namespace BadgeFactor2\Helpers;

use WP_CLI;
use WP_Query;
use BadgeFactor2\BadgrUser;
use BadgeFactor2\Models\Issuer;
use BadgeFactor2\BadgrProvider;

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
			"SELECT ap.*,
				bcs.meta_value AS badge_class_slug,
				bc.meta_value AS submission_id,
				u.user_email AS recipient,
				eu.meta_value as evidence_url,
				u.ID AS recipient_id,
				u.display_name AS requester_name
			FROM {$wpdb->prefix}posts AS ap
			JOIN {$wpdb->prefix}postmeta AS apm
				ON ap.ID = apm.post_id
			JOIN {$wpdb->prefix}users AS u
				ON ap.post_author = u.ID
			JOIN {$wpdb->prefix}postmeta AS bc
				ON ap.ID = bc.post_id
			JOIN {$wpdb->prefix}postmeta AS bcs
				ON bc.meta_value = bcs.post_id
			LEFT JOIN {$wpdb->prefix}posts as e
				ON ap.ID = e.post_parent
			LEFT JOIN {$wpdb->prefix}postmeta as eu
				ON e.ID = eu.post_id
			WHERE ap.post_type = 'submission'
			AND apm.meta_key = '_badgeos_submission_status'
			AND apm.meta_value = 'approved'
			AND bc.meta_key = '_badgeos_submission_achievement_id'
			AND bcs.meta_key = 'badgr_badge_class_slug'
			AND ( e.post_type IS NULL OR e.post_type = 'attachment' )
			AND (eu.meta_key IS NULL OR eu.meta_key = '_wp_attached_file')
			AND NOT EXISTS (
				SELECT aps.meta_id FROM {$wpdb->prefix}postmeta AS aps
				WHERE aps.meta_key = 'badgr_assertion_slug' AND ap.ID = aps.post_id
			)
			;",
			OBJECT_K
		);

		$count = 0;

		foreach ( $assertions as $assertion_post_id => $assertion_post ) {

			$form_id = get_post_meta( $assertion_post->submission_id, 'badgefactor_form_id', true );

			$issued_on = $assertion_post->post_date;

			// Workout full url.
			$evidence_url = null;
			if ( null !== $assertion_post->evidence_url ) {
				$form         = \GFAPI::get_form( $form_id );
				$form_entries = \GFAPI::get_entries(
					$form_id,
					array(
						'field_filters' => array(
							array(
								'key'   => 'created_by',
								'value' => $assertion_post->recipient_id,
							),
						),
					)
				);
				if ( is_array( $form_entries ) && isset( $form_entries[0] ) ) {
					$gf_pdf       = \GPDFAPI::get_pdf_class( 'model' );
					$evidence_url = $gf_pdf->get_pdf_url( array_key_first( $form['gfpdf_form_settings'] ), $form_entries[0]['id'] );
				}
			}

			// Add assertion.
			$assertion_slug = BadgrProvider::add_assertion( $assertion_post->badge_class_slug, $assertion_post->recipient, 'email', $issued_on, $evidence_url );

			if ( false === $assertion_slug ) {
				update_post_meta( $assertion_post_id, 'badgr_assertion_failed', 'failed' );
				continue;
			}

			// Save slug in post meta.
			update_post_meta( $assertion_post_id, 'badgr_assertion_slug', $assertion_slug );

			// Create approved badge request
			// Prepare the post title and post name.
			$request_name_and_title = $assertion_post->requester_name . ' - ' . $assertion_post->post_title;

			// Insert the badge request post.
			$created_post_id = wp_insert_post(
				array(
					'post_author' => 1,
					'post_title'  => $request_name_and_title,
					'post_name'   => $request_name_and_title,
					'post_status' => 'publish',
					'post_type'   => 'badge-request',
					'post_date'   => $issued_on,
					'meta_input'  => array(
						'assertion' => $assertion_slug,
						'badge'     => $assertion_post->badge_class_slug,
						'type'      => 'gravityforms',
						'recipient' => $assertion_post->recipient_id,
						'status'    => 'approved',
						'dates'     => array(
							'requested' => $issued_on,
						),
						'content'   => sprintf( "<a href='%s' target='_blank'>%s</a>", $evidence_url, __( 'Submitted Form', BF2_DATA['TextDomain'] ) ),
					),
				)
			);

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
			"SELECT bc.*,
				os.meta_value AS issuer_slug,
				i.meta_value AS image_name
			FROM {$wpdb->prefix}posts AS bc
			JOIN {$wpdb->prefix}postmeta AS o
				ON bc.ID = o.post_id
			JOIN {$wpdb->prefix}postmeta AS os
				ON o.meta_value = os.post_id
			JOIN {$wpdb->prefix}postmeta AS a
				ON bc.ID = a.post_id
			JOIN {$wpdb->prefix}postmeta as i
				ON a.meta_value = i.post_id
			WHERE bc.post_type = 'badges'
			AND bc.post_status != 'trash'
			AND o.meta_key = 'organisation'
			AND os.meta_key = 'badgr_issuer_slug'
			AND a.meta_key = '_thumbnail_id'
			AND i.meta_key = '_wp_attached_file'
			AND NOT EXISTS(
				SELECT bcs.meta_id FROM {$wpdb->prefix}postmeta as bcs
				WHERE bcs.meta_key = 'badgr_badge_class_slug' AND bc.ID = bcs.post_id
			)
			;",
			OBJECT_K
		);

		$count = 0;

		$forms = \GFAPI::get_forms();

		foreach ( $badges as $badge_post_id => $badge_post ) {

			$class_name  = $badge_post->post_title;
			$issuer_slug = $badge_post->issuer_slug;
			// TODO: trim description.
			$description = $badge_post->post_content;
			$image       = get_home_path() . 'wp-content/uploads/' . $badge_post->image_name;

			$badge_class_slug = get_post_meta( $badge_post_id, 'badgr_badge_class_slug', true );

			if ( ! $badge_class_slug ) {
				$badge_class_slug = BadgrProvider::add_badge_class( $class_name, $issuer_slug, $description, $image );

				if ( false === $badge_class_slug ) {
					update_post_meta( $badge_post_id, 'badgr_badge_class_failed', 'failed' );
					continue;
				}
				// Save slug in post meta.
				update_post_meta( $badge_post_id, 'badgr_badge_class_slug', $badge_class_slug );
			}

			// Add GravityForms badgeclass_id hidden field.
			$form = self::get_form_id_by_badge_post_id( $forms, $badge_post_id );
			self::add_gf_hidden_field( $form, $badge_class_slug );

			$count++;
		}

		return $count;
	}


	/**
	 * Add GravityForms badgeclass_id hidden field.
	 *
	 * @param array  $form Gravity Form ID.
	 * @param string $badge_class_slug BadgeClass slug.
	 * @return void
	 */
	private static function add_gf_hidden_field( array $form, string $badge_class_slug ) {
		$form['fields'][] = new \GF_Field_Hidden(
			array(
				'label'        => 'badgeclass_id',
				'defaultValue' => $badge_class_slug,
			)
		);
		\GFAPI::update_form( $form );
	}

	/**
	 * Return Gravity Form form by badge post ID.
	 *
	 * @param array $forms Forms array.
	 * @param int   $badge_post_id Badge Post ID.
	 *
	 * @return array|bool
	 */
	private static function get_form_id_by_badge_post_id( array $forms = array(), $badge_post_id ) {
		foreach ( $forms as $form ) {
			$right_one = false;
			$migrated  = false;

			foreach ( $form['fields'] as $field ) {
				if ( 'hidden' === $field->type &&
					'achievement_id' === $field->label &&
					intval( $field->defaultValue ) === $badge_post_id
				) {
					$right_one = true;
				}
				if ( 'hidden' === $field->type &&
					'badgeclass_id' === $field->label
				) {
					$migrated = true;
				}
			}
			if ( $right_one && ! $migrated ) {
				return $form;
			}
		}
		return false;
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
			"INSERT INTO {$wpdb->prefix}usermeta (
				user_id,
				meta_key,
				meta_value
			) SELECT u.id,
				'badgr_user_state',
				'to_be_created'
			FROM {$wpdb->prefix}users AS u
			WHERE NOT EXISTS (
				SELECT m.umeta_id
				FROM {$wpdb->prefix}usermeta AS m
				WHERE m.`meta_key` = 'badgr_user_state'
				AND u.id = m.user_id
			)
			AND u.id != 1
			;"
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

	/**
	 * Link badge pages and courses.
	 *
	 * @return mixed
	 */
	public static function link_badge_pages_and_courses() {
		global $wpdb;

		$badge_pages_and_courses_pairs = $wpdb->get_results(
			"SELECT badge_page.ID AS badge_page_id,
				course.ID AS course_id
			FROM {$wpdb->prefix}posts AS badge_page
			JOIN {$wpdb->prefix}postmeta AS badge_page_badge_class_slug
				ON badge_page_badge_class_slug.post_id = badge_page.`ID`
			JOIN {$wpdb->prefix}posts AS course
			JOIN {$wpdb->prefix}postmeta AS course_badge_class_slug
				ON course_badge_class_slug.post_id = course.ID
			WHERE badge_page.post_type = 'badge-page'
			AND badge_page_badge_class_slug.meta_key = 'badge'
			AND course.post_type = 'course'
			AND course_badge_class_slug.meta_key = 'badgr_badge_class_slug'
			AND badge_page_badge_class_slug.meta_value = course_badge_class_slug.meta_value
			;"
		);

		$count = 0;

		foreach ( $badge_pages_and_courses_pairs as $badge_pages_and_courses_pair ) {
			// Add course_badge_page meta with the associated badge page id as its value.
			update_post_meta( $badge_pages_and_courses_pair->course_id, 'course_badge_page', $badge_pages_and_courses_pair->badge_page_id );

			// Add course meta with the associated course id as its value.
			update_post_meta( $badge_pages_and_courses_pair->badge_page_id, 'course', $badge_pages_and_courses_pair->course_id );

			$count++;
		}

		return $count;
	}

	/**
	 * Mark hard coded links from courses
	 *
	 * @return mixed
	 */
	public static function mark_links_to_remove_from_courses() {
		global $wpdb;

		$courses = $wpdb->get_results(
			"SELECT p.ID,
				p.post_content
			FROM {$wpdb->prefix}posts AS p
			WHERE p.post_type = 'course'
			;"
		);

		$count = 0;

		foreach ( $courses as $course ) {
			// if we already have a div with class to-remove, skip.
			if ( 1 === preg_match( '/<div class="to-remove"/', $course->post_content ) ) {
				continue;
			}

			if ( 1 === preg_match( '/\[vc_row\]\[vc_column\]\[vc_column_text\]\s*<h2><a.*>.*quiz.*<\/a><\/h2>\s*.*$/', $course->post_content ) ) {
				$updated_post_content = preg_replace( '/(\[vc_row\]\[vc_column\]\[vc_column_text\]\s*<h2><a.*>.*quiz.*<\/a><\/h2>\s*.*$)/', '<div class="to-remove match1" style="background:yellow;">$1</div>', $course->post_content );
				wp_update_post(
					array(
						'ID'           => $course->ID,
						'post_content' => $updated_post_content,
					)
				);
				$count++;
				continue;
			}

			if ( 1 === preg_match( '/\[vc_row\]\[vc_column\]\[vc_column_text\]\s*<h3><strong><a.*>.*quiz.*<\/a><\/strong><\/h3>\s*.*$/', $course->post_content ) ) {
				$updated_post_content = preg_replace( '/(\[vc_row\]\[vc_column\]\[vc_column_text\]\s*<h3><strong><a.*>.*quiz.*<\/a><\/strong><\/h3>\s*.*$)/', '<div class="to-remove match2" style="background:yellow;">$1</div>', $course->post_content );
				wp_update_post(
					array(
						'ID'           => $course->ID,
						'post_content' => $updated_post_content,
					)
				);
				$count++;
				continue;
			}

			if ( 1 === preg_match( '/\[vc_row\]\[vc_column width="1\/4"\]\[vc_single_image.*\]\[\/vc_column\]\[vc_column width="3\/4"\]\[vc_column_text\]\s*<h2><a.*>.*emande.*<\/a><\/h2>\s*.*$/', $course->post_content ) ) {
				$updated_post_content = preg_replace( '/(\[vc_row\]\[vc_column width="1\/4"\]\[vc_single_image.*\]\[\/vc_column\]\[vc_column width="3\/4"\]\[vc_column_text\]\s*<h2><a.*>.*emande.*<\/a><\/h2>\s*.*$)/', '<div class="to-remove match3" style="background:yellow;">$1</div>', $course->post_content );
				wp_update_post(
					array(
						'ID'           => $course->ID,
						'post_content' => $updated_post_content,
					)
				);
				$count++;
				continue;
			}

			if ( 1 === preg_match( '/(\[\/vc_column_text\])(\[vc_column_text\]\s*<h2><a.*>.*quiz.*<\/a><\/h2>\s*\[\/vc_column_text\])(\[\/vc_column\]\[\/vc_row\]$)/', $course->post_content, $matches ) ) {
				$updated_post_content = preg_replace( '/(\[\/vc_column_text\])(\[vc_column_text\]\s*<h2><a.*>.*quiz.*<\/a><\/h2>\s*\[\/vc_column_text\])(\[\/vc_column\]\[\/vc_row\]$)/', '$1<div class="to-remove match4" style="background:yellow;">$2</div>$3', $course->post_content );
				wp_update_post(
					array(
						'ID'           => $course->ID,
						'post_content' => $updated_post_content,
					)
				);
				$count++;
				continue;
			}

			if ( 1 === preg_match( '/(\[\/vc_column_text\]\[\/vc_column.*\])(\[vc_column.*\]\[vc_column_text\]\s*<h2><a.*>.*emande.*<\/a><\/h2>\s*\[\/vc_column_text\]\[\/vc_column\])(\[\/vc_row\]$)/', $course->post_content, $matches ) ) {
				$updated_post_content = preg_replace( '/(\[\/vc_column_text\]\[\/vc_column.*\])(\[vc_column.*\]\[vc_column_text\]\s*<h2><a.*>.*emande.*<\/a><\/h2>\s*\[\/vc_column_text\]\[\/vc_column\])(\[\/vc_row\]$)/', '$1<div class="to-remove match5" style="background:yellow;">$2</div>$3', $course->post_content );
				wp_update_post(
					array(
						'ID'           => $course->ID,
						'post_content' => $updated_post_content,
					)
				);
				$count++;
				continue;
			}

			if ( 1 === preg_match( '/(\[vc_row\]\[vc_column\]\[vc_column_text\]\s*<h2><a.*>.*emande.*<\/a><\/h2>\s*.*$)/', $course->post_content ) ) {
				$updated_post_content = preg_replace( '/(\[vc_row\]\[vc_column\]\[vc_column_text\]\s*<h2><a.*>.*emande.*<\/a><\/h2>\s*.*$)/', '<div class="to-remove match6" style="background:yellow;">$1</div>', $course->post_content );
				wp_update_post(
					array(
						'ID'           => $course->ID,
						'post_content' => $updated_post_content,
					)
				);
				$count++;
				continue;
			}

			if ( 1 === preg_match( '/(\[\/vc_column_text\])(\[vc_column_text\]\s*<h2><a.*>.*emande.*<\/a><\/h2>\s*\[\/vc_column_text\]\[\/vc_column\]\[\/vc_row\]\[vc_row\]\[vc_column\]\[vc_column_text\]\s*.*\s*\[\/vc_column_text\])(\[\/vc_column\]\[\/vc_row\]$)/', $course->post_content ) ) {
				$updated_post_content = preg_replace( '/(\[\/vc_column_text\])(\[vc_column_text\]\s*<h2><a.*>.*emande.*<\/a><\/h2>\s*\[\/vc_column_text\]\[\/vc_column\]\[\/vc_row\]\[vc_row\]\[vc_column\]\[vc_column_text\]\s*.*\s*\[\/vc_column_text\])(\[\/vc_column\]\[\/vc_row\]$)/', '$1<div class="to-remove match7" style="background:yellow;">$2</div>$3', $course->post_content );
				wp_update_post(
					array(
						'ID'           => $course->ID,
						'post_content' => $updated_post_content,
					)
				);
				$count++;
				continue;
			}

			if ( 1 === preg_match( '/(\[vc_row\]\[vc_column width="1\/4"\]\[vc_single_image.*\]\[\/vc_column\]\[vc_column width="3\/4"\]\[vc_column_text\]\s*<h2><a.*>.*emande.*<\/a><\/h2>\s*\[\/vc_column_text\]\[\/vc_column\]\[\/vc_row\]\[vc_row\]\[vc_column\]\[vc_column_text\]\s*.*\s*\[\/vc_column_text\]\[\/vc_column\]\[\/vc_row\]$)/', $course->post_content ) ) {
				$updated_post_content = preg_replace( '/(\[vc_row\]\[vc_column width="1\/4"\]\[vc_single_image.*\]\[\/vc_column\]\[vc_column width="3\/4"\]\[vc_column_text\]\s*<h2><a.*>.*emande.*<\/a><\/h2>\s*\[\/vc_column_text\]\[\/vc_column\]\[\/vc_row\]\[vc_row\]\[vc_column\]\[vc_column_text\]\s*.*\s*\[\/vc_column_text\]\[\/vc_column\]\[\/vc_row\]$)/', '<div class="to-remove match8" style="background:yellow;">$1</div>', $course->post_content );
				wp_update_post(
					array(
						'ID'           => $course->ID,
						'post_content' => $updated_post_content,
					)
				);
				$count++;
				continue;
			}
		}

		return $count;
	}

	/**
	 * Remove marked links from courses
	 *
	 * @return mixed
	 */
	public static function removed_marked_links_from_courses() {
		global $wpdb;

		$courses = $wpdb->get_results(
			"SELECT p.ID,
				p.post_content
			FROM {$wpdb->prefix}posts AS p
			WHERE p.post_type = 'course'
			AND p.post_content LIKE '%div class=\"to-remove%'
			;"
		);

		$count = 0;

		foreach ( $courses as $course ) {
			$updated_post_content = preg_replace( '/<div class="to-remove.*>(.|\s)*<\/div>/', '', $course->post_content );
			wp_update_post(
				array(
					'ID'           => $course->ID,
					'post_content' => $updated_post_content,
				)
			);
			$count++;
		}

		return $count;
	}


	/**
	 * Undocumented function.
	 *
	 * @return void
	 */
	public static function remove_submissions_and_attachments() {
		$attachments_removed = 0;
		$submissions_removed = 0;

		WP_CLI::log( 'Starting removal of submissions and their attachments.' );

		// Loop through posts of type 'submission'.
		do {
			// Treat 10 posts at a time.
			$posts = get_posts(
				array(
					'post_type'   => 'submission',
					'numberposts' => 10,
				)
			);

			foreach ( $posts as $post ) {
				// Loop through a post's attachments.
				$attachments = get_attached_media( '', $post->ID );

				foreach ( $attachments as $attachment ) {
					// Delete attachement and related media files.
					wp_delete_attachment( $attachment->ID, 'true' );
					$attachments_removed++;
				}

				// Delete post.
				wp_delete_post( $post->ID, true );
				$submissions_removed++;
				// Progress.
				if ( 0 === ( $submissions_removed % 100 ) ) {
					WP_CLI::log( 'Removal progressing...' );
				}
			}
		} while ( count( $posts ) > 0 );

		WP_CLI::log( sprintf( ' %d submission posts and %d attachments deleted.', $submissions_removed, $attachments_removed ) );

	}


	/**
	 * Undocumented function.
	 *
	 * @return void
	 */
	public static function suppress_old_entities() {
		global $wpdb;

		WP_CLI::confirm( 'Are you sure you want to delete previous Badge Factor 1 data?', array() );

		// achievement-type. FIXME only delete achievement-type badges, rest is not BadgeFactor-specific.
		WP_CLI::log( 'Starting `achievement-type` post type deletion.' );
		$count = $wpdb->query( 'DELETE p, m FROM wp_posts AS p JOIN wp_postmeta AS m ON p.ID = m.post_id WHERE p.post_type = "achievement-type";' );
		WP_CLI::log( sprintf( ' %d achievement-type posts deleted.', $count ) );

		// attachments: relevant attachments to be deleted as part of the submissions deletions. TODO remove this comment.

		// badgeos-log-entry.
		WP_CLI::log( 'Starting `badgeos-log-entry` post type deletion.' );
		$count = $wpdb->query( 'DELETE p, m FROM wp_posts AS p JOIN wp_postmeta AS m ON p.ID = m.post_id WHERE p.post_type = "badgeos-log-entry";' );
		WP_CLI::log( sprintf( ' %d badgeos-log-entry posts deleted.', $count ) );

		// badges.
		WP_CLI::log( 'Starting `badges` post type deletion.' );
		$count = $wpdb->query( 'DELETE p, m FROM wp_posts AS p JOIN wp_postmeta AS m ON p.ID = m.post_id WHERE p.post_type = "badges";' );
		WP_CLI::log( sprintf( ' %d badges posts deleted.', $count ) );

		// bp-email: Do not delete. TODO remove this comment.

		// community-badge. TODO remove this from badgefactor repository (client-specific).
		WP_CLI::log( 'Starting `community-badge` post type deletion.' );
		$count = $wpdb->query( 'DELETE p, m FROM wp_posts AS p JOIN wp_postmeta AS m ON p.ID = m.post_id WHERE p.post_type = "community-badge";' );
		WP_CLI::log( sprintf( ' %d community-badge posts deleted.', $count ) );

		// event_participant. TODO remove this from badgefactor repository (client-specific).
		WP_CLI::log( 'Starting `event_participant` post type deletion.' );
		$count = $wpdb->query( 'DELETE p, m FROM wp_posts AS p JOIN wp_postmeta AS m ON p.ID = m.post_id WHERE p.post_type = "event_participant";' );
		WP_CLI::log( sprintf( ' %d event_participant posts deleted.', $count ) );

		// event_users. TODO remove this from badgefactor repository (client-specific).
		WP_CLI::log( 'Starting `event_users` post type deletion.' );
		$count = $wpdb->query( 'DELETE p, m FROM wp_posts AS p JOIN wp_postmeta AS m ON p.ID = m.post_id WHERE p.post_type = "event_users";' );
		WP_CLI::log( sprintf( ' %d event_users posts deleted.', $count ) );

		// events: Do not delete. TODO remove this comment.

		// level. TODO remove this from badgefactor repository (client-specific).
		WP_CLI::log( 'Starting `level` post type deletion.' );
		$count = $wpdb->query( 'DELETE p, m FROM wp_posts AS p JOIN wp_postmeta AS m ON p.ID = m.post_id WHERE p.post_type = "level";' );
		WP_CLI::log( sprintf( ' %d level posts deleted.', $count ) );

		// persobadgecat. TODO remove this from badgefactor repository (client-specific).
		WP_CLI::log( 'Starting `persobadgecat` post type deletion.' );
		$count = $wpdb->query( 'DELETE p, m FROM wp_posts AS p JOIN wp_postmeta AS m ON p.ID = m.post_id WHERE p.post_type = "persobadgecat";' );
		WP_CLI::log( sprintf( ' %d persobadgecat posts deleted.', $count ) );

		// postman_sent_mail. TODO remove this from badgefactor repository (client-specific).
		WP_CLI::log( 'Starting `postman_sent_mail` post type deletion.' );
		$count = $wpdb->query( 'DELETE p, m FROM wp_posts AS p JOIN wp_postmeta AS m ON p.ID = m.post_id WHERE p.post_type = "postman_sent_mail";' );
		WP_CLI::log( sprintf( ' %d postman_sent_mail posts deleted.', $count ) );

		// quest. TODO remove this from badgefactor repository (client-specific).
		WP_CLI::log( 'Starting `quest` post type deletion.' );
		$count = $wpdb->query( 'DELETE p, m FROM wp_posts AS p JOIN wp_postmeta AS m ON p.ID = m.post_id WHERE p.post_type = "quest";' );
		WP_CLI::log( sprintf( ' %d quest posts.', $count ) );

		// quest-badge. TODO remove this from badgefactor repository (client-specific).
		WP_CLI::log( 'Starting `quest-badge` post type deletion.' );
		$count = $wpdb->query( 'DELETE p, m FROM wp_posts AS p JOIN wp_postmeta AS m ON p.ID = m.post_id WHERE p.post_type = "quest-badge";' );
		WP_CLI::log( sprintf( ' %d quest-badge posts deleted.', $count ) );

		// reply. TODO remove this from badgefactor repository (client-specific).
		WP_CLI::log( 'Starting `reply` post type deletion.' );
		$count = $wpdb->query( 'DELETE p, m FROM wp_posts AS p JOIN wp_postmeta AS m ON p.ID = m.post_id WHERE p.post_type = "reply";' );
		WP_CLI::log( sprintf( ' %d reply posts deleted.', $count ) );

		// step. TODO remove this from badgefactor repository (client-specific).
		WP_CLI::log( 'Starting `step` post type deletion.' );
		$count = $wpdb->query( 'DELETE p, m FROM wp_posts AS p JOIN wp_postmeta AS m ON p.ID = m.post_id WHERE p.post_type = "step";' );
		WP_CLI::log( sprintf( ' %d step posts deleted.', $count ) );

		// submission.
		self::remove_submissions_and_attachments();

		// teachers. TODO remove this from badgefactor repository (client-specific).
		WP_CLI::log( 'Starting `teachers` post type deletion.' );
		$count = $wpdb->query( 'DELETE p, m FROM wp_posts AS p JOIN wp_postmeta AS m ON p.ID = m.post_id WHERE p.post_type = "teachers";' );
		WP_CLI::log( sprintf( ' %d teachers posts deleted.', $count ) );

		// topic. TODO remove this from badgefactor repository (client-specific).
		WP_CLI::log( 'Starting `topic` post type deletion.' );
		$count = $wpdb->query( 'DELETE p, m FROM wp_posts AS p JOIN wp_postmeta AS m ON p.ID = m.post_id WHERE p.post_type = "topic";' );
		WP_CLI::log( sprintf( ' %d topic posts deleted.', $count ) );

		// vc_grid_item: Do not delete.  TODO remove this comment.
		// wpcf7_contact_form: Do not delete. TODO remove this comment.

		// Remove orphaned revisions.
		WP_CLI::log( 'Starting to remove orphaned revisions.' );
		$count = $wpdb->query(
			'
		DELETE r FROM wp_posts AS r
		LEFT JOIN wp_posts AS p
		ON SUBSTRING_INDEX(r.post_name, "-", 1) = p.ID
		WHERE r.post_type = "revision" AND p.ID IS NULL;'
		);
		WP_CLI::log( sprintf( ' %d orphaned revisions removed.', $count ) );

		// Remove orphaned metas
		WP_CLI::log( 'Starting removal of orphaned metas.' );
		$count = $wpdb->query( 'DELETE m FROM wp_postmeta AS m LEFT JOIN wp_posts AS p ON p.ID = m.post_id WHERE p.ID IS NULL;' );
		WP_CLI::log( sprintf( ' %d orphaned metas deleted.', $count ) );

	}
}
