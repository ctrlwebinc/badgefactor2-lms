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

use \BadgeFactor2\BadgrUser;
use \BadgeFactor2\BadgrClient;
use \BadgeFactor2\BadgrProvider;

/**
 * Badgr Client Test.
 */
class BadgrUsersTest extends WP_UnitTestCase {

	public function test_user_create_and_change_password() {

		// Setup a completely client and check that we can get the profile info
		$clientParameters = [
			'username' => getenv('BADGR_ADMIN_USERNAME'),
			'as_admin' => true,
			'badgr_server_public_url' => getenv('BADGR_SERVER_PUBLIC_URL'),
			'badgr_server_flavor' => BadgrClient::FLAVOR_LOCAL_R_JAMIROQUAI,
			'badgr_server_internal_url'    => getenv('BADGR_SERVER_INTERNAL_URL'),
			'client_id'     => getenv('BADGR_SERVER_CLIENT_ID'),
			'client_secret' => getenv('BADGR_SERVER_CLIENT_SECRET'),
			'access_token' => getenv('BADGR_SERVER_ACCESS_TOKEN'),
			'refresh_token' => getenv('BADGR_SERVER_REFRESH_TOKEN'),
			'token_expiration' => getenv('BADGR_SERVER_TOKEN_EXPIRATION'),
		];

		$client = BadgrClient::makeInstance( $clientParameters );

		BadgrProvider::setClient( $client );

		// Check that we can retreive information on the authorized user
		// Make GET request to /v2/users/self.
		$response = $client->get( '/v2/users/self' );

		// Check response isn't null.
		$this->assertNotNull($response);

		// Check response has status code 200.
		$this->assertEquals( 200, $response->getStatusCode() );

		$response_info = json_decode( $response->getBody() );

		// Check that entity id exists
		$this->assertTrue( isset( $response_info->result[0]->entityId ) );

		// Setup a random string to avoid data collisions
		$random = $this->generateRandomString(5);

		// Create a user
		$firstname = 'Zeus' . $random;
		$lastname = 'God';
		$email = 'zeus.god.' . $random . '@example.net';
		$password = 'pass456PASS';

		// Make user
		$newUserSlug = BadgrProvider::add_user( $firstname, $lastname, $email, $password);

		$this->assertTrue( false !== $newUserSlug );

		$this->assertTrue( BadgrProvider::change_user_password( $newUserSlug, $password, 'new456PASS' ));
	}

	private function generateRandomString($length = 10) {
	    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	    $charactersLength = strlen($characters);
	    $randomString = 'l';
	    for ($i = 0; $i < $length; $i++) {
	        $randomString .= $characters[rand(0, $charactersLength - 1)];
	    }
	    return $randomString;
	}

