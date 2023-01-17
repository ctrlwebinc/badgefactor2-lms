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
 * @phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
 */

namespace BadgeFactor2;

use BadgeFactor2\Admin\Lists\Assertions;
use BadgeFactor2\Admin\Lists\Badges;
use BadgeFactor2\Admin\Lists\Issuers;
use BadgeFactor2\BadgrClient;
use BadgeFactor2\Models\BadgeClass;
use BadgeFactor2\Post_Types\BadgePage;
use CMB2_Field;

/**
 * Badge Factor 2 Admin Class.
 */
class BadgeFactor2_Admin {

	/**
	 * Issuers.
	 *
	 * @var BadgeFactor2\Admin\Lists\Issuers
	 */
	public static $issuers;

	/**
	 * Badges.
	 *
	 * @var BadgeFactor2\Admin\Lists\Badges
	 */
	public static $badges;

	/**
	 * Assertions.
	 *
	 * @var BadgeFactor2\Admin\Lists\Assertions
	 */
	public static $assertions;

	private static $form_slug = null;


	/**
	 * Init Hooks.
	 *
	 * @return void
	 */
	public static function init_hooks() {

		// Ajax Hooks.
		add_filter( 'set-screen-option', array( self::class, 'set_screen' ), 10, 3 );
		add_action( 'wp_ajax_approve_badge_request', array( self::class, 'ajax_approve_badge_request' ) );
		add_action( 'wp_ajax_bf2_filter_type', array( self::class, 'ajax_filter_type' ) );
		add_action( 'wp_ajax_bf2_filter_value', array( self::class, 'ajax_filter_value' ) );
		add_action( 'wp_ajax_reject_badge_request', array( self::class, 'ajax_reject_badge_request' ) );
		add_action( 'wp_ajax_revise_badge_request', array( self::class, 'ajax_revise_badge_request' ) );
		add_action( 'wp_ajax_cancel_revise_badge_request', array( self::class, 'ajax_cancel_revise_badge_request' ) );
		add_action( 'wp_ajax_cancel_reject_badge_request', array( self::class, 'ajax_cancel_reject_badge_request' ) );

		// BadgeFactor2 Hooks.
		add_action( 'approve_badge_request', array( self::class, 'approve_badge_request' ), 10, 3 );
		add_action( 'badge_request_approval_confirmation_email', array( self::class, 'badge_request_approval_confirmation_email' ), 10 );
		add_action( 'badge_request_rejection_confirmation_email', array( self::class, 'badge_request_rejection_confirmation_email' ), 10 );
		add_action( 'badge_request_revision_confirmation_email', array( self::class, 'badge_request_revision_confirmation_email' ), 10 );
		add_action( 'reject_badge_request', array( self::class, 'reject_badge_request' ), 10, 4 );
		add_action( 'revise_badge_request', array( self::class, 'revise_badge_request' ), 10, 4 );
		add_action( 'cancel_reject_badge_request', array( self::class, 'cancel_reject_badge_request' ), 10, 4 );
		add_action( 'cancel_revise_badge_request', array( self::class, 'cancel_revise_badge_request' ), 10, 4 );

		// CMB2 Hooks.
		add_action( 'cmb2_admin_init', array( self::class, 'admin_init' ) );
		add_action( 'cmb2_save_field_bf2_form_slug', array( self::class, 'save_form_slug' ), 99, 3 );
		add_action( 'cmb2_save_field_bf2_autoevaluation_form_slug', array( self::class, 'save_autoevaluation_form_slug' ), 99, 3 );
		add_action( 'cmb2_save_options-page_fields', array( self::class, 'save_options' ), 99, 1 );

		// WordPress Hooks.
		add_action( 'admin_enqueue_scripts', array( self::class, 'load_resources' ) );
		add_action( 'admin_init', array( self::class, 'add_role_and_capabilities' ), 10 );
		add_action( 'admin_menu', array( self::class, 'admin_menus' ) );
		add_action( 'init', array( self::class, 'hook_flush_rewrite_rules' ), 100 );
		add_action( 'load-edit.php', array( self::class, 'all_by_default_in_admin' ), 10 );
		add_filter( 'pw_cmb2_field_select2_asset_path', array( self::class, 'pw_cmb2_field_select2_asset_path' ), 10 );
	}


	/**
	 * Set screen.
	 *
	 * @param bool   $status Status.
	 * @param string $option Option.
	 * @param int    $value Value.
	 *
	 * @return int|bool
	 */
	public static function set_screen( $status, $option, $value ) {
		return $value;
	}


	/**
	 * CMB2 Admin Init hook.
	 *
	 * @return void
	 */
	public static function admin_init() {
		self::register_settings_metaboxes();
	}


	/**
	 * Admin menus.
	 *
	 * @return void
	 */
	public static function admin_menus() {
		global $menu;

		$menu[] = array( '', 'read', 'separator-badgefactor2', '', 'wp-menu-separator badgefactor2' );

		$menus = array(
			array(
				__( 'Issuers', BF2_DATA['TextDomain'] ),
				'issuers',
			),
			array(
				__( 'Badges', BF2_DATA['TextDomain'] ),
				'badges',
			),
			array(
				__( 'Assertions', BF2_DATA['TextDomain'] ),
				'assertions',
			),
		);

		add_menu_page(
			'Badgr',
			'Badgr',
			'manage_badgr',
			$menus[0][1],
			array( self::class, $menus[0][1] . '_page' ),
			BF2_BASEURL . 'assets/images/badgr.svg'
		);

		foreach ( $menus as $m ) {
			$hook = add_submenu_page(
				$menus[0][1],
				$m[0],
				$m[0],
				'manage_badgr',
				$m[1],
				array( self::class, $m[1] . '_page' )
			);

			add_action(
				"load-$hook",
				array( self::class, $m[1] . '_options' )
			);
		}

	}


	/**
	 * Issuers Page.
	 *
	 * @return void
	 */
	public static function issuers_page() {
		?>
		<div class="wrap">
			<h2><?php echo __( 'Issuers', 'badgefactor' ); ?></h2>

			<div id="poststuff">
				<div id="post-body" class="metabox-holder columns-2">
					<div id="post-body-content">
						<div class="meta-box-sortables ui-sortable">
							<?php
							self::$issuers->prepare_items();
							self::$issuers->display();
							?>
						</div>
					</div>
				</div>
				<br class="clear">
			</div>
		</div>
		<?php
	}


