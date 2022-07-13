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
		add_action( 'init', array( self::class, 'add_rewrite_tags' ), 10, 0 );
		add_action( 'init', array( self::class, 'add_rewrite_rules' ), 10, 0 );
		remove_action( 'register_new_user', 'wp_send_new_user_notifications' );
		add_action( 'register_new_user', array( self::class, 'suppress_new_user_notifications' ), 10, 2 );
		add_filter( 'query_vars', array( self::class, 'add_custom_query_vars' ) );
		add_action( 'wp_enqueue_scripts', array( self::class, 'load_resources' ) );

		if ( ! BuddyPress::is_active() ) {
			add_filter( 'template_include', array( self::class, 'member_template' ) );
		}
		add_filter( 'template_include', array( Assertion_Controller::class, 'single' ), 20 );
		add_filter( 'template_include', array( Issuer_Controller::class, 'archive' ), 20 );
		add_filter( 'template_include', array( Issuer_Controller::class, 'single' ), 20 );
		add_filter( 'document_title_parts', array( Assertion_Controller::class, 'title' ), 20 );
		add_filter( 'unlogged_user_badge_request_form_message', array( self::class, 'badge_request_message_for_unlogged_user' ), 20, 2 );
	}


	/**
	 * Rewrite tags.
	 *
	 * @return void
	 */
	public static function add_rewrite_tags() {
		add_rewrite_tag( '%issuers%', '([^&]+)' );
		add_rewrite_tag( '%issuer%', '([^&]+)' );
		add_rewrite_tag( '%form%', '([^&]+)' );
		add_rewrite_tag( '%member%', '([^&]+)' );
		add_rewrite_tag( '%badge%', '([^&]+)' );

		/*
		 * TODO If we want to make a members list and page without buddypress.
		 *
		 * add_rewrite_tag( '%member%', '([^&]+)' );
		 */
	}


	/**
	 * Rewrite rules.
	 *
	 * @return void
	 */
	public static function add_rewrite_rules() {
		$options = get_option( 'badgefactor2' );

		if ( BuddyPress::is_active() ) {
			// Members page managed by BuddyPress.
			$members_page = BuddyPress::get_members_page_name();
		} else {
			// TODO Manage Members page without BuddyPress.
			$members_page = 'members';
		}

		$form_slug                = ! empty( $options['bf2_form_slug'] ) ? $options['bf2_form_slug'] : 'form';
		$autoevaluation_form_slug = ! empty( $options['bf2_autoevaluation_form_slug'] ) ? $options['bf2_autoevaluation_form_slug'] : 'autoevaluation';
		$issuers_slug             = ! empty( $options['bf2_issuers_slug'] ) ? $options['bf2_issuers_slug'] : 'issuers';

		add_rewrite_rule( "badges/([^/]+)/{$form_slug}/?$", 'index.php?badge-page=$matches[1]&form=1', 'top' );
		add_rewrite_rule( "badges/([^/]+)/{$autoevaluation_form_slug}/?$", 'index.php?badge-page=$matches[1]&form=1&autoevaluation=1', 'top' );
		add_rewrite_rule( "{$members_page}/([^/]+)/badges/([^/]+)/?$", 'index.php?member=$matches[1]&badge=$matches[2]', 'top' );
		add_rewrite_rule( "{$issuers_slug}/?$", 'index.php?issuers=1', 'top' );
		add_rewrite_rule( "{$issuers_slug}/([^/]+)/?$", 'index.php?issuer=$matches[1]', 'top' );
	}


	/**
	 * Custom query variables.
	 *
	 * @param array $vars Query variables.
	 * @return array
	 */
	public static function add_custom_query_vars( $vars ) {
		$vars[] = 'issuers';
		$vars[] = 'issuer';
		$vars[] = 'form';
		$vars[] = 'autoevaluation';
		$vars[] = 'member';
		$vars[] = 'badge';
		$vars[] = 'certificate';

		return $vars;
	}


	/**
	 * Add member template to hierarchy.
	 *
	 * @param string $original_template Original template.
	 * @return string
	 */
	public static function member_template( $original_template ) {
		// TODO Add member page template.
		return $original_template;
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
	 * Public Resources Loader.
	 *
	 * @return void
	 */
	public static function load_resources() {
		wp_enqueue_style( 'badgefactor2-css', BF2_BASEURL . 'assets/css/public.css', array(), BF2_DATA['Version'], 'all' );
		wp_enqueue_script( 'badgefactor2-js', BF2_BASEURL . 'assets/js/public.js', array( 'jquery' ), BF2_DATA['Version'], true );
	}

	/**
	 * Shows message for unlogged in users on badge request form
	 * 
	 * @return string $permalink
	 */
	public static function badge_request_message_for_unlogged_user ( $login_permalink = '', $registration_permalink = '' ) {
		$options = get_option( 'badgefactor2' );

		$login_slug = ! empty( $options['bf2_login_page_slug'] ) ? $options['bf2_login_page_slug'] : '';
		$login_permalink = ( $login_permalink != '' ) ? $login_permalink : $login_slug;
		$login_permalink = site_url( $login_permalink ) . '/';

		$registration_slug = ! empty( $options['bf2_registration_page_slug'] ) ? $options['bf2_registration_page_slug'] : '';
		$registration_permalink = ( $registration_permalink != '' ) ? $registration_permalink : $registration_slug;
		$registration_permalink = site_url( $registration_permalink ) . '/';

		if ( $registration_slug != '' ) {
			$message = sprintf( 
					__( 'Please <a href="%s">register</a> or <a href="%s">login</a> first.', BF2_GRAVITYFORMS_DATA['TextDomain'] ), 
					$registration_permalink,
					$login_permalink
				);
		} else {
			$message = sprintf( 
					__( 'Please <a href="%s">login</a> first.', BF2_GRAVITYFORMS_DATA['TextDomain'] ), 
					$login_permalink
				);
		}
			
		return sprintf( '<p><em>%s</em></p>', $message);
	}
}
