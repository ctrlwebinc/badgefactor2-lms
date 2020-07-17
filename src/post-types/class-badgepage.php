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

namespace BadgeFactor2\Post_Types;

use BadgeFactor2\Models\BadgeClass;
use BadgeFactor2\Post_Types\Approver;

/**
 * Badge Page post type.
 */
class BadgePage {


	/**
	 * Init hooks.
	 *
	 * @return void
	 */
	public static function init_hooks() {
		add_action( 'init', array( BadgePage::class, 'init' ), 10 );
		add_filter( 'post_updated_messages', array( BadgePage::class, 'updated_messages' ), 10 );
		add_action( 'cmb2_admin_init', array( BadgePage::class, 'custom_meta_boxes' ), 10 );
	}


	/**
	 * Registers the `badge_page` post type.
	 */
	public static function init() {

		register_post_type(
			'badge-page',
			array(
				'labels'            => array(
					'name'                  => __( 'Badge Pages', 'badgefactor2' ),
					'singular_name'         => __( 'Badge Page', 'badgefactor2' ),
					'all_items'             => __( 'All Badge Pages', 'badgefactor2' ),
					'archives'              => __( 'Badge Page Archives', 'badgefactor2' ),
					'attributes'            => __( 'Badge Page Attributes', 'badgefactor2' ),
					'insert_into_item'      => __( 'Insert into Badge Page', 'badgefactor2' ),
					'uploaded_to_this_item' => __( 'Uploaded to this Badge Page', 'badgefactor2' ),
					'featured_image'        => _x( 'Featured Image', 'badge-page', 'badgefactor2' ),
					'set_featured_image'    => _x( 'Set featured image', 'badge-page', 'badgefactor2' ),
					'remove_featured_image' => _x( 'Remove featured image', 'badge-page', 'badgefactor2' ),
					'use_featured_image'    => _x( 'Use as featured image', 'badge-page', 'badgefactor2' ),
					'filter_items_list'     => __( 'Filter Badge Pages list', 'badgefactor2' ),
					'items_list_navigation' => __( 'Badge Pages list navigation', 'badgefactor2' ),
					'items_list'            => __( 'Badge Pages list', 'badgefactor2' ),
					'new_item'              => __( 'New Badge Page', 'badgefactor2' ),
					'add_new'               => __( 'Add New', 'badgefactor2' ),
					'add_new_item'          => __( 'Add New Badge Page', 'badgefactor2' ),
					'edit_item'             => __( 'Edit Badge Page', 'badgefactor2' ),
					'view_item'             => __( 'View Badge Page', 'badgefactor2' ),
					'view_items'            => __( 'View Badge Pages', 'badgefactor2' ),
					'search_items'          => __( 'Search Badge Pages', 'badgefactor2' ),
					'not_found'             => __( 'No Badge Pages found', 'badgefactor2' ),
					'not_found_in_trash'    => __( 'No Badge Pages found in trash', 'badgefactor2' ),
					'parent_item_colon'     => __( 'Parent Badge Page:', 'badgefactor2' ),
					'menu_name'             => __( 'Badge Pages', 'badgefactor2' ),
				),
				'public'            => true,
				'hierarchical'      => false,
				'show_ui'           => true,
				'show_in_nav_menus' => true,
				'supports'          => array( 'title', 'editor' ),
				'has_archive'       => true,
				'rewrite'           => true,
				'query_var'         => true,
				'menu_position'     => null,
				'menu_icon'         => BF2_BASEURL . 'assets/images/badge.svg',
				'show_in_rest'      => false,
			)
		);

	}


