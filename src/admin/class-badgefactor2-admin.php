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

namespace BadgeFactor2;

use BadgeFactor2\Admin\Lists\Assertions;
use BadgeFactor2\Admin\Lists\Badges;
use BadgeFactor2\Admin\Lists\Issuers;
use BadgeFactor2\Admin\Lists\Issuers_List;
use BadgeFactor2\BadgrClient;

/**
 * Badge Factor 2 Admin Class.
 */
class BadgeFactor2_Admin {

	public static $issuers;
	public static $badges;
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
		add_action( 'init', array( BadgeFactor2_Admin::class, 'add_custom_roles_and_capabilities' ), 11 );
		add_action( 'admin_menu', array( BadgeFactor2_Admin::class, 'admin_menus' ) );
		add_action( 'wp_ajax_bf2_filter_type', array( BadgeFactor2_Admin::class, 'ajax_filter_type' ) );
		add_action( 'wp_ajax_bf2_filter_value', array( BadgeFactor2_Admin::class, 'ajax_filter_value' ) );
	}


	public static function set_screen( $status, $option, $value ) {
		return $value;
	}

	/**
	 * CMB2 Admin Init hook.
	 *
	 * @return void
	 */
	public static function admin_init() {
		load_plugin_textdomain( 'badgefactor2' );
		self::register_settings_metabox();
	}

	/**
	 * Adds custom roles and capabilities requires by Badge Factor 2.
	 */
	public static function add_custom_roles_and_capabilities() {
		$approver = add_role(
			'approver',
			__( 'Approver' ),
			array(
				'read'                 => true,
				'edit_posts'           => true,
				'edit_published_posts' => true,
				// FIXME List must be validated at a later development stage.
			)
		);

		if ( null !== $approver ) {
			$approver->add_cap( 'badgefactor2_approve_badge_requests' );
		}

	}

	public static function admin_menus() {

		$menus = array(
			array(
				__( 'Issuers', 'badgefactor2' ),
				'issuers',
				'dashicons-admin-home',
			),
			array(
				__( 'Badges', 'badgefactor2' ),
				'badges',
				'dashicons-star-empty',
			),
			array(
				__( 'Assertions', 'badgefactor2' ),
				'assertions',
				'dashicons-star-filled',
			),
		);
		foreach ( $menus as $menu ) {
			$hook = add_menu_page(
				$menu[0],
				$menu[0],
				'manage_options',
				$menu[1],
				array( BadgeFactor2_Admin::class, $menu[1] . '_page' ),
				$menu[2]
			);

			add_action(
				"load-$hook",
				array( BadgeFactor2_Admin::class, $menu[1] . '_options' )
			);
		}

	}

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

	public static function issuers_options() {
		$option = 'per_page';
		$args   = array(
			'label'   => __( 'Issuers', 'badgefactor2' ),
			'default' => 10,
			'option'  => 'issuers_per_page',
		);

		add_screen_option( $option, $args );

		self::$issuers = new Issuers();
	}

	public static function badges_options() {
		$option = 'per_page';
		$args   = array(
			'label'   => __( 'Badges', 'badgefactor2' ),
			'default' => 10,
			'option'  => 'badges_per_page',
		);

		add_screen_option( $option, $args );

		self::$badges = new Badges();
	}

	public static function assertions_options() {
		$option = 'per_page';
		$args   = array(
			'label'   => __( 'Assertions', 'badgefactor2' ),
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
		wp_enqueue_script( 'badgefactor2-admin', BF2_BASEURL . 'assets/js/admin.js', array( 'jquery' ), '1.0.0', true );

	}

	/**
	 * Registers Settings Metabox.
	 *
	 * @return void
	 */
	private static function register_settings_metabox() {
		$args = array(
			'id'           => 'badgefactor2_settings',
			'menu_title'   => 'Badge Factor 2',
			'object_types' => array( 'options-page' ),
			'option_key'   => 'badgefactor2',
			'icon_url'     => BF2_BASEURL . ( 'assets/images/badgefactor2_logo.svg' ),
			'tab_group'    => 'badgefactor2',
			'tab_title'    => __( 'Settings', 'badgefactor2' ),

		);

		// 'tab_group' property is supported in > 2.4.0.
		if ( version_compare( CMB2_VERSION, '2.4.0' ) ) {
			$args['display_cb'] = 'badgefactor2_options_display_with_tabs';
		}
		$badgefactor2_settings = new_cmb2_box( $args );

		/**
		 * Registers Badgr options page.
		 */
		$args = array(
			'id'           => 'badgefactor2_badgr_settings_page',
			'menu_title'   => 'Badgr Server', // Use menu title, & not title to hide main h2.
			'object_types' => array( 'options-page' ),
			'option_key'   => 'badgefactor2_badgr_settings',
			'parent_slug'  => 'badgefactor2',
			'tab_group'    => 'badgefactor2',
			'tab_title'    => 'Badgr Settings',
		);

		// 'tab_group' property is supported in > 2.4.0.
		if ( version_compare( CMB2_VERSION, '2.4.0' ) ) {
			$args['display_cb'] = 'badgefactor2_options_display_with_tabs';
		}

		$badgr_settings = new_cmb2_box( $args );

		// Badgr server quick select
		$badgr_settings->add_field(
			array(
				'name'             => __( 'Badgr server', 'badgefactor2' ),
				'desc'             => __( 'Choose the type of Badgr server you\'re using', 'badgefactor2' ),
				'id'               => 'badgr_server_quick_select',
				'type'             => 'radio',
				'show_option_none' => false,
				'default'          => 'local',
				'options'          => array(
					'local'                => __( 'Local Badgr', 'badgefactor2' ),
					'badgr_io'             => __( 'Badgr.io', 'badgefactor2' ),
					'custom'               => __( 'Custom', 'badgefactor2' ),
				),

			)
		);

		// Badgr server quick select
		$badgr_settings->add_field(
			array(
				'name'             => __( 'Authorization type', 'badgefactor2' ),
				'desc'             => __( 'Choose how to exchange credentials with Badgr', 'badgefactor2' ),
				'id'               => 'badgr_authentication_process_select',
				'type'             => 'radio',
				'show_option_none' => false,
				'default'   => BadgrClient::GRANT_CODE,
				'options'          => array(
					BadgrClient::GRANT_PASSWORD => __( 'Use passwords', 'badgefactor2' ),
					BadgrClient::GRANT_CODE => __( 'Redirect to server', 'badgefactor2' ),
				),
			)
		);

		// Public url to use with custom server setting
		$badgr_settings->add_field(
			array(
				'name'      => __( 'Public URL', 'badgefactor2' ),
				'desc'      => __( 'Format: scheme://URL:port', 'badgefactor2' ),
				'id'        => 'badgr_server_public_url',
				'type'      => 'text_url',
				'default'   => 'http://localhost:8000',
				'protocols' => array( 'http', 'https' ),

			)
		);

		$badgr_settings->add_field(
			array(
				'name'      => __( 'Internal URL', 'badgefactor2' ),
				'desc'      => __( 'Format: scheme://URL:port', 'badgefactor2' ),
				'id'        => 'badgr_server_internal_url',
				'type'      => 'text_url',
				'default'   => '',
				'protocols' => array( 'http', 'https' ),

			)
		);

		$badgr_settings->add_field(
			array(
				'name' => __( 'Client ID', 'badgefactor2' ),
				'id'   => 'badgr_server_client_id',
				'type' => 'text',
			)
		);

		$badgr_settings->add_field(
			array(
				'name'      => __( 'Client Secret', 'badgefactor2' ),
				'id'        => 'badgr_server_client_secret',
				'type'      => 'text',
				'after_row' => function ( $field_args, $field ) {
					include BF2_ABSPATH . 'templates/admin/tpl.badgr-server-status.php';
				},
			)
		);

		/**
		 * Registers badge factor 2 plugins options page.
		 */
		$args = array(
			'id'           => 'badgefactor2_plugins_page',
			'menu_title'   => 'Plugins', // Use menu title, & not title to hide main h2.
			'object_types' => array( 'options-page' ),
			'option_key'   => 'badgefactor2_plugins',
			'parent_slug'  => 'badgefactor2',
			'tab_group'    => 'badgefactor2',
			'tab_title'    => __( 'Plugins', 'badgefactor2' ),
		);

		// 'tab_group' property is supported in > 2.4.0.
		if ( version_compare( CMB2_VERSION, '2.4.0' ) ) {
			$args['display_cb'] = 'badgefactor2_options_display_with_tabs';
		}

		$plugins = new_cmb2_box( $args );

	}

	public static function ajax_filter_type() {
		header( 'Content-Type: application/json' );
		$filter_type = stripslashes( $_POST['filter_type'] );
		if ( ! $filter_type ) {
			$response = array(
				'listClass' => null,
				'options'   => array(
					"<option value=''>" . __( 'Filter for' ) . '</option>',
				),
			);
		} else {
			$filter_values = $filter_type::get_instance()->all();
			$response      = array(
				'listClass' => array_values( $filter_values )[0]->listClass,
				'options'   => array(
					"<option value=''>" . __( 'Filter for' ) . '</option>',
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

	public static function ajax_filter_value() {
		header( 'Content-Type: application/json' );
		$filter_for   = stripslashes( $_POST['filter_for'] );
		$filter_type  = stripslashes( $_POST['filter_type'] );
		$filter_value = stripslashes( $_POST['filter_value'] );
		if ( ! $filter_for || ! $filter_type || ! $filter_value ) {
			$response = array(
				'listClass' => null,
				'options'   => array(
					"<option value=''>" . __( 'Filter for' ) . '</option>',
				),
			);
		} else {
			$instance = new $filter_for();
			$model    = $instance->get_model();
			switch ( $filter_type ) {
				case 'BadgeFactor2\Admin\Lists\Badges':
					$model->all();
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

}
