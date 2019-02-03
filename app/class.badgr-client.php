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


use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\GenericProvider;

class BadgrClient {

	private static $initiated = false;

	public static function init() {
		if (!self::$initiated) {
			self::init_hooks();
		}
	}

	public static function init_hooks() {
		self::$initiated = self::is_active();
	}

	public static function is_active() {
		$badgr_options = get_option('badgefactor2_badgr_settings');

		if (!isset($badgr_options['badgr_server_client_id']) ||
		    !isset($badgr_options['badgr_server_client_secret']) ||
		    !isset($badgr_options['badgr_server_hostname'])
		) {
			$is_active = false;
		}
		else {
			$client = new \GuzzleHttp\Client();
			try {
				$response = $client->request('GET', $badgr_options['badgr_server_hostname']);
				if (!isset($badgr_options['badgr_server_access_token']) ||
				    !isset($badgr_options['badgr_server_refresh_token']) ||
				    !isset($badgr_options['badgr_server_token_expiration'])
				) {
					$is_active = self::badgr_authenticate();
				} else {
					$is_active = self::badgr_keep_token_fresh();
				}
			} catch ( ConnectException $e ) {

				$is_active = false;

			}
		}

		return $is_active;
	}

	public static function get_status() {
		return (self::is_active() ? __('Active', 'badgefactor2') : __('Inactive', 'badgefactor2'));
	}

	private static function badgr_authenticate() {

		$badgr_settings = get_option('badgefactor2_badgr_settings');
		$provider = new GenericProvider([
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

			return false;

		} else {

			try {

				// Try to get an access token using the authorization code grant.
				$accessToken = $provider->getAccessToken('authorization_code', [
					'code' => $_GET['code']
				]);

				$badgefactor2_badgr_settings = get_option('badgefactor2_badgr_settings');
				$badgefactor2_badgr_settings['badgr_server_access_token'] = $accessToken->getToken();
				$badgefactor2_badgr_settings['badgr_server_refresh_token'] = $accessToken->getRefreshToken();
				$badgefactor2_badgr_settings['badgr_server_token_expiration'] = $accessToken->getExpires();
				update_option('badgefactor2_badgr_settings', $badgefactor2_badgr_settings);

			} catch (IdentityProviderException $e) {

				return false;

			}
			return true;
		}
	}

	private static function badgr_keep_token_fresh() {

		$badgr_settings = get_option('badgefactor2_badgr_settings');
		if (isset($badgr_settings) && isset($badgr_settings['badgr_server_access_token']) &&
		    (!isset($badgr_settings['badgr_server_token_expiration']) ||
		     $badgr_settings['badgr_server_token_expiration'] <= time())) {


			$provider = new GenericProvider([
				'clientId'                => $badgr_settings['badgr_server_client_id'],
				'clientSecret'            => $badgr_settings['badgr_server_client_secret'],
				'redirectUri'             => 'http://bf2.test/wp-admin',
				'urlAuthorize'            => $badgr_settings['badgr_server_hostname'].'/o/authorize',
				'urlAccessToken'          => $badgr_settings['badgr_server_hostname'].'/o/token',
				'urlResourceOwnerDetails' => $badgr_settings['badgr_server_hostname'].'/o/resource',
				'scopes'                  => 'rw:profile rw:issuer rw:backpack',
			]);

			try {
				$accessToken = $provider->getAccessToken( 'refresh_token', [
					'refresh_token' => $badgr_settings['badgr_server_refresh_token']
				] );

				$badgefactor2_badgr_settings = get_option('badgefactor2_badgr_settings');
				$badgefactor2_badgr_settings['badgr_server_access_token'] = $accessToken->getToken();
				$badgefactor2_badgr_settings['badgr_server_refresh_token'] = $accessToken->getRefreshToken();
				$badgefactor2_badgr_settings['badgr_server_token_expiration'] = $accessToken->getExpires();
				update_option('badgefactor2_badgr_settings', $badgefactor2_badgr_settings);

			} catch ( IdentityProviderException $e ) {

				return self::badgr_authenticate();

			} catch ( ConnectException $e ) {

				return false;

			}


		}
		return true;
	}
}