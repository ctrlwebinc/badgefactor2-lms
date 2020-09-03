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

use BadgeFactor2\Models\BadgeClass;
use BadgeFactor2\Models\Issuer;
use BadgeFactor2\Roles\Approver;
use BadgeFactor2\Helpers\Template;

/**
 * Badge Page post type.
 */
class BadgePage {

	/**
	 * Custom post type's slug.
	 *
	 * @var string
	 */
	private static $slug = 'badge-page';

	/**
	 * Custom post type's slug, pluralized.
	 *
	 * @var string
	 */
	private static $slug_plural = 'badge-pages';


	/**
	 * Init hooks.
	 *
	 * @return void
	 */
	public static function init_hooks() {
		add_action( 'init', array( BadgePage::class, 'init' ), 10 );
		add_action( 'init', array( BadgePage::class, 'register_taxonomies' ), 10 );
		add_action( 'admin_init', array( BadgePage::class, 'add_capabilities' ), 10 );
		add_filter( 'post_updated_messages', array( BadgePage::class, 'updated_messages' ), 10 );
		add_action( 'cmb2_admin_init', array( BadgePage::class, 'register_cpt_metaboxes' ), 10 );
		add_action( 'single_template', array( BadgePage::class, 'single_template' ), 10 );
		add_filter( 'archive_template', array( BadgePage::class, 'archive_template' ), 10 );
	}


