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
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use http\Exception\BadMethodCallException;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\GenericProvider;

/**
 * BadgrClient Class.
 */
class BadgrIndividualClient {

	const FLAVOR_BADGRIO_01 = 1;
	const FLAVOR_LOCAL_R_JAMIROQUAI = 2;
	const FLAVOR_CLOUD_v1 = 3;

	// Class properties
	protected static $clients = [];
	private static $guzzleClient = null;
	public static $authRedirectUri = '/wp-admin/admin.php?page=badgefactor2_badgr_settings';

	// Minimal properties of instances
	private $username = null;
	private $as_admin = false;
	private $badgr_server_public_url = null;
	private $badgr_server_flavor = null;

	// Additional instance properties
	private $wp_user_id = null;
	private $badgr_server_internal_url = null;

	private $scope; // Scope applicable to token

	private $badgr_password = null;

	private $client_id = null; // Client used for admin access will be different than password grant client
	private $client_secret = null;

	//private $authorization_code = null;
	private $access_token = null;
	private $refresh_token = null;
	private $token_expiration = null;
	private $resource_owner_id = null;

	private $needsConfiguration = true;
	private $needsAuth = true; // Needs auth is true whenever token is expired or if we get a 401 status during a call 

	const STATE_CONFIGURED_AND_ACTIVE = 1;
	const STATE_NEEDS_REFRESH = 2;
	const STATE_NEEDS_TOKEN = 3;
	const STATE_NEEDS_AUTH = 4;
	const STATE_NEEDS_LOGIN = 5;
	const STATE_NEEDS_USER_ACTION = 6;
	const STATE_NEEDS_ADMIN_ACTION = 7;
	const STATE_EXPECTING_AUTHORIZATION_CODE = 8;
	const STATE_EXPECTING_ACCESS_TOKEN_FROM_CODE = 9;
	const STATE_HAVE_ACCESS_TOKEN = 10;
	const STATE_FAILED_GETTING_ACCESS_TOKEN = 11;

	private $state; // configuredAndActive, needsRefresh, needsToken, needsAuth, needsLogin, needsUserAction, needsAdminAction
	public $retryAuthBeforeFailing = true;

	public $client_key = null;
	public $client_hash = null;

	private $lastMessageFromBadgrServer = null;


	// temporary faillures: urls faillures, won't clear tokens
	// identity faillures: clears tokens

	public static function makeInstance(array $parameters)
	{
		// Check that basic parameters are present
		$key_parameters = [
			'username',
			'as_admin',
			'badgr_server_public_url',
			'badgr_server_flavor'
		];

		foreach( $key_parameters as $key_parameter)
		{
			if ( !array_key_exists($key_parameter, $parameters))
			{
				throw new \BadMethodCallException('Missing ' . $key_parameter . ' parameter.');
			}
		}

		// TODO: perform checks on types and values of key parameters

		$client = new self();
		$client->username = $parameters['username'];
		$client->as_admin = $parameters['as_admin'];
		$client->badgr_server_public_url = $parameters['badgr_server_public_url'];
		$client->badgr_server_flavor = $parameters['badgr_server_flavor'];

		// TODO: check validity of optionnal parameters

		// TODO: save optionnal parameters in new instance
		$optionnalParameters = [
			'wp_user_id',
			'badgr_server_internal_url',
			'scope',
			'badgr_password',
			'client_id',
			'client_secret',
			'authorization_code',
			'access_token',
			'refresh_token',
			'token_expiration',
			'badgr_profile',
		];

		foreach ( $optionnalParameters as $optionnalParameter)
		{
			if ( isset($parameters[$optionnalParameter]))
			{
				$client->{$optionnalParameter} = $parameters[$optionnalParameter];
			}
		}

		// TODO: figureOutState

		// Generate key and hash
		$client->client_key = $parameters['username'] . '|' . $parameters['as_admin'] ? 'admin' : 'not_admin' . '|' . $parameters['badgr_server_public_url'];
		$client->client_hash = hash('md5', $client->client_key);

		// Add client to class list
		self::$clients[$client->client_hash] = $client;

		return ($client);

	}


	public static function setGuzzleClient(Client $client)
	{
		self::$guzzleClient = $client;
	}

	private static function getGuzzleClient()
	{
		if ( null === self::$guzzleClient)
		{
			self::$guzzleClient = new Client();
		}

		return self::$guzzleClient;
	}

