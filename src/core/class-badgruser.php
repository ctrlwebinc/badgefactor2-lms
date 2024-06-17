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

use BadgeFactor2\BadgrClient;
use BadgeFactor2\Helpers\Text;
use \WP_User;

/**
 * BadgrUser Class.
 */
class BadgrUser {
	/**
	 * Undocumented variable
	 *
	 * @var string
	 */
	public static $user_meta_key_for_client = 'badgr_client_instance';
	/**
	 * Undocumented variable
	 *
	 * @var string
	 */
	public static $options_key_for_badgr_admin = 'badgefactor2_badgr_admin';

	/**
	 * Undocumented variable
	 *
	 * @var string
	 */
	public static $meta_key_for_user_state = 'badgr_user_state';

	/**
	 * Undocumented variable
	 *
	 * @var string
	 */
	public static $meta_key_for_badgr_user_slug = 'badgr_user_slug';
	/**
	 * Undocumented variable
	 *
	 * @var [type]
	 */

	public static $meta_key_for_badgr_password = 'badgr_password';

	protected $wp_user = null;
	/**
	 * Undocumented variable
	 *
	 * @var [type]
	 */
	protected $user_client = null;

	private static $configuration_key_encryption_alogorithm = 'BF2_ENCRYPTION_ALGORITHM';
	private static $configuration_key_encryption_secret_key = 'BF2_SECRET_KEY';
	private static $configuration_key_encryption_secret_iv = 'BF2_SECRET_IV';

	/**
	 * Undocumented function
	 *
	 * @param WP_User $wp_user WordPress user.
	 */
	public function __construct( WP_User $wp_user ) {
		$this->wp_user = $wp_user;
	}

	public static function encrypt_decrypt( $action, $payload) {
		$output = false;

		$encrypt_method = constant( self::$configuration_key_encryption_alogorithm );
		$secret_key = constant( self::$configuration_key_encryption_secret_key );
		$secret_iv = constant( self::$configuration_key_encryption_secret_iv );
	
		// hash
		$key = hash('sha256', $secret_key);
		
		// iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
		$iv = substr(hash('sha256', $secret_iv), 0, 16);
	
		if ( $action == 'encrypt' ) {
			$output = openssl_encrypt($payload, $encrypt_method, $key, 0, $iv);
		} else if( $action == 'decrypt' ) {
			$output = openssl_decrypt($payload, $encrypt_method, $key, 0, $iv);
		}
	
		return $output;
	}

