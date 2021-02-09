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
 * @phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
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
	const FLAVOR_CLOUD_V1           = 3;

	const BADGR_IO_URL                    = 'https://api.badgr.io';
	const REDIRECT_PATH_AFTER_AUTH        = '/wp-admin/admin.php?page=badgefactor2_badgr_settings';
	const START_ADMIN_LINK_URL            = '/bf2/init';
	const ADMIN_INIT_NONCE_ACTION         = 'init_admin_auth';
	const DEFAULT_LOCAL_BADGR_SERVER_PORT = 8000;

	// Password sources.
	const PASSWORD_SOURCE_CUSTOM           = 1;
	const PASSWORD_SOURCE_USE_WP_PASSWORD  = 2;
	const PASSWORD_SOURCE_ALWAYS_ASK       = 3;
	const PASSWORD_SOURCE_ASK_AND_REMEMBER = 4;

	// Grant types.
	const GRANT_PASSWORD = 1;
	const GRANT_CODE     = 2;

	// Class properties.

	/**
	 * Undocumented variable
	 *
	 * @var Client
	 */
	private static $guzzle_client = null;

	private static $configuration = [];

	/**
	 * Undocumented variable
	 *
	 * @var string
	 */
	public static $auth_redirect_uri = '/bf2/auth';

	// BagrUser.
	// Badgr user associated with client instance.

	/**
	 * Undocumented variable
	 *
	 * @var [type]
	 */
	public $badgr_user = null;

	// Minimal properties of instances.

	/**
	 * Undocumented variable
	 *
	 * @var [type]
	 */
	private $username = null;

	/**
	 * Undocumented variable
	 *
	 * @var boolean
	 */
	private $as_admin = false;

	/**
	 * Undocumented variable
	 *
	 * @var [type]
	 */
	private $badgr_server_public_url = null;

	/**
	 * Undocumented variable
	 *
	 * @var [type]
	 */
	private $badgr_server_flavor = null;

	// Additional instance properties.

	/**
	 * Undocumented variable
	 *
	 * @var [type]
	 */
	private $badgr_server_internal_url = null;

	/**
	 * Undocumented variable
	 *
	 * @var [type]
	 */
	private $scopes; // Scopes applicable to token.

	/**
	 * Undocumented variable
	 *
	 * @var [type]
	 */
	private $badgr_password = null;

	/**
	 * Undocumented variable
	 *
	 * @var [type]
	 */
	private $auth_type = null;

	/**
	 * Undocumented variable
	 *
	 * @var [type]
	 */
	private $client_id = null; // Client used for admin access will be different than password grant client.

	/**
	 * Undocumented variable
	 *
	 * @var [type]
	 */
	private $client_secret = null;

	private $password_grant_client_id = null;
	private $password_grant_client_secret = null;

	/**
	 * Undocumented variable
	 *
	 * @var [type]
	 */
	private $access_token = null;

	/**
	 * Undocumented variable
	 *
	 * @var [type]
	 */
	private $refresh_token = null;

	/**
	 * Undocumented variable
	 *
	 * @var [type]
	 */
	private $token_expiration = null;

	/**
	 * Undocumented variable
	 *
	 * @var [type]
	 */
	private $resource_owner_id = null;

	/**
	 * Undocumented variable
	 *
	 * @var boolean
	 */
	private $needs_configuration = true;

	/**
	 * Undocumented variable
	 *
	 * @var boolean
	 */
	private $needs_auth = true; // Needs auth is true whenever token is expired or if we get a 401 status during a call.

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

	/**
	 * Undocumented variable
	 *
	 * @var [type]
	 */
	private $state = self::STATE_NEW_AND_UNCONFIGURED;

	/**
	 * Guzzle client to use
	 *
	 * @var boolean
	 */
	public $retry_auth_before_failing = true;

	/**
	 * Message fromBadgr server
	 *
	 * @var [type]
	 */
	private $last_message_from_badgr_server = null;

	private static function refresh_config() {
		self::$configuration = get_option( 'badgefactor2_badgr_settings' );
	}

	/**
	 * Make an instance of a BadgrClient
	 *
	 * @param array $parameters Parameters.
	 * @throws \BadMethodCallException Bad Method Call Exception.
	 * @return BadgrClient
	 */
	public static function make_instance( array $parameters ) {
		// Start with un unconfigured client
		$client = new self();
		$client->state = self::STATE_NEW_AND_UNCONFIGURED;

		// Set parameters passed to function
		$key_parameters = array(
			'username',
			'as_admin',
		);

		foreach ( $key_parameters as $key_parameter ) {
			if ( ! array_key_exists( $key_parameter, $parameters ) ) {
				// Return an unconfigured client since some key paramaters are missing.
				return $client;
			}
		}

		$client->username                = $parameters['username'];
		$client->as_admin                = $parameters['as_admin'];

		$optionnal_parameters = array(
			'badgr_server_public_url',
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
			'password_grant_client_id',
			'password_grant_client_secret',
		);

		foreach ( $optionnal_parameters as $optionnal_parameter ) {
			if ( isset( $parameters[ $optionnal_parameter ] ) ) {
				$client->{$optionnal_parameter} = $parameters[ $optionnal_parameter ];
			}
		}

		// If scopes not already set, set to default value.
		if ( null === $client->scopes ) {
			$scopes = 'rw:profile rw:backpack';
			if ( true === $client->as_admin ) {
				$scopes .= ' rw:issuer rw:serverAdmin';
			}

			$client->scopes = $scopes;
		}

		// Set initial state ( for now either configured or have token ).
		if ( null !== $client->access_token
			&& null !== $client->refresh_token
			&& null !== $client->token_expiration
			&& time() < $client->token_expiration ) {
				$client->state = self::STATE_HAVE_ACCESS_TOKEN;
		} else {
			$client->state = self::STATE_CONFIGURED;
		}

		// set BadgrUser if available (also saves instance).
		if ( isset( $parameters['badgr_user'] ) && null !== $parameters['badgr_user'] ) {
			$parameters['badgr_user']->set_client( $client );
		}

		return ( $client );

	}

	/**
	 * Determine if client has Badgr admin access
	 *
	 * @return boolean
	 */
	public function is_admin() {
		return $this->as_admin;
	}

	/**
	 * Get the client's state
	 *
	 * @return int
	 */
	public function get_state() {
		return $this->state;
	}

	/**
	 * Use wp_options to make and configure a client
	 *
	 * @return BadgrClient
	 */