	public static function getClientByUsername($userName, $asAdmin=false, BadgrServer $badgrServer=null){}
	public static function getClient(WPUser $wp_user, $asAdmin=false, BadgrServer $badgrServer=null){}

	public function getClientByHash($hash)
	{
		if ( isset( self::$clients[$hash] ) )
		{
			return self::$clients[$hash];
		} else
		{
			return null;
		}
	}

	protected function getDefaultBadgrServer(){}

	public static function list_clients(){} // BadgrServer,WPUserId,Email

	public function initiateCodeAuthorization()
	{
		// TODO: Check that we have the required parameters

		// Build a callback url with the client's hash
		$redirectUri = site_url( self::$authRedirectUri ) . '&client_hash=' . $this->client_hash;

		// Build the scope list
		$scope = 'rw:profile rw:issuer rw:backpack';
		if ( $this->as_admin == true )
		{
			$scope .= ' rw:serverAdmin';
		}

		$authProvider = new GenericProvider(
			array(
				'clientId'                => $this->client_id,
				'clientSecret'            => $this->client_secret,
				'redirectUri'             => $redirectUri ,
				'urlAuthorize'            => $this->badgr_server_public_url . '/o/authorize',
				'urlAccessToken'          => $this->get_internal_or_external_server_url() . '/o/token',
				'urlResourceOwnerDetails' => $this->get_internal_or_external_server_url() . '/o/resource',
				'scopes'                  => $scopes
			)
		);

		// Fetch the authorization URL from the provider; this returns the
		// urlAuthorize option, generates and applies any necessary parameters
		// (e.g. state).
		$authorization_url = $provider->getAuthorizationUrl();

		// Get the state generated for you and store it to the session.
		$_SESSION['oauth2state'] = $provider->getState();

		// Set internal state
		$this->state = STATE_EXPECTING_AUTHORIZATION_CODE;
		$this->save();

		// Redirect to server
		header( 'Location: ' . $authorization_url );
		exit;

	}

	public static function handleAuthReturn()
	{
		// Called when an auth callback url is invoked

		// Valid auth callbacks have a client_hash parameter
		if ( ! isset( $_GET['client_hash'] ))
		{
			// No client_hash parameter
			throw new BadMethodCallException('Missing client hash on auth callback.');
		}

		// Find the badgr client instance
		$client = self::getClientByHash($_GET['client_hash']);
		if ( null === $client)
		{
			throw new BadMethodCallException('Unknown client hash on auth callback.');
		}

		// Check that we're expecting an authorization code
		if ( $client->state != STATE_EXPECTING_AUTHORIZATION_CODE)
		{
			throw new BadMethodCallException('Not expecting code for client ' . $client->client_hash );
		}

		// CSRF check
		if ( empty( $_GET['state'] ) ||
			( isset( $_SESSION['oauth2state'] ) && $_GET['state'] !== $_SESSION['oauth2state'] ) ) {

			if ( isset( $_SESSION['oauth2state'] ) ) {
				unset( $_SESSION['oauth2state'] );
			}

			throw new BadMethodCallException('CSRF check failed.');

		}

		// TODO: handle user refusal at server

		// Check that we have an actual code
		if ( ! isset( $_GET['code'] ) ) {
			throw new BadMethodCallException('No authorization code present.');
		}

		// Attempt to get an access token
		$client->getAccessTokenFromAuthorizationCode($_GET['code']);
	}

