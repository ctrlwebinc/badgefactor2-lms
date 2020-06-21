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

use GuzzleHttp\Client;
use BadgeFactor2\BadgrUser;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\GenericProvider;

/**
 * BadgrClient Class.
 */
class BadgrClient {

	const FLAVOR_BADGRIO_01         = 1;
	const FLAVOR_LOCAL_R_JAMIROQUAI = 2;
	const FLAVOR_CLOUD_v1           = 3;

	const BADGR_IO_URL                    = 'https://api.badgr.io';
	const DEFAULT_LOCAL_BADGR_SERVER_PORT = 8000;

	// Password sources
	const PASSWORD_SOURCE_CUSTOM           = 1;
	const PASSWORD_SOURCE_USE_WP_PASSWORD  = 2;
	const PASSWORD_SOURCE_ALWAYS_ASK       = 3;
	const PASSWORD_SOURCE_ASK_AND_REMEMBER = 4;

	// Grant types
	const GRANT_PASSWORD = 1;
	const GRANT_CODE     = 2;

	// Class properties
	private static $guzzleClient            = null;
	public static $authRedirectUri          = '/bf2/auth';
	public static $user_meta_key_for_client = 'badgr_client_instance';

	// BagrUser
	// Badgr user associated with client instance
	public $badgr_user = null;

	// Minimal properties of instances
	private $username                = null;
	private $as_admin                = false;
	private $badgr_server_public_url = null;
	private $badgr_server_flavor     = null;

	// Additional instance properties
	private $badgr_server_internal_url = null;

	private $scopes; // Scopes applicable to token

	private $badgr_password = null;

	private $auth_type = null;

	private $client_id     = null; // Client used for admin access will be different than password grant client
	private $client_secret = null;

	private $access_token      = null;
	private $refresh_token     = null;
	private $token_expiration  = null;
	private $resource_owner_id = null;

	private $needsConfiguration = true;
	private $needsAuth          = true; // Needs auth is true whenever token is expired or if we get a 401 status during a call

	const STATE_NEW_AND_UNCONFIGURED                      = 1;
	const STATE_CONFIGURED                                = 2;
	const STATE_CONFIGURED_AND_ACTIVE                     = 3;
	const STATE_NEEDS_REFRESH                             = 4;
	const STATE_NEEDS_TOKEN                               = 5;
	const STATE_NEEDS_AUTH                                = 6;
	const STATE_NEEDS_LOGIN                               = 7;
	const STATE_NEEDS_USER_ACTION                         = 8;
	const STATE_NEEDS_ADMIN_ACTION                        = 9;
	const STATE_EXPECTING_AUTHORIZATION_CODE              = 10;
	const STATE_EXPECTING_ACCESS_TOKEN_FROM_CODE          = 11;
	const STATE_HAVE_ACCESS_TOKEN                         = 12;
	const STATE_FAILED_GETTING_ACCESS_TOKEN               = 13;
	const STATE_EXPECTING_ACCESS_TOKEN_FROM_PASSWORD      = 14;
	const STATE_EXPECTING_ACCESS_TOKEN_FROM_REFRESH_TOKEN = 15;

	private $state = self::STATE_NEW_AND_UNCONFIGURED;
	public $retryAuthBeforeFailing = true;

	//public $client_key  = null;
	//public $client_hash = null;

	private $lastMessageFromBadgrServer = null;