	/**
	 * Registers the `badge_page` post type.
	 */
	public static function init() {

		register_post_type(
			self::$slug,
			array(
				'labels'            => array(
					'name'                  => __( 'Badge Pages', BF2_DATA['TextDomain'] ),
					'singular_name'         => __( 'Badge Page', BF2_DATA['TextDomain'] ),
					'all_items'             => __( 'All Badge Pages', BF2_DATA['TextDomain'] ),
					'archives'              => __( 'Badge Page Archives', BF2_DATA['TextDomain'] ),
					'attributes'            => __( 'Badge Page Attributes', BF2_DATA['TextDomain'] ),
					'insert_into_item'      => __( 'Insert into Badge Page', BF2_DATA['TextDomain'] ),
					'uploaded_to_this_item' => __( 'Uploaded to this Badge Page', BF2_DATA['TextDomain'] ),
					'featured_image'        => _x( 'Featured Image', self::$slug, BF2_DATA['TextDomain'] ),
					'set_featured_image'    => _x( 'Set featured image', self::$slug, BF2_DATA['TextDomain'] ),
					'remove_featured_image' => _x( 'Remove featured image', self::$slug, BF2_DATA['TextDomain'] ),
					'use_featured_image'    => _x( 'Use as featured image', self::$slug, BF2_DATA['TextDomain'] ),
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
				'rewrite'           => array( 'slug' => 'badges' ),
				'query_var'         => true,
				'menu_position'     => 50,
				'menu_icon'         => BF2_BASEURL . 'assets/images/badge.svg',
				'show_in_rest'      => false,
				'taxonomies'        => array( 'badge-category' ),
				'capability_type'   => array( self::$slug, self::$slug_plural ),
				'map_meta_cap'      => true,
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

		$messages[ self::$slug ] = array(
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
				'id'           => 'badgepage_badge_info',
				'title'        => __( 'Badge Info', BF2_DATA['TextDomain'] ),
				'object_types' => array( self::$slug ),
				'context'      => 'normal',
				'priority'     => 'high',
				'show_names'   => true,
			)
		);

		$cmb->add_field(
			array(
				'id'         => 'badge',
				'name'       => __( 'Badge', BF2_DATA['TextDomain'] ),
				'desc'       => __( 'Badgr Badge associated with this Badge Page', BF2_DATA['TextDomain'] ),
				'type'       => 'pw_select',
				'style'      => 'width: 200px',
				'options'    => BadgeClass::select_options(),
				'attributes' => array(
					'required' => 'required',
				),
			)
		);

		$cmb->add_field(
			array(
				'id'         => 'badge_criteria',
				'name'       => __( 'Badge Criteria', BF2_DATA['TextDomain'] ),
				'desc'       => __( 'Criteria to obtain this badge.', BF2_DATA['TextDomain'] ),
				'type'       => 'wysiwyg',
				'attributes' => array(
					'required' => 'required',
				),
			)
		);

		$cmb->add_field(
			array(
				'id'         => 'badge_endorsed_by',
				'name'       => __( 'Endorsed By', BF2_DATA['TextDomain'] ),
				'type'       => 'pw_multiselect',
				'options_cb' => array( Issuer::class, 'select_options' ),
			)
		);

		$cmb->add_field(
			array(
				'id'         => 'badge_request_form_type',
				'name'       => __( 'Badge Request Form type', BF2_DATA['TextDomain'] ),
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
				'id'         => 'badge_approval_type',
				'name'       => __( 'Badge Request Form type', BF2_DATA['TextDomain'] ),
				'type'       => 'select',
				'options'    => array(
					''              => __( 'Select approval type', BF2_DATA['TextDomain'] ),
					'approved'      => __( 'Approved', BF2_DATA['TextDomain'] ),
					'auto-approved' => __( 'Auto-approved', BF2_DATA['TextDomain'] ),
					'given'         => __( 'Given', BF2_DATA['TextDomain'] ),
				),
				'attributes' => array(
					'required' => 'required',
				),
			)
		);

		$cmb->add_field(
			array(
				'id'         => 'badge_request_approver',
				'name'       => __( 'Approvers', BF2_DATA['TextDomain'] ),
				'type'       => 'pw_multiselect',
				'options_cb' => array( Approver::class, 'select_options' ),
				'attributes' => array(
					'data-conditional-id'    => 'badge_approval_type',
					'data-conditional-value' => 'approved',
				),
			)
		);

	}


	/**
	 * Register taxonomies.
	 *
	 * @return void
	 */
	public static function register_taxonomies() {
		$plugin_data = get_plugin_data( __FILE__ );

		register_taxonomy(
			'badge-category',
			array( self::$slug ),
			array(
				'hierarchical'      => true,
				'labels'            => array(
					'name'              => __( 'Category', $plugin_data['TextDomain'] ),
					'singular_name'     => __( 'Category', $plugin_data['TextDomain'] ),
					'search_items'      => __( 'Search Categories', $plugin_data['TextDomain'] ),
					'all_items'         => __( 'All Categories', $plugin_data['TextDomain'] ),
					'parent_item'       => __( 'parent Category', $plugin_data['TextDomain'] ),
					'parent_item_colon' => __( 'parent Category:', $plugin_data['TextDomain'] ),
					'edit_item'         => __( 'Edit Category', $plugin_data['TextDomain'] ),
					'update_item'       => __( 'Update Category', $plugin_data['TextDomain'] ),
					'add_new_item'      => __( 'Add new Category', $plugin_data['TextDomain'] ),
					'new_item_name'     => __( 'new Category Name', $plugin_data['TextDomain'] ),
					'menu_name'         => __( 'Categories', $plugin_data['TextDomain'] ),
				),
				'show_ui'           => true,
				'show_admin_column' => true,
				'query_var'         => true,
				'rewrite'           => array( 'slug' => 'badge-category' ),
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
		if ( is_plugin_active( 'bf2-gravityforms/bf2-gravityforms.php' ) ) {
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
		if ( is_plugin_active( 'bf2-gravityforms/bf2-gravityforms.php' ) ) {

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
			'post_type'   => self::$slug,
			'numberposts' => -1,
			'post_status' => 'publish',
		);
		$posts = get_posts( $args );

		foreach ( $posts as $i => $post ) {
			$badge_entity_id    = get_post_meta( $post->ID, 'badgr_badge', true );
			$badge              = BadgeClass::get( $badge_entity_id );
			$posts[ $i ]->badge = $badge;

			if ( $badge ) {
				$issuer_entity_id           = $badge->issuer;
				$issuer                     = Issuer::get( $issuer_entity_id );
				$posts[ $i ]->badge->issuer = $issuer;
			}
		}

		return $posts;
	}


	/**
	 * Get select-formatted options.
	 *
	 * @return array
	 */
	public static function select_options() {
		$badge_pages  = self::all( -1 );
		$post_options = array();
		foreach ( $badge_pages as $badge_page ) {
			$post_options[ $badge_page->ID ] = $badge_page->post_title;
		}

		return $post_options;
	}

	/**
	 * Single Template.
	 *
	 * @param string $single Single.
	 * @return string
	 */
	public static function single_template( $single ) {
		global $post;

		$slug = self::$slug;

		/* Checks for single template by post type */
		if ( $post->post_type === $slug ) {
			$single = Template::locate( "tpl.{$slug}", $single );
		}

		return $single;
	}


	/**
	 * Archive Template.
	 *
	 * @param string $archive_template Archive template.
	 * @return string
	 */
	public static function archive_template( $archive_template ) {
		global $post;

		$slug = self::$slug_plural;

		/* Checks for single template by post type */
		if ( is_post_type_archive( self::$slug ) ) {
			$archive_template = Template::locate( "tpl.{$slug}", $archive_template );
		}

		// Default template.
		return $archive_template;
	}


	/**
	 * Create badge pages from badges
	 *
	 * @return mixed
	 */
	public static function create_from_badges() {
		global $wpdb;

		// Get badges with a badgr_badge_class_slug meta where no badge-page with same meta exists.
		$badges = $wpdb->get_results(
			"SELECT b.*, bcs.meta_value AS badge_class_slug, c.meta_value AS criteria, t.slug AS badge_category, t.name AS badge_category_name, et.meta_value AS earning_type FROM wp_posts as b
			JOIN wp_postmeta as bcs
			ON b.ID = bcs.post_id
			JOIN wp_postmeta AS c
			ON b.ID = c.post_id
			JOIN wp_postmeta AS et
			ON b.ID = et.post_id
			JOIN wp_term_relationships as tr
			ON b.ID = tr.object_id 
			JOIN wp_terms as t
			ON tr.term_taxonomy_id = t.term_id
			WHERE post_type = 'badges' AND
			bcs.meta_key = 'badgr_badge_class_slug' AND
		    ( tr.term_taxonomy_id = 190 OR tr.term_taxonomy_id = 191 ) AND
			c.meta_key = 'badge_criteria' AND
			et.meta_key = '_badgeos_earned_by'
			AND NOT EXISTS (
			SELECT bp.ID FROM wp_posts AS bp
			JOIN wp_postmeta AS bpbcs
			ON bp.ID = bpbcs.post_id
			WHERE bp.post_type = 'badge-page' AND bpbcs.meta_key = 'badge' AND bcs.meta_value = bpbcs.meta_value);",
			OBJECT_K
		);

		$count = 0;

		foreach ( $badges as $badge_post_id => $badge_post ) {
			// Create a post of post type badge-page.
			$created_post_id = wp_insert_post(
				array(
					'post_author'  => 1,
					'post_content' => $badge_post->post_content, // Reuse post_title.
					'post_title'   => $badge_post->post_title, // Reuse post_content.
					'post_status'  => 'publish',
					'post_type'    => 'badge-page',
				)
			);

			if ( 0 === $created_post_id ) {
				return false;
			}
			// Add badgepage_badge meta with the associated badge class slug as its value.
			update_post_meta( $created_post_id, 'badge', $badge_post->badge_class_slug );
			// Add badge_page_request_form_type with value basic.
			update_post_meta( $created_post_id, 'badge_page_request_form_type', 'basic' );
			// Add criteria as the value of badge_criteria.
			update_post_meta( $created_post_id, 'badge_criteria', $badge_post->criteria );
			// Add badge approval type under badge_approval_type as one of approved, auto-approved or given.
			$approval_type = null;
			switch ( $badge_post->earning_type ) {
				case 'submission_auto':
					$approval_type = 'auto-approved';
					break;
				case 'submission':
					$approval_type = 'approved';
					break;
				default:
			}
			if ( null !== $approval_type ) {
				update_post_meta( $created_post_id, 'badge_approval_type', $approval_type );
			}
			// Add badge category in badge-category taxonomy slug.
			$new_term = wp_set_object_terms( $created_post_id, $badge_post->badge_category, 'badge-category', true );
			// Enrich new term with full name.
			wp_update_term( intval( $new_term[0] ), 'badge-category', array( 'name' => $badge_post->badge_category_name ) );

			$count++;
		}

		return $count;
	}

	/**
	 * Create badge pages from badges
	 *
	 * @return mixed
	 */
	public static function create_courses_from_badges() {
		global $wpdb;

		// Get badges with a badgr_badge_class_slug meta where no course with same meta exists.
		$badges = $wpdb->get_results(
			"SELECT b.*
			, bcs.meta_value AS badge_class_slug
			, ccp.post_content AS course_content
			, ccp.post_title AS course_title
			FROM wp_posts as b
			JOIN wp_postmeta as bcs
			ON b.ID = bcs.post_id
			JOIN wp_postmeta AS ccm
			ON b.ID = ccm.post_id
			JOIN wp_posts AS ccp
			ON ccm.meta_value = ccp.ID
			WHERE b.post_type = 'badges'
			AND	bcs.meta_key = 'badgr_badge_class_slug'
			AND ccm.meta_key = 'badgefactor_page_id';",
			OBJECT_K
		);

		$levels = $wpdb->get_results(
			"SELECT b.ID, tt.taxonomy AS taxonomy, t.slug AS slug, t.name AS term_name, t.term_id AS term_id FROM wp_posts AS b
			JOIN wp_term_relationships AS tr
			ON b.ID = tr.object_id
			JOIN wp_term_taxonomy AS tt
			ON tr.term_taxonomy_id = tt.term_taxonomy_id AND tt.taxonomy = 'badges-level'
			JOIN wp_terms AS t
			ON tt.term_id = t.term_id
			WHERE b.post_type = 'badges';",
			OBJECT_K
		);

		$titles = $wpdb->get_results(
			"SELECT b.ID, tt.taxonomy AS taxonomy, t.slug AS slug, t.name AS term_name, t.term_id AS term_id FROM wp_posts AS b
			JOIN wp_term_relationships AS tr
			ON b.ID = tr.object_id
			JOIN wp_term_taxonomy AS tt
			ON tr.term_taxonomy_id = tt.term_taxonomy_id AND tt.taxonomy = 'badges-title'
			JOIN wp_terms AS t
			ON tt.term_id = t.term_id
			WHERE b.post_type = 'badges';",
			OBJECT_K
		);

		$categories = $wpdb->get_results(
			"SELECT b.ID, cm.meta_value AS badge_categories FROM wp_posts AS b
			JOIN wp_postmeta AS cm
			ON ( b.ID = cm.post_id AND cm.meta_key = 'category')
			WHERE b.post_type = 'badges' AND cm.meta_value != '';",
			OBJECT_K
		);

		$terms = $wpdb->get_results(
			'SELECT t.term_id AS ID, t.slug AS slug, t.name AS term_name FROM wp_terms AS t;',
			OBJECT_K
		);

		$count = 0;

		foreach ( $badges as $badge_post_id => $badge_post ) {
			// Create a post of post type course.
			$created_post_id = wp_insert_post(
				array(
					'post_author'  => 1,
					'post_content' => $badge_post->course_content, // Course content is from BF badgefactor_page_id meta.
					'post_title'   => $badge_post->post_title, // Reuse post_title.
					'post_status'  => 'publish',
					'post_type'    => 'course',
				)
			);

			if ( 0 === $created_post_id ) {
				return false;
			}
			// Add badgepage_badge meta with the associated badge class slug as its value.
			update_post_meta( $created_post_id, 'badgr_badge_class_slug', $badge_post->badge_class_slug );
			// Badge category post category meta => serialized php array => points to id of terms => course-category.
			if ( isset( $categories[ $badge_post->ID ] ) ) {
				$term_ids = unserialize( $categories[ $badge_post->ID ]->badge_categories );

				foreach ( $term_ids as $term_id ) {
					$new_term = wp_set_object_terms( $created_post_id, $terms[ $term_id ]->slug, 'course-category', true );
					// Enrich term with full name.
					wp_update_term( intval( $new_term[0] ), 'course-category', array( 'name' => $terms[ $term_id ]->term_name ) );
				}
			}
			// Course level badges-level => course-level.
			if ( isset( $levels[ $badge_post->ID ] ) ) {
				$new_term = wp_set_object_terms( $created_post_id, $levels[ $badge_post->ID ]->slug, 'course-level', true );
				// Enrich new term with full name.
				wp_update_term( intval( $new_term[0] ), 'course-level', array( 'name' => $levels[ $badge_post->ID ]->term_name ) );
			}
			// Course title badges-title => course-title.
			if ( isset( $titles[ $badge_post->ID ] ) ) {
				$new_term = wp_set_object_terms( $created_post_id, $titles[ $badge_post->ID ]->slug, 'course-title', true );
				// Enrich term with full name.
				wp_update_term( intval( $new_term[0] ), 'course-title', array( 'name' => $titles[ $badge_post->ID ]->term_name ) );

			}

			$count++;
		}

		return $count;
	}


	/**
	 * Get associated courses.
	 *
	 * @param string $badgepage_id Badge Page ID.
	 * @return array
	 */
	public static function get_course( $badgepage_id ) {
		if ( is_plugin_active( sprintf( '%s/%s.php', 'bf2-courses', 'bf2-courses' ) ) ) {
			$query = new \WP_Query(
				array(
					'post_type'    => 'course',
					'meta_key'     => 'course_badge_page',
					'meta_value'   => $badgepage_id,
					'meta_compare' => '=',
					'post_status'  => 'publish',
				)
			);
			if ( $query->posts ) {
				return $query->posts[0];
			}
		}
		return array();
	}

	/**
	 * Get BadgePage by BadgeClass Entity ID.
	 *
	 * @param int $entity_id Entity ID.
	 * @return WP_Post|bool
	 */
	public static function get_by_badgeclass_id( $entity_id ) {
		$query = new \WP_Query(
			array(
				'post_type'    => self::$slug,
				'meta_key'     => 'badge',
				'meta_value'   => $entity_id,
				'meta_compare' => '=',
				'post_status'  => 'publish',
			)
		);
		if ( $query->posts ) {
			return $query->posts[0];
		}
		return false;
	}


	/**
	 * Get Badge Approvers by BadgeClass Entity ID.
	 *
	 * @param string $entity_id Entity ID.
	 * @return WP_Post|bool
	 */
	public static function get_approvers_emails_by_badgeclass_id( $entity_id ) {

		$badge_page = self::get_by_badgeclass_id( $entity_id );
		if ( $badge_page ) {
			$approvers = get_post_meta( $badge_page->ID, 'badge_request_approver', true );
			$emails    = array();

			foreach ( $approvers as $approver ) {
				$user                        = get_userdata( $approver );
				$emails[ $user->user_email ] = $user->user_email;
			}

			return join( ',', array_values( $emails ) );
		}
		return false;

	}


	/**
	 * Check whether or not a badge is auto-approved.
	 *
	 * @param string $entity_id Entity ID.
	 * @return boolean|null
	 */
	public static function is_auto_approved( $entity_id ) {
		$badge_page = self::get_by_badgeclass_id( $entity_id );
		if ( $badge_page ) {
			return 'auto-approved' === get_post_meta( $badge_page->ID, 'badge_approval_type', true );
		}
		return null;
	}

}