    /**
     */
/* 	public function test_issuer_to_assertion_flow() {

		// Setup a completely client and check that we can get the profile info
		$clientParameters = [
			'username' => getenv('BADGR_ADMIN_USERNAME'),
			'as_admin' => true,
			'badgr_server_public_url' => getenv('BADGR_SERVER_PUBLIC_URL'),
			'badgr_server_flavor' => BadgrClient::FLAVOR_LOCAL_R_JAMIROQUAI,
			'badgr_server_internal_url'    => getenv('BADGR_SERVER_INTERNAL_URL'),
			'client_id'     => getenv('BADGR_SERVER_CLIENT_ID'),
			'client_secret' => getenv('BADGR_SERVER_CLIENT_SECRET'),
			'access_token' => getenv('BADGR_SERVER_ACCESS_TOKEN'),
			'refresh_token' => getenv('BADGR_SERVER_REFRESH_TOKEN'),
			'token_expiration' => getenv('BADGR_SERVER_TOKEN_EXPIRATION'),
		];

		$client = BadgrClient::makeInstance( $clientParameters );

		BadgrProvider::setClient( $client );

		// Check that we can retreive information on the authorized user
		// Make GET request to /v2/users/self.
		$response = $client->get( '/v2/users/self' );

		// Check response isn't null.
		$this->assertNotNull($response);

		// Check response has status code 200.
		$this->assertEquals( 200, $response->getStatusCode() );

		$response_info = json_decode( $response->getBody() );

		// Check that entity id exists
		$this->assertTrue( isset( $response_info->result[0]->entityId ) );

		// Setup a random string to avoid data collisions
		$random = $this->generateRandomString(5);

		// Create issuer
		$issuer_slug = BadgrProvider::add_issuer( 'TestIssuer' . $random, 'issuer' . $random . '@example.net' , 'http://' . $random . 'example.net', 'A Description for ' . $random );

		$this->assertTrue( false !== $issuer_slug );
		$this->assertNotEmpty( $issuer_slug);

		// Add a badge class
		$badge_class_slug = BadgrProvider::add_badge_class( 'BadgeClass' . $random, $issuer_slug, 'Description for ' . $random, dirname(__FILE__).'/resources/test_badge_image.svg' );

		$this->assertTrue( false !== $badge_class_slug );
		$this->assertNotEmpty( $badge_class_slug);

		// Issue a badge
		$assertion_slug = BadgrProvider::add_assertion( $issuer_slug, $badge_class_slug, 'recipient' . $random . '@example.net');

		$this->assertTrue( false !== $assertion_slug );
		$this->assertNotEmpty( $assertion_slug);

	} */
    /**
     * @backupStaticAttributes enabled
	 * @runInSeparateProcess
     */
/* 	public function test_issuer_to_new_user_assertion_flow() {

		// Setup a completely client and check that we can get the profile info
		$clientParameters = [
			'username' => getenv('BADGR_ADMIN_USERNAME'),
			'as_admin' => true,
			'badgr_server_public_url' => getenv('BADGR_SERVER_PUBLIC_URL'),
			'badgr_server_flavor' => BadgrClient::FLAVOR_LOCAL_R_JAMIROQUAI,
			'badgr_server_internal_url'    => getenv('BADGR_SERVER_INTERNAL_URL'),
			'client_id'     => getenv('BADGR_SERVER_CLIENT_ID'),
			'client_secret' => getenv('BADGR_SERVER_CLIENT_SECRET'),
			'access_token' => getenv('BADGR_SERVER_ACCESS_TOKEN'),
			'refresh_token' => getenv('BADGR_SERVER_REFRESH_TOKEN'),
			'token_expiration' => getenv('BADGR_SERVER_TOKEN_EXPIRATION'),
		];

		$client = BadgrClient::makeInstance( $clientParameters );

		BadgrProvider::setClient( $client );

		// Check that we can retreive information on the authorized user
		// Make GET request to /v2/users/self.
		$response = $client->get( '/v2/users/self' );

		// Check response isn't null.
		$this->assertNotNull($response);

		// Check response has status code 200.
		$this->assertEquals( 200, $response->getStatusCode() );

		$response_info = json_decode( $response->getBody() );

		// Check that entity id exists
		$this->assertTrue( isset( $response_info->result[0]->entityId ) );

		// Setup a random string to avoid data collisions
		$random = $this->generateRandomString(5);

		// Create a user
		$firstname = 'Zeus' . $random;
		$lastname = 'God';
		$email = 'zeus.god.' . $random . '@example.net';
		$password = 'pass456PASS';

		$request_body = array(
			'first_name'           => $firstname,
			'last_name'            => $lastname,
			'email'                => $email,
			'url'                  => '',
			'telephone'            => '',
			'slug'                 => '',
			'agreed_terms_version' => 1,
			'marketing_opt_in'     => false,
			'has_password_set'     => false,
			'source'               => 'bf2',
			'password'             => $password,
		);

		// Make POST request to /v1/user/profile.
		$response = $client->post( '/v1/user/profile', $request_body );

		$newUserSlug = null;

		// Check for 201 response.
		if ( null !== $response && $response->getStatusCode() == 201 ) {
			// Return slug-entity_id or false if unsucessful.
			$response_info = json_decode( $response->getBody() );
			if ( isset( $response_info->slug ) && strlen( $response_info->slug ) > 0 ) {
				$newUserSlug =  $response_info->slug;
			}
		}

		// Create issuer
		$issuer_slug = BadgrProvider::add_issuer( 'TestIssuer' . $random, 'issuer' . $random . '@example.net' , 'http://' . $random . 'example.net', 'A Description for ' . $random );

		$this->assertTrue( false !== $issuer_slug );
		$this->assertNotEmpty( $issuer_slug);

		// Add a badge class
		$badge_class_slug = BadgrProvider::add_badge_class( 'BadgeClass' . $random, $issuer_slug, 'Description for ' . $random, dirname(__FILE__).'/resources/test_badge_image.svg' );

		$this->assertTrue( false !== $badge_class_slug );
		$this->assertNotEmpty( $badge_class_slug);

		// Issue a badge
		$assertion_slug = BadgrProvider::add_assertion( $issuer_slug, $badge_class_slug, $email);

		$this->assertTrue( false !== $assertion_slug );
		$this->assertNotEmpty( $assertion_slug);

	} */

