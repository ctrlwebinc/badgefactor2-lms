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

/**
 * Text helper class.
 */
class SocialShare {

    /**
	 * Init tasks
	 *
	 * @return void
	 */
	public static function init_hooks() {
		add_action( 'init', array( self::class, 'init' ) );
		add_filter( 'query_vars', array( self::class, 'hook_query_vars' ) );
		add_action( 'template_redirect', array( self::class, 'hook_template_redirect' ) );
	}
    
    /**
	 * Init hook.
	 *
	 * @return void
	 */
	public static function init() {
		// TODO: add auth/welcome.
		add_rewrite_rule(
			'bf2/(share)/?',
			'index.php?bf2=$matches[1]',
			'top'
		);
	}

    /**
	 * Signal ou interest in bf2 query variable
	 *
	 * @param array $vars Variables.
	 * @return array
	 */
	public static function hook_query_vars( $vars ) {
		$vars[] = 'bf2';
		return $vars;
	}

	public static function hook_template_redirect() {
		$bf2 = get_query_var( 'bf2' );
		if ( $bf2 ) {
			if ( 'share' === $bf2 ) {
                header( 'Content-Type: text/plain' );
                echo 'Badgr callback: ' . $bf2;
                echo ' Full uri: ' . $_SERVER['REQUEST_URI'];
                exit();
            }
		}
	}
}