	/**
	 * Sets the post updated messages for the `badge_page` post type.
	 *
	 * @param  array $messages Post updated messages.
	 * @return array Messages for the `badge_page` post type.
	 */
	public static function updated_messages( $messages ) {
		global $post;

		$permalink = get_permalink( $post );

		$messages['badge-page'] = array(
			0  => '', // Unused. Messages start at index 1.
			/* translators: %s: post permalink */
			1  => sprintf( __( 'Badge Page updated. <a target="_blank" href="%s">View Badge Page</a>', 'badgefactor2' ), esc_url( $permalink ) ),
			2  => __( 'Custom field updated.', 'badgefactor2' ),
			3  => __( 'Custom field deleted.', 'badgefactor2' ),
			4  => __( 'Badge Page updated.', 'badgefactor2' ),
			/* translators: %s: date and time of the revision */
			5  => isset( $_GET['revision'] ) ? sprintf( __( 'Badge Page restored to revision from %s', 'badgefactor2' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			/* translators: %s: post permalink */
			6  => sprintf( __( 'Badge Page published. <a href="%s">View Badge Page</a>', 'badgefactor2' ), esc_url( $permalink ) ),
			7  => __( 'Badge Page saved.', 'badgefactor2' ),
			/* translators: %s: post permalink */
			8  => sprintf( __( 'Badge Page submitted. <a target="_blank" href="%s">Preview Badge Page</a>', 'badgefactor2' ), esc_url( add_query_arg( 'preview', 'true', $permalink ) ) ),
			9  => sprintf(
				/* translators: 1: Publish box date format, see https://secure.php.net/date 2: Post permalink */
				__( 'Badge Page scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview Badge Page</a>', 'badgefactor2' ),
				date_i18n( __( 'M j, Y @ G:i', 'badgefactor2' ), strtotime( $post->post_date ) ),
				esc_url( $permalink )
			),
			/* translators: %s: post permalink */
			10 => sprintf( __( 'Badge Page draft updated. <a target="_blank" href="%s">Preview Badge Page</a>', 'badgefactor2' ), esc_url( add_query_arg( 'preview', 'true', $permalink ) ) ),
		);

		return $messages;
	}


	/**
	 * Custom meta boxes.
	 *
	 * @return void
	 */
	public static function custom_meta_boxes() {

		// Links.

		$cmb = new_cmb2_box(
			array(
				'id'           => 'links',
				'title'        => __( 'Links', 'bagefactor2' ),
				'object_types' => array( 'badge-page' ),
				'context'      => 'normal',
				'priority'     => 'high',
				'show_names'   => true,
			)
		);

		$cmb->add_field(
			array(
				'id'      => 'badgr_badge',
				'name'    => __( 'Badge', 'badgefactor2' ),
				'desc'    => __( 'Badgr Badge associated with this Badge Page', 'badgefactor2' ),
				'type'    => 'pw_select',
				'style'   => 'width: 200px',
				'options' => BadgeClass::select_options(),
			)
		);

		$cmb = new_cmb2_box(
			array(
				'id'           => 'badge_request',
				'title'        => __( 'Badge Request Form', 'bagefactor2' ),
				'object_types' => array( 'badge-page' ),
				'context'      => 'normal',
				'priority'     => 'high',
				'show_names'   => true,
			)
		);

		$cmb->add_field(
			array(
				'id'         => 'badge_request_form_type',
				'name'       => __( 'Form type', 'badgefactor2' ),
				'type'       => 'select',
				'options_cb' => array( BadgePage::class, 'form_type_select_options' ),
			)
		);

		$cmb->add_field(
			array(
				'id'         => 'badge_request_form_id',
				'name'       => __( 'Form', 'badgefactor2' ),
				'type'       => 'pw_select',
				'options_cb' => array( BadgePage::class, 'gf_form_select_options' ),
				'attributes' => array(
					'data-conditional-id'    => 'badge_request_form_type',
					'data-conditional-value' => 'gravityforms',
				),
			)
		);

		$cmb->add_field(
			array(
				'id'         => 'badge_request_approver',
				'name'       => __( 'Approvers', 'badgefactor2' ),
				'type'       => 'pw_multiselect',
				'options_cb' => array( Approver::class, 'select_options' ),
			)
		);

	}


	/**
	 * Get select-formatted form type options.
	 *
	 * @return array Options.
	 */
	public static function form_type_select_options() {
		$options = array(
			'basic' => __( 'Basic form', 'badgefactor2' ),
		);
		if ( is_plugin_active( 'gravityforms/gravityforms.php' ) ) {
			$options = array( 'gravityforms' => __( 'Gravity Forms', 'badgefactor2' ) ) + $options;
		}
		return $options;
	}


	/**
	 * Get select-formatted Gravity Forms form options.
	 *
	 * @return array Options.
	 */
	public static function gf_form_select_options() {
		$options = array();
		if ( is_plugin_active( 'gravityforms/gravityforms.php' ) ) {

			$forms = \GFAPI::get_forms();
			foreach ( $forms as $form ) {
				$options[ $form['id'] ] = $form['title'];
			}
		}
		return $options;
	}
}