	public static function makeInstance( array $parameters ) {
		// Check that basic parameters are present
		$key_parameters = array(
			'username',
			'as_admin',
			'badgr_server_public_url',
			'badgr_server_flavor',
		);

		foreach ( $key_parameters as $key_parameter ) {
			if ( ! array_key_exists( $key_parameter, $parameters ) ) {
				throw new \BadMethodCallException( 'Missing ' . $key_parameter . ' parameter.' );
			}
		}

		// TODO: perform checks on types and values of key parameters

		$client                          = new self();
		$client->username                = $parameters['username'];
		$client->as_admin                = $parameters['as_admin'];
		$client->badgr_server_public_url = $parameters['badgr_server_public_url'];
		$client->badgr_server_flavor     = $parameters['badgr_server_flavor'];

		// TODO: check validity of optionnal parameters

		// TODO: save optionnal parameters in new instance

		$optionnalParameters = array(
			'badgr_server_internal_url',
			'scopes',
			'badgr_password',
			'client_id',
			'client_secret',
			'authorization_code',
			'access_token',
			'refresh_token',
			'token_expiration',
			'badgr_profile',
			'auth_type',
		);

		foreach ( $optionnalParameters as $optionnalParameter ) {
			if ( isset( $parameters[ $optionnalParameter ] ) ) {
				$client->{$optionnalParameter} = $parameters[ $optionnalParameter ];
			}
		}

		// If scopes not already set, set to default value
		if ( null === $client->scopes ) {
			$scopes = 'rw:profile rw:backpack';
			if ( $client->as_admin == true ) {
				$scopes .= ' rw:issuer';
				if ( $client->badgr_server_flavor == self::FLAVOR_LOCAL_R_JAMIROQUAI ) {
					$scopes .= ' rw:serverAdmin';
				}
			}

			$client->scopes = $scopes;
		}

		$client->state = self::STATE_CONFIGURED;

		// set BadgrUser if available (also saves instance)
		if ( isset( $parameters['badgr_user'] ) && null !== $parameters['badgr_user'] ) {
			$parameters['badgr_user']->set_client( $client );
		}

		return ( $client );

	}

	public static function makeClientFromSavedOptions() {
		// Make a client from the previous method of using options

		$options = get_option( 'badgefactor2_badgr_settings' );

		$clientParameters = array(
			'username'                  => getenv( 'BADGR_ADMIN_USERNAME' ),
			'as_admin'                  => true,
			'badgr_server_public_url'   => $options['badgr_server_public_url'],
			'badgr_server_flavor'       => BadgrClient::FLAVOR_LOCAL_R_JAMIROQUAI,
			'client_id'                 => $options['badgr_server_client_id'],
			'client_secret'             => $options['badgr_server_client_secret'],
			'access_token'              => $options['badgr_server_access_token'],
			'refresh_token'             => $options['badgr_server_refresh_token'],
			'token_expiration'          => $options['badgr_server_token_expiration'],
		);

		if (isset($options['badgr_server_internal_url']))
		$clientParameters['badgr_server_internal_url'] = $options['badgr_server_internal_url'];

		return self::makeInstance( $clientParameters );
	}


	public static function setGuzzleClient( Client $client ) {
		self::$guzzleClient = $client;
	}

	private static function getGuzzleClient() {
		if ( null === self::$guzzleClient ) {
			self::$guzzleClient = new Client();
		}

		return self::$guzzleClient;
	}

	// public static function getClientByUsername($userName, $asAdmin=false, BadgrServer $badgrServer=null){}
	// public static function getClient(WPUser $wp_user, $asAdmin=false, BadgrServer $badgrServer=null){}

	public function initiateCodeAuthorization() {
		// TODO: Check that we have the required parameters

		// Build a callback url with the client's hash
		$redirectUri = site_url( self::$authRedirectUri );

		$authProvider = new GenericProvider(
			array(
				'clientId'                => $this->client_id,
				'clientSecret'            => $this->client_secret,
				'redirectUri'             => $redirectUri,
				'urlAuthorize'            => $this->badgr_server_public_url . '/o/authorize',
				'urlAccessToken'          => $this->get_internal_or_external_server_url() . '/o/token',
				'urlResourceOwnerDetails' => $this->get_internal_or_external_server_url() . '/o/resource',
				'scopes'                  => $this->scopes,
			)
		);

		$authProvider->setHttpClient( self::getGuzzleClient() );

		// Fetch the authorization URL from the provider; this returns the
		// urlAuthorize option, generates and applies any necessary parameters
		// (e.g. state).
		$authorization_url = $authProvider->getAuthorizationUrl();

		// Get the state generated for you and store it to the session.
		$_SESSION['oauth2state'] = $authProvider->getState();

		// Set internal state
		$this->state = self::STATE_EXPECTING_AUTHORIZATION_CODE;
		$this->save();

		// Redirect to server
		header( 'Location: ' . $authorization_url );
		exit;

	}