	public function getAccessTokenFromAuthorizationCode($code)
	{
		$redirectUri = site_url( self::$authRedirectUri ) . '&client_hash=' . $this->client_hash;

		// Build the scope list
		$scope = 'rw:profile rw:issuer rw:backpack';
		if ( $this->as_admin == true )
		{
			$scope .= ' rw:serverAdmin';
		}

		$authProvider = new GenericProvider(
			array(
				'clientId'                => $this->client_id,
				'clientSecret'            => $this->client_secret,
				'redirectUri'             => $redirectUri ,
				'urlAuthorize'            => $this->badgr_server_public_url . '/o/authorize',
				'urlAccessToken'          => $this->get_internal_or_external_server_url() . '/o/token',
				'urlResourceOwnerDetails' => $this->get_internal_or_external_server_url() . '/o/resource',
				'scopes'                  => $scopes
			)
		);

		try {
			$this->state = STATE_EXPECTING_ACCESS_TOKEN_FROM_CODE;
			$this->save();

			// Try to get an access token using the authorization code grant.
			$this->access_token = $provider->getAccessToken(
				'authorization_code',
				array(
					'code' => $code,
				)
			);

			$this->access_token = $access_token->getToken();
			$this->refresh_token = $access_token->getRefreshToken();
			$this->token_expiration = $access_token->getExpires();
			$this->resource_owner_id = $access_token->getResourceOwnerId();

			$this->state = STATE_HAVE_ACCESS_TOKEN;
			$this->save();

		} catch ( IdentityProviderException $e ) {
			$this->state = STATE_FAILED_GETTING_ACCESS_TOKEN;
			$this->save();
			throw new BadMethodCallException('Idendity provider raised exception ' . $e->getMessage());

		} catch ( ConnectException $e ) {
			$this->state = STATE_FAILED_GETTING_ACCESS_TOKEN;
			$this->save();
			throw new BadMethodCallException('Connection exception ' . $e->getMessage());
		}
	}

	public function save()
	{
		// Placeholder for saving client to storage
		// TODO: implement
	}

	public function probeBadgrServer(){}

	// getUserInfo: get user info from WP storage
	// setUserInfo: set user info in WP storage
	// getClient: given parameters, get an instance of client
	// getBadgrServerInfo: get badgr server information from WP storage
	// checkConnectivity: perform a neutral operation with client to check connectivity

	// Try refresh first

	// getClient(WPUser $user, $asAdmin, BadgrServer $badgrServer)

	// Detect and respond appropriately to revoked token

	/**
	 * Whether or not the BadgrClient is initialized.
	 *
	 * @var boolean
	 */
	private static $initialized = false;

	/**
	 * Array of BadgrClient settings.
	 *
	 * @var array
	 */
	private static $badgr_settings;

	/**
	 * BadgrClient Init.
	 *
	 * @return void
	 */
	public static function init_hooks() {
/*		if ( ! self::$initialized ) {
			add_action( 'init', array( BadgrClient::class, 'init' ), 9966 );
			self::$initialized = self::is_active();
		}*/
	}

	/**
	 * Init hook.
	 *
	 * @return void
	 */
	public static function init() {
		// TODO.
	}

	/**
	 * Check whether or not the Badgr service is active.
	 *
	 * @return boolean
	 */
/*	public static function is_active() {

		if ( ! self::is_configured() ) {
			$is_active = false;
		} else {
			$client = new Client();
			try {
				$response = $client->request( 'GET', self::get_internal_or_external_server_url() );
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
	}*/

	/**
	 * Returns the Badgr service status.
	 *
	 * @return string
	 */
/*	public static function get_status() {
		return ( self::is_active() ? __( 'Active', 'badgefactor2' ) : __( 'Inactive', 'badgefactor2' ) );
	}
*/
	/**
	 * Checks whether or not the Badgr server is properly configured.
	 *
	 * @return boolean
	 */
/*	private static function is_configured() {
		return isset( self::badgr_settings()['badgr_server_client_id'] ) &&
		isset( self::badgr_settings()['badgr_server_client_secret'] ) &&
		isset( self::badgr_settings()['badgr_server_public_url'] );
	}*/

	/**
	 * Checks whether or not the Badgr server is initialized.
	 *
	 * @return boolean
	 */
/*	private static function is_initialized() {
		return isset( self::badgr_settings()['badgr_server_access_token'] ) &&
		isset( self::badgr_settings()['badgr_server_refresh_token'] ) &&
		isset( self::badgr_settings()['badgr_server_token_expiration'] ) &&
		self::badgr_settings()['badgr_server_token_expiration'] >= time();
	}*/

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
	 * Returns Badgr settings.
	 *
	 * @return array
	 */
/*	private static function badgr_settings() {
		if ( ! self::$badgr_settings ) {
			self::$badgr_settings = get_option( 'badgefactor2_badgr_settings' );
		}

		return self::$badgr_settings;
	}*/

