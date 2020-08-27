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

use BadgeFactor2\Helpers\Template;
use BadgeFactor2\Models\BadgeClass;

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
		add_action( 'wp_enqueue_scripts', array( BadgeFactor2_Public::class, 'load_resources' ) );
		add_action( 'wp_ajax_submit_badge_request_form', array( BadgeFactor2_Public::class, 'ajax_badge_request' ) );

		/*
		 * TODO If we want to make a members list and page without buddypress.
		 *
		 * add_filter( 'template_include', array( BadgeFactor2_Public::class, 'add_members_to_hierarchy' ) );
		 */
	}


	/**
	 * Rewrite tags.
	 *
	 * @return void
	 */
	public static function add_rewrite_tags() {
		add_rewrite_tag( '%issuer%', '([^&]+)' );
		add_rewrite_tag( '%form%', '([^&]+)' );

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

		add_rewrite_rule( 'issuers/([^/]+)/?$', 'index.php?issuer=$matches[1]', 'top' );
		$form_slug = isset( $options['bf2_form_slug'] ) ? $options['bf2_form_slug'] : 'form';
		add_rewrite_rule( "badges/([^/]+)/{$form_slug}/?$", 'index.php?badge-page=$matches[1]&form=1', 'top' );

		/*
		 * TODO If we want to make a members list and page without buddypress.
		 *
		 * $member_slug = isset( $options['bf2_members_slug'] ) ? $options['bf2_members_slug'] : 'members';
		 * add_rewrite_rule( "{$member_slug}/?$", 'index.php?member=all', 'top' );
		 * add_rewrite_rule( "{$member_slug}/([^/]*)/?$", 'index.php?member=$matches[1]', 'top' );
		 */
	}


	/**
	 * Custom query variables.
	 *
	 * @param array $vars Query variables.
	 * @return array
	 */
	public static function add_custom_query_vars( $vars ) {
		$vars[] = 'issuer';
		$vars[] = 'form';

		/*
		 * TODO If we want to make a members list and page without buddypress.
		 *
		 * $vars[] = 'member';
		 */

		return $vars;
	}


	/**
	 * Add members to hierarchy.
	 *
	 * @param string $original_template Original template.
	 * @return string
	 */
	public static function add_members_to_hierarchy( $original_template ) {
		/*
		 * TODO If we want to make a members list and page without buddypress.
		 *
		 * return static::add_to_hierarchy( $original_template, 'member', 'members' );
		 */
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
	 * Manage ajax badge requests from basic forms.
	 *
	 * @return void
	 */
	public static function ajax_badge_request() {
		$response     = array(
			'success' => false,
			'message' => __( 'Something went wrong...', BF2_DATA['TextDomain'] ),
		);
		$status_code  = 400;
		$current_user = wp_get_current_user();
		if ( $current_user ) {
			$badge_id = $_POST['badge_id'];
			$badge    = BadgeClass::get( $badge_id );
			$content  = $_POST['content'];
			$type     = $_POST['type'];

			// Create a badge request.
			$badge_request_id = wp_insert_post(
				array(
					'post_type'      => 'badge-request',
					'post_title'     => sprintf( '%s - %s - %s', $current_user->user_nicename, $badge->name, gmdate( 'Y-m-d H:i:s' ) ),
					'post_content'   => '',
					'post_author'    => $current_user,
					'post_status'    => 'publish',
					'comment_status' => 'closed',
					'ping_status'    => 'closed',
				)
			);
			if ( $badge_request_id ) {
				add_post_meta( $badge_request_id, 'badge', $badge_id );
				add_post_meta( $badge_request_id, 'type', $type );
				add_post_meta( $badge_request_id, 'content', $content );
				add_post_meta( $badge_request_id, 'recipient', $current_user->ID );
				add_post_meta( $badge_request_id, 'status', 'requested' );
				add_post_meta( $badge_request_id, 'dates', array( 'requested' => gmdate( 'Y-m-d H:i:s' ) ) );
				$status_code = 201;
				$response    = array(
					'success' => true,
				);
			}
		}

		wp_send_json( $response, $status_code );
	}


	/**
	 * Add item to hierarchy.
	 *
	 * @param string $original_template Original template.
	 * @param string $item Item.
	 * @param string $archive Archive.
	 * @return string
	 */
	private static function add_to_hierarchy( $original_template, $item, $archive = null ) {
		if ( get_query_var( $item, false ) ) {
			$template          = $archive ?? $item;
			$original_template = Template::locate( "tpl.{$template}", $original_template );
		}
		return $original_template;
	}
}
