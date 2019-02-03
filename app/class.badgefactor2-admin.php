<?php
/*
 * Badge Factor 2
 * Copyright (C) 2019 Digital Pygmalion Inc.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace BadgeFactor2;


class BadgeFactor2_Admin {

	private static $initiated = false;
	private static $notices   = array();

	public static function init() {
		if (!self::$initiated) {
			self::init_hooks();
		}
	}

	public static function init_hooks() {
		add_action('cmb2_admin_init', [BadgeFactor2_Admin::class, 'admin_init']);
		add_action('admin_menu', [BadgeFactor2_Admin::class, 'admin_menu'], 5);
		add_action('admin_notices', [BadgeFactor2_Admin::class, 'display_notice']);
		add_action('admin_enqueue_scripts', [BadgeFactor2_Admin::class, 'load_resources']);
		self::$initiated = true;
	}

	public static function admin_init() {
		load_plugin_textdomain('badgefactor2');
		self::register_settings_metabox();
	}

	private static function register_settings_metabox() {
		/**
		 * Registers main options page menu item and form.
		 */
		$args = [
			'id'           => 'badgefactor2_page',
			'title'        => 'Badge Factor 2',
			'object_types' => ['options-page'],
			'option_key'   => 'badgefactor2',
			'tab_group'    => 'badgefactor2',
			'tab_title'    => __('Settings', 'badgefactor2'),
			'icon_url'     => plugin_dir_url(__FILE__).'../assets/images/badgefactor_icon.png',

		];

		// 'tab_group' property is supported in > 2.4.0.
		if ( version_compare( CMB2_VERSION, '2.4.0' ) ) {
			$args['display_cb'] = 'badgefactor2_options_display_with_tabs';
		}

		new_cmb2_box( $args );

		/**
		 * Registers badge factor 2 options page.
		 */
		$args = [
			'id'           => 'badgefactor2_settings_page',
			'title'        => 'Settings',
			'object_types' => ['options-page'],
			'option_key'   => 'badgefactor2_settings',
			'parent_slug'  => 'badgefactor2',
			'tab_group'    => 'badgefactor2',
			'tab_title'    => __('Settings', 'badgefactor2'),
		];

		// 'tab_group' property is supported in > 2.4.0.
		if ( version_compare( CMB2_VERSION, '2.4.0' ) ) {
			$args['display_cb'] = 'badgefactor2_options_display_with_tabs';
		}

		$settings = new_cmb2_box( $args );

		/**
		 * Options fields ids only need
		 * to be unique within this box.
		 * Prefix is not needed.
		 */
		$settings->add_field( array(
			'name'    => 'Site Background Color',
			'id'      => 'bg_color',
			'type'    => 'colorpicker',
			'default' => '#ffffff',
		) );

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

		$badgr_settings->add_field([
			'name'    => __('Hostname', 'badgefactor2'),
			'desc'    => __('Format: URL:port', 'badgefactor2'),
			'id'      => 'badgr_server_hostname',
			'type'    => 'text_url',
			'default' => 'http://localhost:8000',
			'protocols' => ['http','https'],

		]);

		$badgr_settings->add_field([
			'name'    => __('Client ID', 'badgefactor2'),
			'id'      => 'badgr_server_client_id',
			'type'    => 'text',
		]);

		$badgr_settings->add_field([
			'name'    => __('Client Secret', 'badgefactor2'),
			'id'      => 'badgr_server_client_secret',
			'type'    => 'text',
			'after_row' => function($field_args, $field) {
				include __DIR__ . '/../templates/badgr-server-status.tpl.php';
			}
		]);

		$badgr_settings->add_field([
			'name'    => __('Access Token', 'badgefactor2'),
			'id'      => 'badgr_server_access_token',
			'type'    => 'hidden',
		]);

		$badgr_settings->add_field([
			'name'    => __('Refresh Token', 'badgefactor2'),
			'id'      => 'badgr_server_refresh_token',
			'type'    => 'hidden',

		]);


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
			'tab_title'    => __('Plugins', 'badgefactor2'),
		);

		// 'tab_group' property is supported in > 2.4.0.
		if ( version_compare( CMB2_VERSION, '2.4.0' ) ) {
			$args['display_cb'] = 'badgefactor2_options_display_with_tabs';
		}

		$plugins = new_cmb2_box( $args );

		$plugins->add_field( array(
			'name' => 'Test Text Area for Code',
			'id'   => 'textarea_code',
			'type' => 'textarea_code',
		) );
	}


}