	/**
	 * Badges Page.
	 *
	 * @return void
	 */
	public static function badges_page() {
		?>
		<div class="wrap">
			<h2><?php echo __( 'Badges', 'badgefactor' ); ?></h2>

			<div id="poststuff">
				<div id="post-body" class="metabox-holder columns-2">
					<div id="post-body-content">
						<div class="meta-box-sortables ui-sortable">
							<?php
							self::$badges->prepare_items();
							self::$badges->display();
							?>
						</div>
					</div>
				</div>
				<br class="clear">
			</div>
		</div>
		<?php
	}


	/**
	 * Assertions Page.
	 *
	 * @return void
	 */
	public static function assertions_page() {
		?>
		<div class="wrap">
			<h2><?php echo __( 'Assertions', 'badgefactor' ); ?></h2>

			<div id="poststuff">
				<div id="post-body" class="metabox-holder columns-2">
					<div id="post-body-content">
						<div class="meta-box-sortables ui-sortable">
							<?php
							self::$assertions->prepare_items();
							self::$assertions->display();
							?>
						</div>
					</div>
				</div>
				<br class="clear">
			</div>
		</div>
		<?php
	}


	/**
	 * Issuers Options.
	 *
	 * @return void
	 */
	public static function issuers_options() {
		$option = 'per_page';
		$args   = array(
			'label'   => __( 'Issuers', BF2_DATA['TextDomain'] ),
			'default' => 10,
			'option'  => 'issuers_per_page',
		);

		add_screen_option( $option, $args );

		self::$issuers = new Issuers();
	}


	/**
	 * Badges Options.
	 *
	 * @return void
	 */
	public static function badges_options() {
		$option = 'per_page';
		$args   = array(
			'label'   => __( 'Badges', BF2_DATA['TextDomain'] ),
			'default' => 10,
			'option'  => 'badges_per_page',
		);

		add_screen_option( $option, $args );

		self::$badges = new Badges();
	}


	/**
	 * Assertions Options.
	 *
	 * @return void
	 */
	public static function assertions_options() {
		$option = 'per_page';
		$args   = array(
			'label'   => __( 'Assertions', BF2_DATA['TextDomain'] ),
			'default' => 10,
			'option'  => 'assertions_per_page',
		);

		add_screen_option( $option, $args );

		self::$assertions = new Assertions();
	}


	/**
	 * Admin Resources Loader.
	 *
	 * @return void
	 */
	public static function load_resources() {
		wp_enqueue_style( 'cmb2-styles-css', BF2_BASEURL . 'lib/CMB2/css/cmb2.min.css', array(), '5.2.5', 'all' );
		wp_enqueue_script( 'cmb2-conditional-logic', BF2_BASEURL . 'lib/CMB2-conditional-logic/cmb2-conditional-logic.min.js', array( 'jquery' ), '1.0.0', true );
		wp_enqueue_style( 'badgefactor2-admin-css', BF2_BASEURL . 'assets/css/admin.css', array(), BF2_DATA['Version'], 'all' );
		wp_enqueue_script( 'badgefactor2-admin-js', BF2_BASEURL . 'assets/js/admin.js', array( 'jquery' ), 'ml01' /*BF2_DATA['Version']*/, true );
	}