/* 	public static function make_client_from_saved_options() {
		// Make a client from the previous method of using options.

		$options = get_option( 'badgefactor2_badgr_settings' );

		$client_parameters = array(
			'username'            => '',
			'as_admin'            => true,
			'badgr_server_flavor' => self::FLAVOR_LOCAL_R_JAMIROQUAI,
		);

// 		if ( isset( $options['badgr_server_public_url'] ) ) {
//			$client_parameters['badgr_server_public_url'] = $options['badgr_server_public_url'];
//		} 

		if ( isset( $options['badgr_server_client_id'] ) ) {
			$client_parameters['client_id'] = $options['badgr_server_client_id'];
		}

		if ( isset( $options['badgr_server_client_secret'] ) ) {
			$client_parameters['client_secret'] = $options['badgr_server_client_secret'];
		}

// 		if ( isset( $options['badgr_server_internal_url'] ) ) {
//			$client_parameters['badgr_server_internal_url'] = $options['badgr_server_internal_url'];
//		}
 
		if ( isset( $options['badgr_server_access_token'] ) ) {
			$client_parameters['badgr_server_access_token'] = $options['badgr_server_access_token'];
		}

		if ( isset( $options['badgr_server_refresh_token'] ) ) {
			$client_parameters['badgr_server_refresh_token'] = $options['badgr_server_refresh_token'];
		}

		if ( isset( $options['badgr_server_token_expiration'] ) ) {
			$client_parameters['badgr_server_token_expiration'] = $options['badgr_server_token_expiration'];
		}

		return self::make_instance( $client_parameters );
	} */

	/**
	 * Undocumented function
	 *
	 * @param Client $client Guzzle client to use.
	 * @return void
	 */
	public static function set_guzzle_client( Client $client ) {
		self::$guzzle_client = $client;
	}

	/**
	 * Get the Guzzle client to use for a request
	 *
	 * @return Client
	 */
	private static function get_guzzle_client() {
		if ( null === self::$guzzle_client ) {
			self::$guzzle_client = new Client();
		}

		return self::$guzzle_client;
	}

	/**
	 * Setup a code authorization
	 *
	 * @return void
	 */
	public static function setup_admin_code_authorization() {
		// Check that user is logged into WP.
		$current_user = wp_get_current_user();
		if ( 1 !== $current_user->ID ) {
			// Redirect to admin page.
			header( 'Location: ' . site_url( self::REDIRECT_PATH_AFTER_AUTH ) );
			exit;
		}

		$client = BadgrUser::get_or_make_user_client( $current_user );

		$client->initiate_code_authorization();
	}

	/**
	 * Initiate the process of a code authorization
	 *
	 * @return void
	 */
	public function initiate_code_authorization() {

		self::refresh_config();

		// Build a callback url with the client's hash.
		$redirect_uri = site_url( self::$auth_redirect_uri );

		$auth_provider = new GenericProvider(
			array(
				'clientId'                => $this->get_parameter( 'client_id' ),
				'clientSecret'            => $this->get_parameter( 'client_secret' ),
				'redirectUri'             => $redirect_uri,
				'urlAuthorize'            => $this->get_internal_or_external_server_url( true ) . '/o/authorize',
				'urlAccessToken'          => $this->get_internal_or_external_server_url() . '/o/token',
				'urlResourceOwnerDetails' => $this->get_internal_or_external_server_url() . '/o/resource',
				'scopes'                  => $this->scopes,
			)
		);

		$auth_provider->setHttpClient( self::get_guzzle_client() );

		// Fetch the authorization URL from the provider; this returns the
		// urlAuthorize option, generates and applies any necessary parameters
		// (e.g. state).
		$authorization_url = $auth_provider->getAuthorizationUrl();

		// Get the state generated for you and store it to the session.
		$_SESSION['oauth2state'] = $auth_provider->getState();

		// Set internal state.
		$this->state = self::STATE_EXPECTING_AUTHORIZATION_CODE;
		$this->save();

		// Redirect to server.
		header( 'Location: ' . $authorization_url );
		exit;

	}

	/**
	 * Handle Badgr auth callbacks
	 *
	 * @throws \BadMethodCallException Bad Method Call Exception.
	 * @return void
	 */
	public static function handle_auth_return() {
		if ( ! isset( $_GET['code'] ) ) {
			exit();
		}

		// CSRF check.
		if ( empty( $_GET['state'] ) ||
			( isset( $_SESSION['oauth2state'] ) && $_GET['state'] !== $_SESSION['oauth2state'] ) ) {

			if ( isset( $_SESSION['oauth2state'] ) ) {
				unset( $_SESSION['oauth2state'] );
			}

			throw new \BadMethodCallException( 'CSRF check failed.' );

		}

		// TODO: handle user refusal at server.

		// Check that we have an actual code.
		if ( ! isset( $_GET['code'] ) ) {
			// TODO set state.
			throw new \BadMethodCallException( 'No authorization code present.' );
		}

		$client = BadgrUser::get_or_make_user_client();

		// Attempt to get an access token.
		$client->get_access_token_from_authorization_code( $_GET['code'] );

		// Install this user as the admin user for site.
		$client->badgr_user->set_as_admin_instance();

		// Return us to admin page.
		header( 'Location: ' . site_url( self::REDIRECT_PATH_AFTER_AUTH ) );
		exit;
	}

	/**
	 * Complete the code authorization process by obtaining a token
	 *
	 * @param string $code Authorization code.
	 * @throws \BadMethodCallException Bad Method Call Exception.
	 * @return void
	 */
	public function get_access_token_from_authorization_code( $code ) {
		$redirect_uri = site_url( self::$auth_redirect_uri );
		self::refresh_config();

		$auth_provider = new GenericProvider(
			array(
				'clientId'                => $this->get_parameter( 'client_id'),
				'clientSecret'            => $this->get_parameter( 'client_secret'),
				'redirectUri'             => $redirect_uri,
				'urlAuthorize'            => $this->get_internal_or_external_server_url( true ) . '/o/authorize',
				'urlAccessToken'          => $this->get_internal_or_external_server_url() . '/o/token',
				'urlResourceOwnerDetails' => $this->get_internal_or_external_server_url() . '/o/resource',
				'scopes'                  => $this->scopes,
			)
		);

		$auth_provider->setHttpClient( self::get_guzzle_client() );

		try {
			$this->state = self::STATE_EXPECTING_ACCESS_TOKEN_FROM_CODE;
			$this->save();

			// Try to get an access token using the authorization code grant.
			$access_token = $auth_provider->getAccessToken(
				'authorization_code',
				array(
					'code' => $code,
				)
			);

			$this->access_token      = $access_token->getToken();
			$this->refresh_token     = $access_token->getRefreshToken();
			$this->token_expiration  = $access_token->getExpires();
			$this->resource_owner_id = $access_token->getResourceOwnerId();

			$this->state      = self::STATE_HAVE_ACCESS_TOKEN;
			$this->needs_auth = false;
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
	 * Get a token by using a username and password
	 *
	 * @return void
	 * @throws \BadMethodCallException Bad Method Call Exception.
	 */
	public function get_access_token_from_password_grant() {
		$redirect_uri = site_url( self::$auth_redirect_uri );
		self::refresh_config();

		$auth_provider = new GenericProvider(
			array(
				'clientId'                => $this->get_parameter( 'password_grant_client_id'),
				'clientSecret'            => $this->get_parameter( 'password_grant_client_secret'),
				'redirectUri'             => $redirect_uri,
				'urlAuthorize'            => $this->get_internal_or_external_server_url( true ) . '/o/authorize',
				'urlAccessToken'          => $this->get_internal_or_external_server_url() . '/o/token',
				'urlResourceOwnerDetails' => $this->get_internal_or_external_server_url() . '/o/resource',
				'scopes'                  => $this->scopes,
			)
		);

		$auth_provider->setHttpClient( self::get_guzzle_client() );

		try {
			$this->state = self::STATE_EXPECTING_ACCESS_TOKEN_FROM_PASSWORD;
			$this->save();

			// Try to get an access token using the authorization code grant.
			$access_token = $auth_provider->getAccessToken(
				'password',
				array(
					'username' => $this->username,
					'password' => $this->badgr_password,
					'scope'    => $this->scopes,
				)
			);

			$this->access_token      = $access_token->getToken();
			$this->refresh_token     = $access_token->getRefreshToken();
			$this->token_expiration  = $access_token->getExpires();
			$this->resource_owner_id = $access_token->getResourceOwnerId();

			$this->state      = self::STATE_HAVE_ACCESS_TOKEN;
			$this->needs_auth = false;
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
	 * Persist client state
	 *
	 * @return void
	 */
	private function save() {
		if ( null !== $this->badgr_user ) {
			$this->badgr_user->save_client();
		}
	}


	/**
	 * Init hook.
	 *
	 * @return void
	 */
	public static function init() {
		// TODO: add auth/welcome.
		add_rewrite_rule(
			'bf2/(emailConfirm)/?',
			'index.php?bf2=$matches[1]',
			'top'
		);
		add_rewrite_rule(
			'bf2/(forgotPassword)(\S+)/?',
			'index.php?bf2=$matches[1]&token=$matches[2]',
			'top'
		);
		add_rewrite_rule(
			'bf2/(auth|init)/?',
			'index.php?bf2=$matches[1]',
			'top'
		);
		add_rewrite_rule(
			'bf2/(loginRedirect|signupSuccess|signupFailure|signup|uiConnectSuccess)(\S+)/?',
			'index.php?bf2=$matches[1]',
			'top'
		);
	}

	/**
	 * Init tasks
	 *
	 * @return void
	 */
	public static function init_hooks() {
		add_action( 'init', array( self::class, 'init' ) );
		add_filter( 'query_vars', array( self::class, 'hook_query_vars' ) );
		add_action( 'template_redirect', array( self::class, 'hook_template_redirect' ) );
	}

	/**
	 * Signal ou interest in bf2 query variable
	 *
	 * @param array $vars Variables.
	 * @return array
	 */
	public static function hook_query_vars( $vars ) {
		$vars[] = 'bf2';
		return $vars;
	}

	/**
	 * Catch Badge Factor 2 related urls
	 *
	 * @return void
	 */
	public static function hook_template_redirect() {
		$bf2 = get_query_var( 'bf2' );
		if ( $bf2 ) {
			if ( 'auth' === $bf2 ) {
				self::handle_auth_return();
			}
			if ( 'init' === $bf2 ) {
				// Check nonce: if fails, will termnate script with 403 error.
				check_admin_referer( self::ADMIN_INIT_NONCE_ACTION );
				// Launch admin auth linking.
				self::setup_admin_code_authorization();
			}
			header( 'Content-Type: text/plain' );
			echo 'Badgr callback: ' . $bf2;
			echo ' Full uri: ' . $_SERVER['REQUEST_URI'];
			exit();
		}
	}

	/**
	 * Determine is client is active
	 *
	 * @return boolean
	 */
	public static function is_active() {
		// TODO: relocate function.
		$badgr_admin_user = BadgrUser::get_admin_instance();

		if ( null === $badgr_admin_user ) {
			return false;
		}

		$admin_client = $badgr_admin_user->get_client();

		if ( null === $admin_client ) {
			return false;
		}

		if ( self::STATE_HAVE_ACCESS_TOKEN !== $admin_client->get_state() || false === $admin_client->is_admin() ) {
			return false;
		}

		return true;
	}

	/**
	 * Returns the Badgr service status.
	 *
	 * @return string
	 */
	public static function get_status() {
		// TODO return proper status.
		if ( true === self::is_active() ) {
			return 'Active';
		}

		return 'Not ready';
	}



	/**
	 * Checks whether to use internal or public url.
	 *
	 * @return string
	 */
	private function get_internal_or_external_server_url( $public_url_only=false ) {
		if ( false === $public_url_only ) {
			if ( null !== $this->badgr_server_internal_url && '' !== $this->badgr_server_internal_url ) {
				return $this->badgr_server_internal_url;
			} elseif ( isset(self::$configuration['badgr_server_internal_url']) && '' !== self::$configuration['badgr_server_internal_url']) {
				return self::$configuration['badgr_server_internal_url'];
			}
		}

		if ( null !== $this->badgr_server_public_url && '' !== $this->badgr_server_public_url ) {
			return $this->badgr_server_public_url;
		} elseif ( isset(self::$configuration['badgr_server_public_url']) && '' !== self::$configuration['badgr_server_public_url']) {
			return self::$configuration['badgr_server_public_url'];
		} else {
			throw new \UnexpectedValueException('Badgr url unconfigured');
		}
	}



	/**
	 * Refreshes Badgr Server token.
	 *
	 * @throws \BadMethodCallException Bad Method Call Exception.
	 */
	public function refresh_token() {
		self::refresh_config();
		
		$redirect_uri = site_url( self::$auth_redirect_uri );

		$auth_provider = new GenericProvider(
			array(
				'clientId'                => $this->as_admin ? $this->get_parameter('client_id') : $this->get_parameter('password_grant_client_id'),
				'clientSecret'            => $this->as_admin ? $this->get_parameter('client_secret') : $this->get_parameter('password_grant_client_secret'),
				'redirectUri'             => $redirect_uri,
				'urlAuthorize'            => $this->badgr_server_public_url . '/o/authorize',
				'urlAccessToken'          => $this->get_internal_or_external_server_url() . '/o/token',
				'urlResourceOwnerDetails' => $this->get_internal_or_external_server_url() . '/o/resource',
				'scopes'                  => $this->scopes,
			)
		);

		$auth_provider->setHttpClient( self::get_guzzle_client() );

		try {
			$this->state = self::STATE_EXPECTING_ACCESS_TOKEN_FROM_REFRESH_TOKEN;
			$this->save();

			// Try to get an access token using the refresh token.
			$access_token = $auth_provider->getAccessToken(
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

	private function get_parameter( $parameter_name) {
		if (isset($this->{$parameter_name})) {
			return $this->{$parameter_name};
		} else {
			// Fetch from configuration
			if ( true === isset(self::$configuration['badgr_server_' . $parameter_name])) {
				return self::$configuration['badgr_server_' . $parameter_name];
			} else {
				return null;
			}
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

		// Validate that we're using a configured client. If not, return null response.
		if ( self::STATE_NEW_AND_UNCONFIGURED === $this->state ) {
			return null;
		}

		$done = false;
		$refresh = false;

		// Fetch configuration options
		self::refresh_config();

		// If it's a password client with configuration, try to get accesstoken
		if ( false === $this->as_admin && self::STATE_CONFIGURED === $this->state ) {
			try {
				$this->get_access_token_from_password_grant();
			} catch (\Exception $e ) {
				return null;
			}
		}

		do {
			// Refresh token if requested
			if ( true == $refresh ) {
				try {
					$this->refresh_token();
				} catch ( \Exception $e) {
					return null;
				}
			}

			$client = self::get_guzzle_client();
			$method = strtoupper( $method );
			if ( ! in_array( $method, array( 'GET', 'PUT', 'POST', 'DELETE' ), true ) ) {
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
				return null;
			} catch ( GuzzleException $e ) {
				// If we aren't in a refresh cycle, treat 401 as an expired token
				if ( $refresh == false && $e->getCode() == 401) {
					$refresh = true;
				} else {
					return null;
				}
			}
		} while ( $done == false );

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
	 * @param string $body Request body array.
	 * @return GuzzleHttp\Psr7\Response|null
	 */
	public function delete( $path, $body = array() ) {
		return $this->request( 'DELETE', $path, $body );
	}
}
