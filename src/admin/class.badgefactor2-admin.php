<?php
/*
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
 */

/**
 * @package Badge_Factor_2
 */

namespace BadgeFactor2;

class BadgeFactor2_Admin {


	public static function init_hooks() {
		add_action( 'cmb2_admin_init', array( BadgeFactor2_Admin::class, 'admin_init' ) );
		add_action( 'admin_enqueue_scripts', array( BadgeFactor2_Admin::class, 'load_resources' ) );
	}

	public static function admin_init() {
		load_plugin_textdomain( 'badgefactor2' );
		self::register_settings_metabox();
	}

	public static function load_resources() {

	}

	private static function register_settings_metabox() {
		$args = array(
			'id'           => 'badgefactor2_settings',
			'menu_title'   => 'Badge Factor 2',
			'object_types' => array( 'options-page' ),
			'option_key'   => 'badgefactor2',
			'icon_url'     => BF2_BASEURL . ( 'assets/images/badgefactor_icon.png' ),
			'tab_group'    => 'badgefactor2',
			'tab_title'    => __( 'Settings', 'badgefactor2' ),

		);

		// 'tab_group' property is supported in > 2.4.0.
		if ( version_compare( CMB2_VERSION, '2.4.0' ) ) {
			$args['display_cb'] = 'badgefactor2_options_display_with_tabs';
		}
		new_cmb2_box( $args );

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

		$badgr_settings->add_field(
			array(
				'name'      => __( 'Hostname', 'badgefactor2' ),
				'desc'      => __( 'Format: URL:port', 'badgefactor2' ),
				'id'        => 'badgr_server_hostname',
				'type'      => 'text_url',
				'default'   => 'http://localhost:8000',
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
					include BF2_ABSPATH . 'src/admin/tpl.badgr-server-status.php';
				},
			)
		);

		$badgr_settings->add_field(
			array(
				'name' => __( 'Access Token', 'badgefactor2' ),
				'id'   => 'badgr_server_access_token',
				'type' => 'hidden',
			)
		);

		$badgr_settings->add_field(
			array(
				'name' => __( 'Refresh Token', 'badgefactor2' ),
				'id'   => 'badgr_server_refresh_token',
				'type' => 'hidden',

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

		$plugins->add_field(
			array(
				'name' => 'Test Text Area for Code',
				'id'   => 'textarea_code',
				'type' => 'textarea_code',
			)
		);
	}

}
