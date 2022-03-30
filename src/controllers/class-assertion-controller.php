<?php
/**
 * Badge Factor 2
 * Copyright (C) 2021 ctrlweb
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
 * @phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
 * @phpcs:disable WordPress.WP.I18n.NonSingularStringLiteralDomain
 */

namespace BadgeFactor2\Controllers;

use BadgeFactor2\Models\Assertion;
use BadgeFactor2\Models\BadgeClass;
use BadgeFactor2\Models\Issuer;
use BadgeFactor2\Page_Controller;
use BadgeFactor2\Post_Types\BadgePage;
use BadgeFactor2\Post_Types\BadgeRequest;
use stdClass;
use WP_Post;
use BadgeFactor2\AssertionPrivacy;

/**
 * Assertion Controller Class.
 */
class Assertion_Controller extends Page_Controller {

	/**
	 * Post Type.
	 *
	 * @var string
	 */
	protected static $post_type = 'assertion';

	/**
	 * Returns or outputs archive template with $fields array.
	 *
	 * @param string $default_template Default template (for filter hook).
	 * @return void|string
	 */
	public static function archive( $default_template = null ) {
		if ( bp_is_user() ) {
			global $bp;
			$fields               = array();
			$fields['user']       = get_user_by( 'id', $bp->displayed_user->id );

			$unfiltered_assertions =  Assertion::all_for_user( $fields['user'] );

			if ( $fields['user']->ID == get_current_user_id() ) {
				$fields['assertions'] = $unfiltered_assertions;

				foreach ( $fields['assertions'] as $i => $assertion ) {
					$fields['assertions'][ $i ]->badge     = BadgeClass::get( $assertion->badgeclass );
					$fields['assertions'][ $i ]->issuer    = Issuer::get( $fields['assertions'][ $i ]->badge->issuer );
					$fields['assertions'][ $i ]->badgepage = BadgePage::get_by_badgeclass_id( $assertion->badgeclass );
				}
			} else {
				$fields['assertions'] = [];
				$filtered_assertions_count = 0;
				$user_privacy_flags = AssertionPrivacy::get_user_privacy_flags($fields['user']->ID);

				foreach( $unfiltered_assertions as $assertion ) {
					$badge_class = BadgeClass::get( $assertion->badgeclass );
					if ( !in_array( $badge_class->entityId, $user_privacy_flags ) ) {
						$fields['assertions'][ $filtered_assertions_count ] = $assertion;
						$fields['assertions'][ $filtered_assertions_count ]->badge     = $badge_class;
						$fields['assertions'][ $filtered_assertions_count ]->issuer    = Issuer::get( $fields['assertions'][ $filtered_assertions_count ]->badge->issuer );
						$fields['assertions'][ $filtered_assertions_count ]->badgepage = BadgePage::get_by_badgeclass_id( $assertion->badgeclass );
						$filtered_assertions_count++;
					}
				}
			}


			global $bf2_template;
			$bf2_template         = new stdClass();
			$bf2_template->fields = $fields;

			return parent::archive( $default_template );
		}
		if ( $default_template ) {
			return parent::archive( $default_template );
		}
	}


	/**
	 * Outputs single template with $fields array.
	 *
	 * @param string $default_template Default template (for filter hook).
	 * @return void|string
	 */
	public static function single( $default_template = null ) {
		if ( get_query_var( 'member' ) && get_query_var( 'badge' ) ) {
			$fields         = array();
			$fields['user'] = get_user_by( 'slug', get_query_var( 'member' ) );
			if ( ! $fields['user'] ) {
				$is_404 = true;
			} else {
				$fields['logged_in_user_s_own_page'] = $fields['user']->ID == get_current_user_id();
				foreach ( Assertion::all_for_user( $fields['user'] ) as $a ) {
					$badgepage = BadgePage::get_by_badgeclass_id( $a->badgeclass );
					if ( get_query_var( 'badge' ) === $badgepage->post_name ) {
						$fields['badgepage'] = $badgepage;
						$fields['assertion'] = $a;
						break;
					}
				}

				if ( ! isset( $fields['assertion'] ) ) {
					$is_404 = true;
				} else {
					$fields['badge']  = BadgeClass::get( $fields['assertion']->badgeclass );
					$fields['issuer'] = Issuer::get( $fields['assertion']->issuer );
					$fields['badge-request'] = BadgeRequest::get_for_badgeclass_for_user( $fields['assertion']->badgeclass, $fields['user']->ID );

					AssertionPrivacy::enqueue_scripts($fields['badge']->entityId);

					$fields['assertion']->has_privacy_flag = AssertionPrivacy::has_privacy_flag( $fields['badge']->entityId, $fields['user']->ID);

					global $bf2_template;
					$bf2_template         = new stdClass();
					$bf2_template->fields = $fields;
					global $wp_query;
					$wp_query->is_404 = false;
					status_header( 200 );
					nocache_headers();
					return parent::single( true );
				}
			}
			if ( $is_404 ) {
				global $wp_query;
				$wp_query->set_404();
				status_header( 404 );
				nocache_headers();
				set_query_var( 'member', false );
				set_query_var( 'badge', false );
				return get_query_template( '404' );
			}
		}
		if ( $default_template ) {
			return $default_template;
		}
	}


	/**
	 * Returns custom title for template.
	 *
	 * @param array $titles Titles array.
	 * @return string
	 */
	public static function title( $titles = array() ) {
		if ( get_query_var( 'member' ) && get_query_var( 'badge' ) ) {
			$post            = BadgePage::get( get_query_var( 'badge' ) );
			$titles['title'] = $post->post_title;
		}
		return $titles;
	}
}