	public static function handleAuthReturn() {
		if ( false !== strpos($_SERVER['REQUEST_URI'], 'init' ) ) {
			$client = self::makeClientFromSavedOptions();
			$client->initiateCodeAuthorization();
		}
		// Check for code and hash, retrieve client and complete code auth
		header( 'Content-Type: text/plain' );
		echo 'Badgr auth callback.';
		echo ' Full uri: ' . $_SERVER['REQUEST_URI'];
		exit();
		 // Called when an auth callback url is invoked

/* 		// Valid auth callbacks have a client_hash parameter
		if ( ! isset( $_GET['client_hash'] ) ) {
			// No client_hash parameter
			throw new \BadMethodCallException( 'Missing client hash on auth callback.' );
		} */

/* 		// Find the badgr client instance
		$client = self::getClientByHash( $_GET['client_hash'] );
		if ( null === $client ) {
			throw new \BadMethodCallException( 'Unknown client hash on auth callback.' );
		}
 */
/* 		// Check that we're expecting an authorization code
		if ( $client->state != self::STATE_EXPECTING_AUTHORIZATION_CODE ) {
			throw new \BadMethodCallException( 'Not expecting code for client ' . $client->client_hash );
		} */

		// CSRF check
		if ( empty( $_GET['state'] ) ||
			( isset( $_SESSION['oauth2state'] ) && $_GET['state'] !== $_SESSION['oauth2state'] ) ) {

			if ( isset( $_SESSION['oauth2state'] ) ) {
				unset( $_SESSION['oauth2state'] );
			}

			throw new \BadMethodCallException( 'CSRF check failed.' );

		}

		// TODO: handle user refusal at server

		// Check that we have an actual code
		if ( ! isset( $_GET['code'] ) ) {
			throw new \BadMethodCallException( 'No authorization code present.' );
		}

		// Attempt to get an access token
		$client->getAccessTokenFromAuthorizationCode( $_GET['code'] );
	}

	public function getAccessTokenFromAuthorizationCode( $code ) {
		$redirectUri = site_url( self::$authRedirectUri );

		$authProvider = new GenericProvider(
			array(
				'clientId'                => $this->client_id,
				'clientSecret'            => $this->client_secret,
				'redirectUri'             => $redirectUri,
				'urlAuthorize'            => $this->badgr_server_public_url . '/o/authorize',
				'urlAccessToken'          => $this->get_internal_or_external_server_url() . '/o/token',
				'urlResourceOwnerDetails' => $this->get_internal_or_external_server_url() . '/o/resource',
				'scopes'                  => $this->scopes,
			)
		);

		$authProvider->setHttpClient( self::getGuzzleClient() );

		try {
			$this->state = self::STATE_EXPECTING_ACCESS_TOKEN_FROM_CODE;
			$this->save();

			// Try to get an access token using the authorization code grant.
			$access_token = $authProvider->getAccessToken(
				'authorization_code',
				array(
					'code' => $code,
				)
			);

			$this->access_token      = $access_token->getToken();
			$this->refresh_token     = $access_token->getRefreshToken();
			$this->token_expiration  = $access_token->getExpires();
			$this->resource_owner_id = $access_token->getResourceOwnerId();

			$this->state     = self::STATE_HAVE_ACCESS_TOKEN;
			$this->needsAuth = false;
			$this->save();

		} catch ( IdentityProviderException $e ) {
			$this->state = self::STATE_FAILED_GETTING_ACCESS_TOKEN;
			$this->save();
			throw new \BadMethodCallException( 'Idendity provider raised exception ' . $e->getMessage() );

		} catch ( ConnectException $e ) {
			$this->state = self::STATE_FAILED_GETTING_ACCESS_TOKEN;
			$this->save();
			throw new \BadMethodCallException( 'Connection exception ' . $e->getMessage() );
		} catch ( Exception $e ) {
			throw new \BadMethodCallException( 'Connection exception ' . $e->getMessage() );
		}
	}

