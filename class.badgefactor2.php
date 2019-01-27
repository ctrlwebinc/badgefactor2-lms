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

class BadgeFactor2 {
	/**
	 * Badge Factor Version
	 *
	 * @var string
	 */
	public static $version = '2.0.0-alpha';

	/**
	 * The plugin's required WordPress version
	 *
	 * @var string
	 *
	 * @since 2.0.0-alpha
	 */
	public static $required_wp_version = '4.9.9';

	private static $initiated = false;

	public static function init() {
		if ( ! self::$initiated ) {
			self::badgr_keep_token_fresh();
			self::init_hooks();
		}
	}

	private static function init_hooks() {
		self::$initiated = true;
		$badgr_settings = get_option('badgefactor2_badgr_settings');


		/*
		$client = new \GuzzleHttp\Client();
		$response = $client->request('GET', 'http://localhost:8000/v2/users/KgxHLq0iTPSbL7kZV1uQBQ', [
			'headers' => [
				'Authorization' => 'Bearer '. $badgr_settings['badgr_server_access_token']
			]
		]);
		echo $response->getBody();
		die;
		*/


	}

	private static function badgr_keep_token_fresh() {

		$badgr_settings = get_option('badgefactor2_badgr_settings');
		if (isset($badgr_settings) && isset($badgr_settings['badgr_server_access_token']) &&
		    (!isset($badgr_settings['badgr_server_token_expiration']) ||
		    $badgr_settings['badgr_server_token_expiration'] <= time())) {

			$provider = new \League\OAuth2\Client\Provider\GenericProvider([
				'clientId'                => $badgr_settings['badgr_server_client_id'],
				'clientSecret'            => $badgr_settings['badgr_server_client_secret'],
				'redirectUri'             => 'http://bf2.test/wp-admin',
				'urlAuthorize'            => $badgr_settings['badgr_server_hostname'].'/o/authorize',
				'urlAccessToken'          => $badgr_settings['badgr_server_hostname'].'/o/token',
				'urlResourceOwnerDetails' => $badgr_settings['badgr_server_hostname'].'/o/resource',
				'scopes'                  => 'rw:profile rw:issuer rw:backpack',
			]);


			$accessToken = $provider->getAccessToken('refresh_token', [
				'refresh_token' => $badgr_settings['badgr_server_refresh_token']
			]);

			cmb2_update_option('badgefactor2_badgr_settings', 'badgr_server_access_token',
				$accessToken->getToken());

			cmb2_update_option('badgefactor2_badgr_settings', 'badgr_server_refresh_token',
				$accessToken->getRefreshToken());

			cmb2_update_option('badgefactor2_badgr_settings', 'badgr_server_token_expiration',
				$accessToken->getExpires());
		}
	}






}