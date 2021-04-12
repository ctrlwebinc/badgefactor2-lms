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
 * @phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
 * @phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
 * @phpcs:disable WordPress.WP.I18n.NonSingularStringLiteralContext
 * @phpcs:disable WordPress.WP.I18n.NonSingularStringLiteralDomain
 */

namespace BadgeFactor2\Post_Types;

use BadgeFactor2\Helpers\Template;
use BadgeFactor2\Models\BadgeClass;

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

		// WordPress Action Hooks.
		add_action( 'init', array( self::class, 'init' ), 10 );
		add_action( 'admin_init', array( self::class, 'add_capabilities' ), 10 );
		add_action( 'cmb2_admin_init', array( self::class, 'register_cpt_metaboxes' ), 10 );
		add_action( 'save_post_' . self::$slug, array( self::class, 'save_badge_request' ), 10, 3 );
		add_action( 'manage_' . self::$slug . '_posts_custom_column', array( self::class, 'admin_columns' ), 10, 3 );

		// WordPress Filter Hooks.
		add_filter( 'manage_' . self::$slug . '_posts_columns', array( self::class, 'filter_admin_columns' ), 10 );
		add_filter( 'post_updated_messages', array( self::class, 'updated_messages' ), 10 );

		// Ajax Hooks.
		add_action( 'wp_ajax_submit_badge_request_form', array( self::class, 'ajax_badge_request' ) );
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
					'edit_post'    => 'edit_' . self::$slug,
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
			'edit_' . self::$slug                    => array(
				'badgr_administrator',
				'administrator',
			),
			'edit_' . self::$slug_plural             => array(
				'badgr_administrator',
				'administrator',
			),
			'edit_other_' . self::$slug_plural       => array(
				'badgr_administrator',
				'administrator',
			),
			'edit_published_' . self::$slug_plural   => array(
				'badgr_administrator',
				'administrator',
			),
			'publish_' . self::$slug_plural          => array(
				'badgr_administrator',
				'administrator',
			),
			'delete_' . self::$slug                  => array(
				'badgr_administrator',
				'administrator',
			),
			'delete_others_' . self::$slug_plural    => array(
				'badgr_administrator',
				'administrator',
			),
			'delete_published_' . self::$slug_plural => array(
				'badgr_administrator',
				'administrator',
			),
			'delete_private_' . self::$slug_plural   => array(
				'badgr_administrator',
				'administrator',
			),
			'edit_private_' . self::$slug_plural     => array(
				'badgr_administrator',
				'administrator',
			),
			'read_private_' . self::$slug_plural     => array(
				'badgr_administrator',
				'administrator',
			),
			'read_' . self::$slug                    => array(
				'badgr_administrator',
				'administrator',
			),
		);

		foreach ( $capabilities as $capability => $roles ) {
			foreach ( $roles as $role ) {
				$role = get_role( $role );
				if ($role) {
					$role->add_cap( $capability );
				}
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
				'capability'   => 'manage_badgr',
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
				'id'   => 'approver',
				'name' => __( 'Approver', BF2_DATA['TextDomain'] ),
				'type' => 'badge_request_approver',
			)
		);

		$cmb->add_field(
			array(
				'id'   => 'status',
				'name' => __( 'Status', BF2_DATA['TextDomain'] ),
				'type' => 'badge_request_status',
			)
		);

		$cmb->add_field(
			array(
				'id'   => 'rejection_reason',
				'name' => __( 'Rejection Reason', BF2_DATA['TextDomain'] ),
				'type' => 'badge_request_rejection_reason',
			)
		);

		$cmb->add_field(
			array(
				'id'   => 'revision_reason',
				'name' => __( 'Revision Reason', BF2_DATA['TextDomain'] ),
				'type' => 'badge_request_revision_reason',
			)
		);
	}


	/**
	 * Filter admin columns.
	 *
	 * @param array $columns Columns array.
	 * @return array
	 */
	public static function filter_admin_columns( $columns ) {
		unset( $columns['date'] );
		$columns['status'] = __( 'Status', BF2_DATA['TextDomain'] );
		$columns['date']   = __( 'Date', BF2_DATA['TextDomain'] );
		return $columns;
	}


	/**
	 * Custom admin columns content.
	 *
	 * @param string $column Column content.
	 * @param int    $post_id Post ID.
	 * @return void
	 */
	public static function admin_columns( $column, $post_id ) {
		if ( 'status' === $column ) {
			$status = get_post_meta( $post_id, 'status', true );
			echo $status;
		}
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
	 * @param string      $badgeclass_id BadgeClass Entity ID.
	 * @param int         $user_id User ID.
	 * @param int         $nb Number of requests.
	 * @param string|null $status Status or null if all statuses.
	 * @return array
	 */
	public static function get_for_badgeclass_for_user( $badgeclass_id, $user_id, $nb = 1, $status = null ) {
		$args = array(
			'post_type'   => self::$slug,
			'numberposts' => $nb,
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
		if ( $status ) {
			$args['meta_query'][] = array(
				'key'     => 'status',
				'value'   => $status,
				'compare' => '=',
			);
		}
		$posts = get_posts( $args );
		return $posts;
	}


	/**
	 * Return all Badge Requests for a specified BadgeClass and a specified User.
	 *
	 * @param string      $badgeclass_id BadgeClass Entity ID.
	 * @param int         $user_id User ID.
	 * @param string|null $status Status or null if all statuses.
	 * @return array
	 */
	public static function all_for_badgeclass_for_user( $badgeclass_id, $user_id, $status = null ) {
		return self::get_for_badgeclass_for_user( $badgeclass_id, $user_id, -1, $status );
	}


	/**
	 * Display Badge Requests.
	 *
	 * @param array $badge_requests Badge Requests.
	 * @return void
	 */
	public static function display( $badge_requests ) {
		include( Template::locate( 'badge-requests' ) );
	}


	/**
	 * Create a badge request.
	 *
	 * @param int    $badge_id Badge ID.
	 * @param string $recipient_email Recipient email.
	 * @return bool
	 */
	public static function create_badge_request( $badge_id, $recipient_email ) {

		$current_user = wp_get_current_user();
		$recipient    = \get_user_by( 'email', $recipient_email );
		$badge        = BadgeClass::get( $badge_id );
		$type         = 'basic';
		// translators: %s is the user nicename of the user who is requesting the badge.
		$content = sprintf( __( 'Badge issued manually by %s', BF2_DATA['TextDomain'] ), $current_user->user_nicename );

		$status = 'auto-approved';

		$badge_request_id = wp_insert_post(
			array(
				'post_type'      => 'badge-request',
				'post_title'     => sprintf( '%s - %s - %s', $recipient->user_nicename, $badge->name, gmdate( 'Y-m-d H:i:s' ) ),
				'post_content'   => '',
				'post_author'    => $recipient->ID,
				'post_status'    => 'publish',
				'comment_status' => 'closed',
				'ping_status'    => 'closed',
			)
		);

		if ( $badge_request_id ) {
			update_post_meta( $badge_request_id, 'badge', $badge_id );
			update_post_meta( $badge_request_id, 'type', $type );
			update_post_meta( $badge_request_id, 'recipient', $recipient->ID );
			update_post_meta( $badge_request_id, 'status', 'granted' );
			add_post_meta( $badge_request_id, 'dates', array( 'requested' => gmdate( 'Y-m-d H:i:s' ) ) );
			add_post_meta( $badge_request_id, 'dates', array( 'granted' => gmdate( 'Y-m-d H:i:s' ) ) );
			update_post_meta( $badge_request_id, 'approver', $current_user->ID );
			add_post_meta( $badge_request_id, 'content', $content );

			return true;
		}
		return false;
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
		$badge_id     = $_POST['badge_id'] ?? null;

		// The Courses add-on and the WooCommerce add-on are installed.
		if ( class_exists( 'BadgeFactor2\BF2_Courses' ) &&
			class_exists( 'BadgeFactor2\BF2_WooCommerce' ) ) {

			// A Course is linked to the badge page.
			$badge_page = BadgePage::get_by_badgeclass_id( $badge_id );
			$course_id  = get_post_meta( $badge_page, 'course', true );
			if ( $course_id ) {
				// A product is linked to the course.
				$product_id = get_post_meta( $course_id, 'course_product', true );
				if ( $product_id ) {
					// The client has not purchased this product, redirect to the product page.
					if ( ! wc_customer_bought_product( $current_user->user_email, $current_user->ID, $product_id ) ) {
						$status_code         = 402;
						$response['message'] = __( 'You must purchase this product before you can access it.', BF2_DATA['TextDomain'] );
						wp_send_json( $response, $status_code );
					}
				}
			}
		}

		if ( $badge_id && $current_user && self::user_can_request_badge( $badge_id, $current_user->ID ) ) {
			$badge            = BadgeClass::get( $badge_id );
			$content          = $_POST['content'];
			$type             = $_POST['type'];
			$badge_request_id = $_POST['badge_request_id'];

			if ( $badge ) {

				if ( ! $badge_request_id ) {
					// Create a badge request.

					$approvers_emails = BadgePage::get_approvers_emails_by_badgeclass_id( $badge_id );

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
				}

				if ( $badge_request_id ) {
					update_post_meta( $badge_request_id, 'badge', $badge_id );
					update_post_meta( $badge_request_id, 'type', $type );
					update_post_meta( $badge_request_id, 'recipient', $current_user->ID );
					update_post_meta( $badge_request_id, 'status', 'requested' );
					add_post_meta( $badge_request_id, 'dates', array( 'requested' => gmdate( 'Y-m-d H:i:s' ) ) );
					add_post_meta( $badge_request_id, 'content', $content );

					$approvers_emails = BadgePage::get_approvers_emails_by_badgeclass_id( $badge_id );
					$email_subject    = get_option( 'badge_request_approver_email_subject', __( 'A new badge request has been submitted.', BF2_DATA['TextDomain'] ) );
					$email_body       = get_option( 'badge_request_approver_email_body', __( 'The badge $badge$ has been requested by user $user$. You can review it here: $link$.', BF2_DATA['TextDomain'] ) );
					$email_body       = str_replace( '$badge$', $badge->name, $email_body );
					$user_link        = get_site_url() . '/wp-admin/user-edit.php?user_id=' . $current_user->ID;
					$email_body       = str_replace( '$user$', '<a href="' . $user_link . '">' . $current_user->user_nicename . '</a>', $email_body );
					$email_link       = get_site_url() . '/wp-admin/post.php?post=' . $badge_request_id . '&action=edit';
					$email_body       = str_replace( '$link$', '<a href="' . $email_link . '">' . $email_link . '</a>', $email_body );

					if ( BadgePage::is_auto_approved( $badge_id ) ) {
						do_action( 'auto_approve_badge_request', $badge_request_id );
					}

					$email_settings = get_option( 'badgefactor2_emails_settings' );
					if ( ! BadgePage::is_auto_approved( $badge_id ) || (
						isset( $email_settings['send_auto_approved_badge_request_approver_emails'] ) &&
							'on' === $email_settings['send_auto_approved_badge_request_approver_emails']
						)
					) {
						if ( $approvers_emails ) {
							wp_mail( $approvers_emails, $email_subject, $email_body, array( 'Content-Type: text/html; charset=UTF-8' ) );
						}
					}

					$status_code = 201;
					$response    = array(
						'success' => true,
						'message' => __( 'Your request has been submitted!', BF2_DATA['TextDomain'] ),
					);
				}
			}
		}

		wp_send_json( $response, $status_code );
	}


	/**
	 * Check if user can request a badge.
	 *
	 * @param string $badgeclass_id BadgeClass Entity ID.
	 * @param int    $user_id User ID.
	 * @return bool
	 */
	public static function user_can_request_badge( $badgeclass_id, $user_id ) {
		// FIXME Must validate whether or not user can request a badge.
		$user       = get_user_by( 'ID', $user_id );
		$badgeclass = BadgeClass::get( $badgeclass_id );
		$can        = isset( $user->ID ) && $badgeclass;

		return apply_filters( 'user_can_request_badge', $can, $user_id );
	}


	/**
	 * Save (Create or Update) Badge Request.
	 *
	 * @param int     $post_id Badge Request ID.
	 * @param WP_Post $post Post.
	 * @param bool    $update Whether or not this is an update.
	 * @return void
	 */
	public static function save_badge_request( $post_id, $post, $update ) {
		if ( $update ) {
			$status = get_post_meta( $post_id, 'status', true );
			$dates  = get_post_meta( $post_id, 'dates' );

			$dates = array_reverse( $dates );

			// Status change.
			if ( array_key_first( $dates[0] ) !== $status ) {
				add_post_meta( $post_id, 'dates', array( $status => gmdate( 'Y-m-d H:i:s' ) ) );
			}
			do_action( 'update_badge_request', $post );
		} else {
			do_action( 'create_badge_request', $post );
		}
	}


	/**
	 * Checks whether or not Badge Request is in progress.
	 *
	 * @param string  $badgeclass_id BadgeClass entity id.
	 * @param WP_User $user User.
	 * @return boolean
	 */
	public static function is_in_progress( $badgeclass_id, $user = null ) {
		$current_user = $user ? $user : wp_get_current_user();
		return ! empty( self::get_for_badgeclass_for_user( $badgeclass_id, $current_user->ID, 1, 'requested' ) );
	}


	/**
	 * Checks whether or not Badge Request is granted.
	 *
	 * @param string  $badgeclass_id BadgeClass entity id.
	 * @param WP_User $user User.
	 * @return boolean
	 */
	public static function is_granted( $badgeclass_id, $user = null ) {
		$current_user = $user ? $user : wp_get_current_user();
		return ! empty( self::get_for_badgeclass_for_user( $badgeclass_id, $current_user->ID, 1, 'granted' ) );
	}


	/**
	 * Checks whether or not Badge Request is rejected.
	 *
	 * @param string  $badgeclass_id BadgeClass entity id.
	 * @param WP_User $user User.
	 * @return boolean
	 */
	public static function is_rejected( $badgeclass_id, $user = null ) {
		$current_user = $user ? $user : wp_get_current_user();
		return ! empty( self::get_for_badgeclass_for_user( $badgeclass_id, $current_user->ID, 1, 'rejected' ) );
	}


	/**
	 * Returns the latest submitted content of a badge request.
	 *
	 * @param string  $badgeclass_id BadgeClass entity id.
	 * @param WP_User $user User.
	 * @return string
	 */
	public static function get_request_content( $badgeclass_id, $user = null ) {
		$current_user  = $user ? $user : wp_get_current_user();
		$badge_request = self::get_for_badgeclass_for_user( $badgeclass_id, $current_user->ID, 1, 'revision' );
		$content       = '';
		if ( $badge_request ) {
			$content = get_post_meta( $badge_request[0]->ID, 'content' );
			$content = end( $content );
		}
		return $content;
	}


	/**
	 * Returns the badge request ID.
	 *
	 * @param string  $badgeclass_id BadgeClass entity id.
	 * @param WP_User $user User.
	 * @return int
	 */
	public static function get_request_id( $badgeclass_id, $user = null ) {
		$current_user  = $user ? $user : wp_get_current_user();
		$badge_request = self::get_for_badgeclass_for_user( $badgeclass_id, $current_user->ID, 1, 'revision' );
		$id            = false;
		if ( $badge_request ) {
			$id = $badge_request[0]->ID;
		}
		return $id;
	}
}