	public function getAccessTokenFromPasswordGrant() {
		$client = self::getGuzzleClient();
		$args    = array(
			'username'   => $this->username,
			'password'   => $this->badgr_password,
			'grant_type' => 'password',
			'scope'      => $this->scopes,
		);
		if ( $this->badgr_server_flavor != self::FLAVOR_BADGRIO_01 ) {
			$args['client_id'] = $this->client_id;
		}
		$args = array( 'query' => $args );

		try {
			$response = $client->request( 'POST', $this->get_internal_or_external_server_url() . '/o/token', $args );
			// Check for 200 response.
			if ( null !== $response && $response->getStatusCode() == 200 ) {
				$response_info          = json_decode( $response->getBody() );
				$this->access_token     = $response_info->access_token;
				$this->refresh_token    = $response_info->refresh_token;
				$this->token_expiration = time() + $response_info->expires_in;

				$this->save();
			}
		} catch ( ConnectException $e ) {
			$this->state = self::STATE_FAILED_GETTING_ACCESS_TOKEN;
			$this->save();
			throw new \BadMethodCallException( 'Connection exception ' . $e->getMessage() );
		} catch ( GuzzleException $e ) {
			if ( $e->getResponse()->getStatusCode() == 401 ) {
				$this->needsAuth = true;
			} else {
				$this->save();
				throw new \BadMethodCallException( 'Guzzle exception ' . $e->getMessage() );
			}
		}

	}

	private function save() {
		if ( null !== $this->badgr_user ) {
			$this->badgr_user->save_client();
		}
	}

	public static function init_hooks() {

		add_rewrite_rule(
			'bf2/(emailConfirm)/(\S+)/?',
			'index.php?bf2=$matches[1]&user=$matches[2]',
			'top'
		);
		add_rewrite_rule(
			'bf2/(forgotPassword)(\S+)/?',
			'index.php?bf2=$matches[1]&token=$matches[2]',
			'top'
		);
		add_rewrite_rule(
			'bf2/(auth)/?',
			'index.php?bf2=$matches[1]',
			'top'
		);
		add_rewrite_rule(
			'bf2/(loginRedirect|signupSuccess|signupFailure|signup|uiConnectSuccess)(\S+)/?',
			'index.php?bf2=$matches[1]',
			'top'
		);
	}

	public static function pre_init_hooks() {
		add_filter( 'query_vars', array( self::class, 'hook_query_vars' ) );
		add_action( 'template_redirect', array( self::class, 'hook_template_redirect' ) );
	}

	public static function hook_query_vars( $vars ) {
		$vars[] = 'bf2';
		return $vars;
	}

	public static function hook_template_redirect() {
		if ( $bf2 = get_query_var( 'bf2' ) ) {
			if ( 'auth' == $bf2 ) {
				self::handleAuthReturn();
			}
			header( 'Content-Type: text/plain' );
			echo 'Badgr callback: ' . $bf2;
			echo ' Full uri: ' . $_SERVER['REQUEST_URI'];
			exit();
		}
	}


	/**
	 * Init hook.
	 *
	 * @return void
	 */
	public static function init() {

	}

	public static function is_active() {
		// TODO: remove this whole function when dependencies are resolved
		return true;
	}

	/**
	 * Returns the Badgr service status.
	 *
	 * @return string
	 */
	public static function get_status() {
		// TODO return proper status
		return 'Active';
	}



	/**
	 * Checks whether to use internal or public url.
	 *
	 * @return string
	 */
	private function get_internal_or_external_server_url() {
		if ( null !== $this->badgr_server_internal_url && $this->badgr_server_internal_url != '' ) {
			return $this->badgr_server_internal_url;
		} else {
			return $this->badgr_server_public_url;
		}
	}



