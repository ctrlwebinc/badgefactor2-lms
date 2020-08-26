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
 * @phpcs:disable WordPress.WP.I18n.NonSingularStringLiteralContext
 */

namespace BadgeFactor2\Post_Types;

use BadgeFactor2\Helpers\Template;

/**
 * Badge Request post type.
 */
class BadgeRequest {


	/**
	 * Custom post type's slug.
	 *
	 * @var string
	 */
	private static $slug = 'badge-request';

	/**
	 * Custom post type's slug, pluralized.
	 *
	 * @var string
	 */
	private static $slug_plural = 'badge-requests';

	/**
	 * Init hooks.
	 *
	 * @return void
	 */
	public static function init_hooks() {
		add_action( 'init', array( BadgeRequest::class, 'init' ), 10 );
		add_action( 'admin_init', array( BadgeRequest::class, 'add_capabilities' ), 10 );
		add_filter( 'post_updated_messages', array( BadgeRequest::class, 'updated_messages' ), 10 );
		add_action( 'cmb2_admin_init', array( BadgeRequest::class, 'register_cpt_metaboxes' ), 10 );
		add_action( 'save_post_' . self::$slug, array( BadgeRequest::class, 'update_badge_request' ), 10, 3 );
	}


	/**
	 * Registers the `badge_request` post type.
	 */
	public static function init() {
		register_post_type(
			self::$slug,
			array(
				'labels'            => array(
					'name'                  => __( 'Badge Requests', BF2_DATA['TextDomain'] ),
					'singular_name'         => __( 'Badge Request', BF2_DATA['TextDomain'] ),
					'all_items'             => __( 'All Badge Requests', BF2_DATA['TextDomain'] ),
					'archives'              => __( 'Badge Request Archives', BF2_DATA['TextDomain'] ),
					'attributes'            => __( 'Badge Request Attributes', BF2_DATA['TextDomain'] ),
					'insert_into_item'      => __( 'Insert into Badge Request', BF2_DATA['TextDomain'] ),
					'uploaded_to_this_item' => __( 'Uploaded to this Badge Request', BF2_DATA['TextDomain'] ),
					'featured_image'        => _x( 'Featured Image', self::$slug, BF2_DATA['TextDomain'] ),
					'set_featured_image'    => _x( 'Set featured image', self::$slug, BF2_DATA['TextDomain'] ),
					'remove_featured_image' => _x( 'Remove featured image', self::$slug, BF2_DATA['TextDomain'] ),
					'use_featured_image'    => _x( 'Use as featured image', self::$slug, BF2_DATA['TextDomain'] ),
					'filter_items_list'     => __( 'Filter Badge Requests list', BF2_DATA['TextDomain'] ),
					'items_list_navigation' => __( 'Badge Requests list navigation', BF2_DATA['TextDomain'] ),
					'items_list'            => __( 'Badge Requests list', BF2_DATA['TextDomain'] ),
					'new_item'              => __( 'New Badge Request', BF2_DATA['TextDomain'] ),
					'add_new'               => __( 'Add New', BF2_DATA['TextDomain'] ),
					'add_new_item'          => __( 'Add New Badge Request', BF2_DATA['TextDomain'] ),
					'edit_item'             => __( 'Edit Badge Request', BF2_DATA['TextDomain'] ),
					'view_item'             => __( 'View Badge Request', BF2_DATA['TextDomain'] ),
					'view_items'            => __( 'View Badge Requests', BF2_DATA['TextDomain'] ),
					'search_items'          => __( 'Search Badge Requests', BF2_DATA['TextDomain'] ),
					'not_found'             => __( 'No Badge Requests found', BF2_DATA['TextDomain'] ),
					'not_found_in_trash'    => __( 'No Badge Requests found in trash', BF2_DATA['TextDomain'] ),
					'parent_item_colon'     => __( 'Parent Badge Request:', BF2_DATA['TextDomain'] ),
					'menu_name'             => __( 'Badge Requests', BF2_DATA['TextDomain'] ),
				),
				'public'            => false,
				'hierarchical'      => false,
				'show_ui'           => true,
				'show_in_nav_menus' => true,
				'supports'          => array( 'title' ),
				'has_archive'       => false,
				'rewrite'           => true,
				'query_var'         => true,
				'menu_position'     => 52,
				'menu_icon'         => 'dashicons-feedback',
				'show_in_rest'      => false,
				'capability_type'   => array( self::$slug, self::$slug_plural ),
				'capabilities'      => array(
					'create_posts' => 'do_not_allow',
				),
				'map_meta_cap'      => false,
			)
		);

	}


