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

use BadgeFactor2\Models\BadgeClass;
use BadgeFactor2\Models\Issuer;
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
		add_action( 'cmb2_admin_init', array( BadgePage::class, 'register_cpt_metaboxes' ), 10 );
	}


	/**
	 * Registers the `badge_page` post type.
	 */
	public static function init() {

		register_post_type(
			'badge-page',
			array(
				'labels'            => array(
					'name'                  => __( 'Badge Pages', BF2_DATA['TextDomain'] ),
					'singular_name'         => __( 'Badge Page', BF2_DATA['TextDomain'] ),
					'all_items'             => __( 'All Badge Pages', BF2_DATA['TextDomain'] ),
					'archives'              => __( 'Badge Page Archives', BF2_DATA['TextDomain'] ),
					'attributes'            => __( 'Badge Page Attributes', BF2_DATA['TextDomain'] ),
					'insert_into_item'      => __( 'Insert into Badge Page', BF2_DATA['TextDomain'] ),
					'uploaded_to_this_item' => __( 'Uploaded to this Badge Page', BF2_DATA['TextDomain'] ),
					'featured_image'        => _x( 'Featured Image', 'badge-page', BF2_DATA['TextDomain'] ),
					'set_featured_image'    => _x( 'Set featured image', 'badge-page', BF2_DATA['TextDomain'] ),
					'remove_featured_image' => _x( 'Remove featured image', 'badge-page', BF2_DATA['TextDomain'] ),
					'use_featured_image'    => _x( 'Use as featured image', 'badge-page', BF2_DATA['TextDomain'] ),
					'filter_items_list'     => __( 'Filter Badge Pages list', BF2_DATA['TextDomain'] ),
					'items_list_navigation' => __( 'Badge Pages list navigation', BF2_DATA['TextDomain'] ),
					'items_list'            => __( 'Badge Pages list', BF2_DATA['TextDomain'] ),
					'new_item'              => __( 'New Badge Page', BF2_DATA['TextDomain'] ),
					'add_new'               => __( 'Add New', BF2_DATA['TextDomain'] ),
					'add_new_item'          => __( 'Add New Badge Page', BF2_DATA['TextDomain'] ),
					'edit_item'             => __( 'Edit Badge Page', BF2_DATA['TextDomain'] ),
					'view_item'             => __( 'View Badge Page', BF2_DATA['TextDomain'] ),
					'view_items'            => __( 'View Badge Pages', BF2_DATA['TextDomain'] ),
					'search_items'          => __( 'Search Badge Pages', BF2_DATA['TextDomain'] ),
					'not_found'             => __( 'No Badge Pages found', BF2_DATA['TextDomain'] ),
					'not_found_in_trash'    => __( 'No Badge Pages found in trash', BF2_DATA['TextDomain'] ),
					'parent_item_colon'     => __( 'Parent Badge Page:', BF2_DATA['TextDomain'] ),
					'menu_name'             => __( 'Badge Pages', BF2_DATA['TextDomain'] ),
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
			1  => sprintf( __( 'Badge Page updated. <a target="_blank" href="%s">View Badge Page</a>', BF2_DATA['TextDomain'] ), esc_url( $permalink ) ),
			2  => __( 'Custom field updated.', BF2_DATA['TextDomain'] ),
			3  => __( 'Custom field deleted.', BF2_DATA['TextDomain'] ),
			4  => __( 'Badge Page updated.', BF2_DATA['TextDomain'] ),
			/* translators: %s: date and time of the revision */
			5  => isset( $_GET['revision'] ) ? sprintf( __( 'Badge Page restored to revision from %s', BF2_DATA['TextDomain'] ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			/* translators: %s: post permalink */
			6  => sprintf( __( 'Badge Page published. <a href="%s">View Badge Page</a>', BF2_DATA['TextDomain'] ), esc_url( $permalink ) ),
			7  => __( 'Badge Page saved.', BF2_DATA['TextDomain'] ),
			/* translators: %s: post permalink */
			8  => sprintf( __( 'Badge Page submitted. <a target="_blank" href="%s">Preview Badge Page</a>', BF2_DATA['TextDomain'] ), esc_url( add_query_arg( 'preview', 'true', $permalink ) ) ),
			9  => sprintf(
				/* translators: 1: Publish box date format, see https://secure.php.net/date 2: Post permalink */
				__( 'Badge Page scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview Badge Page</a>', BF2_DATA['TextDomain'] ),
				date_i18n( __( 'M j, Y @ G:i', BF2_DATA['TextDomain'] ), strtotime( $post->post_date ) ),
				esc_url( $permalink )
			),
			/* translators: %s: post permalink */
			10 => sprintf( __( 'Badge Page draft updated. <a target="_blank" href="%s">Preview Badge Page</a>', BF2_DATA['TextDomain'] ), esc_url( add_query_arg( 'preview', 'true', $permalink ) ) ),
		);

		return $messages;
	}


	/**
	 * Custom meta boxes.
	 *
	 * @return void
	 */
	public static function register_cpt_metaboxes() {

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
				'name'    => __( 'Badge', BF2_DATA['TextDomain'] ),
				'desc'    => __( 'Badgr Badge associated with this Badge Page', BF2_DATA['TextDomain'] ),
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
				'name'       => __( 'Form type', BF2_DATA['TextDomain'] ),
				'type'       => 'select',
				'options_cb' => array( BadgePage::class, 'form_type_select_options' ),
			)
		);

		$cmb->add_field(
			array(
				'id'         => 'badge_request_form_id',
				'name'       => __( 'Form', BF2_DATA['TextDomain'] ),
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
				'name'       => __( 'Approvers', BF2_DATA['TextDomain'] ),
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
			'basic' => __( 'Basic form', BF2_DATA['TextDomain'] ),
		);
		if ( is_plugin_active( 'gravityforms/gravityforms.php' ) ) {
			$options = array( 'gravityforms' => __( 'Gravity Forms', BF2_DATA['TextDomain'] ) ) + $options;
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


	/**
	 * Undocumented function.
	 *
	 * @return array
	 */
	public static function all() {
		$args  = array(
			'post_type'   => 'badge-page',
			'numberposts' => -1,
			'post_status' => 'publish',
		);
		$posts = get_posts( $args );

		foreach ( $posts as $i => $post ) {
			$badge_entity_id    = get_post_meta( $post->ID, 'badgr_badge', true );
			$badge              = BadgeClass::get( $badge_entity_id );
			$posts[ $i ]->badge = $badge;

			$issuer_entity_id           = $badge->issuer;
			$issuer                     = Issuer::get( $issuer_entity_id );
			$posts[ $i ]->badge->issuer = $issuer;
		}

		return $posts;
	}
}