	/**
	 * Registers Settings Metabox.
	 *
	 * @return void
	 */
	private static function register_settings_metaboxes() {

		$args = array(
			'id'           => 'badgefactor2_settings',
			'menu_title'   => 'Badge Factor 2',
			'object_types' => array( 'options-page' ),
			'option_key'   => 'badgefactor2',
			'icon_url'     => BF2_BASEURL . ( 'assets/images/badgefactor2_logo.svg' ),
			'tab_group'    => 'badgefactor2',
			'tab_title'    => __( 'Settings', BF2_DATA['TextDomain'] ),
			'capability'   => 'manage_badgr',
		);

		// 'tab_group' property is supported in > 2.4.0.
		if ( version_compare( CMB2_VERSION, '2.4.0' ) ) {
			$args['display_cb'] = 'badgefactor2_options_display_with_tabs';
		}

		$badgefactor2_settings = new_cmb2_box( $args );

		$badgefactor2_settings->add_field(
			array(
				'name' => __( 'Send WordPress registration emails?', BF2_DATA['TextDomain'] ),
				'desc' => __( 'Registration emails are managed by Badgr. If you enable this, users will receive two registration validations emails.', BF2_DATA['TextDomain'] ),
				'id'   => 'bf2_send_new_user_notifications',
				'type' => 'checkbox',
			)
		);

		$badgefactor2_settings->add_field(
			array(
				'name'       => __( 'Make issued badges public or private by default?', BF2_DATA['TextDomain'] ),
				'desc'       => __( 'Changing this will not change the visibility of previously issued badges.', BF2_DATA['TextDomain'] ),
				'id'         => 'bf2_assertion_visibility',
				'type'       => 'radio',
				'options'    => array(
					'public'  => __( 'Public', BF2_DATA['TextDomain'] ),
					'private' => __( 'Private', BF2_DATA['TextDomain'] ),
				),
				'attributes' => array(
					'required' => 'required',
				),
			)
		);

		$badgefactor2_settings->add_field(
			array(
				'name'    => __( 'Form slug', BF2_DATA['TextDomain'] ),
				'id'      => 'bf2_form_slug',
				'type'    => 'text',
				'default' => 'form',
			)
		);

		$badgefactor2_settings->add_field(
			array(
				'name'    => __( 'Issuers slug', BF2_DATA['TextDomain'] ),
				'id'      => 'bf2_issuers_slug',
				'type'    => 'text',
				'default' => 'issuers',
			)
		);

		$badgefactor2_settings->add_field(
			array(
				'name'    => __( 'Activate self-evaluation form?', BF2_DATA['TextDomain'] ),
				'id'      => 'bf2_autoevaluation_form',
				'type'    => 'checkbox',
				'default' => false,
			)
		);

		$badgefactor2_settings->add_field(
			array(
				'name'       => __( 'Autoevaluation form slug', BF2_DATA['TextDomain'] ),
				'id'         => 'bf2_autoevaluation_form_slug',
				'type'       => 'text',
				'default'    => 'autoevaluation',
				'attributes' => array(
					'data-conditional-id'    => 'bf2_autoevaluation_form',
					'data-conditional-value' => true,
				),
			)
		);

		/**
		 * Registers Emails settings page.
		 */
		$args = array(
			'id'           => 'badgefactor2_emails_settings_page',
			'menu_title'   => __( 'Emails', BF2_DATA['TextDomain'] ),
			'object_types' => array( 'options-page' ),
			'option_key'   => 'badgefactor2_emails_settings',
			'parent_slug'  => 'badgefactor2',
			'tab_group'    => 'badgefactor2',
			'tab_title'    => __( 'Emails', BF2_DATA['TextDomain'] ),
			'capability'   => 'manage_badgr',
		);

		// 'tab_group' property is supported in > 2.4.0.
		if ( version_compare( CMB2_VERSION, '2.4.0' ) ) {
			$args['display_cb'] = 'badgefactor2_options_display_with_tabs';
		}

		$emails_settings = new_cmb2_box( $args );

		// Approver email - Badge Request.
		$emails_settings->add_field(
			array(
				'name' => __( 'Badge Request - Email to approver', BF2_DATA['TextDomain'] ),
				'desc' => __( 'This email will be sent to approvers when a user submits a badge request.', BF2_DATA['TextDomain'] ),
				'id'   => 'badge_request_approver_email',
				'type' => 'title',
			)
		);

		$emails_settings->add_field(
			array(
				'name'    => __( 'Email from', BF2_DATA['TextDomain'] ),
				'id'      => 'badge_request_approval_email_from',
				'type'    => 'text',
				'default' => get_option( 'admin_email' ),
			)
		);

		$emails_settings->add_field(
			array(
				'name'    => __( 'Title', BF2_DATA['TextDomain'] ),
				'id'      => 'badge_request_approver_email_subject',
				'type'    => 'text',
				'default' => 'A new badge request has been submitted.',
			)
		);

		$emails_settings->add_field(
			array(
				'name'    => __( 'Body', BF2_DATA['TextDomain'] ),
				'id'      => 'badge_request_approver_email_body',
				'type'    => 'wysiwyg',
				'default' => 'The badge $badge$ has been requested by user $user$. You can review it here: $link$.',
				'desc'    => __( 'Available variables: $badge$ $user$ $link$', BF2_DATA['TextDomain'] ),
			)
		);

		$emails_settings->add_field(
			array(
				'name' => __( 'Send auto-approved notifications?', BF2_DATA['TextDomain'] ),
				'id'   => 'send_auto_approved_badge_request_approver_emails',
				'type' => 'checkbox',
			)
		);

		// Approval confirmation - Badge Request.
		$emails_settings->add_field(
			array(
				'name' => __( 'Badge Request - Approval confirmation', BF2_DATA['TextDomain'] ),
				'desc' => __( 'This email will be sent to a user when a badge request is approved.', BF2_DATA['TextDomain'] ),
				'id'   => 'badge_request_approval_confirmation_email',
				'type' => 'title',
			)
		);

		$emails_settings->add_field(
			array(
				'name'    => __( 'Email from', BF2_DATA['TextDomain'] ),
				'id'      => 'badge_request_approval_confirmation_email_from',
				'type'    => 'text',
				'default' => get_option( 'admin_email' ),
			)
		);

		$emails_settings->add_field(
			array(
				'name'    => __( 'Title', BF2_DATA['TextDomain'] ),
				'id'      => 'badge_request_approval_confirmation_email_subject',
				'type'    => 'text',
				'default' => 'Your badge request has been approved !',
			)
		);

		$emails_settings->add_field(
			array(
				'name'    => __( 'Body', BF2_DATA['TextDomain'] ),
				'id'      => 'badge_request_approval_confirmation_email_body',
				'type'    => 'wysiwyg',
				'default' => 'Your request for the badge $badge$ has been approved. You can view it here: $link$.',
				'desc'    => __( 'Available variables: $badge$ $link$', BF2_DATA['TextDomain'] ),
			)
		);

		// Rejection confirmation - Badge Request.
		$emails_settings->add_field(
			array(
				'name' => __( 'Badge Request - Rejection confirmation', BF2_DATA['TextDomain'] ),
				'desc' => __( 'This email will be sent to a user when a badge request is rejected.', BF2_DATA['TextDomain'] ),
				'id'   => 'badge_request_rejection_confirmation_email',
				'type' => 'title',
			)
		);

		$emails_settings->add_field(
			array(
				'name'    => __( 'Email from', BF2_DATA['TextDomain'] ),
				'id'      => 'badge_request_rejection_confirmation_email_from',
				'type'    => 'text',
				'default' => get_option( 'admin_email' ),
			)
		);

		$emails_settings->add_field(
			array(
				'name'    => __( 'Title', BF2_DATA['TextDomain'] ),
				'id'      => 'badge_request_rejection_confirmation_email_subject',
				'type'    => 'text',
				'default' => 'Your badge request has been rejected.',
			)
		);

		$emails_settings->add_field(
			array(
				'name'    => __( 'Body', BF2_DATA['TextDomain'] ),
				'id'      => 'badge_request_rejection_confirmation_email_body',
				'type'    => 'wysiwyg',
				'default' => 'Your request for the badge $badge$ has been rejected. Here is the reason provided:<br/>$reason$<br/>You can resubmit a request here: $link$.',
				'desc'    => __( 'Available variables: $badge$ $reason$ $link$', BF2_DATA['TextDomain'] ),
			)
		);

		// Revision request confirmation - Badge Request.
		$emails_settings->add_field(
			array(
				'name' => __( 'Badge Request - Revision confirmation', BF2_DATA['TextDomain'] ),
				'desc' => __( 'This email will be sent to a user when a badge request needs to be revised.', BF2_DATA['TextDomain'] ),
				'id'   => 'badge_request_revision_confirmation_email',
				'type' => 'title',
			)
		);

		$emails_settings->add_field(
			array(
				'name'    => __( 'Email from', BF2_DATA['TextDomain'] ),
				'id'      => 'badge_request_revision_confirmation_email_from',
				'type'    => 'text',
				'default' => get_option( 'admin_email' ),
			)
		);

		$emails_settings->add_field(
			array(
				'name'    => __( 'Title', BF2_DATA['TextDomain'] ),
				'id'      => 'badge_request_revision_confirmation_email_subject',
				'type'    => 'text',
				'default' => 'Your badge request must be revised.',
			)
		);

		$emails_settings->add_field(
			array(
				'name'    => __( 'Body', BF2_DATA['TextDomain'] ),
				'id'      => 'badge_request_revision_confirmation_email_body',
				'type'    => 'wysiwyg',
				'default' => 'You must revise and resubmit your badge request for the badge $badge$. Here is the reason provided:<br/>$reason$<br/>You can revise your request here: $link$.',
			)
		);

		/**
		 * Registers Badgr settings page.
		 */
		$args = array(
			'id'           => 'badgefactor2_badgr_settings_page',
			'menu_title'   => __( 'Badgr Server', BF2_DATA['TextDomain'] ) . ( BadgrClient::is_active() ? '' : '&nbsp;<span class="awaiting-mod" title="' . __( 'You need to configure the connection to the Badgr service you\'ll use.', BF2_DATA['TextDomain'] ) . '">!</span>' ),
			'object_types' => array( 'options-page' ),
			'option_key'   => 'badgefactor2_badgr_settings',
			'parent_slug'  => 'badgefactor2',
			'tab_group'    => 'badgefactor2',
			'tab_title'    => 'Badgr',
			'capability'   => 'manage_badgr',
		);

		// 'tab_group' property is supported in > 2.4.0.
		if ( version_compare( CMB2_VERSION, '2.4.0' ) ) {
			$args['display_cb'] = 'badgefactor2_options_display_with_tabs';
		}

		$badgr_settings = new_cmb2_box( $args );

		// Badgr server quick select.
		$badgr_settings->add_field(
			array(
				'name'             => __( 'Badgr server', BF2_DATA['TextDomain'] ),
				'desc'             => __( 'Choose the type of Badgr server you\'re using', BF2_DATA['TextDomain'] ),
				'id'               => 'badgr_server_quick_select',
				'type'             => 'radio',
				'show_option_none' => false,
				'default'          => 'local',
				'options'          => array(
					'local'  => __( 'Local Badgr', BF2_DATA['TextDomain'] ),
					'custom' => __( 'Custom', BF2_DATA['TextDomain'] ),
				),

			)
		);

		// Public url to use with custom server setting.
		$badgr_settings->add_field(
			array(
				'name'      => __( 'Public URL', BF2_DATA['TextDomain'] ),
				'desc'      => __( 'Format: scheme://URL:port', BF2_DATA['TextDomain'] ),
				'id'        => 'badgr_server_public_url',
				'type'      => 'text_url',
				'default'   => 'http://localhost:8000',
				'protocols' => array( 'http', 'https' ),

			)
		);

		$badgr_settings->add_field(
			array(
				'name'      => __( 'Internal URL', BF2_DATA['TextDomain'] ),
				'desc'      => __( 'Format: scheme://URL:port', BF2_DATA['TextDomain'] ),
				'id'        => 'badgr_server_internal_url',
				'type'      => 'text_url',
				'default'   => '',
				'protocols' => array( 'http', 'https' ),

			)
		);

		$badgr_settings->add_field(
			array(
				'name' => __( 'Client ID', BF2_DATA['TextDomain'] ),
				'id'   => 'badgr_server_client_id',
				'type' => 'text',
			)
		);

		$badgr_settings->add_field(
			array(
				'name' => __( 'Client Secret', BF2_DATA['TextDomain'] ),
				'id'   => 'badgr_server_client_secret',
				'type' => 'text',
			)
		);

		$badgr_settings->add_field(
			array(
				'name' => __( 'Password Grant Client ID', BF2_DATA['TextDomain'] ),
				'id'   => 'badgr_server_password_grant_client_id',
				'type' => 'text',
			)
		);

		$badgr_settings->add_field(
			array(
				'name'      => __( 'Password Grant Client Secret', BF2_DATA['TextDomain'] ),
				'id'        => 'badgr_server_password_grant_client_secret',
				'type'      => 'text',
				'after_row' => function ( $field_args, $field ) {
					include BF2_ABSPATH . 'templates/admin/badgr/server-status.tpl.php';
					include BF2_ABSPATH . 'templates/admin/badgr/server-link.tpl.php';
				},
			)
		);

		$badgr_settings->add_field(
			array(
				'name'    => __( 'Public pages redirect slug', BF2_DATA['TextDomain'] ),
				'id'      => 'badgr_server_public_pages_redirect_slug',
				'type'    => 'text',
				'default' => 'badgr',
			)
		);

		/**
		 * Registers Add-Ons page.
		 */
		$args = array(
			'id'           => 'badgefactor2_plugins_page',
			'menu_title'   => __( 'Add-Ons', BF2_DATA['TextDomain'] ),
			'object_types' => array( 'options-page' ),
			'option_key'   => 'badgefactor2_plugins',
			'parent_slug'  => 'badgefactor2',
			'tab_group'    => 'badgefactor2',
			'tab_title'    => __( 'Add-Ons', BF2_DATA['TextDomain'] ),
			'capability'   => 'manage_badgr',
		);

		// 'tab_group' property is supported in > 2.4.0.
		if ( version_compare( CMB2_VERSION, '2.4.0' ) ) {
			$args['display_cb'] = 'badgefactor2_options_display_with_tabs';
		}

		$plugins = new_cmb2_box( $args );

		$plugins->add_field(
			array(
				'name' => __( 'Add-Ons List', BF2_DATA['TextDomain'] ),
				'id'   => 'badgefactor2_addons_list',
				'type' => 'addons',
			)
		);

		/**
		 * Registers Social media settings page.
		 */
		$args = array(
			'id'           => 'badgefactor2_social_media_settings_page',
			'menu_title'   => __( 'Social media', BF2_DATA['TextDomain'] ),
			'object_types' => array( 'options-page' ),
			'option_key'   => 'badgefactor2_social_media_settings',
			'parent_slug'  => 'badgefactor2',
			'tab_group'    => 'badgefactor2',
			'tab_title'    => __( 'Social media', BF2_DATA['TextDomain'] ),
			'capability'   => 'manage_badgr',
		);

		// 'tab_group' property is supported in > 2.4.0.
		if ( version_compare( CMB2_VERSION, '2.4.0' ) ) {
			$args['display_cb'] = 'badgefactor2_options_display_with_tabs';
		}

		$social_settings = new_cmb2_box( $args );
		
		$social_settings->add_field(
			array(
				'name'    => __( 'Activate sharing on Facebook', BF2_DATA['TextDomain'] ),
				'id'      => 'bf2_social_media_sharing_facebook',
				'type'    => 'checkbox',
				'default' => false,
			)
		);

		$social_settings->add_field(
			array(
				'name'    => __( 'Activate sharing on Twitter', BF2_DATA['TextDomain'] ),
				'id'      => 'bf2_social_media_sharing_twitter',
				'type'    => 'checkbox',
				'default' => false,
			)
		);

		$social_settings->add_field(
			array(
				'name'    => __( 'Activate sharing on Linkedin', BF2_DATA['TextDomain'] ),
				'id'      => 'bf2_social_media_sharing_linkedin',
				'type'    => 'checkbox',
				'default' => false,
			)
		);
	}


