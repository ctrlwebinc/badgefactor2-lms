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

namespace BadgeFactor2\Post_Types;

/**
 * Badge Request post type.
 */
class BadgeRequest {


	/**
	 * Init hooks.
	 *
	 * @return void
	 */
	public static function init_hooks() {
		add_action( 'init', array( BadgeRequest::class, 'init' ), 10 );
		add_filter( 'post_updated_messages', array( BadgeRequest::class, 'updated_messages' ), 10 );
		add_action( 'cmb2_admin_init', array( BadgeRequest::class, 'custom_meta_boxes' ), 10 );
	}


	/**
	 * Registers the `badge_request` post type.
	 */
	public static function init() {
		register_post_type(
			'badge-request',
			array(
				'labels'                => array(
					'name'                  => __( 'Badge Requests', BF2_DATA['TextDomain'] ),
					'singular_name'         => __( 'Badge Request', BF2_DATA['TextDomain'] ),
					'all_items'             => __( 'All Badge Requests', BF2_DATA['TextDomain'] ),
					'archives'              => __( 'Badge Request Archives', BF2_DATA['TextDomain'] ),
					'attributes'            => __( 'Badge Request Attributes', BF2_DATA['TextDomain'] ),
					'insert_into_item'      => __( 'Insert into Badge Request', BF2_DATA['TextDomain'] ),
					'uploaded_to_this_item' => __( 'Uploaded to this Badge Request', BF2_DATA['TextDomain'] ),
					'featured_image'        => _x( 'Featured Image', 'badge-request', BF2_DATA['TextDomain'] ),
					'set_featured_image'    => _x( 'Set featured image', 'badge-request', BF2_DATA['TextDomain'] ),
					'remove_featured_image' => _x( 'Remove featured image', 'badge-request', BF2_DATA['TextDomain'] ),
					'use_featured_image'    => _x( 'Use as featured image', 'badge-request', BF2_DATA['TextDomain'] ),
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
				'public'                => true,
				'hierarchical'          => false,
				'show_ui'               => true,
				'show_in_nav_menus'     => true,
				'supports'              => array( 'title', 'editor' ),
				'has_archive'           => true,
				'rewrite'               => true,
				'query_var'             => true,
				'menu_position'         => null,
				'menu_icon'             => 'dashicons-feedback',
				'show_in_rest'          => true,
				'rest_base'             => 'badge-request',
				'rest_controller_class' => 'WP_REST_Posts_Controller',
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

		$messages['badge-request'] = array(
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
	 * Custom meta boxes.
	 *
	 * @return void
	 */
	public static function custom_meta_boxes() {

	}

}