	public function test_can_set_admin_instance( ) {
		$badgr_admin_user = BadgrUser::make_from_user_id(1);

		$this->assertNotNull( $badgr_admin_user );

		$badgr_admin_user->set_as_admin_instance( );

		$admin_instance = BadgrUser::get_admin_instance();

		$this->assertNotNull( $admin_instance );

		$this->assertTrue( $badgr_admin_user->is_same_user( $admin_instance ));
	} 

	public function test_client_reports_active_when_admin_client_is_ready() {
		// Clear any previous option
		update_option( BadgrUser::$options_key_for_badgr_admin, null);
		update_user_meta( 1, BadgrUser::$user_meta_key_for_client, null);

		$this->assertFalse( BadgrClient::is_active( ) );

		// Setup user. Start without a client.
		$badgr_admin_user = BadgrUser::make_from_user_id(1);
		$badgr_admin_user->set_as_admin_instance( );
		
		$this->assertNull( $badgr_admin_user->get_client( ) );

		$this->assertFalse( BadgrClient::is_active( ) );

		// Attach a non-admin client
		$basicParameters = [
			'username' => 'dave@example.net',
			'as_admin' => false,
			'badgr_server_public_url' => 'http://127.0.0.1:8000',
			'badgr_server_flavor' => BadgrClient::FLAVOR_LOCAL_R_JAMIROQUAI,
		];

		$client = BadgrClient::makeInstance( $basicParameters );

		$badgr_admin_user = BadgrUser::make_from_user_id(1);
		$badgr_admin_user->set_client( $client );
		$badgr_admin_user->set_as_admin_instance( );


		$this->assertFalse( BadgrClient::is_active( ) );

		// Attach an admin client without token
		$basicParameters = [
			'username' => 'dave@example.net',
			'as_admin' => true,
			'badgr_server_public_url' => 'http://127.0.0.1:8000',
			'badgr_server_flavor' => BadgrClient::FLAVOR_LOCAL_R_JAMIROQUAI,
		];

		$client = BadgrClient::makeInstance( $basicParameters );

		$badgr_admin_user = BadgrUser::make_from_user_id(1);
		$badgr_admin_user->set_client( $client );
		$badgr_admin_user->set_as_admin_instance( );

		$this->assertFalse( BadgrClient::is_active( ) );

		// Attach an admin client with token and check for active status
		$activeClientParameters = [
			'username' => getenv('BADGR_ADMIN_USERNAME'),
			'as_admin' => true,
			'badgr_server_public_url' => getenv('BADGR_SERVER_PUBLIC_URL'),
			'badgr_server_flavor' => BadgrClient::FLAVOR_LOCAL_R_JAMIROQUAI,
			'badgr_server_internal_url'    => getenv('BADGR_SERVER_INTERNAL_URL'),
			'client_id'     => getenv('BADGR_SERVER_CLIENT_ID'),
			'client_secret' => getenv('BADGR_SERVER_CLIENT_SECRET'),
			'access_token' => getenv('BADGR_SERVER_ACCESS_TOKEN'),
			'refresh_token' => getenv('BADGR_SERVER_REFRESH_TOKEN'),
			'token_expiration' => getenv('BADGR_SERVER_TOKEN_EXPIRATION'),
		];

		$client = BadgrClient::makeInstance( $activeClientParameters );

		$badgr_admin_user = BadgrUser::make_from_user_id(1);
		$badgr_admin_user->set_client( $client );
		$badgr_admin_user->set_as_admin_instance( );

		$this->assertEquals( BadgrClient::STATE_HAVE_ACCESS_TOKEN, $client->get_state() );

		$this->assertTrue( BadgrClient::is_active( ) );
	}
}
