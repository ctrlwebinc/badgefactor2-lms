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
 * @phpcs:disable WordPress.WP.I18n.NonSingularStringLiteralDomain
 */

namespace BadgeFactor2;

use BadgeFactor2\Controllers\Assertion_Controller;
use BadgeFactor2\Controllers\Issuer_Controller;
use BadgeFactor2\Helpers\BuddyPress;
use BadgeFactor2\Helpers\Template;
use BadgeFactor2\Models\Assertion;
use BadgeFactor2\Models\BadgeClass;
use BadgeFactor2\Models\Issuer;
use BadgeFactor2\Post_Types\BadgePage;

/**
 * Badge Factor 2 Admin Class.
 */
class Badgr_Public_Urls {

	/**
	 * Init Hooks.
	 *
	 * @return void
	 */
	public static function init_hooks() {
		add_action( 'init', array( self::class, 'add_rewrite_tags' ), 10, 0 );
		add_action( 'init', array( self::class, 'add_rewrite_rules' ), 10, 0 );
		add_filter( 'query_vars', array( self::class, 'add_custom_query_vars' ) );
		add_filter( 'template_redirect', array( self::class, 'redirect' ) );
	}

	/**
	 * Rewrite tags.
	 *
	 * @return void
	 */
	public static function add_rewrite_tags() {
		add_rewrite_tag( '%badgr_redirect_type%', '([^&]+)' );
		add_rewrite_tag( '%badgr_redirect_value%', '([^&]+)' );
	}

	/**
	 * Rewrite rules.
	 *
	 * @return void
	 */
	public static function add_rewrite_rules() {
		$options                    = get_option( 'badgefactor2_badgr_settings' );
		$public_pages_redirect_slug = ! empty( $options['badgr_server_public_pages_redirect_slug'] ) ? $options['badgr_server_public_pages_redirect_slug'] : 'badgr';
		add_rewrite_rule( "{$public_pages_redirect_slug}/assertions/([^/]+)/?$", 'index.php?badgr_redirect_type=assertion&badgr_redirect_value=$matches[1]', 'top' );
		add_rewrite_rule( "{$public_pages_redirect_slug}/badges/([^/]+)/?$", 'index.php?badgr_redirect_type=badge&badgr_redirect_value=$matches[1]', 'top' );
		add_rewrite_rule( "{$public_pages_redirect_slug}/issuers/([^/]+)/?$", 'index.php?badgr_redirect_type=issuer&badgr_redirect_value=$matches[1]', 'top' );
	}

	/**
	 * Custom query variables.
	 *
	 * @param array $vars Query variables.
	 * @return array
	 */
	public static function add_custom_query_vars( $vars ) {
		$vars[] = 'badgr_redirect_type';
		$vars[] = 'badgr_redirect_value';
		return $vars;
	}


	/**
	 * Redirect rules.
	 *
	 * @return void
	 */
	public static function redirect() {
		if ( get_query_var( 'badgr_redirect_type' ) && get_query_var( 'badgr_redirect_value' ) ) {
			switch ( get_query_var( 'badgr_redirect_type' ) ) {
				case 'assertion':
					$assertion = Assertion::get( get_query_var( 'badgr_redirect_value' ) );
					if ( $assertion ) {
						$user = get_user_by( 'email', $assertion->recipient->plaintextIdentity );
						if ( $user ) {
							if ( BuddyPress::is_active() ) {
								$member_page = bp_core_get_user_domain( $user->ID );
							} else {
								// TODO Manage Members page without BuddyPress.
								$member_page = sprintf( '%s/members/%s/', get_site_url(), rawurlencode( $user->user_nicename ) );
							}

							$badge_page = BadgePage::get_by_badgeclass_id( $assertion->badgeclass );
							wp_redirect( sprintf( '%s/badges/%s/', $member_page, $badge_page->post_name ) );
							exit;
						}
					}
					break;
				case 'badge':
					$badge_page = BadgePage::get_by_badgeclass_id( get_query_var( 'badgr_redirect_value' ) );
					if ( $badge_page ) {
						wp_redirect( get_permalink( $badge_page ) );
						exit;
					}
					break;
				case 'issuer':
					$issuer = Issuer::get( get_query_var( 'badgr_redirect_value' ) );
					if ( $issuer ) {
						$options      = get_option( 'badgefactor2' );
						$issuers_slug = ! empty( $options['bf2_issuers_slug'] ) ? $options['bf2_issuers_slug'] : 'issuers';
						$issuer_name  = strtolower( $issuer->name );
						wp_redirect( sprintf( '%s/%s/%s', get_site_url(), $issuers_slug, $issuer_name ) );
						exit;
					}
					break;
			}
		}
	}

}