	/**
	 * Add role and capabilities.
	 *
	 * @return void
	 */
	public static function add_role_and_capabilities() {
		$capabilities = array(
			'read'                          => true,
			'manage_badgr'                  => true,
			'upload_files'                  => true,
			'list_users'                    => true,
			'edit_posts'                    => true,
			'gravityforms_view_entries'     => true,
			'gravityforms_edit_entries'     => true,
			'gravityforms_delete_entries'   => true,
			'gravityforms_view_entry_notes' => true,
			'gravityforms_edit_entry_notes' => true,
		);

		add_role(
			'badgr_administrator',
			__( 'Badgr Administrator', BF2_DATA['TextDomain'] ),
			$capabilities
		);
		$administrator = get_role( 'administrator' );
		$administrator->add_cap( 'manage_badgr', true );
	}


	/**
	 * Approve Badge Request
	 *
	 * @param WP_User $approver Approver user.
	 * @param int     $badge_request_id Badge Request ID.
	 * @param boolean $ajax Whether or not to return an ajax response.
	 * @return bool|void
	 */
	public static function approve_badge_request( $approver, $badge_request_id, $ajax = false ) {

		$response = array(
			'status'  => 'fail',
			'message' => '',
		);

		$badge_request = get_post( $badge_request_id );

		if ( ! in_array( 'approver', $approver->roles, true ) && ! in_array( 'administrator', $approver->roles, true ) ) {
			$response['message'] = __( 'You are not an approver on this website.', BF2_DATA['TextDomain'] );
		} elseif ( ! $badge_request || 'badge-request' !== $badge_request->post_type ) {
			$response['message'] = __( 'This request is invalid!', BF2_DATA['TextDomain'] );
		} else {
			$badge_entity_id = get_post_meta( $badge_request_id, 'badge', true );
			$recipient_id    = get_post_meta( $badge_request_id, 'recipient', true );
			$recipient       = get_user_by( 'ID', $recipient_id );

			$evidence_content = get_post_meta( $badge_request_id, 'content', true );
			$matches = array();
			preg_match("/(<a href=')(.*)(' target=')/",$evidence_content, $matches);
			$evidence_url = site_url( $matches[2]);
			$badge_page = BadgePage::get_by_badgeclass_id( $badge_entity_id );
			$approvers  = get_post_meta( $badge_page->ID, 'badge_request_approver', true );

			update_post_meta( $badge_request_id, 'status', 'granted' );
			update_post_meta( $badge_request_id, 'approver', $approver->ID );
			$issued_on = gmdate( 'Y-m-d H:i:s' );
			add_post_meta( $badge_request_id, 'dates', array( 'granted' =>  $issued_on ) );

			$assertion_entity_id = BadgrProvider::add_assertion( $badge_entity_id, $recipient->user_email, 'email', $issued_on, $evidence_url);
			add_post_meta( $badge_request_id, 'assertion', $assertion_entity_id );
			do_action( 'badge_request_approval_confirmation_email', $badge_request_id );
			$response = array(
				'status'  => 'success',
				'message' => __( 'The badge request has been approved!', BF2_DATA['TextDomain'] ),
			);
		}

		if ( $ajax ) {
			wp_send_json( $response );
		}
		return 'success' === $response['status'];
	}

