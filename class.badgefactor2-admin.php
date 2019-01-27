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
	}

	public static function admin_init() {
		load_plugin_textdomain('badgefactor2');
		self::register_settings_metabox();

		$badgr_options = get_option('badgefactor2_badgr_settings');
		if (isset($badgr_options['badgr_server_client_id']) &&
			!isset($badgr_options['badgr_server_access_token'])) {
			self::badgr_authenticate();
		}
	}

	private static function register_settings_metabox() {
		/**
		 * Registers main options page menu item and form.
		 */
		$args = [
			'id'           => 'badgefactor2_settings_page',
			'title'        => 'Badge Factor 2',
			'object_types' => ['options-page'],
			'option_key'   => 'badgefactor2_settings',
			'tab_group'    => 'badgefactor2_settings',
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
			'desc'    => 'field description (optional)',
			'id'      => 'bg_color',
			'type'    => 'colorpicker',
			'default' => '#ffffff',
		) );

		/**
		 * Registers secondary options page, and set main item as parent.
		 */
		$args = array(
			'id'           => 'badgefactor2_badgr_settings_page',
			'menu_title'   => 'Badgr Server', // Use menu title, & not title to hide main h2.
			'object_types' => array( 'options-page' ),
			'option_key'   => 'badgefactor2_badgr_settings',
			'parent_slug'  => 'badgefactor2_settings',
			'tab_group'    => 'badgefactor2_settings',
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
		 * Registers tertiary options page, and set main item as parent.
		 */
		$args = array(
			'id'           => 'badgefactor2_plugins_page',
			'menu_title'   => 'Plugins', // Use menu title, & not title to hide main h2.
			'object_types' => array( 'options-page' ),
			'option_key'   => 'badgefactor2_plugins',
			'parent_slug'  => 'badgefactor2_settings',
			'tab_group'    => 'badgefactor2_settings',
			'tab_title'    => __('Plugins', 'badgefactor2'),
		);

		// 'tab_group' property is supported in > 2.4.0.
		if ( version_compare( CMB2_VERSION, '2.4.0' ) ) {
			$args['display_cb'] = 'badgefactor2_options_display_with_tabs';
		}

		$plugins = new_cmb2_box( $args );

		$plugins->add_field( array(
			'name' => 'Test Text Area for Code',
			'desc' => 'field description (optional)',
			'id'   => 'textarea_code',
			'type' => 'textarea_code',
		) );
	}

	private static function badgr_authenticate() {

		$badgr_settings = get_option('badgefactor2_badgr_settings');
		$provider = new \League\OAuth2\Client\Provider\GenericProvider([
			'clientId'                => $badgr_settings['badgr_server_client_id'],
			'clientSecret'            => $badgr_settings['badgr_server_client_secret'],
			'redirectUri'             => 'http://bf2.test/wp-admin',
			'urlAuthorize'            => $badgr_settings['badgr_server_hostname'].'/o/authorize',
			'urlAccessToken'          => $badgr_settings['badgr_server_hostname'].'/o/token',
			'urlResourceOwnerDetails' => $badgr_settings['badgr_server_hostname'].'/o/resource',
			'scopes'                  => 'rw:profile rw:issuer rw:backpack',
		]);

		// If we don't have an authorization code then get one
		if (!isset($_GET['code'])) {

			// Fetch the authorization URL from the provider; this returns the
			// urlAuthorize option and generates and applies any necessary parameters
			// (e.g. state).
			$authorizationUrl = $provider->getAuthorizationUrl();

			// Get the state generated for you and store it to the session.
			$_SESSION['oauth2state'] = $provider->getState();

			header('Location: '.$authorizationUrl);
			exit;

		// Check given state against previously stored one to mitigate CSRF attack
		} elseif (empty($_GET['state']) ||
		         (isset($_SESSION['oauth2state']) && $_GET['state'] !== $_SESSION['oauth2state'])) {

			if (isset($_SESSION['oauth2state'])) {
				unset($_SESSION['oauth2state']);
			}

			exit('Invalid state');

		} else {

			try {

				// Try to get an access token using the authorization code grant.
				$accessToken = $provider->getAccessToken('authorization_code', [
					'code' => $_GET['code']
				]);

				cmb2_update_option('badgefactor2_badgr_settings', 'badgr_server_access_token',
					$accessToken->getToken());

				cmb2_update_option('badgefactor2_badgr_settings', 'badgr_server_refresh_token',
					$accessToken->getRefreshToken());

				cmb2_update_option('badgefactor2_badgr_settings', 'badgr_server_token_expiration',
					$accessToken->getExpires());

			} catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {

				// Failed to get the access token or user details.
				exit($e->getMessage());

			}

		}
	}


}