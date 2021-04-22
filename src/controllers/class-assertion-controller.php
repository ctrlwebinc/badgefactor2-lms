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
use stdClass;
use WP_Post;

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
			$fields['assertions'] = Assertion::all_for_user( $fields['user'] );

			foreach ( $fields['assertions'] as $i => $assertion ) {
				$fields['assertions'][ $i ]->badge     = BadgeClass::get( $assertion->badgeclass );
				$fields['assertions'][ $i ]->issuer    = Issuer::get( $fields['assertions'][ $i ]->badge->issuer );
				$fields['assertions'][ $i ]->badgepage = BadgePage::get_by_badgeclass_id( $assertion->badgeclass );
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

					global $bf2_template;
					$bf2_template         = new stdClass();
					$bf2_template->fields = $fields;
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
