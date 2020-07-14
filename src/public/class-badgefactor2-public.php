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

/**
 * Badge Factor 2 Admin Class.
 */
class BadgeFactor2_Public {


	/**
	 * Init Hooks.
	 *
	 * @return void
	 */
	public static function init_hooks() {
		add_action( 'init', array( BadgeFactor2_Public::class, 'add_rewrite_tags' ), 10, 0 );
		add_action( 'init', array( BadgeFactor2_Public::class, 'add_rewrite_rules' ), 10, 0 );
		remove_action( 'register_new_user', 'wp_send_new_user_notifications' );
		add_action( 'register_new_user', array( BadgeFactor2_Public::class, 'suppress_new_user_notifications' ), 10, 2 );
		add_filter( 'query_vars', array( BadgeFactor2_Public::class, 'add_custom_query_vars' ) );
		add_filter( 'template_include', array( BadgeFactor2_Public::class, 'add_badge_to_hierarchy' ) );
		add_filter( 'template_include', array( BadgeFactor2_Public::class, 'add_assertion_to_hierarchy' ) );

	}


	/**
	 * Rewrite tags.
	 *
	 * @return void
	 */
	public static function add_rewrite_tags() {
		add_rewrite_tag( '%issuer%', '([^&]+)' );
		add_rewrite_tag( '%badge%', '([^&]+)' );
		add_rewrite_tag( '%assertion%', '([^&]+)' );
	}


	/**
	 * Rewrite rules.
	 *
	 * @return void
	 */
	public static function add_rewrite_rules() {
		add_rewrite_rule( '^issuers/([^/]*)/?', 'index.php?issuer=$matches[1]', 'top' );
		add_rewrite_rule( '^badges/([^/]*)/?', 'index.php?badge=$matches[1]', 'top' );
		add_rewrite_rule( '^assertions/([^/]*)/?', 'index.php?assertion=$matches[1]', 'top' );
	}


	/**
	 * Custom query variables.
	 *
	 * @param array $vars Query variables.
	 * @return array
	 */
	public static function add_custom_query_vars( $vars ) {
		$vars[] = 'issuer';
		$vars[] = 'badge';
		$vars[] = 'assertion';
		return $vars;
	}


	/**
	 * Add badge to hierarchy.
	 *
	 * @param string $original_template Original template.
	 * @return string
	 */
	public static function add_badge_to_hierarchy( $original_template ) {
		return static::add_to_hierarchy( $original_template, 'badge' );
	}


	/**
	 * Add assertions to hierarchy.
	 *
	 * @param string $original_template Original template.
	 * @return string
	 */
	public static function add_assertion_to_hierarchy( $original_template ) {
		return static::add_to_hierarchy( $original_template, 'assertion' );
	}


	/**
	 * Suppress new user notifications.
	 *
	 * @param int    $user_id User ID.
	 * @param string $notify Whether to notify user, admin or both.
	 * @return void
	 */
	public static function suppress_new_user_notifications( $user_id, $notify = 'both' ) {
		$badgefactor2_options = get_option( 'badgefactor2' );
		if ( isset( $badgefactor2_options['bf2_send_new_user_notifications'] ) && 'on' === $badgefactor2_options['bf2_send_new_user_notifications'] ) {
			wp_send_new_user_notifications( $user_id, $notify );
		}
	}


	/**
	 * Add item to hierarchy.
	 *
	 * @param string $original_template Original template.
	 * @param string $item Item.
	 * @return string
	 */
	private static function add_to_hierarchy( $original_template, $item ) {
		if ( get_query_var( $item, false ) ) {
			$original_template = locate_template( "badgefactor2/tpl.{$item}.php" );
			if ( ! $original_template ) {
				$original_template = BF2_ABSPATH . "templates/tpl.{$item}.php";
			}
		}
		return $original_template;
	}
}
