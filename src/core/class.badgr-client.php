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

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use http\Exception\BadMethodCallException;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\GenericProvider;

class BadgrClient {


	private static $initialized = false;

	private static $badgr_settings;

	public static function init_hooks() {
		if ( ! self::$initialized ) {
			add_action( 'init', array( BadgrClient::class, 'init' ), 9966 );
			self::$initialized = self::is_active();
		}
	}

	public static function is_active() {

		if ( ! self::is_configured() ) {
			$is_active = false;
		} else {
			$client = new Client();
			try {
				$response = $client->request( 'GET', self::getInternalOrExernalServerUrl() );
				if ( ! self::is_initialized() ) {
					$is_active = self::authenticate();
				} else {
					$is_active = self::refresh_token();
				}
			} catch ( ConnectException $e ) {

				$is_active = false;

			} catch ( GuzzleException $e ) {

				$is_active = false;
			}
		}

		return $is_active;
	}

	public static function get_status() {
		return ( self::is_active() ? __( 'Active', 'badgefactor2' ) : __( 'Inactive', 'badgefactor2' ) );
	}

	private static function is_configured() {
		return isset( self::badgr_settings()['badgr_server_client_id'] ) &&
		isset( self::badgr_settings()['badgr_server_client_secret'] ) &&
		isset( self::badgr_settings()['badgr_server_public_url'] );
	}

	private static function is_initialized() {
		return isset( self::badgr_settings()['badgr_server_access_token'] ) &&
		isset( self::badgr_settings()['badgr_server_refresh_token'] ) &&
		isset( self::badgr_settings()['badgr_server_token_expiration'] ) &&
		self::badgr_settings()['badgr_server_token_expiration'] >= time();
	}

	public static function init() {

	}

	private static function getInternalOrExernalServerUrl() {
		$bagrSettings = self::badgr_settings();
		if (isset($bagrSettings['badgr_server_internal_url']) && $bagrSettings['badgr_server_internal_url'] != '')
			return $bagrSettings['badgr_server_internal_url'];
		else
			return $bagrSettings['badgr_server_public_url'];
	}

	private static function badgr_settings() {
		if ( ! self::$badgr_settings ) {
			self::$badgr_settings = get_option( 'badgefactor2_badgr_settings' );
		}

		return self::$badgr_settings;
	}

	private static function get_access_token() {
		if ( self::is_active() ) {
			return self::badgr_settings()['badgr_server_access_token'];
		}

		return null;
	}

	private static function makeProvider() {
		return new GenericProvider(
			array(
				'clientId'                => self::badgr_settings()['badgr_server_client_id'],
				'clientSecret'            => self::badgr_settings()['badgr_server_client_secret'],
				'redirectUri'             => site_url('/wp-admin/admin.php?page=badgefactor2_badgr_settings'),
				'urlAuthorize'            => self::badgr_settings()['badgr_server_public_url'] . '/o/authorize',
				'urlAccessToken'          => self::getInternalOrExernalServerUrl() . '/o/token',
				'urlResourceOwnerDetails' => self::getInternalOrExernalServerUrl() . '/o/resource',
				'scopes'                  => 'rw:profile rw:issuer rw:backpack rw:serverAdmin ',
			)
		);
	}

	private static function authenticate() {

		$provider = self::makeProvider();

		// If we don't have an authorization code then get one
		if ( ! isset( $_GET['code'] ) ) {

			// Fetch the authorization URL from the provider; this returns the
			// urlAuthorize option and generates and applies any necessary parameters
			// (e.g. state).
			$authorizationUrl = $provider->getAuthorizationUrl();

			// Get the state generated for you and store it to the session.
			$_SESSION['oauth2state'] = $provider->getState();
			header( 'Location: ' . $authorizationUrl );
			exit;

			// Check given state against previously stored one to mitigate CSRF attack
		} elseif ( empty( $_GET['state'] ) ||
			( isset( $_SESSION['oauth2state'] ) && $_GET['state'] !== $_SESSION['oauth2state'] ) ) {

			if ( isset( $_SESSION['oauth2state'] ) ) {
				unset( $_SESSION['oauth2state'] );
			}

			return false;

		} else {

			try {

				// Try to get an access token using the authorization code grant.
				$accessToken = $provider->getAccessToken(
					'authorization_code',
					array(
						'code' => $_GET['code'],
					)
				);

				self::$badgr_settings['badgr_server_access_token']     = $accessToken->getToken();
				self::$badgr_settings['badgr_server_refresh_token']    = $accessToken->getRefreshToken();
				self::$badgr_settings['badgr_server_token_expiration'] = $accessToken->getExpires();
				update_option( 'badgefactor2_badgr_settings', self::$badgr_settings );

			} catch ( IdentityProviderException $e ) {

				return false;

			}

			return true;
		}
	}

	private static function refresh_token() {

		if ( isset( self::$badgr_settings ) && isset( self::$badgr_settings['badgr_server_access_token'] ) &&
			( ! isset( self::$badgr_settings['badgr_server_token_expiration'] ) ||
				self::$badgr_settings['badgr_server_token_expiration'] <= time() ) ) {

			$provider = self::makeProvider();

			try {
				$accessToken = $provider->getAccessToken(
					'refresh_token',
					array(
						'refresh_token' => self::$badgr_settings['badgr_server_refresh_token'],
					)
				);

				self::$badgr_settings['badgr_server_access_token']     = $accessToken->getToken();
				self::$badgr_settings['badgr_server_refresh_token']    = $accessToken->getRefreshToken();
				self::$badgr_settings['badgr_server_token_expiration'] = $accessToken->getExpires();
				update_option( 'badgefactor2_badgr_settings', self::$badgr_settings );

			} catch ( IdentityProviderException $e ) {

				return self::authenticate();

			} catch ( ConnectException $e ) {

				return false;

			}
		}

		return true;
	}

	private static function request( $method, $path, $args = null ) {
		$client = new Client();
		$method = strtoupper( $method );
		if ( ! in_array( $method, array( 'GET', 'PUT', 'POST', 'DELETE' ) ) ) {
			throw new BadMethodCallException( 'Method not supported' );
		}

		if ( $args ) {
			switch ( $method ) {
				case 'GET':
					$args = array( 'query' => $args );
					break;
				case 'POST':
					$args = array( 'json' => $args );
					break;
				case 'PUT':
					$args = array( 'json' => $args );
					break;
				case 'DELETE':
					$args = null;
			}
		}
		$args = array_merge(
			$args,
			array(
				'headers' => array(
					'Authorization' => 'Bearer ' . self::get_access_token(),
					'Accept'        => 'application/json',
				),
			)
		);
		try {
			$response = $client->request( $method, self::getInternalOrExernalServerUrl() . $path, $args );

			return $response;

		} catch ( ConnectException $e ) {

			return null;

		} catch ( GuzzleException $e ) {

			return null;
		}
	}

	public static function post( $path, $body ) {
		return self::request( 'POST', $path, $body );
	}

	public static function put( $path, $body ) {
		return self::request( 'PUT', $path, $body );
	}

	public static function get( $path, $queries = [] ) {
		return self::request( 'GET', $path, $queries );
	}

	public static function delete( $path ) {
		return self::request( 'DELETE', $path );
	}
}
