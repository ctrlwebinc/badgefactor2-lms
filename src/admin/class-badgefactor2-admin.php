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

use BadgeFactor2\Admin\Lists\Assertions;
use BadgeFactor2\Admin\Lists\Badges;
use BadgeFactor2\Admin\Lists\Issuers;
use BadgeFactor2\BadgrClient;

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


	/**
	 * Init Hooks.
	 *
	 * @return void
	 */
	public static function init_hooks() {
		add_filter( 'set-screen-option', array( BadgeFactor2_Admin::class, 'set_screen' ), 10, 3 );
		add_action( 'cmb2_admin_init', array( BadgeFactor2_Admin::class, 'admin_init' ) );
		add_action( 'admin_enqueue_scripts', array( BadgeFactor2_Admin::class, 'load_resources' ) );
		add_action( 'admin_menu', array( BadgeFactor2_Admin::class, 'admin_menus' ) );
		add_action( 'wp_ajax_bf2_filter_type', array( BadgeFactor2_Admin::class, 'ajax_filter_type' ) );
		add_action( 'wp_ajax_bf2_filter_value', array( BadgeFactor2_Admin::class, 'ajax_filter_value' ) );
		add_action( 'save_post_badge-page', array( BadgeFactor2_Admin::class, 'create_badge_chain' ), 10, 2 );
		add_filter( 'pw_cmb2_field_select2_asset_path', array( BadgeFactor2_Admin::class, 'pw_cmb2_field_select2_asset_path' ), 10 );
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
		load_plugin_textdomain( BF2_DATA['TextDomain'], false, basename( dirname( __FILE__, 3 ) ) . '/languages/' );
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
			'manage_options',
			$menus[0][1],
			array( BadgeFactor2_Admin::class, $menus[0][1] . '_page' ),
			BF2_BASEURL . 'assets/images/badgr.svg',
		);

		foreach ( $menus as $m ) {
			$hook = add_submenu_page(
				$menus[0][1],
				$m[0],
				$m[0],
				'manage_options',
				$m[1],
				array( BadgeFactor2_Admin::class, $m[1] . '_page' )
			);

			add_action(
				"load-$hook",
				array( BadgeFactor2_Admin::class, $m[1] . '_options' )
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
		wp_enqueue_script( 'badgefactor2-admin-js', BF2_BASEURL . 'assets/js/admin.js', array( 'jquery' ), BF2_DATA['Version'], true );
	}

	/**
	 * Undocumented function.
	 *
	 * @param int     $id Post ID.
	 * @param WP_Post $post Post Object.
	 * @return void
	 */
	public static function create_badge_chain( $id, $post ) {
		// Check if it's the right post type.
		if ( 'badge-page' === $post->post_type ) {

			// Check if it's a published post.
			if ( 'publish' === $post->post_status ) {

			}

			/*
			Commented.
			if ( get_post_meta( $ID, 'badgefactor_form_id', true ) == '' ) {
				if ( $this->check_gravity_forms() ) {
					$form_id = $this->create_badge_submission_form( $post );
					if ( ! is_wp_error( $form_id ) ) {
						update_post_meta( $ID, 'badgefactor_form_id', $form_id );

						if ( get_post_meta( $ID, 'badgefactor_form_page_id', true ) == '' ) {
							$form_page_id = $this->create_badge_form_page( $post->post_title, $form_id );
							if ( ! is_wp_error( $form_page_id ) ) {
								update_post_meta( $ID, 'badgefactor_form_page_id', $form_page_id );
							}
						}
					}
				}
			}

			do_action( 'badgefactor_woocommerce_create_badge', $ID, $post );

			if ( get_post_meta( $ID, 'badgefactor_page_id', true ) == '' ) {
				$page_id = $this->create_course_page( $post, '<a href="' . get_permalink( $form_page_id ) . '">' . __( 'Get this badge', 'badgefactor' ) . '</a>' );
				if ( ! is_wp_error( $page_id ) ) {
					update_post_meta( $ID, 'badgefactor_page_id', $page_id );
				}
				wp_update_post(
					array(
						'ID'          => $form_page_id,
						'post_parent' => $page_id,
					)
				);
			}

			return true;
			*/
		}
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
					'local'    => __( 'Local Badgr', BF2_DATA['TextDomain'] ),
					'badgr_io' => __( 'Badgr.io', BF2_DATA['TextDomain'] ),
					'custom'   => __( 'Custom', BF2_DATA['TextDomain'] ),
				),

			)
		);

		// Badgr server quick select.
		$badgr_settings->add_field(
			array(
				'name'             => __( 'Authorization type', BF2_DATA['TextDomain'] ),
				'desc'             => __( 'Choose how to exchange credentials with Badgr', BF2_DATA['TextDomain'] ),
				'id'               => 'badgr_authentication_process_select',
				'type'             => 'radio',
				'show_option_none' => false,
				'default'          => BadgrClient::GRANT_CODE,
				'options'          => array(
					BadgrClient::GRANT_PASSWORD => __( 'Use passwords', BF2_DATA['TextDomain'] ),
					BadgrClient::GRANT_CODE     => __( 'Redirect to server', BF2_DATA['TextDomain'] ),
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
				'name'      => __( 'Client Secret', BF2_DATA['TextDomain'] ),
				'id'        => 'badgr_server_client_secret',
				'type'      => 'text',
				'after_row' => function ( $field_args, $field ) {
					include BF2_ABSPATH . 'templates/admin/tpl.badgr-server-status.php';
					include BF2_ABSPATH . 'templates/admin/tpl.badgr-server-link-admin.php';
				},
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

	}


	/**
	 * Ajax filter type.
	 *
	 * @return void
	 */
	public static function ajax_filter_type() {
		header( 'Content-Type: application/json' );
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

		echo json_encode( $response );
		wp_die();
	}


	/**
	 * Ajax filter value.
	 *
	 * @return void
	 */
	public static function ajax_filter_value() {
		header( 'Content-Type: application/json' );
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

		echo json_encode( $response );
		wp_die();
	}

	/**
	 * CMB2 Select2 field asset path.
	 *
	 * @return string path to cmb2-field-select2 library
	 */
	public static function pw_cmb2_field_select2_asset_path() {
		return BF2_BASEURL . '/lib/cmb-field-select2';
	}
}