	/**
	 * Refreshes Badgr Server token.
	 *
	 */
	public function refresh_token() {
		$redirectUri = site_url( self::$authRedirectUri );

		$authProvider = new GenericProvider(
			array(
				'clientId'                => $this->client_id,
				'clientSecret'            => $this->client_secret,
				'redirectUri'             => $redirectUri,
				'urlAuthorize'            => $this->badgr_server_public_url . '/o/authorize',
				'urlAccessToken'          => $this->get_internal_or_external_server_url() . '/o/token',
				'urlResourceOwnerDetails' => $this->get_internal_or_external_server_url() . '/o/resource',
				'scopes'                  => $this->scopes,
			)
		);

		$authProvider->setHttpClient( self::getGuzzleClient() );

		try {
			$this->state = self::STATE_EXPECTING_ACCESS_TOKEN_FROM_REFRESH_TOKEN;
			$this->save();

			// Try to get an access token using the refresh token.
			$access_token = $provider->getAccessToken(
				'refresh_token',
				array(
					'refresh_token' => $this->refresh_token,
				)
			);

			$this->access_token      = $access_token->getToken();
			$this->refresh_token     = $access_token->getRefreshToken();
			$this->token_expiration  = $access_token->getExpires();
			$this->resource_owner_id = $access_token->getResourceOwnerId();

			$this->state = self::STATE_HAVE_ACCESS_TOKEN;
			$this->save();

		} catch ( IdentityProviderException $e ) {
			$this->state = self::STATE_FAILED_GETTING_ACCESS_TOKEN;
			$this->save();
			throw new \BadMethodCallException( 'Idendity provider raised exception ' . $e->getMessage() );

		} catch ( ConnectException $e ) {
			$this->state = self::STATE_FAILED_GETTING_ACCESS_TOKEN;
			$this->save();
			throw new \BadMethodCallException( 'Connection exception ' . $e->getMessage() );
		} catch ( Exception $e ) {
			throw new \BadMethodCallException( 'Connection exception ' . $e->getMessage() );
		}
	}

	/**
	 * Make a request to Badgr Server.
	 *
	 * @param string $method Method.
	 * @param string $path Path.
	 * @param array  $args Arguments.
	 * @return GuzzleHttp\Psr7\Response|null
	 * @throws \BadMethodCallException Bad method call exception.
	 */
	private function request( $method, $path, $args = array() ) {

		// Validate that we're using a configured client. If not, return null response
		if ( self::STATE_NEW_AND_UNCONFIGURED == $this->state ) {
			return null;
		}

		$client = self::getGuzzleClient();
		$method = strtoupper( $method );
		if ( ! in_array( $method, array( 'GET', 'PUT', 'POST', 'DELETE' ) ) ) {
			throw new \BadMethodCallException( 'Method not supported' );
		}

		if ( ! empty( $args ) ) {
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
					$args = array( 'json' => $args );
			}
		}
		$args = array_merge(
			$args,
			array(
				'headers' => array(
					'Authorization' => 'Bearer ' . $this->access_token,
					'Accept'        => 'application/json',
				),
			)
		);
		try {
			$response = $client->request( $method, self::get_internal_or_external_server_url() . $path, $args );

			return $response;

		} catch ( ConnectException $e ) {
			// TODO: potentially change client state
			return null;
		} catch ( GuzzleException $e ) {
			// TODO: potentially change client state
			return null;
		}
	}

	/**
	 * Post to Badgr Server.
	 *
	 * @param string $path Path.
	 * @param string $body Body.
	 * @return GuzzleHttp\Psr7\Response|null
	 */
	public function post( $path, $body ) {
		return $this->request( 'POST', $path, $body );
	}

	/**
	 * Put to Badgr Server.
	 *
	 * @param string $path Path.
	 * @param string $body Body.
	 * @return GuzzleHttp\Psr7\Response|null
	 */
	public function put( $path, $body ) {
		return $this->request( 'PUT', $path, $body );
	}

	/**
	 * Get to Badgr Server.
	 *
	 * @param string $path Path.
	 * @param string $queries Queries array.
	 * @return GuzzleHttp\Psr7\Response|null
	 */
	public function get( $path, $queries = array() ) {
		return $this->request( 'GET', $path, $queries );
	}

	/**
	 * Delete to Badgr Server.
	 *
	 * @param string $path Path.
	 * @return GuzzleHttp\Psr7\Response|null
	 */
	public function delete( $path, $body = array() ) {
		return $this->request( 'DELETE', $path, $body );
	}
}