	/**
	 * Sets the post updated messages for the `badge_request` post type.
	 *
	 * @param  array $messages Post updated messages.
	 * @return array Messages for the `badge_request` post type.
	 */
	public static function updated_messages( $messages ) {
		global $post;

		$permalink = get_permalink( $post );

		$messages[ self::$slug ] = array(
			0  => '', // Unused. Messages start at index 1.
		/* translators: %s: post permalink */
			1  => sprintf( __( 'Badge Request updated. <a target="_blank" href="%s">View Badge Request</a>', BF2_DATA['TextDomain'] ), esc_url( $permalink ) ),
			2  => __( 'Custom field updated.', BF2_DATA['TextDomain'] ),
			3  => __( 'Custom field deleted.', BF2_DATA['TextDomain'] ),
			4  => __( 'Badge Request updated.', BF2_DATA['TextDomain'] ),
			/* translators: %s: date and time of the revision */
			5  => isset( $_GET['revision'] ) ? sprintf( __( 'Badge Request restored to revision from %s', BF2_DATA['TextDomain'] ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			/* translators: %s: post permalink */
			6  => sprintf( __( 'Badge Request published. <a href="%s">View Badge Request</a>', BF2_DATA['TextDomain'] ), esc_url( $permalink ) ),
			7  => __( 'Badge Request saved.', BF2_DATA['TextDomain'] ),
			/* translators: %s: post permalink */
			8  => sprintf( __( 'Badge Request submitted. <a target="_blank" href="%s">Preview Badge Request</a>', BF2_DATA['TextDomain'] ), esc_url( add_query_arg( 'preview', 'true', $permalink ) ) ),
			9  => sprintf(
				/* translators: 1: Publish box date format, see https://secure.php.net/date 2: Post permalink */
				__( 'Badge Request scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview Badge Request</a>', BF2_DATA['TextDomain'] ),
				date_i18n( __( 'M j, Y @ G:i', BF2_DATA['TextDomain'] ), strtotime( $post->post_date ) ),
				esc_url( $permalink )
			),
			/* translators: %s: post permalink */
			10 => sprintf( __( 'Badge Request draft updated. <a target="_blank" href="%s">Preview Badge Request</a>', BF2_DATA['TextDomain'] ), esc_url( add_query_arg( 'preview', 'true', $permalink ) ) ),
		);

		return $messages;
	}


	/**
	 * Add roles (capabilities) to custom post type.
	 *
	 * @return void
	 */
	public static function add_capabilities() {
		$capabilities = array(
			'edit_' . self::$slug_plural             => array(
				'administrator',
			),
			'edit_other_' . self::$slug_plural       => array(
				'administrator',
			),
			'edit_published_' . self::$slug_plural   => array(
				'administrator',
			),
			'publish_' . self::$slug_plural          => array(
				'administrator',
			),
			'delete_' . self::$slug_plural           => array(
				'administrator',
			),
			'delete_others_' . self::$slug_plural    => array(
				'administrator',
			),
			'delete_published_' . self::$slug_plural => array(
				'administrator',
			),
			'delete_private_' . self::$slug_plural   => array(
				'administrator',
			),
			'edit_private_' . self::$slug_plural     => array(
				'administrator',
			),
			'read_private_' . self::$slug_plural     => array(
				'administrator',
			),
			'read_' . self::$slug                    => array(
				'administrator',
			),
		);

		foreach ( $capabilities as $capability => $roles ) {
			foreach ( $roles as $role ) {
				$role = get_role( $role );
				$role->add_cap( $capability );
			}
		}

	}


	/**
	 * Custom meta boxes.
	 *
	 * @return void
	 */
	public static function register_cpt_metaboxes() {

		// Badge Info.

		$cmb = new_cmb2_box(
			array(
				'id'           => 'badgerequest_badge_info',
				'title'        => __( 'Badge Info', BF2_DATA['TextDomain'] ),
				'object_types' => array( self::$slug ),
				'context'      => 'normal',
				'priority'     => 'high',
				'show_names'   => true,
			)
		);

		$cmb->add_field(
			array(
				'id'   => 'badge',
				'name' => __( 'Badge', BF2_DATA['TextDomain'] ),
				'desc' => __( 'Badgr Badge associated with this Badge Page', BF2_DATA['TextDomain'] ),
				'type' => 'badge',
			)
		);

		$cmb->add_field(
			array(
				'id'   => 'recipient',
				'name' => __( 'Recipient', BF2_DATA['TextDomain'] ),
				'type' => 'recipient',
			)
		);

		$cmb->add_field(
			array(
				'id'   => 'dates',
				'name' => __( 'Dates', BF2_DATA['TextDomain'] ),
				'type' => 'dates',
			)
		);

		$cmb->add_field(
			array(
				'id'   => 'type',
				'name' => __( 'Type', BF2_DATA['TextDomain'] ),
				'type' => 'badge_request_type',
			)
		);

		$cmb->add_field(
			array(
				'id'   => 'content',
				'name' => __( 'Content', BF2_DATA['TextDomain'] ),
				'type' => 'badge_request_content',
			)
		);

		$cmb->add_field(
			array(
				'id'      => 'status',
				'name'    => __( 'Status', BF2_DATA['TextDomain'] ),
				'type'    => 'select',
				'options' => array(
					'requested' => __( 'Requested', BF2_DATA['TextDomain'] ),
					'granted'   => __( 'Granted', BF2_DATA['TextDomain'] ),
					'rejected'  => __( 'Rejected', BF2_DATA['TextDomain'] ),
				),
			)
		);

		$cmb->add_field(
			array(
				'id'         => 'rejection_reason',
				'name'       => __( 'Rejection Reason', BF2_DATA['TextDomain'] ),
				'type'       => 'text',
				'attributes' => array(
					'data-conditional-id'    => 'status',
					'data-conditional-value' => 'rejected',
				),
			)
		);
	}


	/**
	 * Get the user who requested this badge.
	 *
	 * @param int $badge_request_id Badge Request ID.
	 * @return WP_User|false
	 */
	public static function get_recipient( $badge_request_id ) {
		if ( ! $badge_request_id ) {
			return false;
		}
		$user_id = get_post_meta( $badge_request_id, 'recipient' );
		if ( ! $user_id ) {
			return false;
		}
		return get_user_by( 'ID', $user_id );
	}


	/**
	 * Return all Badge Requests for a specified BadgeClass and a specified User.
	 *
	 * @param string $badgeclass_id BadgeClass Entity ID.
	 * @param int    $user_id User ID.
	 * @return array
	 */
	public static function all_for_badgeclass_for_user( $badgeclass_id, $user_id ) {
		$args  = array(
			'post_type'   => self::$slug,
			'numberposts' => -1,
			'post_status' => 'publish',
			'meta_query'  => array(
				array(
					'key'     => 'recipient',
					'value'   => $user_id,
					'compare' => '=',
				),
				array(
					'key'     => 'badge',
					'value'   => $badgeclass_id,
					'compare' => '=',
				),
			),
		);
		$posts = get_posts( $args );
		return $posts;
	}


	/**
	 * Display Badge Requests.
	 *
	 * @param array $badge_requests Badge Requests.
	 * @return void
	 */
	public static function display( $badge_requests ) {
		include( Template::locate( 'tpl.badge-requests' ) );
	}

	public static function user_can_request_badgeclass( $badgeclass_id, $user_id ) {
		// FIXME Must validate whether or not user can request a badge.
		return true;
	}

	public static function update_badge_request( $post_id, $post, $update ) {
		if ( $update ) {
			$status = get_post_meta( $post_id, 'status', true );
			$reason = get_post_meta( $post_id, 'rejection_reason', true );
			$dates  = get_post_meta( $post_id, 'dates' );
			ksort( $dates );

			// Status change.
			if ( array_key_first( $dates ) !== $status ) {
				$dates[ $status ] = gmdate( 'Y-m-d H:i:s' );
				update_post_meta( $post_id, 'dates', $dates );
			}

			//echo $status . ' '. $reason; die;

		}
	}

}