	/**
	 * Returns Badgr access token, or null if not configured.
	 *
	 * @return string|null
	 */
/*	private static function get_access_token() {
		if ( self::is_active() ) {
			return self::badgr_settings()['badgr_server_access_token'];
		}

		return null;
	}
*/
	/**
	 * Makes Badgr Server provider.
	 *
	 * @return GenericProvider
	 */
/*	private static function make_provider() {
		return new GenericProvider(
			array(
				'clientId'                => self::badgr_settings()['badgr_server_client_id'],
				'clientSecret'            => self::badgr_settings()['badgr_server_client_secret'],
				'redirectUri'             => site_url( self::$authRedirectUri ),
				'urlAuthorize'            => self::badgr_settings()['badgr_server_public_url'] . '/o/authorize',
				'urlAccessToken'          => self::get_internal_or_external_server_url() . '/o/token',
				'urlResourceOwnerDetails' => self::get_internal_or_external_server_url() . '/o/resource',
				'scopes'                  => 'rw:profile rw:issuer rw:backpack rw:serverAdmin ',
			)
		);
	}*/

	/**
	 * Authenticates BadgeFactor 2 to Badgr Server.
	 *
	 * @return boolean
	 */
/*	private static function authenticate() {

		$provider = self::make_provider();

		// If we don't have an authorization code then get one.
		if ( ! isset( $_GET['code'] ) ) {

			// Fetch the authorization URL from the provider; this returns the
			// urlAuthorize option and generates and applies any necessary parameters
			// (e.g. state).
			$authorization_url = $provider->getAuthorizationUrl();

			// Get the state generated for you and store it to the session.
			$_SESSION['oauth2state'] = $provider->getState();
			header( 'Location: ' . $authorization_url );
			exit;

			// Check given state against previously stored one to mitigate CSRF attack.
		} elseif ( empty( $_GET['state'] ) ||
			( isset( $_SESSION['oauth2state'] ) && $_GET['state'] !== $_SESSION['oauth2state'] ) ) {

			if ( isset( $_SESSION['oauth2state'] ) ) {
				unset( $_SESSION['oauth2state'] );
			}

			return false;

		} else {

			try {

				// Try to get an access token using the authorization code grant.
				$access_token = $provider->getAccessToken(
					'authorization_code',
					array(
						'code' => $_GET['code'],
					)
				);

				self::$badgr_settings['badgr_server_access_token']     = $access_token->getToken();
				self::$badgr_settings['badgr_server_refresh_token']    = $access_token->getRefreshToken();
				self::$badgr_settings['badgr_server_token_expiration'] = $access_token->getExpires();
				update_option( 'badgefactor2_badgr_settings', self::$badgr_settings );

			} catch ( IdentityProviderException $e ) {

				return false;

			}

			return true;
		}
	}*/

	/**
	 * Refreshes Badgr Server token.
	 *
	 * @return boolean
	 */
/*	private static function refresh_token() {

		if ( isset( self::$badgr_settings ) && isset( self::$badgr_settings['badgr_server_access_token'] ) &&
			( ! isset( self::$badgr_settings['badgr_server_token_expiration'] ) ||
				self::$badgr_settings['badgr_server_token_expiration'] <= time() ) ) {

			$provider = self::make_provider();

			try {
				$access_token = $provider->getAccessToken(
					'refresh_token',
					array(
						'refresh_token' => self::$badgr_settings['badgr_server_refresh_token'],
					)
				);

				self::$badgr_settings['badgr_server_access_token']     = $access_token->getToken();
				self::$badgr_settings['badgr_server_refresh_token']    = $access_token->getRefreshToken();
				self::$badgr_settings['badgr_server_token_expiration'] = $access_token->getExpires();
				update_option( 'badgefactor2_badgr_settings', self::$badgr_settings );

			} catch ( IdentityProviderException $e ) {

				return self::authenticate();

			} catch ( ConnectException $e ) {

				return false;

			}
		}

		return true;
	}*/

	/**
	 * Make a request to Badgr Server.
	 *
	 * @param string $method Method.
	 * @param string $path Path.
	 * @param array  $args Arguments.
	 * @return GuzzleHttp\Psr7\Response|null
	 * @throws BadMethodCallException Bad method call exception.
	 */
	private function request( $method, $path, $args = array() ) {

		$client = self::getGuzzleClient();
		$method = strtoupper( $method );
		if ( ! in_array( $method, array( 'GET', 'PUT', 'POST', 'DELETE' ) ) ) {
			throw new BadMethodCallException( 'Method not supported' );
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

			return null;

		} catch ( GuzzleException $e ) {

			return null; // 401
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
