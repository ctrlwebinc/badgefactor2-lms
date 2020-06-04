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
		add_filter( 'query_vars', array( BadgeFactor2_Public::class, 'add_custom_query_vars' ) );
		add_filter( 'template_include', array( BadgeFactor2_Public::class, 'add_badge_to_hierarchy' ) );
		add_filter( 'template_include', array( BadgeFactor2_Public::class, 'add_assertion_to_hierarchy' ) );

	}

	public static function add_rewrite_tags( ) {
		add_rewrite_tag( '%badge%', '([^&]+)' );
		add_rewrite_tag( '%assertion%', '([^&]+)' );
	}

	public static function add_rewrite_rules() {
		add_rewrite_rule( '^badges/([^/]*)/?', 'index.php?badge=$matches[1]', 'top' );
		add_rewrite_rule( '^assertions/([^/]*)/?', 'index.php?assertion=$matches[1]', 'top' );
	}

	public static function add_custom_query_vars( $vars ) {
		$vars[] = 'badge';
		$vars[] = 'assertion';
		return $vars;
	}

	public static function add_badge_to_hierarchy( $original_template ) {
		return static::add_to_hierarchy( $original_template, 'badge' );
	}

	public static function add_assertion_to_hierarchy( $original_template ) {
		return static::add_to_hierarchy( $original_template, 'assertion' );
	}

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