	/**
	 * Reject Badge Request
	 *
	 * @param WP_User $approver Approver user.
	 * @param int     $badge_request_id Badge Request ID.
	 * @param string  $rejection_reason Rejection reason.
	 * @param boolean $ajax Whether or not to return an ajax response.
	 * @return bool|void
	 */
	public static function reject_badge_request( $approver, $badge_request_id, $rejection_reason = '', $ajax = false ) {
		$response = array(
			'status'  => 'fail',
			'message' => '',
		);

		$badge_request = get_post( $badge_request_id );

		if ( ! in_array( 'approver', $approver->roles, true ) && ! in_array( 'administrator', $approver->roles, true ) ) {
			$response['message'] = __( 'You are not an approver on this website.', BF2_DATA['TextDomain'] );
		} elseif ( ! $badge_request || 'badge-request' !== $badge_request->post_type ) {
			$response['message'] = __( 'This request is invalid!', BF2_DATA['TextDomain'] );
		} else {
			$badge_entity_id = get_post_meta( $badge_request_id, 'badge', true );
			$badge_page      = BadgePage::get_by_badgeclass_id( $badge_entity_id );
			$approvers       = get_post_meta( $badge_page->ID, 'badge_request_approver', true );

			update_post_meta( $badge_request_id, 'status', 'rejected' );
			update_post_meta( $badge_request_id, 'approver', $approver->ID );
			update_post_meta( $badge_request_id, 'rejection_reason', $rejection_reason );
			add_post_meta( $badge_request_id, 'dates', array( 'rejected' => gmdate( 'Y-m-d H:i:s' ) ) );
			do_action( 'badge_request_rejection_confirmation_email', $badge_request_id );
			$response = array(
				'status'  => 'success',
				'message' => __( 'The badge request has been rejected.', BF2_DATA['TextDomain'] ),
			);
		}
		wp_send_json( $response );
	}