	/**
	 * Undocumented function
	 *
	 * @return null|BadgrUser
	 */
	public static function get_admin_instance() {
		$admin_instance = get_option( self::$options_key_for_badgr_admin );

		if ( false !== $admin_instance && '' !== $admin_instance ) {
			return $admin_instance;
		}

		return null;
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function set_as_admin_instance() {
		update_option( self::$options_key_for_badgr_admin, $this );
	}
	/**
	 * Undocumented function
	 *
	 * @param integer $wp_user_id WordPress user id.
	 * @return BadgrUser
	 */
	public static function make_from_user_id( int $wp_user_id ) {
		return new self( new WP_User( $wp_user_id ) );
	}
	/**
	 * Undocumented function
	 *
	 * @return string
	 */
	public function get_wp_username() {
		return $this->wp_user->user_nicename;
	}
	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	protected function get_client_from_user_meta() {
		$this->user_client = get_user_meta( $this->wp_user->ID, self::$user_meta_key_for_client, true );
	}
	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function save_client() {
		if ( null !== $this->user_client ) {
			update_user_meta( $this->wp_user->ID, self::$user_meta_key_for_client, $this->user_client );
		}
	}

	/**
	 * Undocumented function
	 *
	 * @param BadgrClient $badgr_client Badgr Client to use.
	 * @return void
	 */
	public function set_client( BadgrClient $badgr_client ) {
		$badgr_client->badgr_user = $this;
		$this->user_client        = $badgr_client;
		$this->save_client();
	}
	/**
	 * Undocumented function
	 *
	 * @return null|BadgrClient
	 */
	public function get_client() {

		if ( null !== $this->user_client ) {
			return $this->user_client;
		}

		return self::get_or_make_user_client( $this->wp_user );
	}

	/**
	 * Undocumented function
	 *
	 * @param BadgrUser $other_badgr_user Badgr user to compare.
	 * @return boolean
	 */
	public function is_same_user( BadgrUser $other_badgr_user ) {
		if ( $this->wp_user->ID === $other_badgr_user->wp_user->ID ) {
			return true;
		}

		return false;
	}

	/**
	 * Undocumented function
	 *
	 * @param WP_User $wp_user WordPress user.
	 * @return BadgrClient
	 */
	public static function get_or_make_user_client( WP_User $wp_user = null, $skip_client = false ) {

		// If no user passed, proxy the admin.
		if ( null === $wp_user ) {
			$wp_user = get_user_by( 'ID', 1 );
		}
		// Look in user metas for existing client.
		$client = get_user_meta( $wp_user->ID, self::$user_meta_key_for_client, true );

		if ( null !== $client && '' !== $client && false === $skip_client ) {
			return $client;
		}

		$client_parameters = array(
			'username'   => $wp_user->user_email,
			'badgr_user' => new BadgrUser( $wp_user ),
		);

		// FIXME Bad practice, never use 1 as way to identify admin user.
		if (1 === $wp_user->ID ) {
			$client_parameters[ 'as_admin' ] = true;
		} else {
			$client_parameters[ 'as_admin' ] = false;

			$badgr_password = get_user_meta( $wp_user->ID, self::$meta_key_for_badgr_password, true );

			if ( null !== $badgr_password && '' !== $badgr_password ) {
				$client_parameters['badgr_password'] = $badgr_password;
			}
		}

		$client = BadgrClient::make_instance( $client_parameters );

		return $client;

	}

	/**
	 * BadgrUser Init.
	 *
	 * @return void
	 */
	public static function init_hooks() {
		add_action( 'init', array( self::class, 'init' ), 9966 );
		add_action( 'cmb2_admin_init', array( self::class, 'cmb2_admin_init' ) );
		add_action( BadgrClient::COMPLETE_USER_REGISTRATION_ACTION, array( self::class, 'hook_complete_user_registration' ) );

	}

	/**
	 * Init Hooks.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'user_register', array( self::class, 'new_user_registers' ), 9966 );
		add_action( 'profile_update', array( self::class, 'update_user' ), 9966 );
	}

	/**
	 * Undocumented function
	 *
	 * @param string $username Username.
	 * @return void
	 */
	public static function keep_passwords_synched( $username ) {

		// This hook seems to also be called during logout. Username is then null.

		if ( null === $username ) {
			// It is a logout operation, nothing needs doing for us.
			return;
		}

		if ( ! isset( $_POST['pwd'] ) ) {
			return false;
		}

		$password_from_login = wp_unslash( $_POST['pwd'] );

		// check if password is valid: if not return.
		$auth_result = wp_authenticate( $username, $password_from_login );
		if ( is_wp_error( $auth_result ) ) {
			return;
		}

		// Password is valid.

		// Check if password coresponds to current password.
		$current_password = get_user_meta( $auth_result->ID, 'badgr_password', true );
		if ( $current_password === $auth_result ) {
			// It does, return.
			return;
		}
		// It doesn't, so change password.
		$user_slug = get_user_meta( $auth_result->ID, self::$meta_key_for_badgr_user_slug, true );

		// Perform password change using old and new.
		if ( false !== BadgrProvider::change_user_password( $user_slug, $current_password, $password_from_login ) ) {
			// Record the new one as the old one.
			update_user_meta( $auth_result->ID, 'badgr_password', $password_from_login );

		}

	}

	/**
	 * CMB2 Admin Init hook.
	 *
	 * @return void
	 */
	public static function cmb2_admin_init() {
		// TODO.
	}

	/**
	 * User registration hook.
	 *
	 * @param int $user_id User ID.
	 * @return void
	 */
	public static function new_user_registers( $user_id ) {
		// Don't create a user for Badgr if metas already exist for user.
		$badgr_user_state = get_user_meta( $user_id, self::$meta_key_for_user_state, true );
		if ( 'to_be_created' ==  $badgr_user_state || 'created' == $badgr_user_state ) {
			return;
		}

		// Set badgr user state to 'to_be_created'.
		update_user_meta( $user_id, self::$meta_key_for_user_state, 'to_be_created' );

		// Prepare to add user to badgr.
		$user_data          = get_userdata( $user_id );
		$temporary_password = Text::generate_random_password();

		// Proactively save the generated password
		update_user_meta( $user_id, 'badgr_password', self::encrypt_decrypt( 'encrypt' , $temporary_password ) );

		// Add user
		$slug = BadgrProvider::add_user( $user_data->first_name, $user_data->last_name, $user_data->user_email, $temporary_password );

		// If successful set badgr user state to 'created' and save slug and save previous password.
		if ( false !== $slug ) {
			update_user_meta( $user_id, self::$meta_key_for_badgr_user_slug, $slug );
			update_user_meta( $user_id, self::$meta_key_for_user_state, 'created' );
		}
	}

	/**
	 * Profile update hook.
	 *
	 * @param int $user_id User ID.
	 * @return bool
	 */
	public static function update_user( $user_id ) {
		$result = null;
		$badgr_user_state = get_user_meta( $user_id, self::$meta_key_for_user_state, true );
		if ( null !== $badgr_user_state && 'created' === $badgr_user_state ) {
			$user   = get_userdata( $user_id );
			$result = BadgrProvider::update_user(
				get_user_meta( $user->ID, self::$meta_key_for_badgr_user_slug, true ),
				$user->first_name,
				$user->last_name,
				$user->user_email
			);
		}
		return $result;
	}

	/**
	 * Confirms whether or not current user is verified.
	 *
	 * @return boolean
	 */
	public static function confirm_current_user_verified() {
		// If the user already has the capability, just return.
		if ( current_user_can( 'badgefactor2_use_badgr' ) ) {
			return true;
		}

		// If the user doesn't yet have the capability, check at badgr server.
		$user             = wp_get_current_user();
		$badgr_user_state = get_user_meta( $user->ID, self::$meta_key_for_user_state, true );
		if ( null !== $badgr_user_state && 'created' === $badgr_user_state ) {
			$is_verified = BadgrProvider::check_user_verified( get_user_meta( $user->ID, self::$meta_key_for_badgr_user_slug, true ) );
			if ( true === $is_verified ) {
				$user->add_cap( 'badgefactor2_use_badgr' );
				return true;
			}
		}

		return false;
	}

	public function check_if_user_has_verified_email() {
		$profile = BadgrProvider::get_profile_associated_to_badgr_user( $this );

		if ( false !== $profile && !empty($profile->emails)) {
			foreach ( $profile->emails as $email ) {
				if ( true == $email->verified ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Reset password.
	 *
	 * @param string $user_login User login.
	 * @return void
	 */
	public static function send_to_reset_password( $user_login, $key ) {
		$link = network_site_url( "wp-login.php?action=rp&key=$key&login=" . rawurlencode( $user_login ), 'login' );
		header( 'Location: ' . $link );
		exit();
	}


	/**
	 * Complete user registration.
	 *
	 * @param string $user_email User Email.
	 * @return void
	 */
	public static function hook_complete_user_registration( $user_email ) {
		$user_data = get_user_by( 'email', $user_email );

		if ( false === $user_data ) {
			exit();
		}

		$user_login = $user_data->user_login;
		$key        = get_password_reset_key( $user_data );

		self::send_to_reset_password( $user_login, $key );
	}

}