	public static function cancel_reject_badge_request( $approver, $badge_request_id, $cancellation_reason = '', $ajax = false ) {
		$response = array(
			'status'  => 'fail',
			'message' => '',
		);

		$badge_request = get_post( $badge_request_id );

		if ( ! in_array( 'approver', $approver->roles, true ) && ! in_array( 'administrator', $approver->roles, true ) ) {
			$response['message'] = __( 'You are not an approver on this website.', BF2_DATA['TextDomain'] );
		} elseif ( ! $badge_request || 'badge-request' !== $badge_request->post_type ) {
			$response['message'] = __( 'This request is invalid!', BF2_DATA['TextDomain'] );
		} else {
			$badge_entity_id = get_post_meta( $badge_request_id, 'badge', true );
			$badge_page      = BadgePage::get_by_badgeclass_id( $badge_entity_id );
			$approvers       = get_post_meta( $badge_page->ID, 'badge_request_approver', true );

			update_post_meta( $badge_request_id, 'status', 'requested' );
			update_post_meta( $badge_request_id, 'approver', $approver->ID );
			//update_post_meta( $badge_request_id, 'rejection_reason', $rejection_reason );
			add_post_meta( $badge_request_id, 'dates', array( 'rejection cancelled' => gmdate( 'Y-m-d H:i:s' ) ) );
			//do_action( 'badge_request_rejection_confirmation_email', $badge_request_id );
			$response = array(
				'status'  => 'success',
				'message' => __( 'The badge request rejection has been cancelled.', BF2_DATA['TextDomain'] ),
			);
		}
		wp_send_json( $response );
	}

	/**
	 * Revision Badge Request
	 *
	 * @param WP_User $approver Approver user.
	 * @param int     $badge_request_id Badge Request ID.
	 * @param string  $revision_reason Revision reason.
	 * @param boolean $ajax Whether or not to return an ajax response.
	 * @return bool|void
	 */
	public static function revise_badge_request( $approver, $badge_request_id, $revision_reason = '', $ajax = false ) {
		$response = array(
			'status'  => 'fail',
			'message' => '',
		);

		$badge_request = get_post( $badge_request_id );

		if ( ! in_array( 'approver', $approver->roles, true ) && ! in_array( 'administrator', $approver->roles, true ) ) {
			$response['message'] = __( 'You are not an approver on this website.', BF2_DATA['TextDomain'] );
		} elseif ( ! $badge_request || 'badge-request' !== $badge_request->post_type ) {
			$response['message'] = __( 'This request is invalid!', BF2_DATA['TextDomain'] );
		} else {
			$badge_entity_id = get_post_meta( $badge_request_id, 'badge', true );
			$badge_page      = BadgePage::get_by_badgeclass_id( $badge_entity_id );
			$approvers       = get_post_meta( $badge_page->ID, 'badge_request_approver', true );

			update_post_meta( $badge_request_id, 'status', 'revision' );
			update_post_meta( $badge_request_id, 'approver', $approver->ID );
			update_post_meta( $badge_request_id, 'revision_reason', $revision_reason );
			add_post_meta( $badge_request_id, 'dates', array( 'revision' => gmdate( 'Y-m-d H:i:s' ) ) );
			do_action( 'badge_request_revision_confirmation_email', $badge_request_id );
			$response = array(
				'status'  => 'success',
				'message' => __( 'The badge request has been sent back for revision.', BF2_DATA['TextDomain'] ),
			);

		}
		wp_send_json( $response );
	}

	public static function cancel_revise_badge_request( $approver, $badge_request_id, $cancellation_reason = '', $ajax = false ) {
		$response = array(
			'status'  => 'fail',
			'message' => '',
		);

		$badge_request = get_post( $badge_request_id );

		if ( ! in_array( 'approver', $approver->roles, true ) && ! in_array( 'administrator', $approver->roles, true ) ) {
			$response['message'] = __( 'You are not an approver on this website.', BF2_DATA['TextDomain'] );
		} elseif ( ! $badge_request || 'badge-request' !== $badge_request->post_type ) {
			$response['message'] = __( 'This request is invalid!', BF2_DATA['TextDomain'] );
		} else {
			$badge_entity_id = get_post_meta( $badge_request_id, 'badge', true );
			$badge_page      = BadgePage::get_by_badgeclass_id( $badge_entity_id );
			$approvers       = get_post_meta( $badge_page->ID, 'badge_request_approver', true );

			update_post_meta( $badge_request_id, 'status', 'revision' );
			update_post_meta( $badge_request_id, 'approver', $approver->ID );
			update_post_meta( $badge_request_id, 'revision_reason', $revision_reason );
			add_post_meta( $badge_request_id, 'dates', array( 'revision' => gmdate( 'Y-m-d H:i:s' ) ) );
			do_action( 'badge_request_revision_confirmation_email', $badge_request_id );
			$response = array(
				'status'  => 'success',
				'message' => __( 'The badge request has been sent back for revision.', BF2_DATA['TextDomain'] ),
			);

		}
		wp_send_json( $response );
	}


	/**
	 * Force admin display to list all badge requests, and not just 'mine'.
	 *
	 * @return void
	 */
	public static function all_by_default_in_admin() {
		global $typenow;
		// Not our post type, bail out.
		if ( 'badge-request' !== $typenow ) {
			return;
		}

		// Only the Mine tab fills this conditions, redirect.
		if ( ! isset( $_GET['post_status'] ) && ! isset( $_GET['all_posts'] ) ) {
			wp_redirect( admin_url( 'edit.php?post_type=badge-request&all_posts=1' ) );
			exit();
		}
	}


	/**
	 * Sends a Badge Request approval confirmation email.
	 *
	 * @param int $badge_request_id Badge Request ID.
	 * @return bool
	 */
	public static function badge_request_approval_confirmation_email( $badge_request_id ) {
		$badge_request = get_post( $badge_request_id );
		if ( ! $badge_request ) {
			return false;
		}

		$badge_entity_id = get_post_meta( $badge_request_id, 'badge', true );
		$badge_page      = BadgePage::get_by_badgeclass_id( $badge_entity_id );
		$badge           = BadgeClass::get( $badge_entity_id );
		$email_body      = '';

		if ( ! $badge_page ) {
			return false;
		}

		$recipient_id = get_post_meta( $badge_request_id, 'recipient', true );
		$recipient    = get_user_by( 'ID', $recipient_id );

		$email_subject = cmb2_get_option( 'badgefactor2_emails_settings', 'badge_request_approval_confirmation_email_subject', __( 'Your badge request has been approved !', BF2_DATA['TextDomain'] ) );
		$email_body   .= '<div style="font-family: Sans-serif">';
		$email_body   .= cmb2_get_option( 'badgefactor2_emails_settings', 'badge_request_approval_confirmation_email_body', __( 'Your request for the badge $badge$ has been approved. You can view it here: $link$.', BF2_DATA['TextDomain'] ) );
		$email_body    = str_replace( '$badge$', $badge->name, $email_body );
		$email_link    = self::build_approved_email_link( $badge_page, $recipient_id );
		$email_body    = str_replace( '$link$', '<a href="' . $email_link . '">' . $email_link . '</a>', $email_body );
		$email_body    = apply_filters( 'the_content', $email_body );
		$email_body   .= '</div>';

		$sanitized_blog_name = str_replace( '"', "'", get_option( 'blog_name' ) );
			$from_email          = cmb2_get_option( 'badgefactor2_emails_settings', 'badge_request_approval_confirmation_email_from', get_option( 'admin_email' ) );
		$from                = sprintf( "From: \"%s\" <%s>;\n\r", $sanitized_blog_name, $from_email );

		add_filter(
			'wp_mail_from',
			function( $original_email_address ) use ( $from_email ) {
				return $from_email;
			}
		);

		return wp_mail( $recipient->user_email, $email_subject, $email_body, array( 'Content-Type: text/html; charset=UTF-8', $from ) );
	}


	/**
	 * Sends a Badge Request rejection confirmation email.
	 *
	 * @param int $badge_request_id Badge Request ID.
	 * @return bool
	 */
	public static function badge_request_rejection_confirmation_email( $badge_request_id ) {
		$badge_request = get_post( $badge_request_id );
		if ( ! $badge_request ) {
			return false;
		}

		$badge_entity_id  = get_post_meta( $badge_request_id, 'badge', true );
		$badge_page       = BadgePage::get_by_badgeclass_id( $badge_entity_id );
		$badge            = BadgeClass::get( $badge_entity_id );
		$rejection_reason = get_post_meta( $badge_request_id, 'rejection_reason', true );
		
		if ( ! $badge_page ) {
			return false;
		}

		$recipient_id = get_post_meta( $badge_request_id, 'recipient', true );
		$recipient    = get_user_by( 'ID', $recipient_id );
		$email_body       = '';

		$email_subject = cmb2_get_option( 'badgefactor2_emails_settings', 'badge_request_rejection_confirmation_email_subject', __( 'Your badge request has been rejected.', BF2_DATA['TextDomain'] ) );
		$email_body   .= '<div style="font-family: Sans-serif">';
		$email_body   .= cmb2_get_option( 'badgefactor2_emails_settings', 'badge_request_rejection_confirmation_email_body', __( 'Your request for the badge $badge$ has been rejected. Here is the reason provided:<br/>$reason$<br/>You can resubmit a request here: $link$.', BF2_DATA['TextDomain'] ) );
		$email_body    = str_replace( '$badge$', $badge->name, $email_body );
		$email_body    = str_replace( '$reason$', $rejection_reason, $email_body );
		$email_body    = apply_filters( 'the_content', $email_body );
		$email_body   .= '</div>';

		$email_link = self::build_rejection_email_link( $badge_page );
		$email_body = str_replace( '$link$', '<a href="' . $email_link . '">' . $email_link . '</a>', $email_body );

		$sanitized_blog_name = str_replace( '"', "'", get_option( 'blog_name' ) );
		$from_email          = cmb2_get_option( 'badgefactor2_emails_settings', 'badge_request_rejection_confirmation_email_from', get_option( 'admin_email' ) );
		$from                = sprintf( "From: \"%s\" <%s>;\n\r", $sanitized_blog_name, $from_email );

		add_filter(
			'wp_mail_from',
			function( $original_email_address ) use ( $from_email ) {
				return $from_email;
			}
		);

		return wp_mail( $recipient->user_email, $email_subject, $email_body, array( 'Content-Type: text/html; charset=UTF-8', $from ) );
	}


	/**
	 * Sends a Badge Request revision request confirmation email.
	 *
	 * @param int $badge_request_id Badge Request ID.
	 * @return bool
	 */
	public static function badge_request_revision_confirmation_email( $badge_request_id ) {
		$badge_request = get_post( $badge_request_id );
		if ( ! $badge_request ) {
			return false;
		}

		$badge_entity_id = get_post_meta( $badge_request_id, 'badge', true );
		$badge_page      = BadgePage::get_by_badgeclass_id( $badge_entity_id );
		$badge           = BadgeClass::get( $badge_entity_id );
		$revision_reason = get_post_meta( $badge_request_id, 'revision_reason', true );
		
		if ( ! $badge_page ) {
			return false;
		}

		$recipient_id = get_post_meta( $badge_request_id, 'recipient', true );
		$recipient    = get_user_by( 'ID', $recipient_id );
		$email_body      = '';
		
		$email_subject = cmb2_get_option( 'badgefactor2_emails_settings', 'badge_request_revision_confirmation_email_subject', __( 'Your badge request must be revised', BF2_DATA['TextDomain'] ) );
		$email_body   .= '<div style="font-family: Sans-serif">';
		$email_body   .= cmb2_get_option( 'badgefactor2_emails_settings', 'badge_request_revision_confirmation_email_body', __( 'You must revise and resubmit your badge request for the badge $badge$. Here is the reason provided:<br/>$reason$<br/>You can revise your request here: $link$.', BF2_DATA['TextDomain'] ) );
		$email_body    = str_replace( '$badge$', $badge->name, $email_body );
		$email_body    = str_replace( '$reason$', $revision_reason, $email_body );
		$email_body    = apply_filters( 'the_content', $email_body );
		$email_body   .= '</div>';

		$email_link = self::build_revision_email_link( $badge_page );
		$email_body = str_replace( '$link$', '<a href="' . $email_link . '">' . $email_link . '</a>', $email_body );

		$sanitized_blog_name = str_replace( '"', "'", get_option( 'blog_name' ) );
		$from_email          = cmb2_get_option( 'badgefactor2_emails_settings', 'badge_request_revision_confirmation_email_from', get_option( 'admin_email' ) );
		$from                = sprintf( "From: \"%s\" <%s>;\n\r", $sanitized_blog_name, $from_email );

		add_filter(
			'wp_mail_from',
			function( $original_email_address ) use ( $from_email ) {
				return $from_email;
			}
		);

		return wp_mail( $recipient->user_email, $email_subject, $email_body, array( 'Content-Type: text/html; charset=UTF-8', $from ) );
	}


	/**
	 * Ajax filter type.
	 *
	 * @return void
	 */
	public static function ajax_filter_type() {
		$filter_type = stripslashes( $_POST['filter_type'] );
		if ( ! $filter_type ) {
			$response = array(
				'listClass' => null,
				'options'   => array(
					"<option value=''>" . __( 'Filter for', BF2_DATA['TextDomain'] ) . '</option>',
				),
			);
		} else {
			$filter_values = $filter_type::get_instance()->all( -1 );
			$response      = array(
				'listClass' => array_values( $filter_values )[0]->listClass,
				'options'   => array(
					"<option value=''>" . __( 'Filter for', BF2_DATA['TextDomain'] ) . '</option>',
				),
			);
			foreach ( $filter_values as $filter ) {
				$response['options'][] = "<option value='{$filter->entityId}'>{$filter->name}</option>";
			}
			$response['options'] = join( '', $response['options'] );
		}

		wp_send_json( $response );
	}


	/**
	 * Ajax filter value.
	 *
	 * @return void
	 */
	public static function ajax_filter_value() {
		$filter_for   = stripslashes( $_POST['filter_for'] );
		$filter_type  = stripslashes( $_POST['filter_type'] );
		$filter_value = stripslashes( $_POST['filter_value'] );
		if ( ! $filter_for || ! $filter_type || ! $filter_value ) {
			$response = array(
				'listClass' => null,
				'options'   => array(
					"<option value=''>" . __( 'Filter for', BF2_DATA['TextDomain'] ) . '</option>',
				),
			);
		} else {
			$instance = new $filter_for();
			$model    = $instance->get_model();
			switch ( $filter_type ) {
				case 'BadgeFactor2\Admin\Lists\Badges':
					$model->all( -1 );
					break;
				case 'BadgeFactor2\Admin\Lists\Issuers':
					break;

			}
			$filter_values = $filter_type::get_instance();
			foreach ( $filter_values as $filter ) {
				$response['options'][] = "<option value='{$filter->entityId}'>{$filter->name}</option>";
			}
			$response['options'] = join( '', $response['options'] );
		}

		wp_send_json( $response );
	}


	/**
	 * Receive Ajax badge request approval request.
	 *
	 * @return void
	 */
	public static function ajax_approve_badge_request() {

		$current_user = wp_get_current_user();
		if ( $current_user > 0 ) {
			$badge_request_id = $_POST['badge_request_id'];

			do_action( 'approve_badge_request', $current_user, $badge_request_id, true );
		}
	}


	/**
	 * Receive Ajax badge request rejection request.
	 *
	 * @return void
	 */
	public static function ajax_reject_badge_request() {

		$current_user = wp_get_current_user();
		if ( $current_user > 0 ) {
			$badge_request_id = $_POST['badge_request_id'];
			$rejection_reason = $_POST['rejection_reason'];

			do_action( 'reject_badge_request', $current_user, $badge_request_id, $rejection_reason, true );
		}
	}

	public static function ajax_cancel_reject_badge_request() {

		$current_user = wp_get_current_user();
		if ( $current_user > 0 ) {
			$badge_request_id = $_POST['badge_request_id'];
			do_action( 'cancel_reject_badge_request', $current_user, $badge_request_id, $rejection_reason, true );
		}
	}


	/**
	 * Receive Ajax badge request revision request.
	 *
	 * @return void
	 */
	public static function ajax_revise_badge_request() {

		$current_user = wp_get_current_user();
		if ( $current_user > 0 ) {
			$badge_request_id = $_POST['badge_request_id'];
			$revision_reason  = $_POST['revision_reason'];

			do_action( 'revise_badge_request', $current_user, $badge_request_id, $revision_reason, true );
		}
	}

	public static function ajax_cancel_revise_badge_request() {

		$current_user = wp_get_current_user();
		if ( $current_user > 0 ) {
			$badge_request_id = $_POST['badge_request_id'];
			$revision_reason  = $_POST['revision_reason'];

			do_action( 'cancel_revise_badge_request', $current_user, $badge_request_id, $revision_reason, true );
		}
	}


	/**
	 * CMB2 Select2 field asset path.
	 *
	 * @return string path to cmb2-field-select2 library
	 */
	public static function pw_cmb2_field_select2_asset_path() {
		return BF2_BASEURL . '/lib/cmb-field-select2';
	}



	/**
	 * Hook called to verify if rewrite rules flush is required.
	 *
	 * @return void
	 */
	public static function hook_flush_rewrite_rules() {
		if ( delete_transient( 'bf2_flush_rewrite_rules' ) ) {
			flush_rewrite_rules();
		}
	}


	/**
	 * Hook called on form_slug field save.
	 *
	 * @param boolean    $updated Updated.
	 * @param string     $action Action.
	 * @param CMB2_Field $instance Field instance.
	 * @return void
	 */
	public static function save_form_slug( bool $updated, string $action, CMB2_Field $instance ) {
		set_transient( 'bf2_flush_rewrite_rules', true );
	}


	/**
	 * Hook called on form_slug field save.
	 *
	 * @param boolean    $updated Updated.
	 * @param string     $action Action.
	 * @param CMB2_Field $instance Field instance.
	 * @return void
	 */
	public static function save_autoevaluation_form_slug( bool $updated, string $action, CMB2_Field $instance ) {
		set_transient( 'bf2_flush_rewrite_rules', true );
	}


	/**
	 * Hook called on badgefactor2_badgr_settings options-page save.
	 *
	 * @param array $option_page Options page.
	 * @return void
	 */
	public static function save_options( $option_page ) {
		if ( 'badgefactor2_badgr_settings' === $option_page ) {
			set_transient( 'bf2_flush_rewrite_rules', true );
		}
	}

	private static function get_form_slug() {
		if ( null === self::$form_slug ) {
			self::$form_slug = get_option( 'badgefactor2' )['bf2_form_slug'];
		}

		return self::$form_slug;
	}

	private static function set_form_slug( $slug ) {
		self::$form_slug = $slug;
	}

	private static function build_revision_email_link( $badge_page ) {
		return get_permalink( $badge_page->ID ) . self::get_form_slug();
	}

	private static function build_rejection_email_link( $badge_page ) {
		return get_permalink( $badge_page->ID );
	}

	private static function build_approved_email_link( $badge_page, $recipient_id ) {
		$site_url       = get_site_url();
		$badge_page_url = get_permalink( $badge_page->ID );
		// TODO: remove dependency on Buddy Press function.
		$user_page_url           = bp_core_get_user_domain( $recipient_id );
		$badge_page_relative_url = substr( $badge_page_url, strlen( $site_url ) + 1 );

		return $user_page_url . $badge_page_relative_url;
	}
}
